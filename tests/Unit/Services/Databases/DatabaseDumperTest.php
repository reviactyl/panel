<?php

namespace Tests\Unit\Services\Databases;

use App\Services\Databases\Transfer\DatabaseDumper;
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
}
