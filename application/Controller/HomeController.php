<?php
/**
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Controller;

use Dbm\Classes\FrameworkClass;
use Dbm\Classes\TranslationClass;

class HomeController extends FrameworkClass
{
    private $trans;

    public function __construct()
    {
        $translation = new TranslationClass();
        $this->trans = $translation->Translation();

    }

    public function index()
    {
        // Translation
        //$translation = new TranslationClass();
        //$trans = $translation->Translation();

        $data = array(
            'page_content' => "",
        );

        $this->view("index/home.html.php", $data);
    }
}
