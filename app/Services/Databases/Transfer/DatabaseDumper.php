<?php

namespace App\Services\Databases\Transfer;

use PDO;
use Pdo\Mysql;

class DatabaseDumper
{
    private const INSERT_SIZE = 1048576;

    private const NUMERIC_TYPES = '/^(tinyint|smallint|mediumint|int|integer|bigint|decimal|numeric|float|double|real|year|bit)\b/i';

    private const BINARY_TYPES = '/^(binary|varbinary|tinyblob|blob|mediumblob|longblob|geometry|point|linestring|polygon|multipoint|multilinestring|multipolygon|geometrycollection|geomcollection)\b/i';

    /**
     * @return \Generator<int, string>
     */
    public function statements(PDO $connection): \Generator
    {
        $connection->exec("SET SESSION time_zone = '+00:00'");
        $connection->exec('SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ');
        $connection->exec('START TRANSACTION /*!40100 WITH CONSISTENT SNAPSHOT */');

        $tables = $views = [];
        foreach ($connection->query('SHOW FULL TABLES')->fetchAll() as [$name, $type]) {
            if ($type === 'VIEW') {
                $views[] = $name;
            } elseif ($type === 'BASE TABLE') {
                $tables[] = $name;
            }
        }

        $triggers = $connection->query('SHOW TRIGGERS')->fetchAll(PDO::FETCH_COLUMN, 0);
        $procedures = $this->routines($connection, 'PROCEDURE');
        $functions = $this->routines($connection, 'FUNCTION');
        $events = $connection->query('SHOW EVENTS')->fetchAll(PDO::FETCH_COLUMN, 1);

        yield 'SET NAMES utf8mb4';
        yield "SET time_zone = '+00:00'";
        yield 'SET FOREIGN_KEY_CHECKS = 0';
        yield 'SET UNIQUE_CHECKS = 0';
        yield "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO'";

        foreach ($tables as $table) {
            yield 'DROP TABLE IF EXISTS '.$this->identifier($table);
            yield $connection->query('SHOW CREATE TABLE '.$this->identifier($table))->fetch()[1];

            yield from $this->rows($connection, $table);
        }

        yield from $this->views($connection, $views);
        yield from $this->definitions($connection, 'TRIGGER', $triggers, 2);
        yield from $this->definitions($connection, 'PROCEDURE', $procedures, 2);
        yield from $this->definitions($connection, 'FUNCTION', $functions, 2);
        yield from $this->definitions($connection, 'EVENT', $events, 3);

        yield 'SET UNIQUE_CHECKS = 1';
        yield 'SET FOREIGN_KEY_CHECKS = 1';

        $connection->exec('COMMIT');
    }

    public function isCompound(string $statement): bool
    {
        return preg_match('/^CREATE\s+(TRIGGER|PROCEDURE|FUNCTION|EVENT)\b/i', $statement) === 1;
    }

    /**
     * @return \Generator<int, string>
     */
    private function rows(PDO $connection, string $table): \Generator
    {
        $select = $names = $kinds = [];
        foreach ($connection->query('SHOW COLUMNS FROM '.$this->identifier($table))->fetchAll(PDO::FETCH_ASSOC) as $column) {
            if (preg_match('/\b(VIRTUAL|STORED|PERSISTENT)\b/i', $column['Extra']) === 1) {
                continue;
            }

            $identifier = $this->identifier($column['Field']);
            $names[] = $identifier;

            if (preg_match(self::NUMERIC_TYPES, $column['Type'], $matches) === 1) {
                $kinds[] = 'numeric';
                $select[] = strtolower($matches[1]) === 'bit' ? $identifier.' + 0' : $identifier;
            } else {
                $kinds[] = preg_match(self::BINARY_TYPES, $column['Type']) === 1 ? 'binary' : 'string';
                $select[] = $identifier;
            }
        }

        if (empty($names)) {
            return;
        }

        $prefix = sprintf('INSERT INTO %s (%s) VALUES ', $this->identifier($table), implode(', ', $names));
        $statement = '';

        $buffered = PHP_VERSION_ID >= 80500 ? Mysql::ATTR_USE_BUFFERED_QUERY : PDO::MYSQL_ATTR_USE_BUFFERED_QUERY;
        $connection->setAttribute($buffered, false);

        try {
            $result = $connection->query(sprintf('SELECT %s FROM %s', implode(', ', $select), $this->identifier($table)));

            while ($row = $result->fetch()) {
                $values = [];
                foreach ($row as $index => $value) {
                    $values[] = match (true) {
                        is_null($value) => 'NULL',
                        $kinds[$index] === 'numeric' => $value,
                        $kinds[$index] === 'binary' => $value === '' ? "''" : '0x'.bin2hex($value),
                        default => $connection->quote($value),
                    };
                }

                $statement .= ($statement === '' ? $prefix : ',')."\n(".implode(', ', $values).')';

                if (strlen($statement) >= self::INSERT_SIZE) {
                    yield $statement;
                    $statement = '';
                }
            }

            $result->closeCursor();
        } finally {
            $connection->setAttribute($buffered, true);
        }

        if ($statement !== '') {
            yield $statement;
        }
    }

    /**
     * @param  string[]  $views
     * @return \Generator<int, string>
     */
    private function views(PDO $connection, array $views): \Generator
    {
        $definitions = [];
        foreach ($views as $view) {
            $definitions[$view] = $this->withoutDefiner(
                $connection->query('SHOW CREATE VIEW '.$this->identifier($view))->fetch()[1]
            );
        }

        $created = [];
        while (! empty($definitions)) {
            $progressed = false;

            foreach ($definitions as $view => $definition) {
                $waiting = array_filter(
                    array_keys($definitions),
                    fn (string $other) => $other !== $view && str_contains($definition, $this->identifier($other))
                );

                if (! empty($waiting)) {
                    continue;
                }

                $created[$view] = $definition;
                unset($definitions[$view]);
                $progressed = true;
            }

            if (! $progressed) {
                $created += $definitions;
                break;
            }
        }

        foreach ($created as $view => $definition) {
            yield 'DROP VIEW IF EXISTS '.$this->identifier($view);
            yield $definition;
        }
    }

    /**
     * @return string[]
     */
    private function routines(PDO $connection, string $type): array
    {
        return $connection->query("SHOW $type STATUS WHERE Db = DATABASE()")->fetchAll(PDO::FETCH_COLUMN, 1);
    }

    /**
     * @param  string[]  $names
     * @return \Generator<int, string>
     *
     * @throws \PDOException
     */
    private function definitions(PDO $connection, string $type, array $names, int $column): \Generator
    {
        foreach ($names as $name) {
            $definition = $connection->query("SHOW CREATE $type ".$this->identifier($name))->fetch()[$column] ?? null;

            if (empty($definition)) {
                throw new \PDOException(sprintf('The definition of the %s %s could not be read.', strtolower($type), $name));
            }

            yield "DROP $type IF EXISTS ".$this->identifier($name);
            yield $this->withoutDefiner($definition);
        }
    }

    private function withoutDefiner(string $statement): string
    {
        return preg_replace(
            '/\sDEFINER\s*=\s*(`[^`]*`|\'[^\']*\'|[\w%.\-]+)@(`[^`]*`|\'[^\']*\'|[\w%.\-]+)/i',
            '',
            $statement,
            1
        );
    }

    private function identifier(string $name): string
    {
        return '`'.str_replace('`', '``', $name).'`';
    }
}
