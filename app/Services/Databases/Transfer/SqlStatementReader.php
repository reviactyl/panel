<?php

namespace App\Services\Databases\Transfer;

use App\Exceptions\Service\Database\DatabaseImportException;

class SqlStatementReader
{
    public const MAX_STATEMENT_SIZE = 67108864;

    private const CHUNK_SIZE = 65536;

    /**
     * @param  resource  $stream
     * @return \Generator<int, string>
     *
     * @throws DatabaseImportException
     */
    public function read($stream): \Generator
    {
        $buffer = '';
        $position = 0;
        $delimiter = ';';
        $eof = false;
        $blank = true;

        $fill = function () use ($stream, &$buffer, &$eof): void {
            $chunk = fread($stream, self::CHUNK_SIZE);
            if ($chunk === false || $chunk === '') {
                $eof = true;

                return;
            }

            $buffer .= $chunk;

            if (strlen($buffer) > self::MAX_STATEMENT_SIZE) {
                throw new DatabaseImportException(
                    DatabaseImportException::STATEMENT_FAILED,
                    sprintf('A statement is larger than the %d MiB limit.', self::MAX_STATEMENT_SIZE / 1048576)
                );
            }
        };

        while (true) {
            if ($blank) {
                $position += strspn($buffer, " \t\r\n", $position);

                if (strlen($buffer) - $position < 10 && ! $eof) {
                    $fill();

                    continue;
                }

                if (strncasecmp(substr($buffer, $position, 10), 'DELIMITER ', 10) === 0) {
                    $end = strpos($buffer, "\n", $position);
                    if ($end === false && ! $eof) {
                        $fill();

                        continue;
                    }

                    $end = $end === false ? strlen($buffer) : $end;
                    $delimiter = trim(substr($buffer, $position + 10, $end - $position - 10)) ?: ';';
                    $buffer = (string) substr($buffer, $end);
                    $position = 0;

                    continue;
                }

                $buffer = (string) substr($buffer, $position);
                $position = 0;
            }

            $skipped = strcspn($buffer, "'\"`#-/".$delimiter[0], $position);
            if ($skipped > 0) {
                $position += $skipped;
                $blank = false;
            }

            if (strlen($buffer) - $position < max(3, strlen($delimiter)) && ! $eof) {
                $fill();

                continue;
            }

            if ($position >= strlen($buffer)) {
                break;
            }

            $character = $buffer[$position];

            if (substr_compare($buffer, $delimiter, $position, strlen($delimiter)) === 0) {
                $statement = trim(substr($buffer, 0, $position));
                $buffer = (string) substr($buffer, $position + strlen($delimiter));
                $position = 0;
                $blank = true;

                if ($statement !== '') {
                    yield $statement;
                }

                continue;
            }

            $comment = $character === '#'
                || ($character === '-' && ($buffer[$position + 1] ?? '') === '-' && ctype_space($buffer[$position + 2] ?? ' '));

            if ($comment) {
                $end = strpos($buffer, "\n", $position);
                if ($end === false && ! $eof) {
                    $fill();

                    continue;
                }

                $buffer = substr($buffer, 0, $position).($end === false ? '' : substr($buffer, $end));

                continue;
            }

            if ($character === '/' && ($buffer[$position + 1] ?? '') === '*') {
                $end = strpos($buffer, '*/', $position + 2);
                if ($end === false && ! $eof) {
                    $fill();

                    continue;
                }

                $position = $end === false ? strlen($buffer) : $end + 2;
                $blank = false;

                continue;
            }

            if ($character === "'" || $character === '"' || $character === '`') {
                $end = $this->findClosingQuote($buffer, $position, $character);
                if ($end === null && ! $eof) {
                    $fill();

                    continue;
                }

                $position = $end === null ? strlen($buffer) : $end + 1;
                $blank = false;

                continue;
            }

            $position++;
            $blank = false;
        }

        $statement = trim($buffer);
        if ($statement !== '') {
            yield $statement;
        }
    }

    private function findClosingQuote(string $buffer, int $start, string $quote): ?int
    {
        $position = $start + 1;
        $length = strlen($buffer);
        $mask = $quote === '`' ? $quote : $quote.'\\';

        while ($position < $length) {
            $position += strcspn($buffer, $mask, $position);
            if ($position >= $length) {
                break;
            }

            if ($buffer[$position] === '\\') {
                $position += 2;

                continue;
            }

            return $position;
        }

        return null;
    }
}
