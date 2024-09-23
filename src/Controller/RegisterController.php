<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Controller;

use App\Form\RegisterForm;
use App\Model\RegisterModel;
use App\Service\RegisterService;
use Dbm\Classes\BaseController;
use Dbm\Interfaces\DatabaseInterface;

class RegisterController extends BaseController
{
    private $model;
    private $form;
    private $service;

    public function __construct(DatabaseInterface $database)
    {
        parent::__construct($database);

        $this->model = new RegisterModel($database, $this->translation);
        $this->form = new RegisterForm($this->model, $this->translation);
        $this->service = new RegisterService($this->model, $this->translation);
    }

    /* @Route: "/register" */
    public function index()
    {
        if ($this->getSession('dbmUserId')) {
            $this->redirect("./account");
        }

        $csrfToken = $this->csrfToken();
        $this->setSession('csrf_token', $csrfToken);

        // Pobieramy meta dane z RegisterService
        $meta = $this->service->getMeta();

        $this->render('register/index.phtml', [
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

        // Pobieranie danych z formularza
        $dataForm = $this->getRequestData();

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
            // Wyswietlanie bledow walidacji
            $meta = $this->service->getMeta();
            $dataForm = array_merge($dataForm, $errorValidate);

            $this->render('register/index.phtml', [
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

    // Metoda do pobierania danych formularza
    private function getRequestData(): array
    {
        return [
            'login' => $this->requestData('dbm_login'),
            'email' => $this->requestData('dbm_email'),
            'password' => $this->requestData('dbm_password'),
            'confirmation' => $this->requestData('dbm_confirmation'),
            'token' => $this->requestData('csrf_token'),
        ];
    }
}
