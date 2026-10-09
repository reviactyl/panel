<?php

namespace Tests\Unit\Services\Databases;

use App\Exceptions\Service\Database\DatabaseImportException;
use App\Services\Databases\Transfer\SqlStatementReader;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SqlStatementReaderTest extends TestCase
{
    #[DataProvider('statementDataProvider')]
    public function test_statements_are_split_correctly(string $sql, array $expected, bool $backslashEscapes = true)
    {
        $stream = fopen('php://memory', 'w+');
        fwrite($stream, $sql);
        rewind($stream);

        $this->assertSame($expected, iterator_to_array((new SqlStatementReader())->read($stream, fn () => $backslashEscapes), false));
    }

    public function test_quote_parsing_follows_mode_changes_after_statements_execute()
    {
        $expected = [
            "/*!40101 SET @saved_mode = @@sql_mode, SQL_MODE = 'NO_BACKSLASH_ESCAPES' */",
            "SELECT 'C:\\', 'it''s; literal'",
            'SET SQL_MODE = @saved_mode',
            "SELECT 'it\\'s; escaped'",
        ];
        $stream = fopen('php://memory', 'w+');
        fwrite($stream, implode(';', $expected).';');
        rewind($stream);
        $backslashEscapes = true;
        $calls = 0;
        $mode = function () use (&$backslashEscapes, &$calls): bool {
            $calls++;

            return $backslashEscapes;
        };

        try {
            $statements = [];
            foreach ((new SqlStatementReader())->read($stream, $mode) as $index => $statement) {
                $statements[] = $statement;
                if ($index === 0) {
                    $backslashEscapes = false;
                } elseif ($index === 2) {
                    $backslashEscapes = true;
                }
            }

            $this->assertSame($expected, $statements);
            $this->assertSame(2, $calls);
        } finally {
            fclose($stream);
        }
    }

    public function test_statements_are_read_from_a_compressed_file()
    {
        $path = tempnam(sys_get_temp_dir(), 'sql');
        file_put_contents($path, gzencode("SELECT 1;\nSELECT 2;\n"));

        try {
            $stream = fopen('compress.zlib://'.$path, 'rb');

            $this->assertSame(['SELECT 1', 'SELECT 2'], iterator_to_array((new SqlStatementReader())->read($stream), false));
        } finally {
            unlink($path);
        }
    }

    public function test_oversized_statements_are_rejected()
    {
        $stream = fopen('php://temp', 'w+');
        fwrite($stream, 'SELECT 1; /*');
        for ($written = 0; $written <= SqlStatementReader::MAX_STATEMENT_SIZE; $written += 1048576) {
            fwrite($stream, str_repeat('a', 1048576));
        }
        rewind($stream);

        $statements = (new SqlStatementReader())->read($stream);
        $this->assertSame('SELECT 1', $statements->current());

        try {
            $statements->next();
            $this->fail('The oversized statement was not rejected.');
        } catch (DatabaseImportException $exception) {
            $this->assertSame(DatabaseImportException::STATEMENT_FAILED, $exception->getReason());
        } finally {
            fclose($stream);
        }
    }

    public static function statementDataProvider(): array
    {
        return [
            'simple statements' => ["SELECT 1;\nSELECT 2;", ['SELECT 1', 'SELECT 2']],
            'missing final delimiter' => ["SELECT 1;\nSELECT 2", ['SELECT 1', 'SELECT 2']],
            'empty statements are skipped' => [";;\n SELECT 1;;", ['SELECT 1']],
            'delimiter inside of quotes' => [
                "INSERT INTO `a;b` VALUES ('it''s; ok', \"x\\\";y\", 'z\\\\');SELECT 2;",
                ["INSERT INTO `a;b` VALUES ('it''s; ok', \"x\\\";y\", 'z\\\\')", 'SELECT 2'],
            ],
            'no backslash escapes with single and double quotes' => [
                "SELECT 'C:\\';SELECT \"D:\\\";SELECT 'it''s; literal';SELECT `a\\`;",
                ["SELECT 'C:\\'", 'SELECT "D:\\"', "SELECT 'it''s; literal'", 'SELECT `a\\`'],
                false,
            ],
            'no backslash escapes across reads' => [
                "SELECT '".str_repeat('a', 65526)."\\';SELECT 2;",
                ["SELECT '".str_repeat('a', 65526)."\\'", 'SELECT 2'],
                false,
            ],
            'line comments are removed' => [
                "-- first\n# second\nSELECT 1; -- trailing; comment\nSELECT '-- kept', 5--2;",
                ['SELECT 1', "SELECT '-- kept', 5--2"],
            ],
            'block comments are kept' => [
                "/*!40101 SET NAMES utf8mb4 */;\nSELECT /* a; b */ 1;",
                ['/*!40101 SET NAMES utf8mb4 */', 'SELECT /* a; b */ 1'],
            ],
            'custom delimiters' => [
                "SELECT 1;\nDELIMITER ;;\nCREATE TRIGGER t BEFORE INSERT ON a FOR EACH ROW BEGIN SET NEW.b = 1; SET NEW.c = ';;'; END;;\ndelimiter ;\nSELECT 2;",
                [
                    'SELECT 1',
                    "CREATE TRIGGER t BEFORE INSERT ON a FOR EACH ROW BEGIN SET NEW.b = 1; SET NEW.c = ';;'; END",
                    'SELECT 2',
                ],
            ],
            'escaped quote split across reads' => [
                "INSERT INTO a VALUES ('".str_repeat('a', 65512)."\\'b; c');SELECT 1;",
                ["INSERT INTO a VALUES ('".str_repeat('a', 65512)."\\'b; c')", 'SELECT 1'],
            ],
            'statements larger than a single read' => [
                "INSERT INTO a VALUES ('".str_repeat('a;', 100000)."');SELECT 1;",
                ["INSERT INTO a VALUES ('".str_repeat('a;', 100000)."')", 'SELECT 1'],
            ],
        ];
    }
}
