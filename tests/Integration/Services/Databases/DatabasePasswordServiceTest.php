<?php

namespace Tests\Integration\Services\Databases;

use App\Models\Database;
use App\Models\DatabaseHost;
use App\Repositories\Eloquent\DatabaseRepository;
use App\Services\Databases\DatabasePasswordService;
use Mockery\MockInterface;
use Tests\Integration\IntegrationTestCase;

class DatabasePasswordServiceTest extends IntegrationTestCase
{
    private MockInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->mock(DatabaseRepository::class);
    }

    /**
     * Test that rotating a password alters the existing account instead of dropping and recreating it.
     */
    public function test_password_is_rotated_without_dropping_the_account()
    {
        $database = $this->createDatabase();

        $this->repository->expects('withoutFreshModel')->andReturnSelf();
        $this->repository->expects('update')->with($database->id, \Mockery::any());
        $this->repository->shouldNotReceive('dropUser');
        $this->repository->shouldNotReceive('createUser');

        $password = null;
        $this->repository->expects('updateUserPassword')->with(
            $database->username,
            $database->remote,
            \Mockery::on(function ($value) use (&$password) {
                $password = $value;

                return true;
            })
        );
        $this->repository->expects('flush');

        $result = $this->app->make(DatabasePasswordService::class)->handle($database);

        $this->assertSame($password, $result);
        $this->assertSame(24, strlen($result));
    }

    /**
     * Test that a failure while changing the password keeps the previous password stored in the panel.
     */
    public function test_failed_rotation_keeps_the_stored_password()
    {
        $database = $this->createDatabase();
        $original = $database->getRawOriginal('password');

        $this->repository->expects('withoutFreshModel')->andReturnSelf();
        $this->repository->expects('update')->andReturnUsing(function ($id, $data) {
            Database::query()->where('id', $id)->update($data);

            return true;
        });
        $this->repository->shouldNotReceive('dropUser');
        $this->repository->expects('updateUserPassword')->andThrows(new \RuntimeException());

        try {
            $this->app->make(DatabasePasswordService::class)->handle($database);
            $this->fail('Expected the rotation to fail.');
        } catch (\RuntimeException) {
        }

        $this->assertSame($original, Database::query()->find($database->id)->getRawOriginal('password'));
    }

    private function createDatabase(): Database
    {
        $server = $this->createServerModel();
        $host = DatabaseHost::factory()->create(['node_id' => $server->node_id]);

        return Database::factory()->create(['server_id' => $server->id, 'database_host_id' => $host->id]);
    }
}
