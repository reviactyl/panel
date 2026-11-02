<?php

namespace App\Services\Backups;

use GuzzleHttp\Client;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use ZipArchive;

class BackupPreviewService
{
    public const MAX_ARCHIVE_BYTES = 134217728;

    public const MAX_EXPANDED_BYTES = 536870912;

    public const MAX_FILE_BYTES = 1048576;

    private const MAX_ENTRIES = 10000;

    public function __construct(private Client $client = new Client()) {}

    public function preview(string $url, string $format, string $path, bool $contents, string $cacheKey): array
    {
        try {
            return Cache::lock('backup-preview-cache', 120)->block(10, function () use ($url, $format, $path, $contents, $cacheKey) {
                $directory = storage_path('framework/cache/backup-previews');
                if (! is_dir($directory) && ! mkdir($directory, 0700, true) && ! is_dir($directory)) {
                    throw new \RuntimeException('Unable to create backup preview cache.');
                }
                foreach (glob($directory.'/*.part') as $partial) {
                    unlink($partial);
                }
                $filename = $directory.'/'.hash('sha256', $cacheKey).'.archive';
                foreach (glob($directory.'/*.archive') as $cached) {
                    if (filemtime($cached) < time() - 300) {
                        unlink($cached);
                    }
                }
                if (! is_file($filename)) {
                    $cached = glob($directory.'/*.archive');
                    usort($cached, fn ($a, $b) => filemtime($a) <=> filemtime($b));
                    while (count($cached) >= 3) {
                        unlink(array_shift($cached));
                    }
                    try {
                        $this->download($url, $filename.'.part');
                        if (! rename($filename.'.part', $filename)) {
                            throw new \RuntimeException('Unable to publish backup preview cache.');
                        }
                    } catch (\Throwable $exception) {
                        if (is_file($filename.'.part')) {
                            unlink($filename.'.part');
                        }
                        throw $exception;
                    }
                }

                return $this->inspect($filename, $format, $path, $contents);
            });
        } catch (LockTimeoutException) {
            throw new HttpException(503, __('server/backups.preview-unavailable'));
        } catch (\RuntimeException $exception) {
            if ($exception instanceof HttpExceptionInterface) {
                throw $exception;
            }
            throw new HttpException(502, __('server/backups.preview-unavailable'));
        }
    }

    private function download(string $url, string $filename): void
    {
        $file = fopen($filename, 'wb');
        if ($file === false) {
            throw new \RuntimeException('Unable to create backup preview temporary file.');
        }
        chmod($filename, 0600);
        try {
            $response = $this->client->get($url, [
                'stream' => true, 'timeout' => 30, 'read_timeout' => 10,
                'connect_timeout' => 5, 'allow_redirects' => false,
                'verify' => app()->environment('production'),
            ]);
            $body = $response->getBody();
            try {
                $deadline = microtime(true) + 30;
                $bytes = 0;
                while (! $body->eof()) {
                    $chunk = $body->read(65536);
                    $bytes += strlen($chunk);
                    if ($bytes > self::MAX_ARCHIVE_BYTES || microtime(true) > $deadline) {
                        $this->limit();
                    }
                    if (fwrite($file, $chunk) !== strlen($chunk)) {
                        throw new \RuntimeException('Unable to write backup preview temporary file.');
                    }
                }
            } finally {
                $body->close();
            }
        } finally {
            fclose($file);
        }
    }

    public function inspect(string $filename, string $format, string $path, bool $contents): array
    {
        $path = trim($path, '/');
        if ($path !== '' && $this->safePath($path) !== $path) {
            throw new UnprocessableEntityHttpException(__('server/backups.preview-unavailable'));
        }
        $entries = [];
        $found = $path === '';
        $result = null;
        $count = 0;
        $accept = function (string $name, int $size, bool $directory, callable $read) use ($path, $contents, &$entries, &$found, &$result, &$count): void {
            if (++$count > self::MAX_ENTRIES) {
                $this->limit();
            }
            $name = $this->safePath($name);
            if ($name === null || $name === '') {
                return;
            }
            if ($contents) {
                if ($name === $path && ! $directory) {
                    $found = true;
                    $result = $size > self::MAX_FILE_BYTES ? ['status' => 'large'] : $this->text($read());
                }

                return;
            }
            if ($name === $path && $directory) {
                $found = true;

                return;
            }
            $prefix = $path === '' ? '' : $path.'/';
            if (! str_starts_with($name, $prefix)) {
                return;
            }
            $relative = substr($name, strlen($prefix));
            if ($relative === '') {
                $found = $directory;

                return;
            }
            $found = true;
            $first = explode('/', $relative, 2)[0];
            $isDirectory = $directory || str_contains($relative, '/');
            if (! isset($entries[$first]) || $isDirectory) {
                $entries[$first] = [
                    'name' => $first, 'is_directory' => $isDirectory, 'size' => $isDirectory ? null : $size,
                    'preview_status' => $isDirectory ? null : ($size > self::MAX_FILE_BYTES ? 'large' : $this->text($read())['status']),
                ];
            }
        };

        if ($format === 'zip') {
            $zip = new ZipArchive();
            if ($zip->open($filename, ZipArchive::RDONLY) !== true) {
                throw new UnprocessableEntityHttpException(__('server/backups.preview-unavailable'));
            }
            try {
                if ($zip->numFiles > self::MAX_ENTRIES) {
                    $this->limit();
                }
                $expanded = 0;
                $deadline = microtime(true) + 15;
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $stat = $zip->statIndex($i);
                    if ($stat === false) {
                        throw new UnprocessableEntityHttpException(__('server/backups.preview-unavailable'));
                    }
                    $expanded += $stat['size'];
                    if ($expanded > self::MAX_EXPANDED_BYTES || microtime(true) > $deadline) {
                        $this->limit();
                    }
                    $zip->getExternalAttributesIndex($i, $opsys, $attributes);
                    $type = ($attributes >> 16) & 0170000;
                    if ($type !== 0 && $type !== 0100000 && $type !== 0040000) {
                        continue;
                    }
                    $accept($stat['name'], $stat['size'], str_ends_with($stat['name'], '/'), fn () => $zip->getFromIndex($i, self::MAX_FILE_BYTES + 1));
                }
            } finally {
                $zip->close();
            }
        } elseif ($format === 'tar.gz') {
            $this->tar($filename, $accept);
        } else {
            throw new UnprocessableEntityHttpException(__('server/backups.preview-unavailable'));
        }
        if (! $found) {
            abort(404);
        }
        if ($contents) {
            return $result ?? ['status' => 'binary'];
        }
        usort($entries, fn ($a, $b) => ($b['is_directory'] <=> $a['is_directory']) ?: strnatcasecmp($a['name'], $b['name']));

        return ['entries' => $entries];
    }

    private function tar(string $filename, callable $accept): void
    {
        $gzip = gzopen($filename, 'rb');
        $tar = tmpfile();
        if ($gzip === false || $tar === false) {
            throw new \RuntimeException('Unable to open backup preview temporary file.');
        }
        try {
            $bytes = 0;
            $deadline = microtime(true) + 15;
            while (! gzeof($gzip)) {
                $chunk = gzread($gzip, 65536);
                if ($chunk === false) {
                    throw new UnprocessableEntityHttpException(__('server/backups.preview-unavailable'));
                }
                $bytes += strlen($chunk);
                if ($bytes > self::MAX_EXPANDED_BYTES || microtime(true) > $deadline) {
                    $this->limit();
                }
                if (fwrite($tar, $chunk) !== strlen($chunk)) {
                    throw new \RuntimeException('Unable to write backup preview temporary file.');
                }
            }
            rewind($tar);
            $extended = [];
            $count = 0;
            while (($header = fread($tar, 512)) !== '' && $header !== str_repeat("\0", 512)) {
                if (strlen($header) !== 512 || ++$count > self::MAX_ENTRIES) {
                    $this->limit();
                }
                $checksum = trim(substr($header, 148, 8), "\0 ");
                $checkedHeader = substr_replace($header, str_repeat(' ', 8), 148, 8);
                if (! preg_match('/^[0-7]+$/D', $checksum) || (int) octdec($checksum) !== array_sum(unpack('C*', $checkedHeader))) {
                    throw new UnprocessableEntityHttpException(__('server/backups.preview-unavailable'));
                }
                $rawSize = trim(substr($header, 124, 12), "\0 ");
                if (! preg_match('/^[0-7]+$/D', $rawSize)) {
                    throw new UnprocessableEntityHttpException(__('server/backups.preview-unavailable'));
                }
                $size = (int) octdec($rawSize);
                $offset = ftell($tar);
                if ($size > $bytes - $offset) {
                    throw new UnprocessableEntityHttpException(__('server/backups.preview-unavailable'));
                }
                $type = $header[156];
                $name = rtrim(substr($header, 0, 100), "\0");
                $prefix = substr($header, 257, 6) === "ustar\0" ? rtrim(substr($header, 345, 155), "\0") : '';
                $name = ($prefix === '' ? '' : $prefix.'/').$name;
                if (in_array($type, ['x', 'g', 'L', 'K'], true)) {
                    if ($size > self::MAX_FILE_BYTES) {
                        $this->limit();
                    }
                    $data = $size === 0 ? '' : fread($tar, $size);
                    if ($type === 'L') {
                        $extended['path'] = rtrim($data, "\0\n");
                    } elseif ($type === 'x') {
                        if (preg_match('/(?:^|\n)[0-9]+ path=([^\n]*)\n/', $data, $match)) {
                            $extended['path'] = $match[1];
                        }
                    }
                } else {
                    $name = $extended['path'] ?? $name;
                    $extended = [];
                    if (in_array($type, ["\0", '0', '5'], true)) {
                        $accept($name, $size, $type === '5', fn () => $size === 0 ? '' : fread($tar, min($size, self::MAX_FILE_BYTES + 1)));
                    }
                }
                fseek($tar, $offset + (int) (ceil($size / 512) * 512));
            }
        } finally {
            gzclose($gzip);
            fclose($tar);
        }
    }

    private function safePath(string $path): ?string
    {
        while (str_starts_with($path, './')) {
            $path = substr($path, 2);
        }
        $path = rtrim($path, '/');
        if (! mb_check_encoding($path, 'UTF-8') || preg_match('/[\\\\\x00-\x1f\x7f]/', $path) || str_starts_with($path, '/') || array_intersect(explode('/', $path), ['..', '.', ''])) {
            return null;
        }

        return $path;
    }

    private function text(string|false $value): array
    {
        if ($value === false || ! mb_check_encoding($value, 'UTF-8') || preg_match('/[\x00-\x08\x0b\x0c\x0e-\x1f]/', $value)) {
            return ['status' => 'binary'];
        }

        return ['status' => 'text', 'contents' => $value];
    }

    private function limit(): never
    {
        throw new UnprocessableEntityHttpException(__('server/backups.preview-limit'));
    }
}
