<?php

namespace App\Services\Databases;

use App\Models\Database;
use App\Services\Databases\Transfer\DatabaseConnectionFactory;
use App\Services\Databases\Transfer\DatabaseDumper;
use App\Services\Databases\Transfer\ZipStreamWriter;
use Carbon\CarbonImmutable;
use PDO;

class DatabaseExportService
{
    public const COMPRESSION_GZIP = 'gz';

    public const COMPRESSION_ZIP = 'zip';

    private const ZIP64_THRESHOLD = 268435456;

    public function __construct(
        private DatabaseConnectionFactory $connections,
        private DatabaseDumper $dumper,
    ) {}

    /**
     * @throws \PDOException
     */
    public function handle(Database $database, ?string $compression = null): \Closure
    {
        $connection = $this->connections->forHost($database->host, $database->database);

        return function () use ($connection, $database, $compression) {
            [$write, $finish] = match ($compression) {
                self::COMPRESSION_GZIP => $this->gzip(),
                self::COMPRESSION_ZIP => $this->zip($connection, $database->database.'.sql'),
                default => [$this->output(...), function (): void {}],
            };

            $write(sprintf(
                "-- Reviactyl SQL Dump\n-- Database: %s\n-- Generated: %s\n\n",
                $database->database,
                CarbonImmutable::now()->toAtomString(),
            ));

            foreach ($this->dumper->statements($connection) as $statement) {
                $write($this->dumper->isCompound($statement)
                    ? "DELIMITER ;;\n$statement;;\nDELIMITER ;\n"
                    : "$statement;\n");
            }

            $write("\n-- Dump completed\n");

            $finish();
        };
    }

    /**
     * @return array{\Closure(string): void, \Closure(): void}
     */
    private function gzip(): array
    {
        $deflate = deflate_init(ZLIB_ENCODING_GZIP);

        return [
            function (string $content) use ($deflate): void {
                $this->output(deflate_add($deflate, $content, ZLIB_NO_FLUSH));
            },
            function () use ($deflate): void {
                $this->output(deflate_add($deflate, '', ZLIB_FINISH));
            },
        ];
    }

    /**
     * @return array{\Closure(string): void, \Closure(): void}
     */
    private function zip(PDO $connection, string $name): array
    {
        $size = (int) $connection
            ->query('SELECT COALESCE(SUM(data_length), 0) FROM information_schema.tables WHERE table_schema = DATABASE()')
            ->fetchColumn();

        $writer = new ZipStreamWriter($name, $size >= self::ZIP64_THRESHOLD, $this->output(...));

        return [$writer->write(...), $writer->finish(...)];
    }

    private function output(string $content): void
    {
        echo $content;
    }
}
