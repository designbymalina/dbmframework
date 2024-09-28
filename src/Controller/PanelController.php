<?php
/*
 * DbM Framework (PHP MVC Simple CMS)
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Controller;

use App\Model\PanelModel;
use App\Utility\MethodsUtility;
use Dbm\Classes\AdminBaseController;
use Dbm\Interfaces\DatabaseInterface;

class PanelController extends AdminBaseController
{
    private const DIR_CONTENT = BASE_DIRECTORY . 'data/content/';

    private $model;
    private $utility;

    public function __construct(?DatabaseInterface $database = null)
    {
        parent::__construct($database);

        $this->model = new PanelModel($database);
        $this->utility = new MethodsUtility();
    }

    public function index()
    {
        $dirFiles = $this->utility->scanDirectory(self::DIR_CONTENT);
        $allArticles = $this->model->getAllArticlesLimit(10);
        $arrayArticles = array_column($allArticles, 'page_header');

        $this->render('panel/admin.phtml', [
            'meta' => ['meta.title' => $this->translation->trans('website.name') . ' - Panel'],
            'files' => $dirFiles,
            'articles' => $arrayArticles,
        ]);
    }
}
