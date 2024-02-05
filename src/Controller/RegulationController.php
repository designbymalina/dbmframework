<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Controller;

use App\Model\PageModel;
use Dbm\Classes\AbstractController;

class RegulationController extends AbstractController
{
    private $model;

    public function __construct()
    {
        $model = new PageModel();
        $this->model = $model;
    }

    /* @Route: "/regulation.html" */
    public function index()
    {
        $meta = [
            'meta.title' => $this->model->Title(),
            'meta.description' => $this->model->Description(),
            'meta.keywords' => $this->model->Keywords(),
        ];

        $this->render('page/site.phtml', [
            'meta' => $meta,
            'content' => $this->model->Content(),
        ]);
    }
}
