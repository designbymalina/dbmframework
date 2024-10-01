<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Controller;

use App\Form\AuthenticationForm;
use App\Model\AuthenticationModel;
use App\Service\AuthenticationService;
use Dbm\Classes\BaseController;
use Dbm\Classes\RememberMe;
use Dbm\Interfaces\DatabaseInterface;

class AuthenticationController extends BaseController
{
    private $model;
    private $form;
    private $service;
    private $remember;

    public function __construct(DatabaseInterface $database)
    {
        parent::__construct($database);

        $this->model = new AuthenticationModel($database, $this->translation);
        $this->form = new AuthenticationForm($this->model, $this->translation);
        $this->service = new AuthenticationService($this->model, $this->translation);
        $this->remember = new RememberMe($database);
    }

    /* @Route: "/register" */
    public function register()
    {
        if ($this->getSession(getenv('APP_SESSION_KEY'))) {
            $this->redirect("./account");
        }

        $csrfToken = $this->csrfToken();
        $this->setSession('csrf_token', $csrfToken);

        $meta = $this->service->getMetaRegister();

        $this->render('authentication/register.phtml', [
            'meta' => $meta,
            'form' => ['token' => $csrfToken],
        ]);
    }

    /* @Route: "/register/signup" */
    public function signupMethod()
    {
        $csrfToken = $this->requestData('csrf_token');

        if (!$this->form->validateCsrfToken($this->getSession('csrf_token'), $csrfToken)) {
            $this->setFlash("messageDanger", $this->translation->trans('alert.invalid_csrf_token'));
            $this->redirect("./register");
            return;
        }

        $dataForm = $this->getRegisterRequestData();

        $errorValidate = $this->form->validateRegisterForm(
            $dataForm['login'],
            $dataForm['email'],
            $dataForm['password'],
            $dataForm['confirmation']
        );

        if (empty($errorValidate)) {
            if ($this->service->handleRegistration($dataForm)) {
                $this->setFlash("messageSuccess", $this->translation->trans('register.alert.account_created'));
                $this->redirect("./login");
            } else {
                $this->setFlash("messageDanger", $this->translation->trans('alert.unexpected_error_try_again'));
                $this->redirect("./register");
            }
        } else {
            $meta = $this->service->getMetaRegister();
            $dataForm = array_merge($dataForm, $errorValidate);

            $this->render('authentication/register.phtml', [
                'meta' => $meta,
                'form' => $dataForm,
            ]);
        }
    }

    /* @Route: "/register/verified.php" */
    public function verifiedMethod()
    {
        $verifiedAccount = $this->model->verifiedAccountEmail();

        $this->setFlash($verifiedAccount['type'], $verifiedAccount['message']);
        $this->redirect("./login");
    }

    /* @Route: "/login" */
    public function login()
    {
        if ($this->getSession(getenv('APP_SESSION_KEY'))) {
            $this->redirect("./account");
        }

        $csrfToken = $this->csrfToken();
        $this->setSession('csrf_token', $csrfToken);

        $meta = $this->service->getMetaLogin();

        $this->render('authentication/login.phtml', [
            'meta' => $meta,
            'form' => ['token' => $csrfToken],
        ]);
    }

    /* @Route: "/login/signin" */
    public function signinMethod()
    {
        $csrfToken = $this->requestData('csrf_token');

        if (!$this->form->validateCsrfToken($this->getSession('csrf_token'), $csrfToken)) {
            $this->setFlash("messageDanger", $this->translation->trans('alert.invalid_csrf_token'));
            $this->redirect("./login");
            return;
        }

        $translation = $this->translation;
        $dataForm = $this->getLoginRequestData();

        $errorValidate = $this->form->validateLoginForm($dataForm['login'], $dataForm['password']);

        if (!empty($errorValidate['user_id'])) {
            if (!empty($dataForm['remember_me']) && is_string($dataForm['remember_me'])) {
                $this->remember->createRememberMe((int) $errorValidate['user_id']);
            }

            if (session_regenerate_id(true)) {
                $this->setSession(getenv('APP_SESSION_KEY'), $errorValidate['user_id']);
                $this->setFlash("messageSuccess", $translation->trans('login.message.logged_in'));
                $this->redirect("./account");
            } else {
                $this->setFlash("messageDanger", $translation->trans('alert.unexpected_error'));
                $this->redirect("./login");
            }
        } else {
            $dataForm = array_merge($dataForm, $errorValidate);
            $meta = $this->service->getMetaLogin();

            $this->render('authentication/login.phtml', [
                'meta' => $meta,
                'form' => $dataForm,
            ]);
        }
    }

    /* @Route: "/login/logout" */
    public function logoutMethod(): void
    {
        if ($this->remember->checkRememberMe($this) === true) {
            $this->remember->removeRememberMe($this);
        }

        $this->destroySession();
        $this->setFlash("messageSuccess", $this->translation->trans('login.message.logged_out'));
        $this->redirect('./');
    }

    /**
     * Method to retrieve registration form data
     */
    private function getRegisterRequestData(): array
    {
        return [
            'login' => $this->requestData('dbm_login'),
            'email' => $this->requestData('dbm_email'),
            'password' => $this->requestData('dbm_password'),
            'confirmation' => $this->requestData('dbm_confirmation'),
            'token' => $this->requestData('csrf_token'),
        ];
    }

    /**
     * Method to retrieve login form data
     */
    private function getLoginRequestData(): array
    {
        return [
            'login' => $this->requestData('dbm_login'),
            'password' => $this->requestData('dbm_password'),
            'remember_me' => $this->requestData('dbm_remember_me'),
            'token' => $this->requestData('csrf_token'),
            'error_login' => '',
            'error_password' => '',
        ];
    }
}
