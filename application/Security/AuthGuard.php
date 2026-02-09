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

use Dbm\Infrastructure\Session\SessionManager;

final class AuthGuard
{
    private const SESSION_KEY = 'user_id';

    public function __construct(
        private SessionManager $session
    ) {}

    public function check(): int
    {
        $userId = (int) $this->session->getSession(self::SESSION_KEY);

        if ($userId <= 0) {
            throw new \RuntimeException('Unauthorized');
        }

        return $userId;
    }
}
