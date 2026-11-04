<?php

namespace Tests\Integration\Api\Client\Server\ScheduleTask;

use App\Jobs\Schedule\RunTaskJob;
use App\Models\Schedule;
use App\Repositories\Agent\DaemonCommandRepository;
use GuzzleHttp\Psr7\Response;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Tests\Integration\Api\Client\ClientApiIntegrationTestCase;

class ConcurrentScheduleTaskTest extends ClientApiIntegrationTestCase
{
    public function test_concurrent_appends_execute_both_commands_once(): void
    {
        if (! function_exists('pcntl_fork') || ! in_array(DB::getDriverName(), ['mysql', 'mariadb', 'pgsql'])) {
            $this->markTestSkipped('Requires pcntl and a database with row locks.');
        }

        [$user, $server] = $this->generateTestAccount();
        $schedule = Schedule::factory()->create(['server_id' => $server->id, 'is_active' => true]);
        $url = $this->link($schedule, '/tasks');
        DB::disconnect();
        $workers = [];

        try {
            foreach (['say first', 'say second'] as $command) {
                $sockets = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, 0);
                $pid = pcntl_fork();
                if ($pid === -1) {
                    throw new \RuntimeException('Could not fork request worker.');
                }
                if ($pid === 0) {
                    fclose($sockets[0]);
                    $socket = $sockets[1];
                    stream_set_timeout($socket, 15);
                    DB::purge();
                    $ready = false;
                    $barrier = function (string $stage) use ($socket, &$ready) {
                        if (! $ready) {
                            $ready = true;
                            fwrite($socket, $stage."\n");
                            if (fgets($socket) !== "go\n") {
                                throw new \RuntimeException('Request barrier timed out.');
                            }
                        }
                    };
                    // Fixed requests meet before taking the parent lock; the old code meets after reading the stale tail.
                    DB::connection()->beforeExecuting(function (string $sql) use ($barrier) {
                        if (str_contains($sql, 'schedules') && str_contains(strtolower($sql), 'for update')) {
                            $barrier('lock');
                        }
                    });
                    DB::listen(function (QueryExecuted $query) use ($barrier) {
                        if (str_contains($query->sql, 'tasks') && str_contains($query->sql, 'sequence_id') && str_contains(strtolower($query->sql), 'limit 1')) {
                            $barrier('tail');
                        }
                    });
                    try {
                        $response = $this->actingAs($user)->postJson($url, [
                            'action' => 'command', 'payload' => $command, 'time_offset' => 0,
                        ]);
                        fwrite($socket, $response->status()."\n");
                        exit($response->status() === 200 ? 0 : 1);
                    } catch (\Throwable $exception) {
                        fwrite($socket, $exception->getMessage()."\n");
                        exit(1);
                    }
                }
                fclose($sockets[1]);
                stream_set_timeout($sockets[0], 20);
                $workers[] = [$pid, $sockets[0]];
            }
            $stages = [];
            foreach ($workers as [, $socket]) {
                $stage = fgets($socket);
                $this->assertContains($stage, ["lock\n", "tail\n"]);
                $stages[] = $stage;
            }
            if ($stages === ["lock\n", "lock\n"]) {
                foreach ($workers as [, $socket]) {
                    fwrite($socket, "go\n");
                }
            }
            foreach ($workers as [$pid, $socket]) {
                if ($stages !== ["lock\n", "lock\n"]) {
                    fwrite($socket, "go\n");
                }
                $this->assertSame("200\n", fgets($socket));
                pcntl_waitpid($pid, $status);
                $this->assertSame(0, pcntl_wexitstatus($status));
            }
        } finally {
            foreach ($workers as [$pid, $socket]) {
                if (pcntl_waitpid($pid, $status, WNOHANG) === 0) {
                    posix_kill($pid, SIGTERM);
                    pcntl_waitpid($pid, $status);
                }
                fclose($socket);
            }
            DB::purge();
        }

        $tasks = $schedule->tasks()->orderBy('sequence_id')->get();
        $this->assertSame([1, 2], $tasks->pluck('sequence_id')->all());
        $commands = [];
        $this->mock(DaemonCommandRepository::class, function ($mock) use (&$commands) {
            $mock->shouldReceive('setServer')->twice()->andReturnSelf();
            $mock->shouldReceive('send')->twice()->andReturnUsing(function ($command) use (&$commands) {
                $commands[] = $command;

                return new Response();
            });
        });
        $schedule->update(['is_processing' => true]);
        Bus::dispatchSync(new RunTaskJob($tasks->first(), true));
        $this->assertSame($tasks->pluck('payload')->all(), $commands);
        $this->assertFalse($schedule->refresh()->is_processing);
    }
}
