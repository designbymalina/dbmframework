<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Controller;

use App\Model\PageModel;
use Dbm\Classes\BaseController;
use Dbm\Interfaces\DatabaseInterface;

class ContactController extends BaseController
{
    private $model;

    public function __construct(DatabaseInterface $database)
    {
        parent::__construct($database);

        $model = new PageModel($database);
        $this->model = $model;
    }

    /* @Route: "/contact.html" */
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
