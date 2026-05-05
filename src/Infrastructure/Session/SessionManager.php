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

namespace Dbm\Infrastructure\Session;

use Dbm\Core\Config\AppConfig;

class SessionManager
{
    private bool $started = false;

    /**
     * Starts the session.
     *
     * @return void
     */
    public function start(): void
    {
        if ($this->started || session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $isProduction = AppConfig::getEnv() === AppConfig::ENV_PRODUCTION;

        session_start([
            'cookie_lifetime' => 0,
            'cookie_secure' => $isProduction,
            'cookie_httponly' => true,
            'use_strict_mode' => true,
            'use_only_cookies' => true,
        ]);

        $this->started = true;
    }

    /**
     * Sets a session variable.
     *
     * @param string $sessionName The name/key of the session variable.
     * @param mixed  $sessionValue The value to store.
     */
    public function setSession(string $sessionName, mixed $sessionValue): void
    {
        if ($sessionName === '') {
            return;
        }

        $_SESSION[$sessionName] = $sessionValue;
    }

    /**
     * Retrieves a session variable.
     *
     * @param string $sessionName  The name/key of the session variable.
     * @return mixed
     */
    public function getSession(string $sessionName): mixed
    {
        return $_SESSION[$sessionName] ?? null;
    }

    /**
     * Checks if a session variable exists.
     *
     * @param string $sessionName The name/key of the session variable.
     * @return bool
     */
    public function hasSession(string $sessionName): bool
    {
        return isset($_SESSION[$sessionName]);
    }

    /**
     * Unsets a session variable.
     *
     * @param string $sessionName The name/key of the session variable.
     */
    public function unsetSession(string $sessionName): void
    {
        if (!empty($sessionName)) {
            unset($_SESSION[$sessionName]);
        }
    }

    /**
     * Destroys all session data.
     */
    public function destroySession(): void
    {
        session_unset();
        session_destroy();
    }

    /**
     * Retrieves a session variable by reference.
     *
     * Useful for modifying the session value directly.
     *
     * @param string $sessionName The name/key of the session variable.
     * @return mixed Reference to the session variable.
     */
    public function &getSessionByReference(string $sessionName): mixed
    {
        if (!isset($_SESSION)) {
            session_start();
        }

        if (!array_key_exists($sessionName, $_SESSION)) {
            $_SESSION[$sessionName] = null;
        }

        return $_SESSION[$sessionName];
    }

    public function pop(string $key): mixed
    {
        if (!isset($_SESSION[$key])) {
            return null;
        }

        $value = $_SESSION[$key];
        unset($_SESSION[$key]);
        return $value;
    }

    public function regenerateId(bool $deleteOld = true): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        session_regenerate_id($deleteOld);
    }
}
