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

        session_start();

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
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

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
