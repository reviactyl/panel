<?php

namespace Tests\Unit\Models;

use App\Models\Node;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class NodeTest extends TestCase
{
    #[DataProvider('connectionHosts')]
    public function test_connection_address_preserves_host_and_port(string $fqdn, string $host): void
    {
        foreach (['http', 'https'] as $scheme) {
            $node = new Node(['fqdn' => $fqdn, 'scheme' => $scheme, 'daemonListen' => 8080]);

            $address = $node->getConnectionAddress();
            $this->assertSame($scheme.'://'.$host.':8080', $address);

            $uri = new Uri($address);
            $this->assertSame($host, $uri->getHost());
            $this->assertSame(8080, $uri->getPort());
        }
    }

    #[DataProvider('overallocationLimits')]
    public function test_viability_honors_each_resource_limit(int|float|string $memoryOverallocation, int|float|string $diskOverallocation, int $memory, int $disk, bool $viable): void
    {
        $node = new Node([
            'memory' => 2048,
            'disk' => 2048,
            'memory_overallocate' => $memoryOverallocation,
            'disk_overallocate' => $diskOverallocation,
        ]);
        $node->sum_memory = 2048;
        $node->sum_disk = 2048;

        $this->assertSame($viable, $node->isViable($memory, $disk));
    }

    public static function overallocationLimits(): array
    {
        return [
            'numeric strings unlimited' => ['-1', '-1', 8192, 8192, true],
            'fractional percentage boundary' => [12.5, 12.5, 256, 256, true],
            'fractional percentage exceeded' => [12.5, 12.5, 257, 257, false],
            'both unlimited' => [-1, -1, 8192, 8192, true],
            'unlimited memory' => [-1, 0, 8192, 0, true],
            'unlimited disk' => [0, -1, 0, 8192, true],
            'finite disk still enforced' => [-1, 0, 8192, 1, false],
            'finite memory still enforced' => [0, -1, 1, 8192, false],
            'zero overallocation boundary' => [0, 0, 0, 0, true],
            'zero overallocation exceeded' => [0, 0, 1, 1, false],
            'percentage boundary' => [50, 50, 1024, 1024, true],
            'percentage memory exceeded' => [50, 50, 1025, 1024, false],
            'percentage disk exceeded' => [50, 50, 1024, 1025, false],
        ];
    }

    public static function connectionHosts(): array
    {
        return [
            ['node.example.com', 'node.example.com'],
            ['192.0.2.1', '192.0.2.1'],
            ['2001:db8::1', '[2001:db8::1]'],
            ['[2001:db8::1]', '[2001:db8::1]'],
            ['::1', '[::1]'],
            ['::ffff:192.0.2.1', '[::ffff:192.0.2.1]'],
        ];
    }
}
