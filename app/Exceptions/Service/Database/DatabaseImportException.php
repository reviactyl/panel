<?php

namespace App\Exceptions\Service\Database;

class DatabaseImportException extends \RuntimeException
{
    public const CONNECTION_FAILED = 'connection';

    public const ARCHIVE_EMPTY = 'archive';

    public const FILE_UNREADABLE = 'file';

    public const REMOTE_ACCESS_DENIED = 'remote-access-denied';

    public const REMOTE_NOT_ALLOWED = 'remote-not-allowed';

    public const REMOTE_UNKNOWN_DATABASE = 'remote-unknown-database';

    public const REMOTE_UNREACHABLE = 'remote-unreachable';

    public const REMOTE_READ_FAILED = 'remote-read';

    public const SOURCE_EMPTY = 'empty';

    public const STATEMENT_FAILED = 'statement';

    public const TIMED_OUT = 'timeout';

    public const UNKNOWN = 'unknown';

    public function __construct(string $reason, private ?string $detail = null, ?\Throwable $previous = null)
    {
        parent::__construct($reason, 0, $previous);
    }

    public function getReason(): string
    {
        return $this->getMessage();
    }

    public function getDetail(): ?string
    {
        return $this->detail;
    }
}
