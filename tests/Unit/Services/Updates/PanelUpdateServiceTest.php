<?php

namespace Tests\Unit\Services\Updates;

use App\Services\Helpers\SoftwareVersionService;
use App\Services\Updates\InstallationTypeService;
use App\Services\Updates\PanelUpdateService;
use App\Services\Updates\SoftwareUpdateStatusService;
use GuzzleHttp\Client;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Mockery;
use Phar;
use PharData;
use RuntimeException;
use Tests\TestCase;

class PanelUpdateServiceTest extends TestCase
{
    public function test_rejects_unsafe_archive_paths(): void
    {
        $service = $this->makeInspectableUpdater($this->baseDirectory());

        $service->assertArchiveEntriesAreSafe(['./artisan', 'app/Services/Test.php']);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('unsafe path');
        $service->assertArchiveEntriesAreSafe(['artisan', '../outside.php']);
    }

    public function test_rejects_links_and_special_files_in_release_archive(): void
    {
        $service = $this->makeInspectableUpdater($this->baseDirectory());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('unsupported link or special file');
        $service->assertArchiveTypesAreSafe(['lrwxr-xr-x user/group 0 date path -> /outside']);
    }

    public function test_refuses_automatic_updates_for_docker_panel(): void
    {
        config()->set('panel.installation_type', 'docker');
        $service = $this->makeInspectableUpdater($this->baseDirectory());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('require a released native installation');
        $service->update('26.09.1');
    }

    public function test_refuses_automatic_updates_for_unsupported_database(): void
    {
        config()->set('panel.installation_type', 'native');
        config()->set('app.version', '26.09.0');
        config()->set('database.default', 'sqlsrv');
        config()->set('database.connections.sqlsrv.driver', 'sqlsrv');
        $service = $this->makeInspectableUpdater($this->baseDirectory());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('require a released native installation');
        $service->update('26.09.1');
    }

    public function test_rejects_a_release_that_does_not_match_the_official_digest(): void
    {
        $base = $this->baseDirectory();
        $archive = $base.'/release.tar.gz';
        file_put_contents($archive, 'release archive');
        $service = $this->makeInspectableUpdater($base);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('official SHA-256 digest');
        $service->assertReleaseDigestMatches($archive, str_repeat('0', 64));
    }

    public function test_custom_process_environment_preserves_the_worker_path(): void
    {
        $service = $this->makeInspectableUpdater($this->baseDirectory());

        $output = $service->runProcessForTest(
            ['/bin/sh', '-c', 'printf "%s|%s" "$PATH" "$HOME"'],
            ['UPDATER_TEST_VARIABLE' => 'present'],
        );

        $this->assertSame(getenv('PATH').'|'.getenv('HOME'), $output);
    }

    public function test_successful_update_follows_the_documented_order_and_restarts_the_reviq_worker(): void
    {
        config()->set('panel.installation_type', 'native');
        config()->set('app.version', '26.09.0');
        config()->set('database.default', 'pgsql');
        config()->set('database.connections.pgsql', [
            'driver' => 'pgsql',
            'host' => '127.0.0.1',
            'port' => 5432,
            'database' => 'panel',
            'username' => 'panel',
            'password' => 'secret',
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ]);

        $base = $this->baseDirectory();
        $files = new Filesystem();
        $files->ensureDirectoryExists($base.'/storage/app');
        $files->ensureDirectoryExists($base.'/vendor');
        $files->put($base.'/artisan', 'old artisan');
        $files->put($base.'/composer.json', 'old composer');
        $files->put($base.'/vendor/installed.txt', 'old vendor');

        $service = $this->makeInspectableUpdater($base);
        $service->fixture = $this->releaseArchive([
            'artisan' => 'new artisan',
            'composer.json' => 'new composer',
        ]);
        $service->update('26.09.1');

        $commands = array_map(fn (array $command): string => implode(' ', $command), $service->commands);
        $down = $this->commandIndex($commands, 'artisan down --retry=15');
        $dump = $this->commandIndex($commands, 'pg_dump ');
        $composer = $this->commandIndex($commands, 'composer install');
        $migrate = $this->commandIndex($commands, 'artisan migrate --seed --force');
        $restart = $this->commandIndex($commands, 'artisan queue:restart');
        $up = $this->commandIndex($commands, 'artisan up');

        $this->assertLessThan($dump, $down, 'Maintenance mode must begin before the database snapshot.');
        $this->assertLessThan($composer, $dump);
        $this->assertLessThan($migrate, $composer);
        $this->assertLessThan($restart, $migrate);
        $this->assertLessThan($up, $restart);
        $this->assertStringContainsString('--dbname=panel --port=5432 --clean --if-exists --no-owner --no-privileges', $commands[$dump]);

        $backups = $files->directories($base.'/storage/app/software-updates/backups');
        $this->assertCount(1, $backups);
        $this->assertFileExists($backups[0].'/database.sql');
        $this->assertFileExists($backups[0].'/vendor/installed.txt');
        $this->assertSame(0700, fileperms($backups[0]) & 0777);
        $this->assertSame(0600, fileperms($backups[0].'/database.sql') & 0777);
        $this->assertSame(0600, fileperms($backups[0].'/manifest.json') & 0777);
        $this->assertFileExists($backups[0].'/.completed.json');
        $this->assertSame('new artisan', $files->get($base.'/artisan'));
    }

    public function test_prunes_only_old_completed_update_backups(): void
    {
        config()->set('panel.updates.backups_to_keep', 3);
        $base = $this->baseDirectory();
        $backupsPath = $base.'/storage/app/software-updates/backups';
        $files = new Filesystem();

        foreach (['20260901_000001_oldest', '20260902_000001_old', '20260903_000001_recent', '20260904_000001_newer'] as $backup) {
            $files->ensureDirectoryExists($backupsPath.'/'.$backup);
            $files->put($backupsPath.'/'.$backup.'/.completed.json', '{}');
        }
        $files->ensureDirectoryExists($backupsPath.'/failed-or-active');
        $files->put($backupsPath.'/failed-or-active/database.sql', 'recovery data');
        $current = $backupsPath.'/20260905_000001_current';
        $files->ensureDirectoryExists($current);

        $service = $this->makeInspectableUpdater($base);
        $service->completeBackupForTest($current, $backupsPath, '26.09.1');

        $remaining = array_map('basename', $files->directories($backupsPath));
        sort($remaining);
        $this->assertSame([
            '20260903_000001_recent',
            '20260904_000001_newer',
            '20260905_000001_current',
            'failed-or-active',
        ], $remaining);
        $this->assertFileExists($current.'/.completed.json');
        $this->assertFileExists($backupsPath.'/failed-or-active/database.sql');
    }

    public function test_failed_dependency_install_restores_files_database_and_vendor(): void
    {
        config()->set('panel.installation_type', 'native');
        config()->set('app.version', '26.09.0');
        config()->set('database.default', 'mysql');
        config()->set('database.connections.mysql', [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => 3306,
            'database' => 'panel',
            'username' => 'panel',
            'password' => 'secret',
        ]);

        $base = $this->baseDirectory();
        $files = new Filesystem();
        $files->ensureDirectoryExists($base.'/storage/app');
        $files->ensureDirectoryExists($base.'/vendor');
        $files->put($base.'/artisan', 'old artisan');
        $files->put($base.'/composer.json', 'old composer');
        $files->put($base.'/vendor/installed.txt', 'old vendor');

        $fixture = $this->releaseArchive([
            'artisan' => 'new artisan',
            'composer.json' => 'new composer',
            'new-file.php' => 'new file',
        ]);
        $service = $this->makeInspectableUpdater($base);
        $service->fixture = $fixture;
        $service->failComposer = true;

        try {
            $service->update('26.09.1');
            $this->fail('Expected the Composer installation to fail.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('The previous installation was restored.', $exception->getMessage());
        }

        $this->assertSame('old artisan', $files->get($base.'/artisan'));
        $this->assertSame('old composer', $files->get($base.'/composer.json'));
        $this->assertSame('old vendor', $files->get($base.'/vendor/installed.txt'));
        $this->assertFileDoesNotExist($base.'/new-file.php');

        $backups = $files->directories($base.'/storage/app/software-updates/backups');
        $this->assertCount(1, $backups);
        $this->assertFileExists($backups[0].'/database.sql');
        $this->assertSame('database backup', $files->get($backups[0].'/database.sql'));

        $status = app(SoftwareUpdateStatusService::class)->get('panel');
        $this->assertSame('failed', $status['state']);
    }

    public function test_delta_update_installs_changed_files_and_removes_manifest_deletions(): void
    {
        $this->configureMysql();
        $base = $this->baseDirectory();
        $files = new Filesystem();
        $files->ensureDirectoryExists($base.'/storage/app');
        $files->ensureDirectoryExists($base.'/vendor');
        $files->put($base.'/artisan', 'old artisan');
        $files->put($base.'/composer.json', 'old composer');
        $files->put($base.'/changed.php', 'old changed file');
        $files->put($base.'/deleted.php', 'deleted in the new release');

        $service = $this->makeInspectableUpdater($base);
        $service->updateFixture = $this->updateArchive([
            'changed.php' => 'new changed file',
            'added.php' => 'new file',
            'executable' => '#!/bin/sh',
        ], ['deleted.php'], modes: ['executable' => 0755]);
        $service->update('26.09.1');

        $this->assertSame('new changed file', $files->get($base.'/changed.php'));
        $this->assertSame('new file', $files->get($base.'/added.php'));
        $this->assertSame(0755, fileperms($base.'/executable') & 0777);
        $this->assertFileDoesNotExist($base.'/deleted.php');
        $this->assertSame(0, $service->fullReleaseDownloads);
    }

    public function test_delta_update_rollback_restores_modified_and_deleted_files(): void
    {
        $this->configureMysql();
        $base = $this->baseDirectory();
        $files = new Filesystem();
        $files->ensureDirectoryExists($base.'/storage/app');
        $files->ensureDirectoryExists($base.'/vendor');
        $files->put($base.'/artisan', 'old artisan');
        $files->put($base.'/composer.json', 'old composer');
        $files->put($base.'/changed.php', 'old changed file');
        $files->put($base.'/deleted.php', 'deleted in the new release');

        $service = $this->makeInspectableUpdater($base);
        $service->updateFixture = $this->updateArchive([
            'changed.php' => 'new changed file',
            'added.php' => 'new file',
        ], ['deleted.php']);
        $service->failComposer = true;

        try {
            $service->update('26.09.1');
            $this->fail('Expected the Composer installation to fail.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('The previous installation was restored.', $exception->getMessage());
        }

        $this->assertSame('old changed file', $files->get($base.'/changed.php'));
        $this->assertSame('deleted in the new release', $files->get($base.'/deleted.php'));
        $this->assertFileDoesNotExist($base.'/added.php');
    }

    public function test_delta_update_rejects_unsafe_deleted_paths_before_maintenance_mode(): void
    {
        $this->configureMysql();
        $base = $this->baseDirectory();
        $files = new Filesystem();
        $files->ensureDirectoryExists($base.'/storage/app');
        $files->ensureDirectoryExists($base.'/vendor');
        $files->put($base.'/artisan', 'old artisan');
        $files->put($base.'/composer.json', 'old composer');
        $outside = dirname($base).'/outside.php';
        $files->put($outside, 'must remain untouched');
        $this->beforeApplicationDestroyed(fn () => $files->delete($outside));

        $service = $this->makeInspectableUpdater($base);
        $service->updateFixture = $this->updateArchive([], ['../outside.php']);

        try {
            $service->update('26.09.1');
            $this->fail('Expected the unsafe deletion manifest to be rejected.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('unsafe path', $exception->getMessage());
        }

        $commands = array_map(fn (array $command): string => implode(' ', $command), $service->commands);
        $this->assertNotContains(PHP_BINARY.' artisan down --retry=15', $commands);
        $this->assertSame('must remain untouched', $files->get($outside));
    }

    public function test_delta_for_another_starting_version_falls_back_to_the_full_release(): void
    {
        $this->configureMysql();
        $base = $this->baseDirectory();
        $files = new Filesystem();
        $files->ensureDirectoryExists($base.'/storage/app');
        $files->ensureDirectoryExists($base.'/vendor');
        $files->put($base.'/artisan', 'old artisan');
        $files->put($base.'/composer.json', 'old composer');

        $service = $this->makeInspectableUpdater($base);
        $service->updateFixture = $this->updateArchive(['changed.php' => 'wrong delta'], [], '26.08.0');
        $service->fixture = $this->releaseArchive([
            'artisan' => 'new artisan',
            'composer.json' => 'new composer',
        ]);
        $service->update('26.09.1');

        $this->assertSame(1, $service->fullReleaseDownloads);
        $this->assertSame('new artisan', $files->get($base.'/artisan'));
        $this->assertFileDoesNotExist($base.'/changed.php');
    }

    public function test_missing_database_utility_fails_before_maintenance_mode(): void
    {
        $this->configureMysql();
        $base = $this->baseDirectory();
        $files = new Filesystem();
        $files->ensureDirectoryExists($base.'/storage/app');
        $files->ensureDirectoryExists($base.'/vendor');
        $files->put($base.'/artisan', 'old artisan');
        $files->put($base.'/composer.json', 'old composer');

        $service = $this->makeInspectableUpdater($base);
        $service->fixture = $this->releaseArchive([
            'artisan' => 'new artisan',
            'composer.json' => 'new composer',
        ]);
        $service->missingExecutable = 'mysqldump';

        try {
            $service->update('26.09.1');
            $this->fail('Expected the missing backup utility to abort the update.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('[mysqldump] is not available', $exception->getMessage());
        }

        $commands = array_map(fn (array $command): string => implode(' ', $command), $service->commands);
        $this->assertNotContains(PHP_BINARY.' artisan down --retry=15', $commands);
        $this->assertSame('old artisan', $files->get($base.'/artisan'));
    }

    public function test_sqlite_update_uses_an_internal_snapshot_and_restores_it_on_failure(): void
    {
        config()->set('panel.installation_type', 'native');
        config()->set('app.version', '26.09.0');
        $base = $this->baseDirectory();
        $database = $base.'/panel.sqlite';
        $pdo = new \PDO('sqlite:'.$database);
        $pdo->exec('CREATE TABLE settings (value TEXT NOT NULL)');
        $pdo->exec("INSERT INTO settings VALUES ('before update')");
        unset($pdo);
        config()->set('database.default', 'sqlite-update-test');
        config()->set('database.connections.sqlite-update-test', [
            'driver' => 'sqlite',
            'database' => $database,
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        DB::purge('sqlite-update-test');

        $files = new Filesystem();
        $files->ensureDirectoryExists($base.'/storage/app');
        $files->ensureDirectoryExists($base.'/vendor');
        $files->put($base.'/artisan', 'old artisan');
        $files->put($base.'/composer.json', 'old composer');

        $service = $this->makeInspectableUpdater($base);
        $service->fixture = $this->releaseArchive([
            'artisan' => 'new artisan',
            'composer.json' => 'new composer',
        ]);
        $service->failComposer = true;
        $service->mutateSqliteBeforeComposerFailure = true;

        try {
            $service->update('26.09.1');
            $this->fail('Expected the Composer installation to fail.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('The previous installation was restored.', $exception->getMessage());
        }

        DB::purge('sqlite-update-test');
        $restored = new \PDO('sqlite:'.$database);
        $this->assertSame('before update', $restored->query('SELECT value FROM settings')->fetchColumn());
        $backups = $files->directories($base.'/storage/app/software-updates/backups');
        $this->assertFileExists($backups[0].'/database.sqlite');
    }

    public function test_postgresql_rollback_recreates_public_schema_in_one_transaction(): void
    {
        config()->set('panel.installation_type', 'native');
        config()->set('app.version', '26.09.0');
        config()->set('database.default', 'pgsql');
        config()->set('database.connections.pgsql', [
            'driver' => 'pgsql',
            'host' => '127.0.0.1',
            'port' => 5432,
            'database' => 'panel',
            'username' => 'panel',
            'password' => 'secret',
            'search_path' => 'public',
            'sslmode' => 'require',
        ]);

        $base = $this->baseDirectory();
        $files = new Filesystem();
        $files->ensureDirectoryExists($base.'/storage/app');
        $files->ensureDirectoryExists($base.'/vendor');
        $files->put($base.'/artisan', 'old artisan');
        $files->put($base.'/composer.json', 'old composer');
        $files->put($base.'/vendor/installed.txt', 'old vendor');

        $service = $this->makeInspectableUpdater($base);
        $service->fixture = $this->releaseArchive([
            'artisan' => 'new artisan',
            'composer.json' => 'new composer',
        ]);
        $service->failComposer = true;

        try {
            $service->update('26.09.1');
            $this->fail('Expected the Composer installation to fail.');
        } catch (RuntimeException) {
            // The assertions below verify the rollback command.
        }

        $commands = array_map(fn (array $command): string => implode(' ', $command), $service->commands);
        $restore = $this->commandIndex($commands, 'psql ');
        $this->assertStringContainsString('--set=ON_ERROR_STOP=1 --single-transaction', $commands[$restore]);
        $this->assertStringContainsString('DROP SCHEMA public CASCADE; CREATE SCHEMA public;', $commands[$restore]);
        $this->assertSame('old artisan', $files->get($base.'/artisan'));
    }

    private function commandIndex(array $commands, string $needle): int
    {
        foreach ($commands as $index => $command) {
            if (str_contains($command, $needle)) {
                return $index;
            }
        }

        $this->fail("Command containing [{$needle}] was not run.");
    }

    private function baseDirectory(): string
    {
        $path = sys_get_temp_dir().'/reviactyl-panel-update-'.bin2hex(random_bytes(8));
        mkdir($path, 0755, true);
        $this->beforeApplicationDestroyed(fn () => (new Filesystem())->deleteDirectory($path));

        return $path;
    }

    private function releaseArchive(array $files): string
    {
        $directory = $this->baseDirectory();
        $tarPath = $directory.'/panel.tar';
        $archive = new PharData($tarPath);
        foreach ($files as $path => $contents) {
            $archive->addFromString($path, $contents);
        }
        $archive->compress(Phar::GZ);
        unset($archive);

        return $tarPath.'.gz';
    }

    private function updateArchive(
        array $files,
        array $deleted,
        string $from = '26.09.0',
        string $to = '26.09.1',
        array $modes = [],
    ): string {
        $path = $this->baseDirectory().'/update.zip';
        $archive = new \ZipArchive();
        $this->assertTrue($archive->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE));
        foreach ($files as $file => $contents) {
            $archive->addFromString($file, $contents);
            if (isset($modes[$file])) {
                $archive->setExternalAttributesName($file, \ZipArchive::OPSYS_UNIX, (0100000 | $modes[$file]) << 16);
            }
        }
        $archive->addFromString('manifest.json', json_encode([
            'from' => $from,
            'to' => $to,
            'deleted' => $deleted,
        ], JSON_THROW_ON_ERROR));
        $archive->close();

        return $path;
    }

    private function configureMysql(): void
    {
        config()->set('panel.installation_type', 'native');
        config()->set('app.version', '26.09.0');
        config()->set('database.default', 'mysql');
        config()->set('database.connections.mysql', [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => 3306,
            'database' => 'panel',
            'username' => 'panel',
            'password' => 'secret',
        ]);
    }

    private function makeInspectableUpdater(string $basePath): PanelUpdateService
    {
        $versions = Mockery::mock(SoftwareVersionService::class);
        $versions->shouldReceive('getPanel')->andReturn('26.09.1');

        return new class(new Client(), new Filesystem(), new InstallationTypeService(), app(SoftwareUpdateStatusService::class), $versions, $basePath) extends PanelUpdateService
        {
            public ?string $fixture = null;

            public ?string $updateFixture = null;

            public bool $failComposer = false;

            public bool $mutateSqliteBeforeComposerFailure = false;

            public ?string $missingExecutable = null;

            public int $fullReleaseDownloads = 0;

            public array $commands = [];

            public function assertArchiveEntriesAreSafe(array $entries): void
            {
                $this->validateArchiveEntries($entries);
            }

            public function assertArchiveTypesAreSafe(array $entries): void
            {
                $this->validateArchiveTypes($entries);
            }

            public function runProcessForTest(array $command, array $environment): string
            {
                return $this->runProcess($command, $environment);
            }

            public function assertReleaseDigestMatches(string $path, string $expectedDigest): void
            {
                $this->validateReleaseDigest($path, $expectedDigest);
            }

            public function completeBackupForTest(string $backupPath, string $backupsPath, string $version): void
            {
                $this->markBackupCompletedAndPrune($backupPath, $backupsPath, $version);
            }

            protected function downloadRelease(string $version, string $archivePath): void
            {
                $this->fullReleaseDownloads++;
                if ($this->fixture === null || ! copy($this->fixture, $archivePath)) {
                    throw new RuntimeException('Missing test release fixture.');
                }
            }

            protected function downloadUpdatePackage(string $version, string $archivePath): bool
            {
                return $this->updateFixture !== null && copy($this->updateFixture, $archivePath);
            }

            protected function findExecutable(string $name): ?string
            {
                return $name === $this->missingExecutable ? null : '/usr/bin/'.$name;
            }

            protected function runProcess(
                array $command,
                array $environment = [],
                int $timeout = 120,
                mixed $input = null,
                ?string $outputPath = null,
            ): string {
                $this->commands[] = $command;
                if ($command[0] === 'mysqldump' || $command[0] === 'pg_dump') {
                    file_put_contents($outputPath, 'database backup');

                    return '';
                }
                if ($command[0] === 'mysql' || $command[0] === 'psql' || $command[0] === PHP_BINARY || $command[0] === 'chmod') {
                    return '';
                }
                if ($command[0] === 'composer') {
                    if ($this->failComposer) {
                        if ($this->mutateSqliteBeforeComposerFailure) {
                            $connection = (string) config('database.default');
                            DB::purge($connection);
                            DB::connection($connection)->update("UPDATE settings SET value = 'during update'");
                            DB::purge($connection);
                        }
                        throw new RuntimeException('Composer failed for testing.');
                    }

                    return '';
                }

                return parent::runProcess($command, $environment, $timeout, $input, $outputPath);
            }
        };
    }
}
