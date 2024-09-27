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

use Dbm\Interfaces\DatabaseInterface;

class AdminBaseController extends BaseController
{
    protected $database;

    public function __construct(?DatabaseInterface $database = null)
    {
        // Przekazanie bazy danych do klasy nadrzędnej (BaseController)
        parent::__construct($database);
        $this->database = $database;

        if (empty(getenv('DB_NAME'))) {
            $this->setFlash('messageInfo', 'Brak połączenia z bazą danych.');
            $this->redirect('./home');
        }

        // Sprawdzenie, czy użytkownik jest zalogowany
        $sessionKey = $this->getSession(getenv('APP_SESSION_KEY'));

        if (empty($sessionKey)) {
            $this->redirect("./login");
        }

        // Pobranie ID użytkownika z sesji
        $userId = (int) $this->getSession(getenv('APP_SESSION_KEY'));

        // Sprawdzenie uprawnień użytkownika. Działa jeśli baza danych jest zainicjalizowana, co zapewnia bezpieczne działanie aplikacji.
        if ($this->userPermissions($userId) !== 'ADMIN') {
            $this->redirect("./");
        }

        /* TODO! Dodatkowo sprawdź ważność sesji (opcjonalne)
        if (!$this->isValidSession($userId)) {
            $this->redirect("./login");
        } */
    }

    /* TODO! Można dopisać dodatkowe zabezpieczenie dla penelu administracyjnego
    // Metoda do sprawdzania, czy sesja jest ważna
    protected function isValidSession(int $userId): bool
    {
        // Zakładamy, że `getUserSession()` pobiera sesję użytkownika z bazy
        $userSession = $this->database->getUserSession($userId);

        // Sprawdzenie, czy sesja istnieje i czy nie wygasła
        if (!$userSession || $userSession['session_expiry'] < time()) {
            return false; // Sesja wygasła lub nie istnieje
        }

        return true; // Sesja jest ważna
    } */
}
