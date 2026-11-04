<?php

namespace Tests\Unit\Services\Databases;

use App\Services\Databases\Transfer\DatabaseDumper;
use Mockery;
use PDO;
use PDOStatement;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DatabaseDumperTest extends TestCase
{
    #[DataProvider('definerDataProvider')]
    public function test_only_object_header_definers_are_removed(string $sql, string $expected): void
    {
        $this->assertSame($expected, (new DatabaseDumper())->withoutDefiner($sql));
    }

    public static function definerDataProvider(): array
    {
        return [
            'view' => ['CREATE ALGORITHM=UNDEFINED DEFINER=`game`@`198.51.100.20` SQL SECURITY DEFINER VIEW v AS SELECT 1', 'CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW v AS SELECT 1'],
            'replace view' => ['CREATE OR REPLACE DEFINER = game @ localhost VIEW v AS SELECT 1', 'CREATE OR REPLACE VIEW v AS SELECT 1'],
            'procedure' => ["CREATE DEFINER='game'@'host' PROCEDURE p() SELECT ' DEFINER=game@host'", "CREATE PROCEDURE p() SELECT ' DEFINER=game@host'"],
            'function' => ['CREATE DEFINER="game"@"host" FUNCTION f() RETURNS INT RETURN 1', 'CREATE FUNCTION f() RETURNS INT RETURN 1'],
            'current user' => ['CREATE DEFINER=CURRENT_USER() EVENT e ON SCHEDULE EVERY 1 DAY DO SELECT 1', 'CREATE EVENT e ON SCHEDULE EVERY 1 DAY DO SELECT 1'],
            'current user spacing' => ['CREATE DEFINER = CURRENT_USER ( ) VIEW v AS SELECT 1', 'CREATE VIEW v AS SELECT 1'],
            'quoted account' => ["CREATE DEFINER='it''s'@'host' TRIGGER t BEFORE INSERT ON items FOR EACH ROW SET NEW.value=1", 'CREATE TRIGGER t BEFORE INSERT ON items FOR EACH ROW SET NEW.value=1'],
            'version comments' => ['/*!50003 CREATE*/ /*!50017 DEFINER=`game`@`host`*/ /*!50003 TRIGGER t BEFORE INSERT ON items FOR EACH ROW SET NEW.value=1 */', '/*!50003 CREATE*/ /*!50017 */ /*!50003 TRIGGER t BEFORE INSERT ON items FOR EACH ROW SET NEW.value=1 */'],
            'mariadb comment' => ['/*M!100101 CREATE DEFINER=`game`@`host` VIEW v AS SELECT 1 */', '/*M!100101 CREATE VIEW v AS SELECT 1 */'],
            'row contents' => ["INSERT INTO items VALUES (' DEFINER=game@host')", "INSERT INTO items VALUES (' DEFINER=game@host')"],
            'view contents' => ["CREATE VIEW v AS SELECT ' DEFINER=game@host'", "CREATE VIEW v AS SELECT ' DEFINER=game@host'"],
            'routine contents' => ["CREATE PROCEDURE p() SELECT ' DEFINER=game@host'", "CREATE PROCEDURE p() SELECT ' DEFINER=game@host'"],
            'table contents' => ["CREATE TABLE items (value TEXT DEFAULT ' DEFINER=game@host')", "CREATE TABLE items (value TEXT DEFAULT ' DEFINER=game@host')"],
        ];
    }

    #[DataProvider('sqlModeDataProvider')]
    public function test_strings_are_escaped_for_the_dump_sql_mode(bool $noBackslashEscapes)
    {
        $connection = $this->createMock(PDO::class);
        $connection->method('quote')->willReturnCallback(fn (string $value) => $noBackslashEscapes
            ? "'".str_replace("'", "''", $value)."'"
            : "'".strtr($value, ['\\' => '\\\\', "\0" => '\\0', "\n" => '\\n', "\r" => '\\r', "\t" => '\\t', "\x1a" => '\\Z', "'" => "\\'", '"' => '\\"'])."'");

        $connection->method('query')->willReturnCallback(function (string $query) {
            $statement = $this->createMock(PDOStatement::class);
            $statement->method('fetchAll')->willReturn(match ($query) {
                'SHOW FULL TABLES' => [['strings', 'BASE TABLE']],
                'SHOW COLUMNS FROM `strings`' => [
                    ['Field' => 'value', 'Type' => 'text', 'Extra' => ''],
                    ['Field' => 'nullable', 'Type' => 'text', 'Extra' => ''],
                    ['Field' => 'binary_value', 'Type' => 'blob', 'Extra' => ''],
                    ['Field' => 'amount', 'Type' => 'decimal(10,2)', 'Extra' => ''],
                ],
                default => [],
            });
            if ($query === 'SHOW CREATE TABLE `strings`') {
                $statement->method('fetch')->willReturn(['strings', 'CREATE TABLE `strings` (`value` TEXT)']);
            } elseif (str_starts_with($query, 'SELECT ')) {
                $statement->method('fetch')->willReturnOnConsecutiveCalls(
                    ['C:\\new\\test', null, "\0\xff", '123.45'],
                    ["quote' double\" ends\\", null, '', '0.00'],
                    ["nul\0tab\tline\nreturn\rctrl\x1a", null, null, '-1.00'],
                    ['你好 🎮 café', null, null, '1.00'],
                    ['', null, null, '2.00'],
                    false,
                );
            }

            return $statement;
        });

        $statements = iterator_to_array((new DatabaseDumper())->statements($connection), false);

        $this->assertContains("SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO'", $statements);
        $this->assertContains(
            'INSERT INTO `strings` (`value`, `nullable`, `binary_value`, `amount`) VALUES '.
            "\n('C:\\\\new\\\\test', NULL, 0x00ff, 123.45),".
            "\n('quote\\' double\\\" ends\\\\', NULL, '', 0.00),".
            "\n('nul\\0tab\\tline\\nreturn\\rctrl\\Z', NULL, NULL, -1.00),".
            "\n('你好 🎮 café', NULL, NULL, 1.00),".
            "\n('', NULL, NULL, 2.00)",
            $statements,
        );
    }

    public static function sqlModeDataProvider(): array
    {
        return [
            'backslash escapes enabled' => [false],
            'NO_BACKSLASH_ESCAPES' => [true],
        ];
    }

    public function test_functions_are_restored_before_dependent_views_and_triggers()
    {
        $connection = Mockery::mock(PDO::class);
        $connection->shouldReceive('exec')->andReturn(0);
        $connection->shouldReceive('setAttribute')->andReturn(true);

        $queries = [
            'SHOW FULL TABLES' => [['a_nested', 'VIEW'], ['z_function_view', 'VIEW'], ['numbers', 'BASE TABLE']],
            'SHOW TRIGGERS' => ['numbers_trigger'],
            'SHOW PROCEDURE STATUS WHERE Db = DATABASE()' => ['read_numbers'],
            'SHOW FUNCTION STATUS WHERE Db = DATABASE()' => ['double_number'],
            'SHOW EVENTS' => ['refresh_numbers', 'clear_numbers'],
            'SHOW COLUMNS FROM `numbers`' => [['Field' => 'n', 'Type' => 'int', 'Extra' => '']],
        ];
        foreach ($queries as $query => $rows) {
            $result = Mockery::mock(PDOStatement::class);
            $result->shouldReceive('fetchAll')->andReturn($rows);
            $connection->shouldReceive('query')->with($query)->andReturn($result);
        }

        $definitions = [
            'SHOW CREATE TABLE `numbers`' => [1 => 'CREATE TABLE `numbers` (`n` INT)'],
            'SHOW CREATE VIEW `a_nested`' => [1 => 'CREATE VIEW `a_nested` AS SELECT * FROM `z_function_view`'],
            'SHOW CREATE VIEW `z_function_view`' => [1 => 'CREATE VIEW `z_function_view` AS SELECT double_number(n) FROM `numbers`'],
            'SHOW CREATE FUNCTION `double_number`' => [1 => 'PIPES_AS_CONCAT,ANSI_QUOTES', 2 => 'CREATE DEFINER="root"@"%" FUNCTION double_number(n INT) RETURNS INT RETURN n * 2'],
            'SHOW CREATE PROCEDURE `read_numbers`' => [1 => 'ANSI_QUOTES', 2 => 'CREATE DEFINER="root"@"%" PROCEDURE read_numbers() SELECT double_number(n) FROM numbers'],
            'SHOW CREATE TRIGGER `numbers_trigger`' => [1 => '', 2 => 'CREATE TRIGGER numbers_trigger BEFORE INSERT ON numbers FOR EACH ROW SET NEW.n = double_number(NEW.n)'],
            'SHOW CREATE EVENT `refresh_numbers`' => [1 => 'NO_BACKSLASH_ESCAPES', 2 => '+05:30', 3 => "CREATE EVENT refresh_numbers ON SCHEDULE EVERY 1 DAY STARTS '2026-01-01 12:00:00' DO CALL read_numbers()"],
            'SHOW CREATE EVENT `clear_numbers`' => [1 => '', 2 => 'SYSTEM', 3 => "CREATE EVENT clear_numbers ON SCHEDULE EVERY 1 DAY STARTS '2026-01-01 12:00:00' DO DELETE FROM numbers"],
        ];
        foreach ($definitions as $query => $definition) {
            $result = Mockery::mock(PDOStatement::class);
            $result->shouldReceive('fetch')->andReturn($definition);
            $connection->shouldReceive('query')->with($query)->andReturn($result);
        }

        $offset = Mockery::mock(PDOStatement::class);
        $offset->shouldReceive('fetchColumn')->andReturn('-9000');
        $connection->shouldReceive('query')->with("SELECT TIMESTAMPDIFF(SECOND, UTC_TIMESTAMP(), CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', 'SYSTEM'))")->andReturn($offset);

        $rows = Mockery::mock(PDOStatement::class);
        $rows->shouldReceive('fetch')->andReturn(['21'], false);
        $rows->shouldReceive('closeCursor')->once();
        $connection->shouldReceive('query')->with('SELECT `n` FROM `numbers`')->andReturn($rows);

        $statements = iterator_to_array((new DatabaseDumper())->statements($connection), false);
        $position = function (string $query) use ($definitions, $statements): int {
            $index = array_search(str_replace(' DEFINER="root"@"%"', '', end($definitions[$query])), $statements, true);
            $this->assertNotFalse($index, 'The dump is missing the definition from '.$query);

            return $index;
        };
        $insert = array_search("INSERT INTO `numbers` (`n`) VALUES \n(21)", $statements, true);
        $this->assertNotFalse($insert, 'The dump is missing the table rows.');

        foreach (['FUNCTION', 'PROCEDURE', 'TRIGGER', 'EVENT'] as $type) {
            $query = array_values(array_filter(array_keys($definitions), fn (string $query) => str_starts_with($query, 'SHOW CREATE '.$type)))[0];
            $index = $position($query);
            $this->assertTrue((new DatabaseDumper())->isCompound($statements[$index]));
            $this->assertSame("SET SQL_MODE = '".$definitions[$query][1]."'", $statements[$index - 1]);
            $this->assertSame("SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO'", $statements[$index + 1]);
        }

        $event = $position('SHOW CREATE EVENT `refresh_numbers`');
        $this->assertSame("SET time_zone = '+05:30'", $statements[$event - 2]);
        $this->assertSame("SET time_zone = '+00:00'", $statements[$event + 2]);

        $event = $position('SHOW CREATE EVENT `clear_numbers`');
        $this->assertSame("SET time_zone = '-02:30'", $statements[$event - 2]);
        $this->assertSame("SET time_zone = '+00:00'", $statements[$event + 2]);

        $this->assertLessThan($position('SHOW CREATE VIEW `z_function_view`'), $position('SHOW CREATE FUNCTION `double_number`'));
        $this->assertLessThan($position('SHOW CREATE PROCEDURE `read_numbers`'), $position('SHOW CREATE FUNCTION `double_number`'));
        $this->assertLessThan($position('SHOW CREATE VIEW `a_nested`'), $position('SHOW CREATE VIEW `z_function_view`'));
        $this->assertLessThan($position('SHOW CREATE TRIGGER `numbers_trigger`'), $insert);
        $this->assertLessThan($position('SHOW CREATE EVENT `refresh_numbers`'), $position('SHOW CREATE PROCEDURE `read_numbers`'));
    }
}
