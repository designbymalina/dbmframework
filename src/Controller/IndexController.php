<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Controller;

use App\Config\ConstantConfig;
use App\Model\BlogModel;
use Dbm\Classes\BaseController;
use Dbm\Interfaces\DatabaseInterface;

class IndexController extends BaseController
{
    private $model;

    public function __construct(DatabaseInterface $database)
    {
        parent::__construct($database);

        $model = new BlogModel($database);
        $this->model = $model;
    }

    /* @Route: "/" */
    public function index()
    {
        // Option for render: blog/index.phtml
        if (empty(getenv('DB_NAME'))) {
            $this->redirect('./home');
        }

        $translation = $this->translation;

        $meta = [
            'meta.title' => $translation->trans('index.title'),
            'meta.description' => $translation->trans('index.description'),
            'meta.keywords' => $translation->trans('index.keywords'),
        ];

        $articlesLimit = $this->model->getJoinArticlesLimit(ConstantConfig::BLOG_INDEX_ITEM_LIMIT);

        // OPTIONS to Choose: 1. index/index.phtml, 2. page/index.phtml, 3. blog/index.phtml
        $this->render('blog/index.phtml', [
            'meta' => $meta,
            'articles' => $articlesLimit,
        ]);
    }

    /* @Route: "/link.html" */
    public function linkMethod()
    {
        $this->render('index/index.phtml', []);
    }
}
