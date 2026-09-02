<?php

namespace Tests\Integration\Api\Client\Server\Backup;

use App\Models\Backup;
use App\Models\Permission;
use App\Repositories\Agent\DaemonBackupRepository;
use Carbon\CarbonImmutable;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Illuminate\Http\Response;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Integration\Api\Client\ClientApiIntegrationTestCase;

class RestoreBackupTest extends ClientApiIntegrationTestCase
{
    private MockInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->mock(DaemonBackupRepository::class);
    }

    public function test_backup_can_be_restored()
    {
        [$user, $server] = $this->generateTestAccount([Permission::ACTION_BACKUP_RESTORE]);

        /** @var Backup $backup */
        $backup = Backup::factory()->create(['server_id' => $server->id]);

        $this->repository->expects('setServer->restore')->with(
            \Mockery::on(function ($value) use ($backup) {
                return $value instanceof Backup && $value->uuid === $backup->uuid;
            }),
            null,
            true,
        )->andReturn(new GuzzleResponse());

        $this->actingAs($user)->postJson($this->link($backup, 'restore'), ['truncate' => true])
            ->assertStatus(Response::HTTP_NO_CONTENT);
    }

    #[DataProvider('invalid_backup_data_provider')]
    public function test_backup_cannot_be_restored_until_successful_and_complete(bool $isSuccessful, bool $isCompleted)
    {
        [$user, $server] = $this->generateTestAccount([Permission::ACTION_BACKUP_RESTORE]);

        /** @var Backup $backup */
        $backup = Backup::factory()->create([
            'server_id' => $server->id,
            'is_successful' => $isSuccessful,
            'completed_at' => $isCompleted ? CarbonImmutable::now() : null,
        ]);

        $this->repository->shouldNotReceive('setServer');

        $this->actingAs($user)->postJson($this->link($backup, 'restore'), ['truncate' => true])
            ->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    public static function invalid_backup_data_provider(): array
    {
        return [
            'failed completed' => [false, true],
            'failed incomplete' => [false, false],
            'successful incomplete' => [true, false],
        ];
    }
}
