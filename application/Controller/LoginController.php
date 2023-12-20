<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Controller;

use Dbm\Classes\FrameworkClass;
use Dbm\Classes\TranslationClass;

class LoginController extends FrameworkClass
{
    private $controllerModel;
    private $translation;

    public function __construct()
    {
        $this->controllerModel = $this->model('LoginModel');

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
            'meta.title' => $translation->trans('login.title') . ' - ' . $translation->trans('website.name'),
            'meta.description' => $translation->trans('login.description'),
            'meta.keywords' => $translation->trans('login.keywords'),
        ];

        $this->view("login/index.html.php", $data);
    }

    public function signinMethod()
    {
        $userKey = 'correct_user_id';
        $translation = $this->translation;

        $dataForm = [
            'login' => $this->requestData('dbm_login'),
            'password' => $this->requestData('dbm_password'),
            'error_login' => '',
            'error_password' => '',
        ];

        $errorValidate = $this->controllerModel->validateLoginForm($dataForm['login'], $dataForm['password']);

        if (array_key_exists($userKey, $errorValidate)) {
            $this->setSession("dbmUserId", $errorValidate[$userKey]);
            $this->setFlash("messageSuccess", $translation->trans('login.message.logged_in'));
            $this->redirect("account");
        } else {
            $dataForm = array_merge($dataForm, $errorValidate);

            $data = [
                'meta.title' => $translation->trans('login.title') . ' - ' . $translation->trans('website.name'),
                'meta.description' => $translation->trans('login.description'),
                'meta.keywords' => $translation->trans('login.keywords'),
                'data.form' => $dataForm,
            ];

            $this->view("login/index.html.php", $data);
        }
    }

    public function logoutMethod(): void
    {
        $translation = $this->translation;

        $this->unsetSession('dbmUserId');
        $this->setFlash("messageSuccess", $translation->trans('login.message.logged_out'));
        $this->redirect('./'); // TODO! Sprawdz 'index' dla zmiany jezyka
    }
}
