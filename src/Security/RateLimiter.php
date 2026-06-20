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

namespace Dbm\Security;

use Dbm\Security\Repository\RateLimitRepository;

final class RateLimiter
{
    public function __construct(
        private readonly RateLimitRepository $repository
    ) {}

    public function check(string $action, string $ip, string $identifier, int $maxAttempts): bool
    {
        // @INFO Przy większym ruchu każde logowanie robi DELETE.
        // To jest kosztowne i można przenieś cleanup do np.: CRON lub zostawić warunek (zwykle 1% na 100 logowań).
        if (random_int(1, 100) === 1) {
            $this->repository->cleanup();
        }

        $record = $this->repository->findActive($action, $ip, $identifier);

        if (!$record) {
            return true;
        }

        return (int) $record['attempts'] < $maxAttempts;
    }

    public function hit(string $action, string $ip, string $identifier, int $ttl): void
    {
        $this->repository->hit($action, $ip, $identifier, $ttl);
    }

    public function clear(string $action, string $ip, string $identifier): void
    {
        $this->repository->clear($action, $ip, $identifier);
    }

    public function remainingAttempts(string $action, string $ip, string $identifier, int $maxAttempts): int
    {
        $record = $this->repository->findActive($action, $ip, $identifier);

        if (!$record) {
            return $maxAttempts;
        }

        return max(0, $maxAttempts - (int) $record['attempts']);
    }

    public function retryAfter(string $action, string $ip, string $identifier): int
    {
        $record = $this->repository->findActive($action, $ip, $identifier);

        if (!$record || empty($record['expires_at'])) {
            return 0;
        }

        return max(0, strtotime($record['expires_at']) - time());
    }
}
