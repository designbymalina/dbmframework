<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Controller;

use Dbm\Classes\FrameworkClass;

class UserController extends FrameworkClass
{
    private $controllerModel;

    public function __construct()
    {
        $this->controllerModel = $this->model('UserModel');
    }

    public function index()
    {
        $id = $this->requestData('id');

        $userAccount = $this->controllerModel->userAccount([':id' => $id]);

        $data = [
            'data.user' => $userAccount,
        ];

        $this->view("user/index.html.php", $data);
    }
}
