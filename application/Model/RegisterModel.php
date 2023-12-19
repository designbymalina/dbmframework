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
 * TODO!
 *  1. __construct() -> translation itp.?
 *  2. Poprawic wyswietlanie bledow w ClassModel! Nie wyswietla szczegolowych informacji gdzie jest blad w zapytaniu (tylko ogolny blad)?
 */
class RegisterModel extends DatabaseClass
{
    public function createAccount(array $data): bool
    {
        $query = "INSERT INTO dbm_user (login, email, password, token)"
            . " VALUES (:login, :email, :password, :token)";

        if ($this->queryExecute($query, $data)) {
            $userId = $this->lastInsertId();
            $query = "INSERT INTO dbm_user_details (user_id) VALUES ($userId)";

            if ($this->queryExecute($query)) {
                return true;
            }
        }

        return false;
    }

    public function validateRegisterForm(string $login, string $email, string $password, string $confirmation): array
    {
        $translation = new TranslationClass(); // TODO! Powtarza sie, zmien to?!
        $data = [];

        if (empty($login)) {
            $data['error_login'] = $translation->trans('register.alert.login_required');
        } elseif (!preg_match("/^[a-z\d_]{2,30}$/i", $login)) {
            $data['error_login'] = $translation->trans('register.alert.login_pattern');
        } elseif ($this->checkLogin([$login])) {
            $data['error_login'] = $translation->trans('register.alert.login_exist');
        }

        if (empty($email)) {
            $data['error_email'] = $translation->trans('register.alert.email_required');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $data['error_email'] = $translation->trans('register.alert.email_filter');
        } elseif ($this->checkEmail([$email])) {
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

    public function verifiedAccountEmail(): array
    {
        $translation = new TranslationClass(); // TODO! Powtarza sie, zmien to?!

        if (isset($_GET['token'])) {
            $token = trim($_GET['token']);

            $query = "SELECT token FROM dbm_user WHERE token='$token' AND verified=false LIMIT 1";

            if ($this->queryExecute($query)) {
                if ($this->rowCount() > 0) {
                    $query = "UPDATE dbm_user SET verified=1 WHERE token='$token'";

                    if ($this->queryExecute($query)) {
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

    private function checkLogin(array $data): bool
    {
        $query = "SELECT login FROM dbm_user WHERE login = ? LIMIT 1";

        if ($this->queryExecute($query, $data)) {
            if ($this->rowCount() > 0) {
                return true;
            }

            return false;
        }
    }

    private function checkEmail(array $data): bool
    {
        $query = "SELECT email FROM dbm_user WHERE email = ? LIMIT 1";

        if ($this->queryExecute($query, $data)) {
            if ($this->rowCount() > 0) {
                return true;
            }

            return false;
        }
    }
}
