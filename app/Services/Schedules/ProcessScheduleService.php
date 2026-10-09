<?php

namespace App\Services\Schedules;

use App\Exceptions\DisplayException;
use App\Jobs\Schedule\RunTaskJob;
use App\Models\Schedule;
use App\Repositories\Agent\DaemonServerRepository;
use Exception;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Str;
use Throwable;

class ProcessScheduleService
{
    /**
     * ProcessScheduleService constructor.
     */
    public function __construct(private ConnectionInterface $connection, private Dispatcher $dispatcher, private DaemonServerRepository $serverRepository) {}

    /**
     * Process a schedule and push the first task onto the queue worker.
     *
     * @throws Throwable
     */
    public function handle(Schedule $schedule, bool $now = false): bool
    {
        $task = $schedule->tasks()->orderBy('sequence_id')->first();
        if (is_null($task)) {
            throw new DisplayException('Cannot process schedule for task execution: no tasks are registered.');
        }

        $shouldRun = true;
        if ($schedule->only_when_online) {
            try {
                $details = $this->serverRepository->setServer($schedule->server)->getDetails();
                $shouldRun = ! in_array($details['state'] ?? 'offline', ['offline', 'stopping']);
            } catch (Exception) {
                $shouldRun = false;
            }
        }

        $job = new RunTaskJob($task, $now);
        $nextRunAt = $schedule->next_run_at;
        $processingToken = (string) Str::uuid();

        $ready = $this->connection->transaction(function () use ($schedule, $task, $job, $shouldRun, $processingToken) {
            $schedule->setRelation('server', $schedule->server()->sharedLock()->firstOrFail());

            $schedule->forceFill([
                'is_processing' => true,
                'processing_token' => $processingToken,
                'next_run_at' => $schedule->getNextRunDate(),
            ])->saveOrFail();

            $task->update(['is_queued' => true]);
            if (! $shouldRun) {
                $job->failed();

                return false;
            }

            return true;
        });

        if (! $ready) {
            return false;
        }

        if (! $now) {
            try {
                $this->dispatcher->dispatch($job->delay($task->time_offset));
            } catch (Throwable $exception) {
                $this->connection->transaction(function () use ($schedule, $task, $nextRunAt, $processingToken) {
                    // Only recover this execution; a newer run or a completed sync job owns its state.
                    $reset = $schedule->newQuery()->whereKey($schedule->id)
                        ->where('is_processing', true)
                        ->where('processing_token', $processingToken)
                        ->update(['is_processing' => false, 'next_run_at' => $nextRunAt]);

                    if ($reset) {
                        $task->newQuery()->whereKey($task->id)->update(['is_queued' => false]);
                        $schedule->refresh();
                    }
                });

                throw $exception;
            }
        } else {
            // When using dispatchNow the RunTaskJob::failed() function is not called automatically
            // so we need to manually trigger it and then continue with the exception throw.
            //
            // @see https://github.com/pterodactyl/panel/issues/2550
            try {
                $this->dispatcher->dispatchNow($job);
            } catch (Exception $exception) {
                $job->failed($exception);

                throw $exception;
            }
        }

        return true;
    }
}
