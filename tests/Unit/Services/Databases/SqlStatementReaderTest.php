<?php

namespace Tests\Unit\Services\Databases;

use App\Services\Databases\Transfer\SqlStatementReader;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SqlStatementReaderTest extends TestCase
{
    #[DataProvider('statementDataProvider')]
    public function test_statements_are_split_correctly(string $sql, array $expected)
    {
        $stream = fopen('php://memory', 'w+');
        fwrite($stream, $sql);
        rewind($stream);

        $this->assertSame($expected, iterator_to_array((new SqlStatementReader())->read($stream), false));
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
            'statements larger than a single read' => [
                "INSERT INTO a VALUES ('".str_repeat('a;', 100000)."');SELECT 1;",
                ["INSERT INTO a VALUES ('".str_repeat('a;', 100000)."')", 'SELECT 1'],
            ],
        ];
    }
}
