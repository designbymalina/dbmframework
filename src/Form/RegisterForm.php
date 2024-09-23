<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 * Module: DbMPayments
 */

declare(strict_types=1);

namespace App\Form;

use App\Model\RegisterModel;
use Dbm\Interfaces\TranslationInterface;

class RegisterForm
{
    private $translation;
    private $model;

    public function __construct(RegisterModel $model, TranslationInterface $translation)
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
}
