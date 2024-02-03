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

class AccountController extends FrameworkClass
{
    private $controllerModel;
    private $translation;

    public function __construct()
    {
        if (!$this->getSession('dbmUserId')) {
            $this->redirect("login");
        }

        $this->controllerModel = $this->model('UserModel');

        $translation = new TranslationClass();
        $this->translation = $translation;
    }

    public function index()
    {
        $translation = $this->translation;
        $id = (int) $this->getSession('dbmUserId');

        $userAccount = $this->controllerModel->userAccount($id);

        $data = [
            'meta.title' => $translation->trans('account.title') . ' - ' . $translation->trans('website.name'),
            'meta.description' => null,
            'meta.keywords' => null,
            'user' => $userAccount,
        ];

        $this->view("account/index.phtml", $data);
    }
}
