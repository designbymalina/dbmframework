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
use Dbm\Classes\Database;

class PageController extends AbstractController
{
    private $model;

    public function __construct()
    {
        $database = new Database;

        $model = new PageModel($database);
        $this->model = $model;
    }

    /* @Route: "/page" */
    public function index()
    {
        $meta = [
            'meta.title' => $this->model->Title(),
            'meta.description' => $this->model->Description(),
            'meta.keywords' => $this->model->Keywords(),
        ];

        $this->render('page/index.phtml', [
            'meta' => $meta,
            'content' => $this->model->Content(),
        ]);
    }

    /* @Route: "/page/site" and more pages "page/site[-website-title].html" or "/website-title,site.html" */
    public function siteMethod()
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

    /* @Route: website-title,offer.html */
    public function offerMethod()
    {
        $meta = [
            'meta.title' => $this->model->Title(),
            'meta.description' => $this->model->Description(),
            'meta.keywords' => $this->model->Keywords(),
        ];

        $this->render('page/offer.phtml', [
            'meta' => $meta,
            'content' => $this->model->Content(),
        ]);
    }
}
