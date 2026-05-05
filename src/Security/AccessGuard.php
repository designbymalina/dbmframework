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

use Dbm\Exceptions\ForbiddenException;
use Dbm\Exceptions\UnauthorizedWebException;
use Dbm\Security\Contracts\AccessControlInterface;

final class AccessGuard
{
    public function __construct(
        private readonly AuthGuard $auth,
        private readonly AccessControlInterface $access
    ) {}

    public function checkPermission(string $permission): void
    {
        $userId = $this->auth->id();

        if ($userId <= 0) {
            throw new UnauthorizedWebException();
        }

        if (!$this->access->userCan($userId, $permission)) {
            throw new ForbiddenException();
        }
    }

    /**
     * @INFO Metoda nie używana
     *
     * @param string[] $permissions
     */
    public function checkPermissions(array $permissions): void
    {
        $userId = $this->auth->id();

        if ($userId <= 0) {
            throw new UnauthorizedWebException();
        }

        foreach ($permissions as $permission) {
            if ($this->access->userCan($userId, $permission)) {
                return;
            }
        }

        throw new ForbiddenException();
    }
}
