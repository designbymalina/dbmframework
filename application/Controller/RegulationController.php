<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Controller;

use Dbm\Classes\FrameworkClass;

class RegulationController extends FrameworkClass
{
    private $pageModel;

    public function __construct()
    {
        $this->pageModel = $this->model('PageModel');
    }

    public function index()
    {
        $data = [
            'meta.title' => $this->pageModel->Title(),
            'meta.description' => $this->pageModel->Description(),
            'meta.keywords' => $this->pageModel->Keywords(),
            'page_content' => $this->pageModel->Content(),
        ];

        $this->view("page/site.html.php", $data);
    }
}
