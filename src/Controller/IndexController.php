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
use App\Service\MethodService;
use Dbm\Classes\AbstractController;
use Dbm\Classes\Database;
use Dbm\Classes\Translation;

class IndexController extends AbstractController
{
    private $model;
    private $translation;

    /*
     * TODO! public function __construct(Database $database, Translation $translation) // TODO! Wstrzykiwanie do konstruktora!?
     */
    public function __construct(Database $database)
    {
        $model = new BlogModel($database);
        $this->model = $model;

        $translation = new Translation();
        $this->translation = $translation;
    }

    /*
     * TODO! public function index(Translation $translation, etc...) // TODO! Wstrzykiwanie do metody!?
     *
     * @Route: "/"
     */
    public function index()
    {
        if (empty(DB_DATABASE)) {
            $this->redirect('home');
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
