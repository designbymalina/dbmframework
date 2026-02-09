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

namespace Dbm\Security\Contracts;

interface AccessControlInterface
{
    /**
     * Sprawdza czy użytkownik o podanym id ma podane uprawnienie.
     *
     * @param int $userId
     * @param string $permission
     * @return bool
     */
    public function userCan(int $userId, string $permission): bool;
}
