<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Controller;

use App\Model\LoginModel;
use Dbm\Classes\BaseController;
use Dbm\Classes\Translation;
use Dbm\Interfaces\DatabaseInterface;

class LoginController extends BaseController
{
    private $model;
    private $translation;

    public function __construct(DatabaseInterface $database)
    {
        parent::__construct($database);

        $model = new LoginModel($database);
        $this->model = $model;

        $translation = new Translation();
        $this->translation = $translation;
    }

    /* @Route: "/login" */
    public function index()
    {
        if ($this->getSession('dbmUserId')) {
            $this->redirect("account");
        }

        $translation = $this->translation;

        $meta = [
            'meta.title' => $translation->trans('login.title') . ' - ' . $translation->trans('website.name'),
            'meta.description' => $translation->trans('login.description'),
            'meta.keywords' => $translation->trans('login.keywords'),
        ];

        $this->render('login/index.phtml', [
            'meta' => $meta,
        ]);
    }

    /* @Route: "/login/signin" */
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

        $errorValidate = $this->model->validateLoginForm($dataForm['login'], $dataForm['password']);

        if (array_key_exists($userKey, $errorValidate)) {
            $this->setSession("dbmUserId", $errorValidate[$userKey]);
            $this->setFlash("messageSuccess", $translation->trans('login.message.logged_in'));
            $this->redirect("account");
        } else {
            $dataForm = array_merge($dataForm, $errorValidate);

            $meta = [
                'meta.title' => $translation->trans('login.title') . ' - ' . $translation->trans('website.name'),
                'meta.description' => $translation->trans('login.description'),
                'meta.keywords' => $translation->trans('login.keywords'),
            ];

            $this->render('login/index.phtml', [
                'meta' => $meta,
                'form' => $dataForm,
            ]);
        }
    }

    /* @Route: "/login/logout" */
    public function logoutMethod(): void
    {
        $translation = $this->translation;

        $this->unsetSession('dbmUserId');
        $this->setFlash("messageSuccess", $translation->trans('login.message.logged_out'));
        $this->redirect('./');
    }
}
