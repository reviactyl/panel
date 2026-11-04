<?php

namespace Tests\Unit\Services\Databases;

use App\Exceptions\Service\Database\DatabaseImportException;
use App\Extensions\DynamicDatabaseConnection;
use App\Models\Database;
use App\Models\DatabaseHost;
use App\Repositories\Eloquent\DatabaseRepository;
use App\Services\Databases\DatabaseImportService;
use App\Services\Databases\Transfer\DatabaseConnectionFactory;
use App\Services\Databases\Transfer\DatabaseDumper;
use App\Services\Databases\Transfer\SqlStatementReader;
use Mockery;
use PDO;
use PDOStatement;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DatabaseImportServiceTest extends TestCase
{
    #[DataProvider('wipeDataProvider')]
    public function test_remote_dump_finishes_before_the_destination_is_changed(bool $wipe)
    {
        $database = new Database(['database' => 'same_database']);
        $source = Mockery::mock(PDO::class);
        $target = Mockery::mock(PDO::class);
        $captured = false;
        $expected = [
            'SET NAMES utf8mb4',
            'DROP TABLE IF EXISTS `items`',
            'CREATE TABLE `items` (`value` LONGTEXT)',
            "INSERT INTO `items` VALUES ('".str_repeat('x', 3 * 1024 * 1024)."')",
            "CREATE PROCEDURE `read_items`() BEGIN SELECT 'a; b'; SELECT * FROM `items`; END",
        ];
        $connections = Mockery::mock(DatabaseConnectionFactory::class);
        $connections->expects('forRemote')->with('alias.example.com', 3306, 'same_database', 'user', '')->andReturn($source);
        $connections->expects('import')->with($database, Mockery::type(\Closure::class))->andReturnUsing(function ($database, $import) use (&$captured, $target) {
            $this->assertTrue($captured);

            return $import($target);
        });
        $dumper = Mockery::mock(DatabaseDumper::class);
        $dumper->expects('statements')->with($source)->andReturn((function () use ($expected, &$captured) {
            yield from $expected;
            $captured = true;
        })());
        $executed = [];
        $target->shouldReceive('query')->andReturnUsing(function (string $statement) use (&$executed) {
            $executed[] = $statement;
            $result = Mockery::mock(PDOStatement::class);
            $result->expects('closeCursor')->andReturn(true);

            return $result;
        });
        $dynamic = Mockery::mock(DynamicDatabaseConnection::class);
        $repository = Mockery::mock(DatabaseRepository::class);
        if ($wipe) {
            $database->setRelation('host', new DatabaseHost());
            $dynamic->expects('set')->with('dynamic', $database->host);
            $repository->shouldReceive('createDatabase')->with('same_database')->twice();
            $repository->expects('dropDatabase')->with('same_database');
            $target->expects('exec')->with('USE `same_database`')->andReturn(0);
        } else {
            $repository->shouldNotReceive('dropDatabase');
        }
        $service = new DatabaseImportService($connections, $dumper, new SqlStatementReader(), $dynamic, $repository);

        $this->assertSame(count($expected), $service->fromRemote($database, [
            'host' => 'alias.example.com', 'port' => 3306, 'database' => 'same_database', 'username' => 'user',
        ], $wipe));
        $this->assertSame($expected, $executed);
    }

    #[DataProvider('wipeDataProvider')]
    public function test_remote_read_failure_leaves_the_destination_untouched(bool $wipe)
    {
        $connections = Mockery::mock(DatabaseConnectionFactory::class);
        $source = Mockery::mock(PDO::class);
        $connections->expects('forRemote')->andReturn($source);
        $connections->shouldNotReceive('import');
        $dumper = Mockery::mock(DatabaseDumper::class);
        $dumper->expects('statements')->with($source)->andReturn((function () {
            yield 'SET NAMES utf8mb4';
            yield 'DROP TABLE IF EXISTS `items`';
            throw new \PDOException('Source disconnected');
        })());
        $repository = Mockery::mock(DatabaseRepository::class);
        $repository->shouldNotReceive('dropDatabase');
        $service = new DatabaseImportService($connections, $dumper, new SqlStatementReader(), Mockery::mock(DynamicDatabaseConnection::class), $repository);

        try {
            $service->fromRemote(new Database(), [
                'host' => 'alias.example.com', 'port' => 3306, 'database' => 'same_database', 'username' => 'user',
            ], $wipe);
            $this->fail('Expected a remote read failure.');
        } catch (DatabaseImportException $exception) {
            $this->assertSame(DatabaseImportException::REMOTE_READ_FAILED, $exception->getReason());
        }
    }

    public function test_remote_capture_reports_progress_before_applying_statements()
    {
        $source = Mockery::mock(PDO::class);
        $target = Mockery::mock(PDO::class);
        $connections = Mockery::mock(DatabaseConnectionFactory::class);
        $connections->expects('forRemote')->andReturn($source);
        $connections->expects('import')->andReturnUsing(fn ($database, $import) => $import($target));
        $dumper = Mockery::mock(DatabaseDumper::class);
        $dumper->expects('statements')->with($source)->andReturn((function () {
            yield 'SET NAMES utf8mb4';
            usleep(1100000);
            yield 'SELECT 1';
            yield 'SELECT 2';
        })());
        $events = [];
        $target->shouldReceive('query')->andReturnUsing(function ($statement) use (&$events) {
            $events[] = ['apply', $statement];
            $result = Mockery::mock(PDOStatement::class);
            $result->expects('closeCursor')->andReturn(true);

            return $result;
        });
        $service = new DatabaseImportService($connections, $dumper, new SqlStatementReader(), Mockery::mock(DynamicDatabaseConnection::class), Mockery::mock(DatabaseRepository::class));
        $service->fromRemote(new Database(), [
            'host' => 'alias.example.com', 'port' => 3306, 'database' => 'source', 'username' => 'user',
        ], false, function ($count) use (&$events) {
            $events[] = ['import', $count];
        }, function ($count) use (&$events) {
            $events[] = ['capture', $count];
        });

        $this->assertSame([
            ['capture', 0], ['capture', 2], ['capture', 3], ['import', 0],
            ['apply', 'SET NAMES utf8mb4'], ['apply', 'SELECT 1'], ['apply', 'SELECT 2'],
        ], $events);
    }

    public static function wipeDataProvider(): array
    {
        return [[false], [true]];
    }
}
