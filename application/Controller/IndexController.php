<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Controller;

use App\Config\ConstantConfig;
use Dbm\Classes\FrameworkClass;
use Dbm\Classes\TranslationClass;

class IndexController extends FrameworkClass
{
    private $blogModel;
    private $translation;

    /*
     * TODO! public function __construct(DatabaseClass $database, TranslationClass $translation) // TODO! Wstrzykiwanie!?
     */
    public function __construct()
    {
        $this->blogModel = $this->model('BlogModel'); // TODO! Zmienic sposob ladowania ClassModel ?!

        $translation = new TranslationClass();
        $this->translation = $translation;
    }

    /*
     * TODO! public function index(TranslationClass $translation, etc...) // TODO! Wstrzykiwanie!?
     *
     * @Route: "/"
     */
    public function index()
    {
        if (empty(DB_DATABASE)) {
            $this->redirect('home');
        }

        $translation = $this->translation;

        $allArticlesLimit = $this->blogModel->getJoinArticlesLimit(ConstantConfig::BLOG_INDEX_ITEM_LIMIT);

        $data = [
            'meta.title' => $translation->trans('index.title'),
            'meta.description' => $translation->trans('index.description'),
            'meta.keywords' => $translation->trans('index.keywords'),
            'data.articles' => $allArticlesLimit,
        ];

        // OPTIONS to Choose
        // $this->view("index/index.phtml", $data);
        // $this->view("page/index.phtml", $data);
        // $this->view("blog/index.phtml", $data);
        $this->view("blog/index.phtml", $data);
    }

    /* @Route: "/index/link.html" */
    public function linkMethod()
    {
        $this->view("index/index.phtml");
    }
}
