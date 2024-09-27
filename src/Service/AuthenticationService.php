<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 * Module: DbMPayments
 */

declare(strict_types=1);

namespace App\Service;

use App\Model\AuthenticationModel;
use App\Utility\MailerUtility;
use Dbm\Interfaces\TranslationInterface;

class AuthenticationService
{
    private $model;
    private $translation;
    private $mailer;

    public function __construct(AuthenticationModel $model, TranslationInterface $translation)
    {
        $this->model = $model;
        $this->translation = $translation;
        $this->mailer = new MailerUtility();
    }

    public function getMetaRegister(): array
    {
        return [
            'meta.title' => $this->translation->trans('register.title') . ' - ' . $this->translation->trans('website.name'),
            'meta.description' => $this->translation->trans('register.description'),
            'meta.keywords' => $this->translation->trans('register.keywords'),
        ];
    }

    public function handleRegistration(array $dataForm): bool
    {
        $password = password_hash($dataForm['password'], PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(20));

        $queryParams = [
            ':login' => $dataForm['login'],
            ':email' => $dataForm['email'],
            ':password' => $password,
            ':token' => $token
        ];

        if ($this->model->createAccount($queryParams)) {
            $this->sendRegistrationEmail($dataForm['login'], $dataForm['email'], $token);
            return true;
        }

        return false;
    }

    public function getMetaLogin(): array
    {
        return [
            'meta.title' => $this->translation->trans('login.title') . ' - ' . $this->translation->trans('website.name'),
            'meta.description' => $this->translation->trans('login.description'),
            'meta.keywords' => $this->translation->trans('login.keywords'),
        ];
    }

    private function sendRegistrationEmail(string $login, string $email, string $token): void
    {
        $arraySend = [
            'subject' => $this->translation->trans('register.mailer.subject'),
            'sender_name' => trim(getenv('APP_NAME'), '"'),
            'sender_email' => trim(getenv('APP_EMAIL'), '"'),
            'recipient_name' => $login,
            'recipient_email' => $email,
            'page_address' => getenv('APP_URL'),
            'message_template' => "register-created-account.html",
            'token' => $token,
        ];

        $this->mailer->sendMessage($arraySend);
    }
}
