<?php
/*
 * Application: DbM Framework v2.1
 * Author: Arthur Malinowsky (Design by Malina)
 * License: MIT
 * Web page: www.dbm.org.pl
 * Contact: biuro@dbm.org.pl
*/

declare(strict_types=1);

namespace Dbm\Classes;

use Dbm\Classes\TemplateEngine;
use Dbm\Interfaces\BaseInterface;
use Dbm\Interfaces\DatabaseInterface;

class BaseController extends TemplateEngine implements BaseInterface
{
    public $translation;
    private $database;

    public function __construct(DatabaseInterface $database)
    {
        $this->database = $database;

        $translation = new Translation();
        $this->translation = $translation;
    }

    // Request data
    public function requestData(string $fieldName): ?string
    {
        if ($_SERVER['REQUEST_METHOD'] == "POST" || $_SERVER['REQUEST_METHOD'] == 'post') {
            if (array_key_exists($fieldName, $_POST)) {
                return trim($_POST[$fieldName]);
            } elseif (array_key_exists($fieldName, $_GET)) {
                return trim($_GET[$fieldName]);
            }
        } elseif ($_SERVER['REQUEST_METHOD'] == 'GET' || $_SERVER['REQUEST_METHOD'] == 'get') {
            if (array_key_exists($fieldName, $_GET)) {
                return trim($_GET[$fieldName]);
            }
        }

        return null;
    }

    // Set session
    public function setSession(string $sessionName, string $sessionValue): void
    {
        if (!empty($sessionName) && !empty($sessionValue)) {
            $_SESSION[$sessionName] = $sessionValue;
        }
    }

    // Get session
    public function getSession(string $sessionName): ?string
    {
        if (isset($_SESSION['dbmUserId']) && !empty($sessionName)) {
            return $_SESSION[$sessionName];
        }

        return null;
    }

    // Unset session
    public function unsetSession(string $sessionName): void
    {
        if (!empty($sessionName)) {
            unset($_SESSION[$sessionName]);
        }
    }

    // Destroy whole sessions
    public function destroySession(): void
    {
        session_destroy();
    }

    // Set flash message
    public function setFlash(string $sessionName, string $message): void
    {
        if (!empty($sessionName) && !empty($message)) {
            $_SESSION[$sessionName] = $message;
        }
    }

    // Show flash message
    public function flash(string $sessionName, string $className): void
    {
        if (!empty($sessionName) && !empty($className) && isset($_SESSION[$sessionName])) {
            echo('<div class="container">' . "\n"
                . '    <div class="alert ' . $className . ' alert-dismissible fade show mt-3" role="alert">' . "\n"
                . '        <span>' . $_SESSION[$sessionName] . '</span>' . "\n"
                . '        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' . "\n"
                . '    </div>' . "\n"
                . '</div>' . "\n");

            unset($_SESSION[$sessionName]);
        }
    }

    // Show panel flash message
    public function flashPanel(string $sessionName, string $className): void
    {
        if (!empty($sessionName) && !empty($className) && isset($_SESSION[$sessionName])) {
            echo('    <div class="alert ' . $className . ' alert-dismissible fade show" role="alert">' . "\n"
                . '        <span>' . $_SESSION[$sessionName] . '</span>' . "\n"
                . '        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' . "\n"
                . '    </div>' . "\n");

            unset($_SESSION[$sessionName]);
        }
    }

    // Redirect to location
    public function redirect(string $path, array $params = []): void
    {
        if (!empty($params)) {
            $path = $path . '?' . http_build_query($params);
        }

        $dir = dirname($_SERVER['PHP_SELF']);
        $public = '/';

        if (strpos($dir, 'public')) { // for localhost (application in catalog)
            $public = strstr($dir, 'public', true);
        }

        $url = empty($_SERVER['HTTPS']) ? 'http' : 'https';
        $url = $url . '://' . $_SERVER['HTTP_HOST'] . $public . $path;

        header("Location: " . $url);
        exit;
    }

    // Get Database
    public function getDatabase(): DatabaseInterface
    {
        return $this->database;
    }

    // User permissions
    public function userPermissions(int $id): ?string
    {
        $database = $this->database;

        $query = "SELECT roles FROM dbm_user WHERE id = ?";

        $database->queryExecute($query, [$id]);

        if ($database->rowCount() == 0) {
            return null;
        }

        $data = $database->fetchObject();

        return $data->roles;
    }
}
