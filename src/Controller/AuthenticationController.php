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
use DateTime;

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

        if (!$this->validateCsrfAndSession($csrfToken, './register')) {
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

    /* @Route: "/register/verified" */
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

        if (!$this->validateCsrfAndSession($csrfToken, './login')) {
            return;
        }

        $dataForm = $this->getLoginRequestData();

        $errorValidate = $this->form->validateLoginForm($dataForm['login'], $dataForm['password']);

        if (!empty($errorValidate['user_id'])) {
            if (!empty($dataForm['remember_me']) && is_string($dataForm['remember_me'])) {
                $this->remember->createRememberMe((int) $errorValidate['user_id']);
            }

            if (session_regenerate_id(true)) {
                $this->setSession(getenv('APP_SESSION_KEY'), $errorValidate['user_id']);
                $this->setFlash("messageSuccess", $this->translation->trans('login.message.logged_in'));
                $this->redirect("./account");
            } else {
                $this->setFlash("messageDanger", $this->translation->trans('alert.unexpected_error'));
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

    /* @Route: "/reset" */
    public function resetMethod(): void
    {
        if ($this->getSession(getenv('APP_SESSION_KEY'))) {
            $this->redirect("./account");
        }

        $csrfToken = $this->csrfToken();
        $this->setSession('csrf_token', $csrfToken);

        $this->render('authentication/reset.phtml', [
            'meta' => $this->service->getMetaReset(),
            'form' => ['token' => $csrfToken],
        ]);
    }

    /* @Route: "/reset/pass" */
    public function resetPassMethod()
    {
        $dataForm = $this->getResetRequestData();

        if (!$this->validateCsrfAndSession($dataForm['token'], './reset')) {
            return;
        }

        $errorValidate = $this->form->validateResetForm($dataForm['email']);

        if (empty($errorValidate)) {
            $this->model->deleteResetPassword();
            $resetPassword = $this->service->makeInsertResetPassword($dataForm['email']);

            if ($resetPassword) {
                $this->setFlash("messageSuccess", $this->translation->trans('reset.alert.send_reset_password'));
                $this->redirect("./login");
            } else {
                $this->setFlash("messageDanger", $this->translation->trans('alert.unexpected_error_try_again'));
                $this->redirect("./reset");
            }
        } elseif (!empty($errorValidate['error_no_email'])) {
            $this->setFlash("messageSuccess", $this->translation->trans('reset.alert.send_reset_password'));
            $this->redirect("./login");
        } else {
            $dataForm = array_merge($dataForm, $errorValidate);

            $this->render('authentication/reset.phtml', [
                'meta' => $this->service->getMetaReset(),
                'form' => $dataForm,
            ]);
        }
    }

    /* @Route: "/reset/password?token={token}" */
    public function resetPasswordMethod(): void
    {
        $token = $this->requestData('token'); // Pobranie tokena z URL (GET)

        // Part of the method for the GET action
        if ($token) {
            $dataReset = $this->model->getResetPassword(['token' => $token]);

            if (!$dataReset) {
                $this->setFlash("messageWarning", $this->translation->trans('reset.alert.token_expires'));
                $this->redirect("./reset");
                return;
            } elseif (!empty($dataReset->expires) && (new DateTime($dataReset->expires) < new DateTime())) {
                $this->setFlash("messageWarning", $this->translation->trans('reset.alert.token_expires'));
                $this->redirect("./reset");
                return;
            }
        }

        // Part of the method for the POST action
        if ($this->service->isPostRequest()) {
            $dataForm = $this->getResetPasswordRequestData(); // Pobiera dane z formularza (POST)

            if (!$this->validateCsrfAndSession($dataForm['csrf_token'], './reset')) {
                return;
            }

            $errorValidate = $this->form->validateResetPasswordForm($dataForm);

            if (isset($dataReset) && empty($errorValidate)) {
                $isUpdated = $this->service->makeUpdateUserPassword($dataReset->email, $dataForm['password']);

                if ($isUpdated) {
                    $this->setFlash('messageSuccess', $this->translation->trans('reset.alert.updated_success'));
                    $this->redirect("./login");
                } else {
                    $this->setFlash('messageDanger', $this->translation->trans('alert.unexpected_error_try_again'));
                    $this->redirect("./reset");
                }
            } else {
                $dataForm = array_merge($dataForm, $errorValidate);
            }
        }

        $csrfToken = $this->csrfToken();
        $this->setSession('csrf_token', $csrfToken);
        $dataForm['csrf_token'] = $csrfToken;

        $this->render('authentication/reset_password.phtml', [
            'meta' => $this->service->getMetaReset(),
            'form' => $dataForm ?? null,
            'token' => $token,
        ]);
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

    /**
     * Method to retrieve reset form data
     */
    private function getResetRequestData(): array
    {
        return [
            'email' => $this->requestData('dbm_email'),
            'token' => $this->requestData('csrf_token'),
        ];
    }

    /**
     * Method to retrieve reset password form data
     */
    private function getResetPasswordRequestData(): array
    {
        return [
            'password' => $this->requestData('dbm_password'),
            'password_repeat' => $this->requestData('dbm_password_repeat'),
            'csrf_token' => $this->requestData('csrf_token'),
        ];
    }

    /**
     * Method for central CSRF and session validation
     */
    private function validateCsrfAndSession(string $csrfToken, string $redirectUrl): bool
    {
        if (!$this->form->validateCsrfToken($this->getSession('csrf_token'), $csrfToken)) {
            $this->setFlash("messageDanger", $this->translation->trans('alert.invalid_csrf_token'));
            $this->redirect($redirectUrl);
            return false;
        }
        return true;
    }
}
