<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Model;

use Dbm\Interfaces\DatabaseInterface;
use Dbm\Interfaces\TranslationInterface;

class AuthenticationModel
{
    private const VALIDATION_LOGIN = 'loginNotFound';
    private const VALIDATION_PASSWORD = 'passwordNotMatched';

    private $database;
    private $translation;

    public function __construct(DatabaseInterface $database, TranslationInterface $translation)
    {
        $this->database = $database;
        $this->translation = $translation;
    }

    public function checkLogin(string $login): bool
    {
        $query = "SELECT login FROM dbm_user WHERE login = '$login' LIMIT 1";

        $stmt = $this->database->querySql($query);

        if ($stmt->rowCount() == 0) {
            return false;
        }

        return true;
    }

    public function checkEmail(string $email): bool
    {
        $query = "SELECT email FROM dbm_user WHERE email = '$email' LIMIT 1";

        $stmt = $this->database->querySql($query);

        if ($stmt->rowCount() == 0) {
            return false;
        }

        return true;
    }

    public function createAccount(array $data): bool
    {
        $query = "INSERT INTO dbm_user (login, email, password, token)"
            . " VALUES (:login, :email, :password, :token)";

        if ($this->database->queryExecute($query, $data)) {
            $userId = $this->database->getLastInsertId();
            $query = "INSERT INTO dbm_user_details (user_id) VALUES (:uid)";

            return $this->database->queryExecute($query, [':uid' => $userId]);
        }

        return false;
    }

    public function verifiedAccountEmail(): array
    {
        $translation = $this->translation;

        if (isset($_GET['token'])) {
            $token = trim($_GET['token']);

            $query = "SELECT token FROM dbm_user WHERE token = :token AND verified = false LIMIT 1";

            if ($this->database->queryExecute($query, [':token' => $token])) {
                if ($this->database->rowCount() > 0) {
                    $query = "UPDATE dbm_user SET verified = true WHERE token = :token";

                    if ($this->database->queryExecute($query, [':token' => $token])) {
                        $message = ['type' => "messageSuccess", 'message' => $translation->trans('register.alert.account_verified')];
                    } else {
                        $message = ['type' => "messageDanger", 'message' => $translation->trans('alert.unexpected_error')];
                    }
                } else {
                    $message = ['type' => "messageWarning", 'message' => $translation->trans('register.alert.token_expired')];
                }
            } else {
                $message = ['type' => "messageDanger", 'message' => $translation->trans('alert.unexpected_error')];
            }
        } else {
            $message = ['type' => "messageDanger", 'message' => $translation->trans('register.alert.no_token')];
        }

        return $message;
    }

    public function checkIsUserCorrect(array $params, string $password): string
    {
        $query = "SELECT * FROM dbm_user WHERE (login=:login OR email=:email) AND verified=true LIMIT 1";

        $this->database->queryExecute($query, $params);

        if ($this->database->rowCount() == 0) {
            return self::VALIDATION_LOGIN;
        }

        $result = $this->database->fetchObject();

        if (!password_verify($password, $result->password)) {
            return self::VALIDATION_PASSWORD;
        }

        return (string) $result->id;
    }

    public function getResetPassword(array $data): ?object
    {
        $query = "SELECT * FROM dbm_reset_password WHERE token = :token LIMIT 1";

        $this->database->queryExecute($query, $data);

        if ($this->database->rowCount() == 0) {
            return null;
        }

        return $this->database->fetchObject();
    }

    public function insertResetPassword(array $data): bool
    {
        [$columns, $placeholders, $filteredData] = $this->database->buildInsertQuery($data);
        $query = "INSERT INTO dbm_reset_password ($columns) VALUES ($placeholders)";

        return $this->database->queryExecute($query, $filteredData);
    }

    public function deleteResetPassword(): bool
    {
        $expires = date('Y-m-d H:i:s', strtotime("+1 week"));
        $query = "DELETE FROM dbm_reset_password WHERE expires<:expires";

        return $this->database->queryExecute($query, [':expires' => $expires]);
    }

    public function updateUserPassword(array $data): bool
    {
        [$setClause, $filteredData] = $this->database->buildUpdateQuery($data);
        $query = "UPDATE dbm_user SET $setClause WHERE email=:email";

        return $this->database->queryExecute($query, $filteredData);
    }
}
