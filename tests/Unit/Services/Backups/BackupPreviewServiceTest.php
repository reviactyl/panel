<?php

namespace Tests\Unit\Services\Backups;

use App\Services\Backups\BackupPreviewService;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\PumpStream;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Sleep;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;
use ZipArchive;

class BackupPreviewServiceTest extends TestCase
{
    private array $files = [];

    protected function tearDown(): void
    {
        foreach ($this->files as $file) {
            @unlink($file);
        }
        parent::tearDown();
    }

    public static function formats(): array
    {
        return [['zip'], ['tar.gz']];
    }

    #[DataProvider('formats')]
    public function test_browses_implicit_folders_and_reads_archived_text_without_modifying_archive(string $format): void
    {
        $file = $this->archive($format, ['z.txt' => 'last', 'config/server.properties' => "motd=<script>alert(1)</script>\n", 'config/empty.txt' => '', 'é space.txt' => 'utf8']);
        $hash = hash_file('sha256', $file);
        $service = new BackupPreviewService();
        $this->assertSame([
            ['name' => 'config', 'is_directory' => true, 'size' => null, 'preview_status' => null],
            ['name' => 'z.txt', 'is_directory' => false, 'size' => 4, 'preview_status' => 'text'],
            ['name' => 'é space.txt', 'is_directory' => false, 'size' => 4, 'preview_status' => 'text'],
        ], $service->inspect($file, $format, '', false)['entries']);
        $this->assertCount(2, $service->inspect($file, $format, 'config', false)['entries']);
        $this->assertSame(['status' => 'text', 'contents' => "motd=<script>alert(1)</script>\n"], $service->inspect($file, $format, 'config/server.properties', true));
        $this->assertSame(['status' => 'text', 'contents' => ''], $service->inspect($file, $format, 'config/empty.txt', true));
        $this->assertSame($hash, hash_file('sha256', $file));
    }

    #[DataProvider('formats')]
    public function test_rejects_binary_and_large_text_without_returning_contents(string $format): void
    {
        $file = $this->archive($format, ['binary' => "abc\0def", 'invalid' => "\xff", 'large' => str_repeat('a', BackupPreviewService::MAX_FILE_BYTES + 1)]);
        $service = new BackupPreviewService();
        $this->assertSame(['status' => 'binary'], $service->inspect($file, $format, 'binary', true));
        $this->assertSame(['status' => 'binary'], $service->inspect($file, $format, 'invalid', true));
        $this->assertSame(['status' => 'large'], $service->inspect($file, $format, 'large', true));
    }

    #[DataProvider('formats')]
    public function test_hides_unsafe_archive_paths_and_returns_not_found_for_missing_files(string $format): void
    {
        $file = $this->archive($format, ['../escape' => 'secret', '/absolute' => 'secret', 'safe' => 'ok']);
        $service = new BackupPreviewService();
        $this->assertSame([['name' => 'safe', 'is_directory' => false, 'size' => 2, 'preview_status' => 'text']], $service->inspect($file, $format, '', false)['entries']);
        $this->expectException(HttpException::class);
        $service->inspect($file, $format, 'missing', true);
    }

    #[DataProvider('formats')]
    public function test_empty_folders_are_browsable_and_unpreviewable_files_are_identified_in_listing(string $format): void
    {
        $file = $this->archive($format, ['empty/' => '', 'binary.dat' => "abc\0def", 'large.log' => str_repeat('x', BackupPreviewService::MAX_FILE_BYTES + 1)]);
        $service = new BackupPreviewService();
        $this->assertSame(['entries' => []], $service->inspect($file, $format, 'empty', false));
        $entries = $service->inspect($file, $format, '', false)['entries'];
        $this->assertSame('binary', $entries[1]['preview_status']);
        $this->assertSame('large', $entries[2]['preview_status']);
    }

    public function test_download_failures_do_not_expose_signed_urls(): void
    {
        $handler = new MockHandler([new Response(403)]);
        $service = new BackupPreviewService(new Client(['handler' => HandlerStack::create($handler)]));
        try {
            $service->preview('https://node.example/download?token=secret', 'zip', '', false, 'test-failure');
            $this->fail('Expected a controlled upstream error.');
        } catch (HttpException $exception) {
            $this->assertSame(502, $exception->getStatusCode());
            $this->assertStringNotContainsString('secret', $exception->getMessage());
            $this->assertNull($exception->getPrevious());
        }
    }

    public static function invalidDownloads(): array
    {
        return [[302, false], [200, true]];
    }

    #[DataProvider('invalidDownloads')]
    public function test_redirected_or_incomplete_downloads_are_not_cached(int $status, bool $truncated): void
    {
        $body = gzencode(str_repeat("\0", 1024));
        $handler = new MockHandler([new Response($status, ['Content-Length' => strlen($body) + ($truncated ? 1 : 0)], $body)]);
        $service = new BackupPreviewService(new Client(['handler' => HandlerStack::create($handler)]));
        $key = 'invalid-'.bin2hex(random_bytes(8));
        $file = storage_path('framework/cache/backup-previews/').hash('sha256', $key).'.archive';
        try {
            $service->preview('https://node.example/download', 'tar.gz', '', false, $key);
            $this->fail('Expected the invalid download to be rejected.');
        } catch (HttpException $exception) {
            $this->assertSame(502, $exception->getStatusCode());
            $this->assertFileDoesNotExist($file);
            $this->assertFileDoesNotExist($file.'.part');
        }
    }

    public function test_reuses_private_cached_archive_without_refetching_signed_url(): void
    {
        $file = $this->archive('zip', ['config/a.txt' => 'archived']);
        $history = [];
        $stack = HandlerStack::create(new MockHandler([new Response(200, [], file_get_contents($file))]));
        $stack->push(Middleware::history($history));
        $service = new BackupPreviewService(new Client(['handler' => $stack]));
        $key = 'test-'.bin2hex(random_bytes(8));
        $cacheFile = storage_path('framework/cache/backup-previews/').hash('sha256', $key).'.archive';
        if (! is_dir(dirname($cacheFile))) {
            mkdir(dirname($cacheFile), 0700, true);
        }
        file_put_contents($cacheFile.'.part', 'interrupted download');
        try {
            $this->assertCount(1, $service->preview('https://node.example/download?token=first', 'zip', '', false, $key)['entries']);
            $this->assertSame(['status' => 'text', 'contents' => 'archived'], $service->preview('https://node.example/download?token=second', 'zip', 'config/a.txt', true, $key));
            $this->assertCount(1, $history);
            $this->assertFileDoesNotExist($cacheFile.'.part');
            $this->assertSame(0600, fileperms($cacheFile) & 0777);
        } finally {
            @unlink($cacheFile);
        }
    }

    public function test_rejects_path_traversal_in_request(): void
    {
        $this->expectException(HttpException::class);
        (new BackupPreviewService())->inspect('/not/opened', 'zip', '../secret', true);
    }

    public function test_does_not_follow_zip_symlinks(): void
    {
        $file = $this->archive('zip', ['link' => '/etc/passwd', 'file' => 'ok']);
        $zip = new ZipArchive();
        $zip->open($file);
        $zip->setExternalAttributesName('link', ZipArchive::OPSYS_UNIX, 0120777 << 16);
        $zip->close();
        $this->assertSame([['name' => 'file', 'is_directory' => false, 'size' => 2, 'preview_status' => 'text']], (new BackupPreviewService())->inspect($file, 'zip', '', false)['entries']);
    }

    public function test_slow_download_does_not_block_another_cached_backup(): void
    {
        $file = $this->archive('zip', ['a.txt' => 'cached']);
        $key = 'test-'.bin2hex(random_bytes(8));
        $this->files[] = storage_path('framework/cache/backup-previews/').hash('sha256', $key).'.archive';
        $cached = new BackupPreviewService(new Client(['handler' => HandlerStack::create(new MockHandler([new Response(200, [], file_get_contents($file))]))]));
        $cached->preview('https://node.example/cached', 'zip', '', false, $key);
        $otherKey = 'test-'.bin2hex(random_bytes(8));
        $this->files[] = storage_path('framework/cache/backup-previews/').hash('sha256', $otherKey).'.archive';
        $handler = new MockHandler([function () use ($cached, $key, $file) {
            $this->assertSame(['status' => 'text', 'contents' => 'cached'], $cached->preview('https://node.example/cached', 'zip', 'a.txt', true, $key));

            return new Response(200, [], file_get_contents($file));
        }]);
        $service = new BackupPreviewService(new Client(['handler' => HandlerStack::create($handler)]));
        $this->assertCount(1, $service->preview('https://node.example/slow', 'zip', '', false, $otherKey)['entries']);
    }

    public function test_cleanup_does_not_remove_an_expired_archive_in_use(): void
    {
        $file = $this->archive('zip', ['a.txt' => 'cached']);
        $id = hash('sha256', bin2hex(random_bytes(8)));
        $directory = storage_path('framework/cache/backup-previews');
        if (! is_dir($directory)) {
            mkdir($directory, 0700, true);
        }
        $active = $directory.'/'.$id.'.archive';
        copy($file, $active);
        touch($active, time() - 600);
        $this->files[] = $active;
        $lock = Cache::lock('backup-preview:'.$id, 120);
        $this->assertTrue($lock->get());
        $key = 'test-'.bin2hex(random_bytes(8));
        $this->files[] = $directory.'/'.hash('sha256', $key).'.archive';
        try {
            $service = new BackupPreviewService(new Client(['handler' => HandlerStack::create(new MockHandler([new Response(200, [], file_get_contents($file))]))]));
            $service->preview('https://node.example/other', 'zip', '', false, $key);
            $this->assertFileExists($active);
        } finally {
            $lock->release();
        }
    }

    public function test_uncached_preview_waits_for_an_active_cache_slot_to_be_released(): void
    {
        $locks = $this->occupyCache();
        Sleep::fake();
        $waits = 0;
        Sleep::whenFakingSleep(function () use ($locks, &$waits) {
            $cleanup = Cache::lock('backup-preview-cleanup', 10);
            $this->assertTrue($cleanup->get());
            $cleanup->release();
            if (++$waits === 2) {
                $locks[0]->release();
            }
        });
        $file = $this->archive('zip', ['a.txt' => 'queued']);
        $key = 'queued-'.bin2hex(random_bytes(8));
        $cached = storage_path('framework/cache/backup-previews/').hash('sha256', $key).'.archive';
        $this->files[] = $cached;
        $handler = new MockHandler([function () use ($file) {
            $this->assertCount(3, glob(storage_path('framework/cache/backup-previews/*.archive*')));

            return new Response(200, [], file_get_contents($file));
        }]);
        try {
            $service = new BackupPreviewService(new Client(['handler' => HandlerStack::create($handler)]));
            $this->assertSame(['status' => 'text', 'contents' => 'queued'], $service->preview('https://node.example/queued', 'zip', 'a.txt', true, $key));
            Sleep::assertSleptTimes(2);
        } finally {
            Sleep::fake(false);
            foreach ($locks as $lock) {
                $lock->release();
            }
        }
    }

    public function test_cache_slot_wait_is_bounded_and_does_not_start_a_download_when_full(): void
    {
        $locks = $this->occupyCache();
        Sleep::fake();
        $key = 'timeout-'.bin2hex(random_bytes(8));
        $cached = storage_path('framework/cache/backup-previews/').hash('sha256', $key).'.archive';
        $service = new BackupPreviewService(new Client(['handler' => HandlerStack::create(new MockHandler())]));
        try {
            try {
                $service->preview('https://node.example/queued', 'zip', '', false, $key);
                $this->fail('Expected the capacity wait to expire.');
            } catch (HttpException $exception) {
                $this->assertSame(503, $exception->getStatusCode());
            }
            Sleep::assertSleptTimes(120);
            $this->assertFileDoesNotExist($cached);
            $this->assertFileDoesNotExist($cached.'.part');
            $this->assertCount(3, glob(storage_path('framework/cache/backup-previews/*.archive*')));
        } finally {
            Sleep::fake(false);
            foreach ($locks as $lock) {
                $lock->release();
            }
        }
    }

    private function occupyCache(): array
    {
        $directory = storage_path('framework/cache/backup-previews');
        if (! is_dir($directory)) {
            mkdir($directory, 0700, true);
        }
        $locks = [];
        for ($i = 0; $i < 3; $i++) {
            $id = hash('sha256', bin2hex(random_bytes(8)));
            $file = $directory.'/'.$id.'.archive.part';
            file_put_contents($file, 'active download');
            $this->files[] = $file;
            $lock = Cache::lock('backup-preview:'.$id, 180);
            $this->assertTrue($lock->get());
            $locks[] = $lock;
        }

        return $locks;
    }

    public static function downloadSizes(): array
    {
        return [[BackupPreviewService::MAX_ARCHIVE_BYTES, true], [BackupPreviewService::MAX_ARCHIVE_BYTES + 1, false]];
    }

    #[DataProvider('downloadSizes')]
    public function test_download_limit_and_partial_cleanup(int $bytes, bool $allowed): void
    {
        $body = new PumpStream(function (int $length) use (&$bytes) {
            if ($bytes === 0) {
                return false;
            }
            $length = min($length, $bytes);
            $bytes -= $length;

            return str_repeat('x', $length);
        });
        $client = new Client(['handler' => HandlerStack::create(new MockHandler([new Response(200, [], $body)]))]);
        $service = new class($client) extends BackupPreviewService
        {
            public function inspect(string $filename, string $format, string $path, bool $contents): array
            {
                return ['entries' => []];
            }
        };
        $key = 'limit-'.bin2hex(random_bytes(8));
        $file = storage_path('framework/cache/backup-previews/').hash('sha256', $key).'.archive';
        $this->files[] = $file;
        try {
            $this->assertSame(['entries' => []], $service->preview('https://node.example/limit', 'zip', '', false, $key));
            $this->assertTrue($allowed);
            $this->assertSame(BackupPreviewService::MAX_ARCHIVE_BYTES, filesize($file));
        } catch (HttpException $exception) {
            $this->assertFalse($allowed);
            $this->assertSame(422, $exception->getStatusCode());
            $this->assertSame(__('server/backups.preview-limit'), $exception->getMessage());
            $this->assertFileDoesNotExist($file);
        }
        $this->assertFileDoesNotExist($file.'.part');
    }

    public static function expandedSizes(): array
    {
        return [[BackupPreviewService::MAX_EXPANDED_BYTES, true], [BackupPreviewService::MAX_EXPANDED_BYTES + 1, false]];
    }

    #[DataProvider('expandedSizes')]
    public function test_gzip_expansion_limit(int $bytes, bool $allowed): void
    {
        $file = tempnam(sys_get_temp_dir(), 'backup-expansion-');
        $this->files[] = $file;
        $gzip = gzopen($file, 'wb1');
        $chunk = str_repeat("\0", 1048576);
        while ($bytes > 0) {
            $length = min($bytes, strlen($chunk));
            gzwrite($gzip, substr($chunk, 0, $length));
            $bytes -= $length;
        }
        gzclose($gzip);
        try {
            $this->assertSame(['entries' => []], (new BackupPreviewService())->inspect($file, 'tar.gz', '', false));
            $this->assertTrue($allowed);
        } catch (HttpException $exception) {
            $this->assertFalse($allowed);
            $this->assertSame(422, $exception->getStatusCode());
            $this->assertSame(__('server/backups.preview-limit'), $exception->getMessage());
        }
    }

    #[DataProvider('expandedSizes')]
    public function test_zip_expansion_limit(int $bytes, bool $allowed): void
    {
        $source = tempnam(sys_get_temp_dir(), 'backup-expanded-source-');
        $this->files[] = $source;
        $handle = fopen($source, 'wb');
        ftruncate($handle, $bytes);
        fclose($handle);
        $file = tempnam(sys_get_temp_dir(), 'backup-expanded-zip-');
        $this->files[] = $file;
        $zip = new ZipArchive();
        $zip->open($file, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFile($source, 'large.bin');
        $zip->setCompressionName('large.bin', ZipArchive::CM_DEFLATE, 1);
        $zip->close();
        try {
            $this->assertCount(1, (new BackupPreviewService())->inspect($file, 'zip', '', false)['entries']);
            $this->assertTrue($allowed);
        } catch (HttpException $exception) {
            $this->assertFalse($allowed);
            $this->assertSame(422, $exception->getStatusCode());
            $this->assertSame(__('server/backups.preview-limit'), $exception->getMessage());
        }
    }

    public static function entryCounts(): array
    {
        return [['zip', 10000, true], ['zip', 10001, false], ['tar.gz', 10000, true], ['tar.gz', 10001, false]];
    }

    #[DataProvider('entryCounts')]
    public function test_archive_entry_limit(string $format, int $count, bool $allowed): void
    {
        $entries = [];
        for ($i = 0; $i < $count; $i++) {
            $entries['file-'.$i] = '';
        }
        $file = $this->archive($format, $entries);
        try {
            $this->assertCount($count, (new BackupPreviewService())->inspect($file, $format, '', false)['entries']);
            $this->assertTrue($allowed);
        } catch (HttpException $exception) {
            $this->assertFalse($allowed);
            $this->assertSame(422, $exception->getStatusCode());
            $this->assertSame(__('server/backups.preview-limit'), $exception->getMessage());
        }
    }

    private function archive(string $format, array $entries): string
    {
        $file = tempnam(sys_get_temp_dir(), 'backup-preview-test-');
        $this->files[] = $file;
        if ($format === 'zip') {
            $zip = new ZipArchive();
            $zip->open($file, ZipArchive::CREATE | ZipArchive::OVERWRITE);
            foreach ($entries as $name => $value) {
                $zip->addFromString($name, $value);
            }
            $zip->close();
        } else {
            $tar = '';
            foreach ($entries as $name => $value) {
                $header = str_pad($name, 100, "\0").str_repeat("0000000\0", 3).sprintf('%011o', strlen($value))."\0".str_repeat('0', 11)."\0".str_repeat(' ', 8).(str_ends_with($name, '/') ? '5' : '0');
                $header = str_pad($header, 512, "\0");
                $checksum = array_sum(unpack('C*', $header));
                $header = substr_replace($header, sprintf('%06o', $checksum)."\0 ", 148, 8);
                $tar .= $header.$value.str_repeat("\0", (512 - strlen($value) % 512) % 512);
            }
            file_put_contents($file, gzencode($tar.str_repeat("\0", 1024)));
        }

        return $file;
    }
}
