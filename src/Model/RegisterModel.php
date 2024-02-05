<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Model;

use Dbm\Classes\DatabaseClass;
use Dbm\Classes\TranslationClass;

/*
 * TODO! __construct() -> translation itp.?
 */
class RegisterModel extends DatabaseClass
{
    public function createAccount(array $data): bool
    {
        $query = "INSERT INTO dbm_user (login, email, password, token)"
            . " VALUES (:login, :email, :password, :token)";

        if ($this->queryExecute($query, $data)) {
            $userId = $this->getLastInsertId();
            $query = "INSERT INTO dbm_user_details (user_id) VALUES (:uid)";

            return $this->queryExecute($query, [':uid' => $userId]);
        }

        return false;
    }

    public function verifiedAccountEmail(): array
    {
        $translation = new TranslationClass(); // TODO! Powtarza sie, zmien to?!

        if (isset($_GET['token'])) {
            $token = trim($_GET['token']);

            $query = "SELECT token FROM dbm_user WHERE token = :token AND verified = false LIMIT 1";

            if ($this->queryExecute($query, [':token' => $token])) {
                if ($this->rowCount() > 0) {
                    $query = "UPDATE dbm_user SET verified = true WHERE token = :token";

                    if ($this->queryExecute($query, [':token' => $token])) {
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

    public function validateRegisterForm(string $login, string $email, string $password, string $confirmation): array
    {
        $translation = new TranslationClass(); // TODO! Powtarza sie, zmien to?!
        $data = [];

        if (empty($login)) {
            $data['error_login'] = $translation->trans('register.alert.login_required');
        } elseif (!preg_match("/^[a-z\d_]{2,30}$/i", $login)) {
            $data['error_login'] = $translation->trans('register.alert.login_pattern');
        } elseif ($this->checkLogin($login)) {
            $data['error_login'] = $translation->trans('register.alert.login_exist');
        }

        if (empty($email)) {
            $data['error_email'] = $translation->trans('register.alert.email_required');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $data['error_email'] = $translation->trans('register.alert.email_filter');
        } elseif ($this->checkEmail($email)) {
            $data['error_email'] = $translation->trans('register.alert.email_exist');
        }

        if (empty($password)) {
            $data['error_password'] = $translation->trans('register.alert.password_required');
        } elseif (!preg_match("/^(?=.*[0-9])(?=.*[A-Z]).{6,30}$/", $password)) {
            $data['error_password'] = $translation->trans('register.alert.password_pattern');
        }

        if (empty($confirmation)) {
            $data['error_confirmation'] = $translation->trans('register.alert.password_confirmation_required');
        } elseif ($password !== $confirmation) {
            $data['error_confirmation'] = $translation->trans('register.alert.password_confirmation_different');
        }

        return $data;
    }

    private function checkLogin(string $login): bool
    {
        $query = "SELECT login FROM dbm_user WHERE login = '$login' LIMIT 1";

        $stmt = $this->querySql($query);

        if ($stmt->rowCount() == 0) {
            return false;
        }

        return true;
    }

    private function checkEmail(string $email): bool
    {
        $query = "SELECT email FROM dbm_user WHERE email = '$email' LIMIT 1";

        $stmt = $this->querySql($query);

        if ($stmt->rowCount() == 0) {
            return false;
        }

        return true;
    }
}
