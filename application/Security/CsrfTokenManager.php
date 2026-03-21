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

final class CsrfTokenManager
{
    private const SESSION_KEY = 'csrf_token';

    public function __construct(
        private SessionManager $session
    ) {}

    public function isValid(?string $token): bool
    {
        $sessionToken = $this->session->getSession(self::SESSION_KEY);

        return is_string($sessionToken)
            && is_string($token)
            && hash_equals($sessionToken, $token);
    }
}
