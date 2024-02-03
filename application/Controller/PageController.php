<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Controller;

use Dbm\Classes\FrameworkClass;

class PageController extends FrameworkClass
{
    private $pageModel;

    public function __construct()
    {
        $this->pageModel = $this->model('PageModel');
    }

    /* @Route: "/page" */
    public function index()
    {
        $data = [
            'meta.title' => $this->pageModel->Title(),
            'meta.description' => $this->pageModel->Description(),
            'meta.keywords' => $this->pageModel->Keywords(),
            'page_content' => $this->pageModel->Content(),
        ];

        $this->view("page/index.phtml", $data);
    }

    /* @Route: "/page/site.html" and more pages "page/site[-website-title].html" or "/website-title,site.html" */
    public function siteMethod()
    {
        $data = [
            'meta.title' => $this->pageModel->Title(),
            'meta.description' => $this->pageModel->Description(),
            'meta.keywords' => $this->pageModel->Keywords(),
            'page_content' => $this->pageModel->Content(),
        ];

        $this->view("page/site.phtml", $data);
    }

    /* @Route: website-title,offer.html */
    public function offerMethod()
    {
        $data = [
            'meta.title' => $this->pageModel->Title(),
            'meta.description' => $this->pageModel->Description(),
            'meta.keywords' => $this->pageModel->Keywords(),
            'page_content' => $this->pageModel->Content(),
        ];

        $this->view("page/offer.phtml", $data);
    }
}
