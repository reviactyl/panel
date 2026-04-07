<?php

namespace App\Services\Allocations;

use App\Contracts\Repository\AllocationRepositoryInterface;
use App\Exceptions\DisplayException;
use App\Exceptions\Service\Allocation\CidrOutOfRangeException;
use App\Exceptions\Service\Allocation\InvalidPortMappingException;
use App\Exceptions\Service\Allocation\PortOutOfRangeException;
use App\Exceptions\Service\Allocation\TooManyPortsInRangeException;
use App\Models\Node;
use Illuminate\Database\ConnectionInterface;
use IPTools\Network;

class AssignmentService
{
    public const CIDR_MAX_BITS = 25;

    public const CIDR_MIN_BITS = 32;

    public const PORT_FLOOR = 1024;

    public const PORT_CEIL = 65535;

    public const PORT_RANGE_LIMIT = 1000;

    public const PORT_RANGE_REGEX = '/^(\d{4,5})-(\d{4,5})$/';

    /**
     * AssignmentService constructor.
     */
    public function __construct(protected AllocationRepositoryInterface $repository, protected ConnectionInterface $connection) {}

    /**
     * Insert allocations into the database and link them to a specific node.
     *
     * @throws DisplayException
     * @throws CidrOutOfRangeException
     * @throws InvalidPortMappingException
     * @throws PortOutOfRangeException
     * @throws TooManyPortsInRangeException
     */
    public function handle(Node $node, array $data): void
    {
        $explode = explode('/', $data['allocation_ip']);
        if (count($explode) !== 1) {
            if (! ctype_digit($explode[1]) || ($explode[1] > self::CIDR_MIN_BITS || $explode[1] < self::CIDR_MAX_BITS)) {
                throw new CidrOutOfRangeException();
            }
        }

        try {
            $underlying = $this->resolveAllocationIp($data['allocation_ip']);
            $parsed = Network::parse($underlying);
        } catch (DisplayException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            // @phpstan-ignore-next-line variable.undefined
            throw new DisplayException("Could not parse provided allocation IP address ({$underlying}): {$exception->getMessage()}", $exception);
        }

        $this->connection->transaction(function () use ($parsed, $data, $node) {
            foreach ($parsed as $ip) {
                foreach ($data['allocation_ports'] as $port) {
                    if (! is_digit($port) && ! preg_match(self::PORT_RANGE_REGEX, $port)) {
                        throw new InvalidPortMappingException($port);
                    }

                    $insertData = [];
                    if (preg_match(self::PORT_RANGE_REGEX, $port, $matches)) {
                        $block = range($matches[1], $matches[2]);

                        if (count($block) > self::PORT_RANGE_LIMIT) {
                            throw new TooManyPortsInRangeException();
                        }

                        if ((int) $matches[1] <= self::PORT_FLOOR || (int) $matches[2] > self::PORT_CEIL) {
                            throw new PortOutOfRangeException();
                        }

                        foreach ($block as $unit) {
                            $insertData[] = [
                                'node_id' => $node->id,
                                'ip' => $ip->__toString(),
                                'port' => (int) $unit,
                                'ip_alias' => array_get($data, 'allocation_alias'),
                                'server_id' => null,
                            ];
                        }
                    } else {
                        if ((int) $port <= self::PORT_FLOOR || (int) $port > self::PORT_CEIL) {
                            throw new PortOutOfRangeException();
                        }

                        $insertData[] = [
                            'node_id' => $node->id,
                            'ip' => $ip->__toString(),
                            'port' => (int) $port,
                            'ip_alias' => array_get($data, 'allocation_alias'),
                            'server_id' => null,
                        ];
                    }

                    $this->repository->insertIgnore($insertData);
                }
            }
        });
    }

    protected function resolveAllocationIp(string $allocationIp): string
    {
        $cidr = '';

        if (str_contains($allocationIp, '/')) {
            [$allocationIp, $cidr] = explode('/', $allocationIp, 2);
            $cidr = '/' . $cidr;
        }

        if (filter_var($allocationIp, FILTER_VALIDATE_IP)) {
            return $allocationIp . $cidr;
        }

        $records = @dns_get_record($allocationIp, DNS_A + DNS_AAAA);

        foreach ($records ?: [] as $record) {
            $resolved = $record['ip'] ?? $record['ipv6'] ?? null;

            if ($resolved && filter_var($resolved, FILTER_VALIDATE_IP)) {
                return $resolved . $cidr;
            }
        }

        $resolved = gethostbyname($allocationIp);

        if (filter_var($resolved, FILTER_VALIDATE_IP)) {
            return $resolved . $cidr;
        }

        throw new DisplayException("Could not resolve provided allocation IP address ({$allocationIp}).");
    }
}
