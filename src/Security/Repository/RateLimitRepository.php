<?php

/**
 * Application: DbM Framework
 * A lightweight PHP framework for building web applications.
 *
 * @author Artur Malinowski
 * @copyright Design by Malina (All Rights Reserved)
 * @license MIT
 * @link https://www.dbm.org.pl
 */

declare(strict_types=1);

namespace Dbm\Security\Repository;

use Dbm\Database\Contracts\DatabaseInterface;
use Dbm\Database\Repository\AbstractRepository;

class RateLimitRepository extends AbstractRepository
{
    protected string $table = 'dbm_rate_limit';

    public function __construct(
        protected DatabaseInterface $database
    ) {
        parent::__construct($database);
    }

    /**
     * @return array{
     *     id: int,
     *     ip_address: string,
     *     action: string,
     *     identifier: ?string,
     *     attempts: int,
     *     expires_at: string,
     *     created_at: string,
     *     updated_at: string
     * }|null
     */
    public function findActive(
        string $action,
        string $ip,
        ?string $identifier
    ): ?array {
        $qb = $this->database->createQueryBuilder();

        $qb->select('*')
            ->from($this->table)
            ->where('action = :action')
            ->andWhere('ip_address = :ip')
            ->andWhere('expires_at > NOW()')
            ->setParameter('action', $action)
            ->setParameter('ip', $ip)
            ->setMaxResults(1);

        if ($identifier !== null) {
            $qb->andWhere('identifier = :identifier');
            $qb->setParameter('identifier', $identifier);
        } else {
            $qb->andWhere('identifier IS NULL');
        }

        $row = $this->database->fetch(
            $qb->getSQL(),
            $qb->getParameters()
        );

        return $row ?: null;
    }

    public function hit(
        string $action,
        string $ip,
        ?string $identifier,
        int $ttl
    ): void {
        $expiresAt = date('Y-m-d H:i:s', time() + $ttl);

        $sql = "
            INSERT INTO {$this->table}
            (
                ip_address, action, identifier, attempts, expires_at
            )
            VALUES
            (
                :ip, :action, :identifier, 1, :expires_at
            )
            ON DUPLICATE KEY UPDATE
                attempts = attempts + 1,
                expires_at = :expires_update
        ";

        $this->database->execute($sql, [
            'ip' => $ip,
            'action' => $action,
            'identifier' => $identifier,
            'expires_at' => $expiresAt,
            'expires_update' => $expiresAt,
        ]);
    }

    public function clear(
        string $action,
        string $ip,
        ?string $identifier
    ): bool {
        $where = 'action = :action AND ip_address = :ip';
        $params = [
            'action' => $action,
            'ip' => $ip,
        ];

        if ($identifier !== null) {
            $where .= ' AND identifier = :identifier';
            $params['identifier'] = $identifier;
        }

        return $this->delete($where, $params);
    }

    public function cleanup(): bool
    {
        return $this->delete('expires_at < NOW()');
    }

    public function cleanupIdentifier(string $action, string $identifier): bool
    {
        $where = 'action = :action AND identifier = :identifier';
        $params = [
            'action' => $action,
            'identifier' => $identifier,
        ];

        return $this->delete($where, $params);
    }
}
