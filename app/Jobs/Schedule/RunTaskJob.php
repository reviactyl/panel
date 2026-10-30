<?php

namespace App\Jobs\Schedule;

use App\Jobs\Job;
use App\Models\Schedule;
use App\Models\Task;
use App\Repositories\Agent\DaemonCommandRepository;
use App\Repositories\Agent\DaemonPowerRepository;
use App\Services\Backups\InitiateBackupService;
use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunTaskJob extends Job implements ShouldQueue
{
    use DispatchesJobs;
    use InteractsWithQueue;
    use SerializesModels {
        __unserialize as private restoreProperties;
    }

    private ?Task $task = null;

    public int $taskId;

    private ?int $scheduleId = null;

    /**
     * RunTaskJob constructor.
     */
    public function __construct(Task $task, public bool $manualRun = false, private ?string $processingToken = null)
    {
        $this->queue = 'standard';
        $this->taskId = $task->id;
        $this->scheduleId = $task->schedule_id;
    }

    public function __unserialize(array $values): void
    {
        if (isset($values['task']) && ! isset($values['taskId'])) {
            $values['taskId'] = $values['task']->id;
            unset($values['task']);
            $task = Task::query()->find($values['taskId']);
            $this->scheduleId = $task?->schedule_id;
            $this->processingToken = $task?->schedule->processing_token;
        }

        $this->restoreProperties($values);
    }

    /**
     * Run the job and send actions to the daemon running the server.
     *
     * @throws \Throwable
     */
    public function handle(
        DaemonCommandRepository $commandRepository,
        InitiateBackupService $backupService,
        DaemonPowerRepository $powerRepository,
    ) {
        $this->task = (new Task())->getConnection()->transaction(function () {
            Schedule::query()->whereKey($this->scheduleId)->lockForUpdate()->first();
            $task = Task::query()->find($this->taskId);
            $task?->update(['is_queued' => false]);

            return $task;
        });
        if (is_null($this->task)) {
            $this->markScheduleComplete();

            return;
        }

        // Do not process a task that is not set to active, unless it's been manually triggered.
        if (! $this->task->schedule->is_active && ! $this->manualRun) {
            $this->markTaskNotQueued();
            $this->markScheduleComplete();

            return;
        }

        $server = $this->task->server;
        // If we made it to this point and the server status is not null it means the
        // server was likely suspended or marked as reinstalling after the schedule
        // was queued up. Just end the task right now — this should be a very rare
        // condition.
        if (! is_null($server->status)) {
            $this->failed();

            return;
        }

        // Perform the provided task against the daemon.
        try {
            switch ($this->task->action) {
                case Task::ACTION_POWER:
                    $powerRepository->setServer($server)->send($this->task->payload);
                    break;
                case Task::ACTION_COMMAND:
                    $commandRepository->setServer($server)->send($this->task->payload);
                    break;
                case Task::ACTION_BACKUP:
                    $backupService->setIgnoredFiles(explode(PHP_EOL, $this->task->payload))->handle($server, null, true);
                    break;
                default:
                    throw new \InvalidArgumentException('Invalid task action provided: '.$this->task->action);
            }
        } catch (Exception $exception) {
            if (! $this->task->continue_on_failure) {
                throw $exception;
            }
        }

        $this->markTaskNotQueued();
        $this->queueNextTask();
    }

    /**
     * Handle a failure while sending the action to the daemon or otherwise processing the job.
     */
    public function failed(?Exception $exception = null)
    {
        $this->markTaskNotQueued();
        $this->markScheduleComplete();
    }

    /**
     * Get the next task in the schedule and queue it for running after the defined period of wait time.
     */
    private function queueNextTask()
    {
        /** @var Task|null $nextTask */
        $nextTask = Task::query()->where('schedule_id', $this->task->schedule_id)
            ->orderBy('sequence_id', 'asc')
            ->where('sequence_id', '>', $this->task->sequence_id)
            ->first();

        if (is_null($nextTask)) {
            $this->markScheduleComplete();

            return;
        }

        $nextTask->update(['is_queued' => true]);

        $this->dispatch((new self($nextTask, $this->manualRun, $this->processingToken))->delay($nextTask->time_offset));
    }

    /**
     * Marks the parent schedule as being complete.
     */
    private function markScheduleComplete()
    {
        Schedule::query()->whereKey($this->scheduleId)
            ->where('processing_token', $this->processingToken)
            ->update([
                'is_processing' => false,
                'last_run_at' => CarbonImmutable::now()->toDateTimeString(),
            ]);
    }

    /**
     * Mark a specific task as no longer being queued.
     */
    private function markTaskNotQueued()
    {
        Task::query()->whereKey($this->taskId)->update(['is_queued' => false]);
    }
}
