<?php

namespace Tests\Unit\Services\Databases;

use App\Exceptions\Service\Database\DatabaseImportException;
use App\Services\Databases\Transfer\DatabaseConnectionFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DatabaseConnectionFactoryTest extends TestCase
{
    #[DataProvider('refusedHostDataProvider')]
    public function test_remote_hosts_that_are_not_public_are_refused(string $host, bool $allowPrivate)
    {
        config()->set('panel.client_features.databases.allow_private_remote_import', $allowPrivate);

        try {
            $this->app->make(DatabaseConnectionFactory::class)->forRemote($host, 3306, 'database', 'user', 'password');

            $this->fail('The remote host was not refused.');
        } catch (DatabaseImportException $exception) {
            $this->assertSame(DatabaseImportException::REMOTE_NOT_ALLOWED, $exception->getReason());
        }
    }

    public function test_remote_database_name_cannot_change_the_connection()
    {
        $this->expectExceptionObject(new DatabaseImportException(DatabaseImportException::REMOTE_UNKNOWN_DATABASE));

        $this->app->make(DatabaseConnectionFactory::class)->forRemote('203.0.113.10', 3306, 'database;host=127.0.0.1', 'user', 'password');
    }

    public static function refusedHostDataProvider(): array
    {
        return [
            'loopback' => ['127.0.0.1', true],
            'loopback ipv6' => ['::1', true],
            'ipv4 mapped loopback' => ['::ffff:127.0.0.1', true],
            'link local' => ['169.254.169.254', true],
            'unspecified' => ['0.0.0.0', true],
            'private class a' => ['10.0.0.5', false],
            'private class b' => ['172.16.0.5', false],
            'private class c' => ['192.168.1.5', false],
            'ipv4 mapped private' => ['::ffff:10.0.0.5', false],
            'unique local ipv6' => ['fd00::5', false],
        ];
    }
}
