<?php

namespace Tests\Integration\Api\Client\Server\Database;

use App\Contracts\Extensions\HashidsInterface;
use App\Exceptions\Service\Database\DatabaseImportException;
use App\Jobs\Databases\ImportDatabaseJob;
use App\Models\Database;
use App\Models\DatabaseHost;
use App\Models\Permission;
use App\Models\Server;
use App\Services\Databases\DatabaseExportService;
use App\Services\Databases\DatabaseImportService;
use App\Services\Databases\DatabaseImportStatusService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Integration\Api\Client\ClientApiIntegrationTestCase;

class DatabaseTransferTest extends ClientApiIntegrationTestCase
{
    public function test_uploaded_file_is_queued_for_import()
    {
        Queue::fake();
        Storage::fake('local');

        [$user, $server] = $this->generateTestAccount();
        $database = $this->createDatabase($server);

        $this->actingAs($user)
            ->post($this->databaseLink($server, $database, '/import'), [
                'wipe' => '1',
                'file' => UploadedFile::fake()->createWithContent('dump.sql', 'SELECT 1;'),
            ], ['Accept' => 'application/json'])
            ->assertStatus(202)
            ->assertJsonPath('object', 'database_import')
            ->assertJsonPath('attributes.state', DatabaseImportStatusService::STATE_RUNNING);

        Queue::assertPushed(ImportDatabaseJob::class, function (ImportDatabaseJob $job) use ($database) {
            return $job->database === $database->id
                && $job->wipe
                && is_null($job->remote)
                && Storage::disk('local')->get($job->file) === 'SELECT 1;';
        });

        $this->assertActivityLogged('server:database.import');
    }

    #[DataProvider('fileNameDataProvider')]
    public function test_sql_files_can_be_uploaded_compressed(string $name)
    {
        Queue::fake();
        Storage::fake('local');

        [$user, $server] = $this->generateTestAccount();
        $database = $this->createDatabase($server);

        $this->actingAs($user)
            ->post($this->databaseLink($server, $database, '/import'), [
                'file' => UploadedFile::fake()->createWithContent($name, 'SELECT 1;'),
            ], ['Accept' => 'application/json'])
            ->assertStatus(202);

        Queue::assertPushed(ImportDatabaseJob::class);
    }

    public static function fileNameDataProvider(): array
    {
        return [['dump.sql'], ['dump.sql.gz'], ['dump.SQL.ZIP']];
    }

    public function test_remote_database_is_queued_for_import()
    {
        Queue::fake();

        [$user, $server] = $this->generateTestAccount();
        $database = $this->createDatabase($server);

        $this->actingAs($user)
            ->postJson($this->databaseLink($server, $database, '/import'), [
                'remote_host' => 'db.example.com',
                'remote_port' => 3307,
                'remote_database' => 'source',
                'remote_username' => 'user',
                'remote_password' => 'secret',
            ])
            ->assertStatus(202);

        Queue::assertPushed(ImportDatabaseJob::class, function (ImportDatabaseJob $job) {
            return ! $job->wipe && is_null($job->file) && $job->remote === [
                'host' => 'db.example.com',
                'port' => 3307,
                'database' => 'source',
                'username' => 'user',
                'password' => 'secret',
            ];
        });
    }

    public function test_database_cannot_be_changed_while_an_import_is_running()
    {
        Queue::fake();
        Storage::fake('local');

        [$user, $server] = $this->generateTestAccount();
        $database = $this->createDatabase($server);

        $this->app->make(DatabaseImportStatusService::class)->start($database);

        $this->actingAs($user)
            ->post($this->databaseLink($server, $database, '/import'), [
                'file' => UploadedFile::fake()->createWithContent('dump.sql', 'SELECT 1;'),
            ], ['Accept' => 'application/json'])
            ->assertStatus(409);

        $this->actingAs($user)
            ->getJson($this->databaseLink($server, $database, '/export'))
            ->assertStatus(409);

        $this->actingAs($user)
            ->deleteJson($this->databaseLink($server, $database, ''))
            ->assertStatus(409);

        $this->actingAs($user)
            ->postJson($this->databaseLink($server, $database, '/rotate-password'))
            ->assertStatus(409);

        Queue::assertNotPushed(ImportDatabaseJob::class);
    }

    public function test_import_source_is_validated()
    {
        Queue::fake();
        Storage::fake('local');

        [$user, $server] = $this->generateTestAccount();
        $database = $this->createDatabase($server);
        $link = $this->databaseLink($server, $database, '/import');

        $this->actingAs($user)->postJson($link, ['wipe' => true])->assertUnprocessable();

        foreach (['dump.txt', 'backup.gz', 'backup.tar.gz', 'archive.zip'] as $name) {
            $this->actingAs($user)
                ->post($link, ['file' => UploadedFile::fake()->createWithContent($name, 'SELECT 1;')], ['Accept' => 'application/json'])
                ->assertUnprocessable();
        }

        $this->actingAs($user)
            ->post($link, [
                'file' => UploadedFile::fake()->createWithContent('dump.sql', 'SELECT 1;'),
                'remote_host' => 'db.example.com',
                'remote_port' => 3306,
                'remote_database' => 'source',
                'remote_username' => 'user',
            ], ['Accept' => 'application/json'])
            ->assertUnprocessable();

        $this->actingAs($user)
            ->postJson($link, ['remote_host' => 'db.example.com', 'remote_port' => 70000, 'remote_database' => 'a;host=b', 'remote_username' => 'user'])
            ->assertUnprocessable();

        Queue::assertNotPushed(ImportDatabaseJob::class);

        $this->assertNull($this->app->make(DatabaseImportStatusService::class)->get($database));
    }

    public function test_import_status_and_database_list_report_the_last_import()
    {
        [$user, $server] = $this->generateTestAccount();
        $database = $this->createDatabase($server);

        $this->actingAs($user)
            ->getJson($this->databaseLink($server, $database, '/import'))
            ->assertOk()
            ->assertJsonPath('attributes', null);

        $status = $this->app->make(DatabaseImportStatusService::class);
        $status->start($database);
        $status->fail($database, DatabaseImportException::STATEMENT_FAILED, '#2: Table does not exist');

        $this->actingAs($user)
            ->getJson($this->databaseLink($server, $database, '/import'))
            ->assertOk()
            ->assertJsonPath('attributes.state', DatabaseImportStatusService::STATE_FAILED)
            ->assertJsonPath('attributes.error', DatabaseImportException::STATEMENT_FAILED)
            ->assertJsonPath('attributes.detail', '#2: Table does not exist');

        $this->actingAs($user)
            ->getJson($this->link($server, '/databases'))
            ->assertOk()
            ->assertJsonPath('data.0.attributes.import.state', DatabaseImportStatusService::STATE_FAILED);

        $this->assertNotNull($status->start($database));
    }

    public function test_database_is_exported_as_a_download()
    {
        [$user, $server] = $this->generateTestAccount();
        $database = $this->createDatabase($server);

        $this->mock(DatabaseExportService::class)
            ->expects('handle')
            ->withArgs(fn (Database $model, ?string $compress) => $model->is($database) && $compress === 'zip')
            ->andReturn(function () {
                echo 'dump';
            });

        $response = $this->actingAs($user)->get($this->databaseLink($server, $database, '/export?compress=zip'));

        $response->assertOk()->assertHeader('Content-Type', 'application/zip');
        $this->assertMatchesRegularExpression(
            '/^attachment; filename='.preg_quote($database->database, '/').'_[\d\-_]+\.sql\.zip$/',
            $response->headers->get('Content-Disposition')
        );
        $this->assertSame('dump', $response->streamedContent());

        $this->assertActivityLogged('server:database.export');

        $this->actingAs($user)->getJson($this->databaseLink($server, $database, '/export?compress=rar'))->assertUnprocessable();
    }

    public function test_import_and_export_require_their_own_permissions()
    {
        Queue::fake();

        [$user, $server] = $this->generateTestAccount([Permission::ACTION_DATABASE_READ, Permission::ACTION_DATABASE_UPDATE]);
        $database = $this->createDatabase($server);

        $this->actingAs($user)->getJson($this->databaseLink($server, $database, '/import'))->assertOk();
        $this->actingAs($user)->getJson($this->databaseLink($server, $database, '/export'))->assertForbidden();
        $this->actingAs($user)
            ->postJson($this->databaseLink($server, $database, '/import'), ['remote_host' => 'db.example.com'])
            ->assertForbidden();

        Queue::assertNotPushed(ImportDatabaseJob::class);
    }

    public function test_job_imports_the_file_and_records_the_result()
    {
        Storage::fake('local');
        Storage::disk('local')->put('database-imports/dump.sql', 'SELECT 1;');

        [, $server] = $this->generateTestAccount();
        $database = $this->createDatabase($server);

        $status = $this->app->make(DatabaseImportStatusService::class);
        $token = $status->start($database);

        $this->mock(DatabaseImportService::class)
            ->expects('fromFile')
            ->withArgs(fn (Database $model, string $path, bool $wipe) => $model->is($database) && $wipe && str_ends_with($path, 'database-imports/dump.sql'))
            ->andReturn(12);

        $this->app->call([new ImportDatabaseJob($database->id, 'database-imports/dump.sql', null, $token, true), 'handle']);

        $this->assertSame(DatabaseImportStatusService::STATE_COMPLETED, $status->get($database)['state']);
        $this->assertSame(12, $status->get($database)['statements']);
        $this->assertFalse($status->isRunning($database));
        Storage::disk('local')->assertMissing('database-imports/dump.sql');
    }

    public function test_job_does_not_run_while_another_import_holds_the_database()
    {
        Storage::fake('local');
        Storage::disk('local')->put('database-imports/dump.sql', 'SELECT 1;');

        [, $server] = $this->generateTestAccount();
        $database = $this->createDatabase($server);

        $status = $this->app->make(DatabaseImportStatusService::class);
        $token = $status->start($database);

        $lock = Cache::lock('database:import:'.$database->id.':job', 60);
        $this->assertTrue($lock->get());

        $this->mock(DatabaseImportService::class)->shouldNotReceive('fromFile');

        $this->app->call([new ImportDatabaseJob($database->id, 'database-imports/dump.sql', null, $token, true), 'handle']);

        $this->assertTrue($status->isRunning($database));
        $this->assertTrue($status->owns($database, $token));
        Storage::disk('local')->assertMissing('database-imports/dump.sql');

        $lock->release();
    }

    public function test_job_does_not_run_after_its_claim_on_the_database_is_lost()
    {
        Storage::fake('local');
        Storage::disk('local')->put('database-imports/dump.sql', 'SELECT 1;');

        [, $server] = $this->generateTestAccount();
        $database = $this->createDatabase($server);

        $status = $this->app->make(DatabaseImportStatusService::class);
        $token = $status->start($database);
        $status->clear($database);
        $status->start($database);

        $this->mock(DatabaseImportService::class)->shouldNotReceive('fromFile');

        $this->app->call([new ImportDatabaseJob($database->id, 'database-imports/dump.sql', null, $token, true), 'handle']);

        $this->assertTrue($status->isRunning($database));
        Storage::disk('local')->assertMissing('database-imports/dump.sql');
    }

    public function test_job_reports_an_import_that_expired_before_it_started()
    {
        Storage::fake('local');
        Storage::disk('local')->put('database-imports/dump.sql', 'SELECT 1;');

        [, $server] = $this->generateTestAccount();
        $database = $this->createDatabase($server);

        $status = $this->app->make(DatabaseImportStatusService::class);
        $token = $status->start($database);
        $status->clear($database);

        $this->mock(DatabaseImportService::class)->shouldNotReceive('fromFile');

        $this->app->call([new ImportDatabaseJob($database->id, 'database-imports/dump.sql', null, $token, true), 'handle']);

        $this->assertSame(DatabaseImportStatusService::STATE_FAILED, $status->get($database)['state']);
        $this->assertSame(DatabaseImportException::TIMED_OUT, $status->get($database)['error']);
        Storage::disk('local')->assertMissing('database-imports/dump.sql');
    }

    public function test_job_records_why_an_import_failed()
    {
        [, $server] = $this->generateTestAccount();
        $database = $this->createDatabase($server);

        $status = $this->app->make(DatabaseImportStatusService::class);
        $token = $status->start($database);

        $this->mock(DatabaseImportService::class)
            ->expects('fromRemote')
            ->andThrow(new DatabaseImportException(DatabaseImportException::REMOTE_ACCESS_DENIED));

        $remote = ['host' => 'db.example.com', 'port' => 3306, 'database' => 'source', 'username' => 'user', 'password' => null];
        $this->app->call([new ImportDatabaseJob($database->id, null, $remote, $token), 'handle']);

        $this->assertSame(DatabaseImportStatusService::STATE_FAILED, $status->get($database)['state']);
        $this->assertSame(DatabaseImportException::REMOTE_ACCESS_DENIED, $status->get($database)['error']);
        $this->assertNotNull($status->start($database));
    }

    private function createDatabase(Server $server): Database
    {
        return Database::factory()->create([
            'server_id' => $server->id,
            'database_host_id' => DatabaseHost::factory()->create()->id,
        ]);
    }

    private function databaseLink(Server $server, Database $database, string $append): string
    {
        return $this->link($server, '/databases/'.$this->app->make(HashidsInterface::class)->encode($database->id).$append);
    }
}
