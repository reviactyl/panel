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
            'SHOW EVENTS' => ['refresh_numbers'],
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
            'SHOW CREATE FUNCTION `double_number`' => [2 => 'CREATE FUNCTION double_number(n INT) RETURNS INT RETURN n * 2'],
            'SHOW CREATE PROCEDURE `read_numbers`' => [2 => 'CREATE PROCEDURE read_numbers() SELECT double_number(n) FROM numbers'],
            'SHOW CREATE TRIGGER `numbers_trigger`' => [2 => 'CREATE TRIGGER numbers_trigger BEFORE INSERT ON numbers FOR EACH ROW SET NEW.n = double_number(NEW.n)'],
            'SHOW CREATE EVENT `refresh_numbers`' => [3 => 'CREATE EVENT refresh_numbers ON SCHEDULE EVERY 1 DAY DO CALL read_numbers()'],
        ];
        foreach ($definitions as $query => $definition) {
            $result = Mockery::mock(PDOStatement::class);
            $result->shouldReceive('fetch')->andReturn($definition);
            $connection->shouldReceive('query')->with($query)->andReturn($result);
        }

        $rows = Mockery::mock(PDOStatement::class);
        $rows->shouldReceive('fetch')->andReturn(['21'], false);
        $rows->shouldReceive('closeCursor')->once();
        $connection->shouldReceive('query')->with('SELECT `n` FROM `numbers`')->andReturn($rows);

        $statements = iterator_to_array((new DatabaseDumper())->statements($connection), false);
        $position = function (string $query) use ($definitions, $statements): int {
            $index = array_search($definitions[$query][array_key_first($definitions[$query])], $statements, true);
            $this->assertNotFalse($index, 'The dump is missing the definition from '.$query);

            return $index;
        };
        $insert = array_search("INSERT INTO `numbers` (`n`) VALUES \n(21)", $statements, true);
        $this->assertNotFalse($insert, 'The dump is missing the table rows.');

        $this->assertLessThan($position('SHOW CREATE VIEW `z_function_view`'), $position('SHOW CREATE FUNCTION `double_number`'));
        $this->assertLessThan($position('SHOW CREATE PROCEDURE `read_numbers`'), $position('SHOW CREATE FUNCTION `double_number`'));
        $this->assertLessThan($position('SHOW CREATE VIEW `a_nested`'), $position('SHOW CREATE VIEW `z_function_view`'));
        $this->assertLessThan($position('SHOW CREATE TRIGGER `numbers_trigger`'), $insert);
        $this->assertLessThan($position('SHOW CREATE EVENT `refresh_numbers`'), $position('SHOW CREATE PROCEDURE `read_numbers`'));
    }
}
