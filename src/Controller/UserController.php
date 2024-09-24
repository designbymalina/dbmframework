<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Controller;

use App\Model\AccountModel;
use Dbm\Classes\BaseController;
use Dbm\Interfaces\DatabaseInterface;

class UserController extends BaseController
{
    private $model;

    public function __construct(DatabaseInterface $database)
    {
        parent::__construct($database);

        $model = new AccountModel($database);
        $this->model = $model;
    }

    /* @Route: "/user.{id}.html" */
    public function index(int $id)
    {
        $userAccount = $this->model->userAccount($id);

        $this->render('user/index.phtml', [
            'user' => $userAccount,
        ]);
    }
}
