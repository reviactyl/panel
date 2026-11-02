<?php

namespace Tests\Unit\Services\Backups;

use App\Services\Backups\BackupPreviewService;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
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
