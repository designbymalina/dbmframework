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

class LoginModel extends DatabaseClass
{
    private const VALIDATION_LOGIN = 'loginNotFound';
    private const VALIDATION_PASSWORD = 'passwordNotMatched';

    public function validateLoginForm(string $login, string $password): array
    {
        $data = [];
        $translation = new TranslationClass();

        if (empty($login)) {
            $data['error_login'] = $translation->trans('login.message.login_required');
        }

        if (empty($password)) {
            $data['error_password'] = $translation->trans('login.message.password_required');
        }

        if (empty($data['error_login']) && empty($data['error_password'])) {
            $queryParams = [':login' => $login, ':email' => $login];
            $correctUser = $this->checkIsUserCorrect($queryParams, $password);

            if (!empty($correctUser)) {
                if ($correctUser == self::VALIDATION_LOGIN) {
                    $data['error_login'] = $translation->trans('login.message.login_incorrect');
                } elseif ($correctUser == self::VALIDATION_PASSWORD) {
                    $data['error_password'] = $translation->trans('login.message.password_incorrect');
                } else {
                    // INFO: $userKey name identical to the key in LoginController.php -> signinMethod()
                    $data['correct_user_id'] = trim($correctUser);
                }
            }
        }

        return $data;
    }

    private function checkIsUserCorrect(array $params, string $password): string
    {
        $query = "SELECT * FROM dbm_user WHERE (login=:login OR email=:email) AND verified=true LIMIT 1";

        $this->queryExecute($query, $params);

        if ($this->rowCount() == 0) {
            return self::VALIDATION_LOGIN;
        }

        $result = $this->fetchObject();

        if (!password_verify($password, $result->password)) {
            return self::VALIDATION_PASSWORD;
        }

        return $result->id;
    }
}
