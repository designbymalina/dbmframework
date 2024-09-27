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
use App\Service\IndexService;
use Dbm\Classes\BaseController;
use Dbm\Interfaces\DatabaseInterface;

class IndexController extends BaseController
{
    private $model;
    private $service;

    public function __construct(?DatabaseInterface $database = null)
    {
        parent::__construct($database);

        $this->model = new BlogModel($database);
        $this->service = new IndexService($this->translation);
    }

    /* @Route: "/" */
    public function index()
    {
        // Option for render: blog/index.phtml
        if (empty(getenv('DB_NAME'))) {
            $this->setFlash('messageInfo', 'Brak połączenia z bazą danych.');
            $this->redirect('./home');
        }

        $articlesLimit = $this->model->getJoinArticlesLimit(ConstantConfig::BLOG_INDEX_ITEM_LIMIT);

        // OPTIONS to Choose: 1. index/index.phtml, 2. page/index.phtml, 3. blog/index.phtml
        $this->render('blog/index.phtml', [
            'meta' => $this->service->getMetaIndex(),
            'articles' => $articlesLimit,
        ]);
    }

    /* @Route: "/home" */
    public function home()
    {
        $this->render("index/home.phtml");
    }

    /* @Route: "/link.html" */
    public function link()
    {
        $this->render('index/index.phtml');
    }
}
