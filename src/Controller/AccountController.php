<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Controller;

use App\Model\UserModel;
use Dbm\Classes\BaseController;
use Dbm\Classes\Translation;
use Dbm\Interfaces\DatabaseInterface;

class AccountController extends BaseController
{
    private $model;
    private $translation;

    public function __construct(DatabaseInterface $database)
    {
        if (!$this->getSession('dbmUserId')) {
            $this->redirect("./login");
        }

        parent::__construct($database);

        $model = new UserModel($database);
        $this->model = $model;

        $translation = new Translation();
        $this->translation = $translation;
    }

    /* @Route: "/account" */
    public function index()
    {
        $translation = $this->translation;
        $id = (int) $this->getSession('dbmUserId');

        $userAccount = $this->model->userAccount($id);

        $meta = [
            'meta.title' => $translation->trans('account.title') . ' - ' . $translation->trans('website.name'),
        ];

        $this->render('account/index.phtml', [
            'meta' => $meta,
            'user' => $userAccount,
        ]);
    }
}
