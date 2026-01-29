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
 * TODO! Klasa i cąły moduł do poprawienia, usunąć SELECT z Core "dbm", podobnie RoleEnum itd...
 * Core powinno operować na stringach / value objects
 * public function userHasRole(int $userId, string $role): bool
 */

declare(strict_types=1);

namespace Dbm\Security;

use Dbm\Database\Contracts\DatabaseInterface;
use Dbm\Enum\RoleEnum;

class AccessControl
{
    private ?DatabaseInterface $database;

    /** @var array<int, RoleEnum> Cache ról użytkowników (user_id => RoleEnum) */
    private array $roleCache = [];

    public function __construct(?DatabaseInterface $database = null)
    {
        $this->database = $database;
    }

    /**
     * Pobiera rolę użytkownika z cache lub bazy.
     *
     * @param int $userId ID użytkownika
     * @return RoleEnum|null Rola użytkownika
     */
    private function getUserRole(int $userId): ?RoleEnum
    {
        // Jeśli już mamy w pamięci
        if (isset($this->roleCache[$userId])) {
            return $this->roleCache[$userId];
        }

        // Bez bazy nie da się nic zrobić
        if (!$this->database) {
            return null;
        }

        $query = "SELECT roles FROM dbm_user WHERE id = :id";
        $user = $this->database->fetch($query, ['id' => $userId]);

        if (empty($user)) {
            return null;
        }

        $roleValue = strtoupper(trim($user['roles'] ?? ''));
        $role = RoleEnum::tryFrom($roleValue);

        // Zapisz w cache (nawet null, aby nie powtarzać błędnych zapytań)
        $this->roleCache[$userId] = $role;

        return $role;
    }

    /**
     * Sprawdza, czy użytkownik ma określoną rolę.
     *
     * @param int $userId ID użytkownika
     * @param RoleEnum $role Rola do sprawdzenia
     * @return bool Czy użytkownik ma daną rolę
     */
    public function userHasRole(int $userId, RoleEnum $role): bool
    {
        $userRole = $this->getUserRole($userId);
        return $userRole === $role;
    }

    /**
     * Sprawdza, czy użytkownik ma dane uprawnienie (RBAC ready).
     *
     * @param int $userId ID użytkownika
     * @param string $permission Nazwa uprawnienia do sprawdzenia
     * @return bool Czy użytkownik ma dane uprawnienie
     */
    public function userCan(int $userId, string $permission): bool
    {
        $role = $this->getUserRole($userId)?->value;

        if (!$role) {
            return false;
        }

        // Mapa ról i ich uprawnień (tylko dla aktualnych `roles` => ADMIN i USER)
        $permissionsMap = [
            RoleEnum::ADMIN->value => ['*'], // ADMIN może wszystko
            RoleEnum::USER->value  => ['access_to_account'],
        ];

        $allowed = $permissionsMap[$role] ?? [];

        return in_array('*', $allowed, true) || in_array($permission, $allowed, true);
    }
}
