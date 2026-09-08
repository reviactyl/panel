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
