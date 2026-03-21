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
    public function __construct(
        private SessionManager $session,
        private ?string $sessionKey = null
    ) {
        $this->sessionKey = $sessionKey ?? getenv('APP_SESSION_KEY');
    }

    public function id(): int
    {
        return (int) $this->session->getSession($this->sessionKey);
    }

    public function check(): int
    {
        $userId = $this->id();

        if ($userId <= 0) {
            throw new \RuntimeException('Unauthorized');
        }

        return $userId;
    }
}
