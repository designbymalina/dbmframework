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

use Dbm\Security\Contracts\UserRoleProviderInterface;

final class NullUserRoleProvider implements UserRoleProviderInterface
{
    public function getUserRole(int $userId): ?string
    {
        return null;
    }
}
