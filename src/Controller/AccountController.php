<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Controller;

use App\Model\UserModel;
use App\Service\UserService;
use Dbm\Classes\BaseController;
use Dbm\Interfaces\DatabaseInterface;

class AccountController extends BaseController
{
    private $model;
    private $service;

    public function __construct(DatabaseInterface $database)
    {
        if (!$this->getSession('dbmUserId')) {
            $this->redirect("./login");
        }

        parent::__construct($database);

        $this->model = new UserModel($database);
        $this->service = new UserService($this->model);
    }

    /* @Route: "/account" */
    public function index()
    {
        $translation = $this->translation;
        $id = (int) $this->getSession('dbmUserId');
        $userAccount = $this->model->userAccount($id);

        $this->render('account/index.phtml', [
            'meta' => ['meta.title' => $translation->trans('account.title') . ' - ' . $translation->trans('website.name')],
            'user' => $userAccount,
        ]);
    }

    /* @Route: "/account/profileChange" */
    public function profileChangeMethod()
    {
        $id = (int) $this->getSession('dbmUserId');
        $userAccount = $this->model->userAccount($id);
        $dataForm = $this->service->prepareProfileFormData($this, $userAccount);

        if ($this->service->isPostRequest()) {
            $errorValidate = $this->service->doValidateProfile($dataForm);

            if (empty($errorValidate)) {
                if ($this->service->doUpdateUserProfile($id, $dataForm, $userAccount)) {
                    $this->setFlash('messageSuccess', 'Profil został pomyślnie edytowany.');
                } else {
                    $this->setFlash('messageDanger', 'Wystąpił nieoczekiwany błąd podczas edycji profilu!');
                }

                $this->redirect("./account");
            } else {
                $dataForm = array_merge($dataForm, $errorValidate);
            }
        }

        $this->render('account/profile.phtml', [
            'meta' => ['meta.title' => 'Edycja profilu użytkownika'],
            'user' => $userAccount,
            'form' => $dataForm,
        ]);
    }

    /* @Route: "/account/passwordChange" */
    public function passwordChangeMethod()
    {
        $id = (int) $this->getSession('dbmUserId');
        $userAccount = $this->model->userAccount($id);
        $dataForm = $this->service->preparePasswordFormData($this);

        if ($this->service->isPostRequest()) {
            $errorValidate = $this->service->doValidatePassword($id, $dataForm);

            if (empty($errorValidate)) {
                if ($this->service->doUpdatePassword($id, $dataForm['password'])) {
                    $this->setFlash('messageSuccess', 'Hasło zostało pomyślnie zmienione.');
                } else {
                    $this->setFlash('messageDanger', 'Wystąpił nieoczekiwany błąd podczas zmiany hasła!');
                }

                $this->redirect("./account");
            } else {
                $dataForm = array_merge($dataForm, $errorValidate);
            }
        }

        $this->render('account/password.phtml', [
            'meta' => ['meta.title' => 'Zmiana hasła'],
            'user' => $userAccount,
            'form' => $dataForm,
        ]);
    }
}
