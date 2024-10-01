<?php
/*
 * Application: DbM Framework v2
 * Author: Arthur Malinowsky (Design by Malina)
 * License: MIT
 * Web page: www.dbm.org.pl
 * Contact: biuro@dbm.org.pl
*/

namespace Dbm\Classes;

use Dbm\Interfaces\DatabaseInterface;

class RememberMe
{
    public const COOKIE_REMEMBER_ME = 'dbmRememberMe'; // Cookie name for "Remember Me"

    private $database;

    public function __construct(DatabaseInterface $database)
    {
        $this->database = $database;
    }

    /**
     * Check if the user is logged in via session or "Remember Me" token.
     */
    public function checkRememberMe(object $controller): bool
    {
        if (!empty($controller->getSession(getenv('APP_SESSION_KEY')))) {
            return true;
        }

        $rememberMeToken = filter_input(INPUT_COOKIE, self::COOKIE_REMEMBER_ME, FILTER_DEFAULT);

        if ($rememberMeToken && preg_match('/^[a-f0-9]{16}:[a-f0-9]{64}$/', $rememberMeToken)) {
            $userRemember = $this->findUserByToken($rememberMeToken);

            if ($userRemember) {
                session_regenerate_id(true);
                $controller->setSession(getenv('APP_SESSION_KEY'), (string) $userRemember->user_id);

                $this->createRememberMe($userRemember->user_id);

                return true;
            } else {
                setcookie(self::COOKIE_REMEMBER_ME, '', time() - 3600, '/', '', true, true);
            }
        }

        return false;
    }

    /**
     * Create a new "Remember Me" cookie and store token in the database.
     */
    public function createRememberMe(int $userId): bool
    {
        $selector = bin2hex(random_bytes(8));
        $validator = bin2hex(random_bytes(32));
        $hashedValidator = hash('sha256', $validator);

        $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));

        $tokenData = [
            'user_id' => $userId,
            'selector' => $selector,
            'validator' => $hashedValidator,
            'expiry' => $expiry,
        ];

        if ($this->insertUserToken($tokenData)) {
            $this->setCookie(self::COOKIE_REMEMBER_ME, "$selector:$validator", time() + (86400 * 30));
            return true;
        }

        return false;
    }

    /**
     * Remove the "Remember Me" cookie and delete the associated token.
     */
    public function removeRememberMe(object $controller): void
    {
        $sessionUserId = $controller->getSession(getenv('APP_SESSION_KEY'));
        $controller->unsetSession(getenv('APP_SESSION_KEY'));

        $this->deleteUserToken((int) $sessionUserId);

        $this->unsetCookie(self::COOKIE_REMEMBER_ME);
    }

    private function findUserByToken(string $token): ?object
    {
        $tokens = $this->parseToken($token);

        if (!$tokens) {
            return null;
        }

        $query = "SELECT * FROM dbm_remember_me WHERE selector=:selector AND expiry > now() LIMIT 1";
        $this->database->queryExecute($query, [':selector' => $tokens[0]]);

        if ($this->database->rowCount() == 0) {
            return null;
        }

        return $this->database->fetchObject();
    }

    private function parseToken(string $token): ?array
    {
        $parts = explode(':', $token);

        if ($parts && count($parts) == 2) {
            return [$parts[0], $parts[1]];
        }

        return null;
    }

    private function insertUserToken(array $params): bool
    {
        [$columns, $placeholders, $filteredData] = $this->database->buildInsertQuery($params);

        $query = "INSERT INTO dbm_remember_me ($columns) VALUES ($placeholders)";

        return $this->database->queryExecute($query, $filteredData);
    }

    private function deleteUserToken(int $uid): bool
    {
        $query = "DELETE FROM dbm_remember_me WHERE user_id=:user_id";

        return $this->database->queryExecute($query, [':user_id' => $uid]);
    }

    private function setCookie(string $name, string $value, int $expiry): void
    {
        setcookie($name, $value, time() + $expiry, '/', '', true, true);
        $_COOKIE[$name] = $value;
    }

    private function unsetCookie(string $name): void
    {
        if (isset($_COOKIE[$name])) {
            setcookie($name, '', time() - 3600, '/', '', true, true);
            unset($_COOKIE[$name]);
        }
    }
}
