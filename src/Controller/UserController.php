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
use Dbm\Interfaces\DatabaseInterface;

class UserController extends BaseController
{
    private $model;

    public function __construct(DatabaseInterface $database)
    {
        parent::__construct($database);

        $model = new UserModel($database);
        $this->model = $model;
    }

    /* @Route: "/user,{id}.html" */
    public function index()
    {
        $id = (int) $this->requestData('id');

        $userAccount = $this->model->userAccount($id);

        $this->render('user/index.phtml', [
            'user' => $userAccount,
        ]);
    }
}
