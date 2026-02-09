<?php

/**
 * Application: DbM Framework
 * A lightweight PHP framework for building web applications.
 *
 * @author Artur Malinowski
 * @copyright Design by Malina (All Rights Reserved)
 * @license MIT
 * @link https://www.dbm.org.pl
 *
 * Example of usage
 *
 * BaseController:
 * protected function requireAuth(): int
 * {
 *     return $this->authGuard->check();
 * }
 * Controller:
 * public function index(): ResponseInterface
 * {
 *     $userId = $this->requireAuth();
 * }
 */

declare(strict_types=1);

namespace Dbm\Security;

use Dbm\Security\Contracts\AccessControlInterface;

final class NullAccessControl implements AccessControlInterface
{
    public function userCan(int $userId, string $permission): bool
    {
        return false;
    }
}
