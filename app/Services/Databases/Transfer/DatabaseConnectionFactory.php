<?php

namespace App\Services\Databases\Transfer;

use App\Exceptions\Service\Database\DatabaseImportException;
use App\Models\Database;
use App\Models\DatabaseHost;
use Illuminate\Contracts\Encryption\Encrypter;
use PDO;
use Pdo\Mysql;

class DatabaseConnectionFactory
{
    private const CONNECT_TIMEOUT = 10;

    public function __construct(private Encrypter $encrypter) {}

    public function forHost(DatabaseHost $host, ?string $database = null): PDO
    {
        return $this->connect(
            $host->host,
            $host->port,
            $database,
            $host->username,
            $this->encrypter->decrypt($host->password),
        );
    }

    public function forDatabase(Database $database): PDO
    {
        return $this->connect(
            $database->host->host,
            $database->host->port,
            $database->database,
            $database->username,
            $this->encrypter->decrypt($database->password),
        );
    }

    /**
     * @throws DatabaseImportException
     */
    public function forRemote(string $host, int $port, string $database, string $username, string $password): PDO
    {
        if (str_contains($database, ';')) {
            throw new DatabaseImportException(DatabaseImportException::REMOTE_UNKNOWN_DATABASE);
        }

        $address = $this->resolve($host);

        try {
            return $this->connect($address, $port, $database, $username, $password);
        } catch (\PDOException $exception) {
            throw new DatabaseImportException(match ($exception->errorInfo[1] ?? null) {
                1044, 1045, 1698 => DatabaseImportException::REMOTE_ACCESS_DENIED,
                1049 => DatabaseImportException::REMOTE_UNKNOWN_DATABASE,
                default => DatabaseImportException::REMOTE_UNREACHABLE,
            }, previous: $exception);
        }
    }

    /**
     * @throws DatabaseImportException
     */
    protected function resolve(string $host): string
    {
        $host = trim($host, '[]');

        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            $addresses = [$host];
        } else {
            $addresses = gethostbynamel($host) ?: array_column(@dns_get_record($host, DNS_AAAA) ?: [], 'ipv6');
        }

        if (empty($addresses)) {
            throw new DatabaseImportException(DatabaseImportException::REMOTE_UNREACHABLE);
        }

        foreach ($addresses as $address) {
            if (! $this->isAllowedAddress($address)) {
                throw new DatabaseImportException(DatabaseImportException::REMOTE_NOT_ALLOWED);
            }
        }

        return $addresses[0];
    }

    protected function isAllowedAddress(string $address): bool
    {
        $packed = @inet_pton($address);
        if ($packed === false) {
            return false;
        }

        if (strlen($packed) === 16 && str_starts_with($packed, str_repeat("\0", 10)."\xff\xff")) {
            $address = inet_ntop(substr($packed, 12));
        }

        $flags = config('panel.client_features.databases.allow_private_remote_import')
            ? FILTER_FLAG_NO_RES_RANGE
            : FILTER_FLAG_NO_RES_RANGE | FILTER_FLAG_NO_PRIV_RANGE;

        return filter_var($address, FILTER_VALIDATE_IP, $flags) !== false;
    }

    protected function connect(string $host, int $port, ?string $database, string $username, string $password): PDO
    {
        $dsn = sprintf('mysql:host=%s;port=%d;charset=utf8mb4', $host, $port);
        if (! is_null($database)) {
            $dsn .= ';dbname='.$database;
        }

        return new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_NUM,
            PDO::ATTR_EMULATE_PREPARES => true,
            PDO::ATTR_STRINGIFY_FETCHES => true,
            PDO::ATTR_TIMEOUT => self::CONNECT_TIMEOUT,
            (PHP_VERSION_ID >= 80500 ? Mysql::ATTR_LOCAL_INFILE : PDO::MYSQL_ATTR_LOCAL_INFILE) => false,
            (PHP_VERSION_ID >= 80500 ? Mysql::ATTR_MULTI_STATEMENTS : PDO::MYSQL_ATTR_MULTI_STATEMENTS) => false,
        ]);
    }
}
