<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Controller;

use Dbm\Classes\FrameworkClass;
use App\Service\MailerService;
use Dbm\Classes\TranslationClass;

class RegisterController extends FrameworkClass
{
    private $controllerModel;
    private $translation;

    public function __construct()
    {
        $this->controllerModel = $this->model('registerModel');

        $translation = new TranslationClass();
        $this->translation = $translation;
    }

    public function index()
    {
        if ($this->getSession('dbmUserId')) {
            $this->redirect("account");
        }

        $translation = $this->translation;

        $data = [
            'meta.title' => $translation->trans('register.title') . ' - ' . $translation->trans('website.name'),
            'meta.description' => $translation->trans('register.description'),
            'meta.keywords' => $translation->trans('register.keywords'),
            'data.form' => array(),
        ];

        $this->view("register/index.html.php", $data);
    }

    public function signupMethod()
    {
        $translation = $this->translation;

        $dataForm = [
            'login' => $this->requestData('dbm_login'),
            'email' => $this->requestData('dbm_email'),
            'password' => $this->requestData('dbm_password'),
            'confirmation' => $this->requestData('dbm_confirmation'),
        ];

        $errorValidate = $this->controllerModel->validateRegisterForm(
            $dataForm['login'],
            $dataForm['email'],
            $dataForm['password'],
            $dataForm['confirmation']
        );

        if (empty($errorValidate)) {
            $password = password_hash($dataForm['password'], PASSWORD_DEFAULT);
            $token = bin2hex(random_bytes(20));
            $queryParams = [':login' => $dataForm['login'], ':email' => $dataForm['email'], ':password' => $password, ':token' => $token];

            if ($this->controllerModel->createAccount($queryParams)) {
                $arraySend = [
                    'subject' => $translation->trans('register.mailer.subject'),
                    'recipient_email' => $dataForm['email'],
                    'recipient_name' => $dataForm['login'],
                    'message_template' => "register-created-account.html",
                    'token' => $token,
                ];

                $send = new MailerService();
                $send->sendMessage($arraySend);

                $this->setFlash("messageSuccess", $translation->trans('register.alert.account_created'));
                $this->redirect("login");
            } else {
                $this->setFlash("messageDanger", $translation->trans('alert.unexpected_error_try_again'));
                $this->redirect("index");
            }
        } else {
            $dataForm = array_merge($dataForm, $errorValidate);

            $data = [
                'meta.title' => $translation->trans('register.title') . ' - ' . $translation->trans('website.name'),
                'meta.description' => $translation->trans('register.description'),
                'meta.keywords' => $translation->trans('register.keywords'),
                'data.form' => $dataForm,
            ];

            $this->view("register/index.html.php", $data);
        }
    }

    public function verifiedMethod()
    {
        $verifiedAccount = $this->controllerModel->verifiedAccountEmail();

        $this->setFlash($verifiedAccount['type'], $verifiedAccount['message']);
        $this->redirect("login");
    }
}
