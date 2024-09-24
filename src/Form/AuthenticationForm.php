<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 * Module: DbMPayments
 */

declare(strict_types=1);

namespace App\Form;

use App\Model\AuthenticationModel;
use Dbm\Interfaces\TranslationInterface;

class AuthenticationForm
{
    private const VALIDATION_LOGIN = 'loginNotFound';
    private const VALIDATION_PASSWORD = 'passwordNotMatched';

    private $translation;
    private $model;

    public function __construct(AuthenticationModel $model, TranslationInterface $translation)
    {
        $this->translation = $translation;
        $this->model = $model;
    }

    public function validateCsrfToken(string $sessionToken, string $formToken): bool
    {
        return hash_equals($sessionToken, $formToken);
    }

    public function validateRegisterForm(string $login, string $email, string $password, string $confirmation): array
    {
        $translation = $this->translation;
        $data = [];

        if (empty($login)) {
            $data['error_login'] = $translation->trans('register.alert.login_required');
        } elseif (!preg_match("/^[a-z\d_]{2,30}$/i", $login)) {
            $data['error_login'] = $translation->trans('register.alert.login_pattern');
        } elseif ($this->model->checkLogin($login)) {
            $data['error_login'] = $translation->trans('register.alert.login_exist');
        }

        if (empty($email)) {
            $data['error_email'] = $translation->trans('register.alert.email_required');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $data['error_email'] = $translation->trans('register.alert.email_filter');
        } elseif ($this->model->checkEmail($email)) {
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

    public function validateLoginForm(string $login, string $password): array
    {
        $data = [];
        $translation = $this->translation;

        if (empty($login)) {
            $data['error_login'] = $translation->trans('login.message.login_required');
        }

        if (empty($password)) {
            $data['error_password'] = $translation->trans('login.message.password_required');
        }

        if (empty($data['error_login']) && empty($data['error_password'])) {
            $queryParams = [':login' => $login, ':email' => $login];
            $correctUser = $this->model->checkIsUserCorrect($queryParams, $password);

            if (!empty($correctUser)) {
                if ($correctUser == self::VALIDATION_LOGIN) {
                    $data['error_login'] = $translation->trans('login.message.login_incorrect');
                } elseif ($correctUser == self::VALIDATION_PASSWORD) {
                    $data['error_password'] = $translation->trans('login.message.password_incorrect');
                } else {
                    $data['user_id'] = trim($correctUser);
                }
            }
        }

        return $data;
    }
}
