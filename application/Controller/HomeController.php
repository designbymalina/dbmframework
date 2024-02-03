<?php
/**
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Controller;

use Dbm\Classes\FrameworkClass;

class HomeController extends FrameworkClass
{
    public function index()
    {
        $this->view("index/home.phtml");
    }
}
