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
use App\Utility\ErrorLoggerUtility;
use App\Utility\MailerUtility;
use Dbm\Interfaces\TranslationInterface;
use Exception;

class AuthenticationService
{
    private const TOKEN_EXPIRES_TIME = '2'; // Token expiration time in hours, type string
    private $model;
    private $translation;
    private $mailer;
    private $logger;

    public function __construct(AuthenticationModel $model, TranslationInterface $translation)
    {
        $this->model = $model;
        $this->translation = $translation;
        $this->mailer = new MailerUtility();
        $this->logger = new ErrorLoggerUtility();
    }

    public function isPostRequest()
    {
        return strtolower($_SERVER['REQUEST_METHOD']) === 'post';
    }

    public function getMetaRegister(): array
    {
        $translation = $this->translation;

        return [
            'meta.title' => $translation->trans('register.title') . ' - ' . $translation->trans('website.name'),
            'meta.description' => $translation->trans('register.description'),
            'meta.keywords' => $translation->trans('register.keywords'),
            'meta.robots' => "noindex,nofollow",
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
        $translation = $this->translation;

        return [
            'meta.title' => $translation->trans('login.title') . ' - ' . $translation->trans('website.name'),
            'meta.description' => $translation->trans('login.description'),
            'meta.keywords' => $translation->trans('login.keywords'),
        ];
    }

    public function getMetaReset(): array
    {
        $translation = $this->translation;

        return [
            'meta.title' => $translation->trans('reset.title') . ' - ' . $translation->trans('website.name'),
            'meta.description' => $translation->trans('reset.description'),
            'meta.keywords' => $translation->trans('reset.keywords'),
            'meta.robots' => "noindex,nofollow",
        ];
    }

    public function makeInsertResetPassword(string $email): bool
    {
        try {
            $expiresTime = self::TOKEN_EXPIRES_TIME;
            $expiresAt = date('Y-m-d H:i:s', strtotime("+$expiresTime hour"));
            $tokenReset = bin2hex(random_bytes(32));

            $insertData = [
                'email' => $email,
                'token' => $tokenReset,
                'expires' => $expiresAt,
            ];

            $this->model->insertResetPassword($insertData);

            if (!$this->sendResetPassword($email, $tokenReset, $expiresTime)) {
                $this->logger->log(sprintf($this->translation->trans('reset.keywords'), $email));
                return false;
            }

            return true;
        } catch (Exception $e) {
            $this->logger->logException($e);
            return false;
        }
    }

    public function makeUpdateUserPassword(string $email, string $password): bool
    {
        try {
            $updateData = [
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
            ];

            return $this->model->updateUserPassword($updateData);
        } catch (Exception $e) {
            $this->logger->logException($e);
            return false;
        }
    }

    private function sendResetPassword(string $email, string $token, string $expires): bool
    {
        $arraySend = [
            'subject' => $this->translation->trans('reset.mailer.subject'),
            'sender_name' => getenv('MAIL_FROM_NAME'),
            'sender_email' => getenv('MAIL_FROM_EMAIL'),
            'recipient_email' => $email,
            'page_address' => getenv('APP_URL'),
            'message_template' => "reset-password.html",
            'token' => $token,
            'expires' => $expires,
        ];

        return $this->mailer->sendMessage($arraySend);
    }

    private function sendRegistrationEmail(string $login, string $email, string $token)
    {
        $arraySend = [
            'subject' => $this->translation->trans('register.mailer.subject'),
            'sender_name' => getenv('MAIL_FROM_NAME'),
            'sender_email' => getenv('MAIL_FROM_EMAIL'),
            'recipient_name' => $login,
            'recipient_email' => $email,
            'page_address' => getenv('APP_URL'),
            'message_template' => "register-created-account.html",
            'token' => $token,
        ];

        $this->mailer->sendMessage($arraySend);
    }
}
