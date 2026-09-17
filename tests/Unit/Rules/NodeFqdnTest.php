<?php

namespace Tests\Unit\Rules;

use App\Models\Node;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class NodeFqdnTest extends TestCase
{
    #[DataProvider('hosts')]
    public function test_node_validation_requires_a_host(mixed $host, bool $valid): void
    {
        $factory = new Factory(new Translator(new ArrayLoader(), 'en'));

        foreach ([Node::getRulesForField('fqdn'), Node::getRulesForUpdate(1)['fqdn']] as $rules) {
            $validator = $factory->make(['fqdn' => $host], ['fqdn' => $rules]);

            $this->assertSame($valid, $validator->passes());
        }
    }

    public static function hosts(): array
    {
        return [
            ['node.example.com', true],
            ['node-1.example.com', true],
            ['node.example.com.', true],
            ['localhost', true],
            ['192.0.2.1', true],
            ['2001:db8::1', true],
            ['[2001:db8::1]', true],
            ['https://node.example.com', false],
            ['http://node.example.com', false],
            ['//node.example.com', false],
            ['node.example.com:8080', false],
            ['[2001:db8::1]:8080', false],
            ['node.example.com/path', false],
            ['node.example.com?query=1', false],
            ['node.example.com#fragment', false],
            ['user@node.example.com', false],
            ['node.example.com\\path', false],
            ['node example.com', false],
            [' node.example.com', false],
            ['node.example.com\n', false],
            ['-node.example.com', false],
            ['', false],
            [null, false],
            [123, false],
            [['node.example.com'], false],
        ];
    }
}
