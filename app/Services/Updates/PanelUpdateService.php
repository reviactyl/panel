<?php

namespace App\Services\Updates;

use App\Services\Helpers\SoftwareVersionService;
use GuzzleHttp\Client;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use Throwable;
use ZipArchive;

class PanelUpdateService
{
    private const COMPLETED_BACKUP_MARKER = '.completed.json';

    private const RELEASE_ASSET_URL = 'https://github.com/reviactyl/panel/releases/download/v%s/%s';

    private const RELEASE_METADATA_URL = 'https://api.github.com/repos/reviactyl/panel/releases/tags/v%s';

    private const VERSION_PATTERN = '/^[0-9]+\.[0-9]+\.[0-9]+(?:[-+][0-9A-Za-z.-]+)?$/';

    private string $basePath;

    public function __construct(
        private Client $client,
        private Filesystem $files,
        private InstallationTypeService $installationTypes,
        private SoftwareUpdateStatusService $statuses,
        private SoftwareVersionService $versions,
        ?string $basePath = null,
    ) {
        $this->basePath = $basePath ?? base_path();
    }

    public function update(string $version): void
    {
        if (! $this->installationTypes->panelSupportsAutomaticUpdates()) {
            throw new RuntimeException('Automatic Panel updates require a released native installation using MySQL, MariaDB, PostgreSQL, or SQLite.');
        }
        if (! preg_match(self::VERSION_PATTERN, $version)) {
            throw new RuntimeException('The requested Panel version is invalid.');
        }
        if ($version !== $this->versions->getPanel()) {
            throw new RuntimeException('The requested Panel version is not the current official release.');
        }
        if (version_compare((string) config('app.version'), $version, '>=')) {
            throw new RuntimeException('The Panel is already up to date.');
        }

        $workingRoot = $this->basePath.'/storage/app/software-updates';
        $this->files->ensureDirectoryExists($workingRoot);
        $lock = fopen($workingRoot.'/update.lock', 'c+');
        if ($lock === false || ! flock($lock, LOCK_EX | LOCK_NB)) {
            if (is_resource($lock)) {
                fclose($lock);
            }
            throw new RuntimeException('Another Panel update is already in progress.');
        }

        try {
            $this->performUpdate($version, $workingRoot);
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    private function performUpdate(string $version, string $workingRoot): void
    {
        $statusKey = $this->statuses->panelKey();
        $runId = now()->format('Ymd_His').'_'.bin2hex(random_bytes(4));
        $runPath = $workingRoot.'/runs/'.$runId;
        $stagingPath = $runPath.'/staging';
        $archivePath = $runPath.'/update.zip';
        $backupPath = $workingRoot.'/backups/'.$runId;
        $databaseBackup = $backupPath.($this->databaseDriver() === 'sqlite' ? '/database.sqlite' : '/database.sql');
        $manifestPath = $backupPath.'/manifest.json';
        $vendorBackup = $backupPath.'/vendor';
        $maintenanceEnabled = false;
        $vendorMoved = false;
        $changesStarted = false;

        $this->files->ensureDirectoryExists($stagingPath);
        $this->files->ensureDirectoryExists($backupPath.'/files', 0700);

        try {
            $this->statuses->set($statusKey, 'downloading', trans('admin/updates.status.panel_downloading'), $version);
            $usesUpdatePackage = $this->downloadUpdatePackage($version, $archivePath);

            $this->statuses->set($statusKey, 'validating', trans('admin/updates.status.panel_validating'), $version);
            $deletedFiles = [];
            $usesFullRelease = ! $usesUpdatePackage;
            if ($usesUpdatePackage) {
                $packageDeletedFiles = $this->extractUpdatePackage($archivePath, $stagingPath, (string) config('app.version'), $version);
                if ($packageDeletedFiles === null) {
                    $this->files->deleteDirectory($stagingPath);
                    $this->files->ensureDirectoryExists($stagingPath);
                    $archivePath = $runPath.'/panel.tar.gz';
                    $this->downloadRelease($version, $archivePath);
                    $this->extractFullRelease($archivePath, $stagingPath);
                    $usesFullRelease = true;
                } else {
                    $deletedFiles = $packageDeletedFiles;
                }
            } else {
                $archivePath = $runPath.'/panel.tar.gz';
                $this->downloadRelease($version, $archivePath);
                $this->extractFullRelease($archivePath, $stagingPath);
            }
            $releaseFiles = $this->releaseFiles($stagingPath);
            $this->validateReleaseFileSet($releaseFiles, $deletedFiles, $usesFullRelease);
            $this->assertDatabaseUtilitiesAvailable();

            $this->runProcess([PHP_BINARY, 'artisan', 'down', '--retry=15']);
            $maintenanceEnabled = true;

            $this->statuses->set($statusKey, 'backing_up', trans('admin/updates.status.panel_backing_up'), $version);
            $this->dumpDatabase($databaseBackup);
            $manifest = $this->backUpReleaseFiles($releaseFiles, $deletedFiles, $backupPath.'/files');
            $this->files->put($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
            chmod($manifestPath, 0600);

            $vendorPath = $this->basePath.'/vendor';
            $changesStarted = true;
            if (is_dir($vendorPath)) {
                if (! rename($vendorPath, $vendorBackup)) {
                    throw new RuntimeException('Unable to back up the current Composer dependencies.');
                }
                $vendorMoved = true;
            }

            $this->statuses->set($statusKey, 'installing', trans('admin/updates.status.panel_installing'), $version);
            $this->installReleaseFiles($releaseFiles, $stagingPath);
            $this->deleteReleaseFiles($deletedFiles);
            $this->runProcess(
                ['composer', 'install', '--no-dev', '--optimize-autoloader', '--no-interaction'],
                ['COMPOSER_ALLOW_SUPERUSER' => '1'],
                600,
            );
            $this->runProcess(['chmod', '-R', '755', 'storage', 'bootstrap/cache']);
            // The documented permission repair also reaches storage, so restore
            // the private modes on the updater's retained recovery material.
            chmod($backupPath, 0700);
            chmod($backupPath.'/files', 0700);
            chmod($databaseBackup, 0600);
            chmod($manifestPath, 0600);

            $this->statuses->set($statusKey, 'migrating', trans('admin/updates.status.panel_migrating'), $version);
            $this->runProcess([PHP_BINARY, 'artisan', 'migrate', '--seed', '--force'], timeout: 300);
            $this->runProcess([PHP_BINARY, 'artisan', 'optimize:clear']);
            // The documented reviq.service runs queue:work with Restart=always.
            // A graceful queue restart lets this job finish before systemd starts
            // the service runner again; invoking systemctl here would kill the
            // www-data worker during its own update and normally requires root.
            $this->statuses->set($statusKey, 'restarting', trans('admin/updates.status.panel_restarting'), $version);
            $this->runProcess([PHP_BINARY, 'artisan', 'queue:restart']);
            $this->runProcess([PHP_BINARY, 'artisan', 'up']);
            $maintenanceEnabled = false;

            $this->files->deleteDirectory($runPath);
            $this->statuses->set($statusKey, 'complete', trans('admin/updates.status.panel_complete'), $version);
            $this->markBackupCompletedAndPrune($backupPath, $workingRoot.'/backups', $version);
        } catch (Throwable $exception) {
            $rollbackErrors = [];
            if ($changesStarted && is_file($manifestPath)) {
                try {
                    $manifest = json_decode($this->files->get($manifestPath), true, flags: JSON_THROW_ON_ERROR);
                    $this->rollBackReleaseFiles($manifest, $backupPath.'/files');
                } catch (Throwable $rollbackException) {
                    $rollbackErrors[] = $rollbackException->getMessage();
                }
            }

            if ($changesStarted) {
                try {
                    $this->files->deleteDirectory($this->basePath.'/vendor');
                    if ($vendorMoved && ! rename($vendorBackup, $this->basePath.'/vendor')) {
                        throw new RuntimeException('Unable to restore the previous Composer dependencies.');
                    }
                } catch (Throwable $rollbackException) {
                    $rollbackErrors[] = $rollbackException->getMessage();
                }
            }

            if ($changesStarted && is_file($databaseBackup)) {
                try {
                    $this->restoreDatabase($databaseBackup);
                } catch (Throwable $rollbackException) {
                    $rollbackErrors[] = $rollbackException->getMessage();
                }
            }

            if ($maintenanceEnabled) {
                try {
                    $this->runProcess([PHP_BINARY, 'artisan', 'up']);
                } catch (Throwable) {
                    // The original and rollback exceptions contain the actionable failure.
                }
            }

            $message = 'Panel update failed: '.$exception->getMessage();
            if ($rollbackErrors !== []) {
                $message .= ' Rollback also failed: '.implode(' ', $rollbackErrors);
            } elseif ($changesStarted) {
                $message .= ' The previous installation was restored.';
            } else {
                $message .= ' No live files were changed.';
            }
            $this->statuses->set($statusKey, 'failed', $message, $version);

            throw new RuntimeException($message, previous: $exception);
        }
    }

    protected function downloadRelease(string $version, string $archivePath): void
    {
        $expectedDigest = $this->releaseDigest($version, 'panel.tar.gz');
        if ($expectedDigest === null) {
            throw new RuntimeException('The official Panel release does not include the full release archive.');
        }
        $this->downloadAsset($version, 'panel.tar.gz', $archivePath, $expectedDigest);
    }

    protected function downloadUpdatePackage(string $version, string $archivePath): bool
    {
        $expectedDigest = $this->releaseDigest($version, 'update.zip');
        if ($expectedDigest === null) {
            return false;
        }
        $this->downloadAsset($version, 'update.zip', $archivePath, $expectedDigest);

        return true;
    }

    private function downloadAsset(string $version, string $asset, string $archivePath, string $expectedDigest): void
    {
        $response = $this->client->request('GET', sprintf(self::RELEASE_ASSET_URL, $version, $asset), [
            'allow_redirects' => true,
            'connect_timeout' => 15,
            'sink' => $archivePath,
            'timeout' => 180,
        ]);
        $size = is_file($archivePath) ? filesize($archivePath) : false;
        if ($response->getStatusCode() !== 200 || $size === false || $size === 0 || $size > 256 * 1024 * 1024) {
            throw new RuntimeException('The Panel release download was empty, oversized, or unsuccessful.');
        }
        $this->validateReleaseDigest($archivePath, $expectedDigest);
    }

    protected function releaseDigest(string $version, string $assetName): ?string
    {
        $response = $this->client->request('GET', sprintf(self::RELEASE_METADATA_URL, $version), [
            'connect_timeout' => 15,
            'headers' => [
                'Accept' => 'application/vnd.github+json',
                'User-Agent' => 'Reviactyl-Panel-Updater',
                'X-GitHub-Api-Version' => '2022-11-28',
            ],
            'timeout' => 30,
        ]);
        $metadata = json_decode((string) $response->getBody(), true, flags: JSON_THROW_ON_ERROR);
        if ($response->getStatusCode() !== 200 || ($metadata['tag_name'] ?? null) !== 'v'.$version) {
            throw new RuntimeException('Unable to validate the official Panel release metadata.');
        }

        foreach ($metadata['assets'] ?? [] as $asset) {
            if (($asset['name'] ?? null) !== $assetName) {
                continue;
            }
            if (preg_match('/^sha256:([a-f0-9]{64})$/i', (string) ($asset['digest'] ?? ''), $matches)) {
                return strtolower($matches[1]);
            }

            throw new RuntimeException("The official Panel release asset [{$assetName}] does not include a SHA-256 digest.");
        }

        return null;
    }

    protected function validateReleaseDigest(string $archivePath, string $expectedDigest): void
    {
        $actualDigest = hash_file('sha256', $archivePath);
        if ($actualDigest === false || ! hash_equals($expectedDigest, $actualDigest)) {
            throw new RuntimeException('The Panel release download did not match its official SHA-256 digest.');
        }
    }

    private function extractFullRelease(string $archivePath, string $stagingPath): void
    {
        $entries = preg_split('/\r?\n/', trim($this->runProcess(['tar', '-tzf', $archivePath]))) ?: [];
        $this->validateArchiveEntries($entries);
        $archiveTypes = preg_split('/\r?\n/', trim($this->runProcess(['tar', '-tvzf', $archivePath]))) ?: [];
        $this->validateArchiveTypes($archiveTypes);
        $this->runProcess(['tar', '-xzf', $archivePath, '-C', $stagingPath]);
    }

    private function extractUpdatePackage(string $archivePath, string $stagingPath, string $fromVersion, string $toVersion): ?array
    {
        $archive = new ZipArchive();
        if ($archive->open($archivePath) !== true || $archive->numFiles < 1 || $archive->numFiles > 50000) {
            throw new RuntimeException('The Panel update package is invalid.');
        }

        try {
            $entries = [];
            $entryModes = [];
            $uncompressedSize = 0;
            for ($index = 0; $index < $archive->numFiles; $index++) {
                $entry = $archive->getNameIndex($index);
                $statistics = $archive->statIndex($index);
                if ($entry === false || $statistics === false || isset($entries[$entry])) {
                    throw new RuntimeException('The Panel update package contains an invalid or duplicate entry.');
                }
                $entries[$entry] = true;
                $uncompressedSize += (int) ($statistics['size'] ?? 0);
                if ($uncompressedSize > 512 * 1024 * 1024) {
                    throw new RuntimeException('The Panel update package is oversized.');
                }

                $this->validateArchiveEntries([$entry]);
                if ($archive->getExternalAttributesIndex($index, $operatingSystem, $attributes) && $operatingSystem === ZipArchive::OPSYS_UNIX) {
                    $type = ($attributes >> 16) & 0170000;
                    if ($type !== 0 && $type !== 0040000 && $type !== 0100000) {
                        throw new RuntimeException('The Panel update package contains an unsupported link or special file.');
                    }
                    $entryModes[$entry] = ($attributes >> 16) & 0777;
                }
            }

            $manifestContents = $archive->getFromName('manifest.json');
            if ($manifestContents === false) {
                throw new RuntimeException('The Panel update package is missing manifest.json.');
            }
            $manifest = json_decode($manifestContents, true, flags: JSON_THROW_ON_ERROR);
            if (! is_array($manifest) || ($manifest['to'] ?? null) !== $toVersion) {
                throw new RuntimeException('The Panel update package does not target the requested version.');
            }
            if (($manifest['from'] ?? null) !== $fromVersion) {
                return null;
            }

            $deletedFiles = $manifest['deleted'] ?? null;
            if (! is_array($deletedFiles) || ! array_is_list($deletedFiles)) {
                throw new RuntimeException('The Panel update package contains an invalid deleted-files manifest.');
            }
            $this->validateManagedPaths($deletedFiles);
            if (count($deletedFiles) !== count(array_unique($deletedFiles))) {
                throw new RuntimeException('The Panel update package contains duplicate deleted files.');
            }

            if (! $archive->extractTo($stagingPath)) {
                throw new RuntimeException('Unable to extract the Panel update package.');
            }
            foreach ($entryModes as $entry => $mode) {
                if ($mode !== 0 && ! chmod($stagingPath.'/'.$entry, $mode)) {
                    throw new RuntimeException('Unable to preserve permissions from the Panel update package.');
                }
            }
        } finally {
            $archive->close();
        }

        $this->files->delete($stagingPath.'/manifest.json');

        return $deletedFiles;
    }

    /**
     * @param  string[]  $entries
     */
    protected function validateArchiveEntries(array $entries): void
    {
        if ($entries === [] || count($entries) > 50000) {
            throw new RuntimeException('The Panel release archive contains an invalid number of entries.');
        }

        foreach ($entries as $entry) {
            $entry = str_replace('\\', '/', trim($entry));
            if ($entry === '.' || $entry === './') {
                continue;
            }
            $entry = preg_replace('#^\./#', '', $entry) ?? $entry;
            if ($entry === '' || str_starts_with($entry, '/') || in_array('..', explode('/', rtrim($entry, '/')), true)) {
                throw new RuntimeException('The Panel release archive contains an unsafe path.');
            }
        }
    }

    /**
     * @param  string[]  $entries
     */
    protected function validateArchiveTypes(array $entries): void
    {
        foreach ($entries as $entry) {
            $type = substr(ltrim($entry), 0, 1);
            if ($type !== '-' && $type !== 'd') {
                throw new RuntimeException('The Panel release archive contains an unsupported link or special file.');
            }
        }
    }

    /**
     * @return string[]
     */
    private function releaseFiles(string $stagingPath): array
    {
        $files = [];
        foreach ($this->files->allFiles($stagingPath) as $file) {
            if ($file->isLink()) {
                throw new RuntimeException('The Panel release archive contains a symbolic link.');
            }

            $relative = str_replace('\\', '/', $file->getRelativePathname());
            if ($relative === '.env' || str_starts_with($relative, 'storage/') || str_starts_with($relative, 'vendor/')) {
                continue;
            }
            $files[] = $relative;
        }

        sort($files);

        return $files;
    }

    private function validateReleaseFileSet(array $releaseFiles, array $deletedFiles, bool $usesFullRelease): void
    {
        $this->validateManagedPaths($releaseFiles);
        if (array_intersect($releaseFiles, $deletedFiles) !== []) {
            throw new RuntimeException('The Panel update package cannot install and delete the same file.');
        }
        if ($usesFullRelease && (! in_array('artisan', $releaseFiles, true) || ! in_array('composer.json', $releaseFiles, true))) {
            throw new RuntimeException('The Panel release archive is missing required application files.');
        }
        if (! is_file($this->basePath.'/artisan') || ! is_file($this->basePath.'/composer.json')) {
            throw new RuntimeException('The current Panel installation is missing required application files.');
        }
    }

    private function validateManagedPaths(array $paths): void
    {
        foreach ($paths as $path) {
            if (! is_string($path) || $path === '' || str_ends_with($path, '/')) {
                throw new RuntimeException('The Panel update package contains an invalid managed file path.');
            }
            $this->validateArchiveEntries([$path]);
            if ($path === '.env' || $path === 'manifest.json' || str_starts_with($path, 'storage/') || str_starts_with($path, 'vendor/')) {
                throw new RuntimeException('The Panel update package attempts to modify a protected path.');
            }
        }
    }

    private function backUpReleaseFiles(array $releaseFiles, array $deletedFiles, string $backupFilesPath): array
    {
        $manifest = ['existing' => [], 'created' => []];
        foreach (array_values(array_unique([...$releaseFiles, ...$deletedFiles])) as $relative) {
            $destination = $this->basePath.'/'.$relative;
            if (! is_file($destination)) {
                if (in_array($relative, $releaseFiles, true)) {
                    $manifest['created'][] = $relative;
                }

                continue;
            }

            $backup = $backupFilesPath.'/'.$relative;
            $this->files->ensureDirectoryExists(dirname($backup));
            if (! $this->files->copy($destination, $backup)) {
                throw new RuntimeException('Unable to back up '.$relative.'.');
            }
            $mode = fileperms($destination);
            $manifest['existing'][$relative] = $mode === false ? null : ($mode & 0777);
        }

        return $manifest;
    }

    private function installReleaseFiles(array $releaseFiles, string $stagingPath): void
    {
        foreach ($releaseFiles as $relative) {
            $source = $stagingPath.'/'.$relative;
            $destination = $this->basePath.'/'.$relative;
            $this->files->ensureDirectoryExists(dirname($destination));
            $temporary = $destination.'.updating';
            if (! $this->files->copy($source, $temporary) || ! rename($temporary, $destination)) {
                $this->files->delete($temporary);
                throw new RuntimeException('Unable to install '.$relative.'.');
            }
            $mode = fileperms($source);
            if ($mode !== false) {
                chmod($destination, $mode & 0777);
            }
        }
    }

    private function deleteReleaseFiles(array $deletedFiles): void
    {
        foreach ($deletedFiles as $relative) {
            $this->files->delete($this->basePath.'/'.$relative);
        }
    }

    protected function markBackupCompletedAndPrune(string $backupPath, string $backupsPath, string $version): void
    {
        try {
            $marker = $backupPath.'/'.self::COMPLETED_BACKUP_MARKER;
            $this->files->put($marker, json_encode([
                'version' => $version,
                'completed_at' => now()->toIso8601String(),
            ], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
            chmod($marker, 0600);

            $completed = array_values(array_filter(
                $this->files->directories($backupsPath),
                fn (string $path): bool => $path !== $backupPath && is_file($path.'/'.self::COMPLETED_BACKUP_MARKER),
            ));
            rsort($completed, SORT_STRING);
            $backupsToKeep = max(1, (int) config('panel.updates.backups_to_keep', 3));
            foreach (array_slice($completed, $backupsToKeep - 1) as $expiredBackup) {
                $this->files->deleteDirectory($expiredBackup);
            }
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function rollBackReleaseFiles(array $manifest, string $backupFilesPath): void
    {
        foreach ($manifest['created'] ?? [] as $relative) {
            $this->files->delete($this->basePath.'/'.$relative);
        }
        foreach ($manifest['existing'] ?? [] as $relative => $mode) {
            $source = $backupFilesPath.'/'.$relative;
            $destination = $this->basePath.'/'.$relative;
            $this->files->ensureDirectoryExists(dirname($destination));
            if (! $this->files->copy($source, $destination)) {
                throw new RuntimeException('Unable to restore '.$relative.'.');
            }
            if (is_int($mode)) {
                chmod($destination, $mode);
            }
        }
    }

    private function dumpDatabase(string $path): void
    {
        $this->files->ensureDirectoryExists(dirname($path), 0700);
        if ($this->databaseDriver() === 'sqlite') {
            $connection = (string) config('database.default');
            $pdo = DB::connection($connection)->getPdo();
            $quotedPath = $pdo->quote($path);
            if ($quotedPath === false || $pdo->exec("VACUUM INTO {$quotedPath}") === false) {
                throw new RuntimeException('Unable to back up the SQLite database.');
            }
            chmod($path, 0600);

            return;
        }

        $this->runProcess($this->databaseCommand('mysqldump'), $this->databaseEnvironment(), 300, outputPath: $path);
        chmod($path, 0600);
    }

    private function restoreDatabase(string $path): void
    {
        if ($this->databaseDriver() === 'sqlite') {
            $connection = (string) config('database.default');
            $databasePath = $this->sqliteDatabasePath();
            $temporaryPath = $databasePath.'.update-restore';
            $mode = is_file($databasePath) ? fileperms($databasePath) : false;

            DB::purge($connection);
            try {
                $this->files->delete([$temporaryPath, $databasePath.'-wal', $databasePath.'-shm']);
                if (! $this->files->copy($path, $temporaryPath)) {
                    throw new RuntimeException('Unable to restore the SQLite database backup.');
                }
                chmod($temporaryPath, $mode === false ? 0600 : ($mode & 0777));
                if (! rename($temporaryPath, $databasePath)) {
                    throw new RuntimeException('Unable to replace the SQLite database during rollback.');
                }
            } finally {
                $this->files->delete($temporaryPath);
                DB::reconnect($connection);
            }

            return;
        }

        $input = fopen($path, 'r');
        if ($input === false) {
            throw new RuntimeException('Unable to read the Panel database backup.');
        }

        try {
            $this->runProcess($this->databaseCommand('mysql'), $this->databaseEnvironment(), 300, $input);
        } finally {
            fclose($input);
        }
    }

    private function databaseCommand(string $binary): array
    {
        $connection = config('database.default');
        $database = config("database.connections.{$connection}");
        if (! is_array($database) || ! in_array($database['driver'] ?? null, ['mysql', 'mariadb', 'pgsql'], true)) {
            throw new RuntimeException('Automatic Panel updates require a MySQL, MariaDB, PostgreSQL, or SQLite database.');
        }

        $driver = $database['driver'];
        if ($driver === 'pgsql') {
            $command = [
                $binary === 'mysqldump' ? 'pg_dump' : 'psql',
                '--host='.(string) ($database['host'] ?? '127.0.0.1'),
                '--username='.(string) ($database['username'] ?? ''),
                '--dbname='.(string) ($database['database'] ?? ''),
            ];
            if (! empty($database['port'])) {
                $command[] = '--port='.(string) $database['port'];
            }
            if ($binary === 'mysqldump') {
                return [...$command, '--clean', '--if-exists', '--no-owner', '--no-privileges'];
            }

            // Recreate the configured schema inside the same transaction as the
            // restore. This also removes objects introduced by a partially run
            // migration, while avoiding privileges to drop the entire database.
            if (($database['search_path'] ?? 'public') !== 'public') {
                throw new RuntimeException('Automatic PostgreSQL rollback currently requires the public schema.');
            }

            return [
                ...$command,
                '--set=ON_ERROR_STOP=1',
                '--single-transaction',
                '--command=DROP SCHEMA public CASCADE; CREATE SCHEMA public;',
                '--file=-',
            ];
        }

        $command = [$binary, '--host='.(string) ($database['host'] ?? '127.0.0.1'), '--user='.(string) ($database['username'] ?? '')];
        if (! empty($database['port'])) {
            $command[] = '--port='.(string) $database['port'];
        }
        if (! empty($database['unix_socket'])) {
            $command[] = '--socket='.(string) $database['unix_socket'];
        }
        if ($binary === 'mysqldump') {
            $command[] = '--single-transaction';
            $command[] = '--skip-lock-tables';
            $command[] = '--no-tablespaces';
            $command[] = '--add-drop-database';
            $command[] = '--databases';
            $command[] = (string) ($database['database'] ?? '');
        } else {
            // The dump selects and recreates the database so rollback also removes
            // tables introduced by a partially applied migration.
            return $command;
        }

        return $command;
    }

    private function databaseEnvironment(): array
    {
        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");
        if ($driver === 'pgsql') {
            return [
                'PGPASSWORD' => (string) config("database.connections.{$connection}.password"),
                'PGSSLMODE' => (string) config("database.connections.{$connection}.sslmode", 'prefer'),
            ];
        }

        return ['MYSQL_PWD' => (string) config("database.connections.{$connection}.password")];
    }

    private function assertDatabaseUtilitiesAvailable(): void
    {
        $required = match ($this->databaseDriver()) {
            'mysql', 'mariadb' => ['mysqldump', 'mysql'],
            'pgsql' => ['pg_dump', 'psql'],
            'sqlite' => [],
            default => throw new RuntimeException('Automatic Panel updates require a MySQL, MariaDB, PostgreSQL, or SQLite database.'),
        };

        foreach ($required as $executable) {
            if ($this->findExecutable($executable) === null) {
                throw new RuntimeException("The required database utility [{$executable}] is not available.");
            }
        }

        if ($this->databaseDriver() === 'sqlite') {
            $databasePath = $this->sqliteDatabasePath();
            if (! is_file($databasePath) || ! is_readable($databasePath) || ! is_writable($databasePath)) {
                throw new RuntimeException('The SQLite database must be a readable and writable file before updating.');
            }
        }
    }

    private function databaseDriver(): string
    {
        $connection = (string) config('database.default');

        return (string) config("database.connections.{$connection}.driver");
    }

    private function sqliteDatabasePath(): string
    {
        $connection = (string) config('database.default');
        $database = (string) config("database.connections.{$connection}.database");
        if ($database === '' || $database === ':memory:') {
            throw new RuntimeException('Automatic Panel updates require a file-backed SQLite database.');
        }

        return realpath($database) ?: $database;
    }

    protected function findExecutable(string $name): ?string
    {
        return (new ExecutableFinder())->find($name);
    }

    /**
     * @param  resource|string|null  $input
     */
    protected function runProcess(
        array $command,
        array $environment = [],
        int $timeout = 120,
        mixed $input = null,
        ?string $outputPath = null,
    ): string {
        $inheritedEnvironment = getenv();
        if (is_array($inheritedEnvironment)) {
            $environment = array_replace($inheritedEnvironment, $environment);
        }

        $process = new Process($command, $this->basePath, $environment, $input, $timeout);
        if ($outputPath !== null) {
            $output = fopen($outputPath, 'w');
            if ($output === false) {
                throw new RuntimeException('Unable to create the database backup file.');
            }
            try {
                $process->run(function (string $type, string $buffer) use ($output): void {
                    if ($type === Process::OUT) {
                        fwrite($output, $buffer);
                    }
                });
            } finally {
                fclose($output);
            }
        } else {
            $process->run();
        }

        if (! $process->isSuccessful()) {
            throw new RuntimeException(sprintf(
                'Command %s failed: %s',
                $process->getCommandLine(),
                trim($process->getErrorOutput() ?: $process->getOutput()),
            ));
        }

        return $process->getOutput();
    }
}
