<?php
/**
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Controller;

use Dbm\Classes\AbstractController;

class HomeController extends AbstractController
{
    /* @Route: "/home" */
    public function index()
    {
        $this->render("index/home.phtml");
    }
}
