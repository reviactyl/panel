<?php

namespace Tests\Integration\Jobs\Backups;

use App\Exceptions\Http\Connection\DaemonConnectionException;
use App\Jobs\Backups\RotateBackupsJob;
use App\Models\Backup;
use App\Repositories\Agent\DaemonBackupRepository;
use App\Services\Backups\RotateBackupsService;
use Carbon\CarbonImmutable;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Tests\Integration\IntegrationTestCase;

class RotateBackupsJobTest extends IntegrationTestCase
{
    public function test_deletion_failure_can_be_retried_and_then_completes_rotation(): void
    {
        $server = $this->createServerModel(['backup_limit' => 1]);
        $existing = Backup::factory()->for($server)->create(['created_at' => CarbonImmutable::now()->subHour()]);
        $replacement = Backup::factory()->for($server)->create();
        $attempts = 0;

        $repository = $this->mock(DaemonBackupRepository::class);
        $repository->allows('setServer')->andReturnSelf();
        $repository->expects('delete')->twice()->andReturnUsing(function () use (&$attempts) {
            if (++$attempts === 1) {
                throw new DaemonConnectionException(
                    new BadResponseException('Deletion failed', new Request('DELETE', '/backup'), new Response(500))
                );
            }

            return new Response();
        });

        $job = new RotateBackupsJob($server->id, $replacement->id);

        try {
            $job->handle($this->app->make(RotateBackupsService::class));
        } catch (DaemonConnectionException) {
            // The queue will retry this failed attempt.
        }

        $this->assertSame(3, $job->tries);
        $this->assertNotSoftDeleted($existing);

        $job->handle($this->app->make(RotateBackupsService::class));

        $this->assertSame(2, $attempts);
        $this->assertSoftDeleted($existing);
        $this->assertNotSoftDeleted($replacement);
    }

    public function test_backup_locked_before_cleanup_is_preserved(): void
    {
        $server = $this->createServerModel(['backup_limit' => 1]);
        $existing = Backup::factory()->for($server)->create([
            'created_at' => CarbonImmutable::now()->subHour(),
            'is_locked' => true,
        ]);
        $replacement = Backup::factory()->for($server)->create();

        $repository = $this->mock(DaemonBackupRepository::class);
        $repository->allows('setServer')->andReturnSelf();
        $repository->expects('delete')->with(\Mockery::on(function ($backup) use ($replacement) {
            return $backup instanceof Backup && $backup->id === $replacement->id;
        }))->andReturn(new Response());

        (new RotateBackupsJob($server->id, $replacement->id))
            ->handle($this->app->make(RotateBackupsService::class));

        $this->assertNotSoftDeleted($existing);
        $this->assertSoftDeleted($replacement);
    }

    public function test_cleanup_is_limited_to_three_failed_attempts(): void
    {
        $server = $this->createServerModel(['backup_limit' => 1]);
        $existing = Backup::factory()->for($server)->create(['created_at' => CarbonImmutable::now()->subHour()]);
        $replacement = Backup::factory()->for($server)->create();
        $attempts = 0;

        $repository = $this->mock(DaemonBackupRepository::class);
        $repository->allows('setServer')->andReturnSelf();
        $repository->expects('delete')->times(3)->andReturnUsing(function () use (&$attempts) {
            $attempts++;

            throw new DaemonConnectionException(
                new BadResponseException('Deletion failed', new Request('DELETE', '/backup'), new Response(500))
            );
        });

        $job = new RotateBackupsJob($server->id, $replacement->id);
        for ($attempt = 0; $attempt < $job->tries; $attempt++) {
            try {
                $job->handle($this->app->make(RotateBackupsService::class));
            } catch (DaemonConnectionException) {
                // Laravel marks the job as failed after the third exception.
            }
        }

        $this->assertSame(3, $job->tries);
        $this->assertSame([30, 120], $job->backoff());
        $this->assertSame(3, $attempts);
        $this->assertNotSoftDeleted($existing);
        $this->assertNotSoftDeleted($replacement);
    }
}
