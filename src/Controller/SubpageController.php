<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Controller;

use App\Model\PageModel;
use App\Service\PageService;
use Dbm\Classes\BaseController;
use Dbm\Interfaces\DatabaseInterface;

class SubpageController extends BaseController
{
    private $model;
    private $service;

    public function __construct(?DatabaseInterface $database = null)
    {
        parent::__construct($database);

        $this->model = new PageModel($database);
        $this->service = new PageService($this->model);
    }

    /* @Route: "/about.html" */
    public function about()
    {
        $this->render('page/site.phtml', [
            'meta' => $this->service->getMetaPage(),
            'content' => $this->model->Content(),
        ]);
    }

    /* @Route: "/contact.html" */
    public function contact()
    {
        $this->render('page/site.phtml', [
            'meta' => $this->service->getMetaPage(),
            'content' => $this->model->Content(),
        ]);
    }

    /* @Route: "/regulation.html" */
    public function regulation()
    {
        $this->render('page/site.phtml', [
            'meta' => $this->service->getMetaPage(),
            'content' => $this->model->Content(),
        ]);
    }
}
