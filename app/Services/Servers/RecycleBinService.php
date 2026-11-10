<?php

namespace App\Services\Servers;

use App\Models\Server;
use App\Repositories\Agent\DaemonFileRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class RecycleBinService
{
    public const DIRECTORY = '.recycle-bin';

    public function __construct(private DaemonFileRepository $files) {}

    public static function retentionDays(): int
    {
        $days = filter_var(config('panel.files.recycle_bin_retention_days', 31), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 3650]]);

        return $days === false ? 31 : $days;
    }

    public function path(string $path): string
    {
        $parts = [];
        foreach (explode('/', $path) as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }
            if ($part === '..' || str_contains($part, "\0")) {
                throw new BadRequestHttpException('Invalid file path.');
            }
            $parts[] = $part;
        }
        if (! $parts || $parts[0] === self::DIRECTORY) {
            throw new BadRequestHttpException('Use the recycle bin to manage recycled files.');
        }

        return '/'.implode('/', $parts);
    }

    public function listing(Server $server): array
    {
        $this->files->setServer($server);
        $entries = DB::table('recycled_files')->where('server_id', $server->id)->orderByDesc('created_at')->get();
        $data = [];
        foreach ($entries as $entry) {
            $path = $this->storagePath($entry->id);
            $available = $this->exists($path);
            $size = $available ? $this->size($path) : null;
            $data[] = [
                'id' => $entry->id,
                'original_path' => $entry->original_path,
                'storage_path' => $path,
                'created_at' => Carbon::parse($entry->created_at)->toIso8601String(),
                'size' => $size,
                'is_file' => $available ? (collect($this->files->getDirectory(dirname($path)))->firstWhere('name', 'file')['file'] ?? true) : true,
            ];
        }

        return ['data' => $data, 'size' => array_sum(array_column($data, 'size')), 'retention_days' => self::retentionDays()];
    }

    private function size(string $path): int
    {
        $entry = collect($this->files->getDirectory(dirname($path)))->firstWhere('name', basename($path));
        if (! $entry) {
            return 0;
        }
        if (($entry['file'] ?? true) || ($entry['symlink'] ?? false)) {
            return (int) ($entry['size'] ?? 0);
        }
        $size = 0;
        $directories = [$path];
        while ($directories) {
            $directory = array_pop($directories);
            foreach ($this->files->getDirectory($directory) as $child) {
                if (! ($child['file'] ?? true) && ! ($child['symlink'] ?? false)) {
                    $directories[] = $directory.'/'.$child['name'];
                } else {
                    $size += (int) ($child['size'] ?? 0);
                }
            }
        }

        return $size;
    }

    public function recycle(Server $server, string $path): void
    {
        $path = $this->path($path);
        $this->files->setServer($server);
        if (! $this->exists($path)) {
            throw new ConflictHttpException('The selected file no longer exists. Refresh and try again.');
        }
        $id = (string) Str::uuid();
        $this->exists($this->storagePath($id));
        // Persist recovery information before moving data, including when Agent times out.
        DB::table('recycled_files')->insert([
            'id' => $id, 'server_id' => $server->id, 'original_path' => $path, 'created_at' => now(),
        ]);
        $this->files->createDirectory($id, '/'.self::DIRECTORY);
        $this->move($path, $this->storagePath($id));
    }

    public function restore(Server $server, string $id, bool $replace, bool $preview = false, string $relativePath = ''): array
    {
        $entry = DB::table('recycled_files')->where('server_id', $server->id)->where('id', $id)->first();
        abort_unless($entry !== null, 404);
        $this->files->setServer($server);
        $relativePath = $this->relativePath($relativePath);
        $path = $this->path($entry->original_path.$relativePath);
        $source = $this->storagePath($id).$relativePath;
        if (! $this->exists($source)) {
            throw new ConflictHttpException('The recycled file is missing. It may have been moved outside the panel.');
        }
        $exists = $this->exists($path);
        if ($preview) {
            return ['conflict' => $exists ? $path : null];
        }
        if ($exists) {
            if (! $replace) {
                return ['conflict' => $path];
            }
            $this->recycle($server, $path);
        }
        if (dirname($path) !== '/') {
            $this->files->createDirectory(basename(dirname($path)), dirname(dirname($path)));
        }
        $this->move($source, $path);
        if ($relativePath === '') {
            DB::table('recycled_files')->where('id', $id)->delete();
        }

        return [];
    }

    public function purge(Server $server, string $id, string $relativePath = ''): void
    {
        $entry = DB::table('recycled_files')->where('server_id', $server->id)->where('id', $id)->first();
        abort_unless($entry !== null, 404);
        $this->files->setServer($server);
        $relativePath = $this->relativePath($relativePath);
        $source = $this->storagePath($id).$relativePath;
        $this->exists($source);
        if ($relativePath === '') {
            $this->files->deleteFiles('/'.self::DIRECTORY, [$id]);
        } else {
            $this->files->deleteFiles(dirname($source), [basename($source)]);
        }
        if ($this->exists($source)) {
            throw new ConflictHttpException('The recycled file could not be deleted. Try again.');
        }
        if ($relativePath === '') {
            DB::table('recycled_files')->where('id', $id)->delete();
        }
    }

    private function relativePath(string $path): string
    {
        if ($path === '') {
            return '';
        }
        if (str_starts_with($path, '/') || str_contains($path, "\0")) {
            throw new BadRequestHttpException('Invalid relative file path.');
        }
        foreach (explode('/', $path) as $part) {
            if ($part === '..' || $part === '.' || $part === '') {
                throw new BadRequestHttpException('Invalid relative file path.');
            }
        }

        return '/'.$path;
    }

    public function empty(Server $server): void
    {
        foreach (DB::table('recycled_files')->where('server_id', $server->id)->pluck('id') as $id) {
            $this->purge($server, $id);
        }
    }

    private function storagePath(string $id): string
    {
        return '/'.self::DIRECTORY.'/'.$id.'/file';
    }

    private function exists(string $path): bool
    {
        $directory = '/';
        foreach (explode('/', ltrim($path, '/')) as $part) {
            $entry = collect($this->files->getDirectory($directory))->firstWhere('name', $part);
            if (! $entry) {
                return false;
            }
            $directory = rtrim($directory, '/').'/'.$part;
            if ($directory !== $path && (($entry['file'] ?? true) || ($entry['symlink'] ?? false))) {
                throw new ConflictHttpException('A parent path is a file or symbolic link. Move it before restoring.');
            }
        }

        return true;
    }

    private function move(string $from, string $to): void
    {
        $this->files->renameFiles('/', [['from' => ltrim($from, '/'), 'to' => ltrim($to, '/')]]);
        if (! $this->exists($to)) {
            throw new ConflictHttpException('The file could not be moved. Refresh and try again.');
        }
    }
}
