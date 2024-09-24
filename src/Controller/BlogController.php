<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Controller;

use App\Model\BlogModel;
use App\Service\BlogService;
use Dbm\Classes\BaseController;
use Dbm\Interfaces\DatabaseInterface;

class BlogController extends BaseController
{
    private $model;
    private $service;

    public function __construct(DatabaseInterface $database)
    {
        parent::__construct($database);

        $this->model = new BlogModel($database);
        $this->service = new BlogService($this->translation);
    }

    /* @Route: "/blog" */
    public function index()
    {
        $this->redirect("./");
    }

    /* @Route: "/blog/sections" */
    public function sectionsMethod(): void
    {
        $allSections = $this->model->getSections();

        $this->render('blog/sections.phtml', [
            'meta' => $this->service->getMetaIndex($allSections),
            'sections' => $allSections,
        ]);
    }

    /* @Route: "/blog/section-name.sec.{id}.html" */
    public function sectionMethod(int $id): void
    {
        $translation = $this->translation;
        $querySectionArticles = $this->model->getJoinSectionArticles($id);
        $querySection = $this->model->getSection($id);

        if (empty($querySectionArticles)) {
            $querySectionArticles = $translation->trans('alert.no_content');
        }

        $this->render('blog/section.phtml', [
            'meta' => $this->service->getMetaSection($querySection),
            'section' => $querySection,
            'articles' => $querySectionArticles,
        ]);
    }

    /* @Route: "/article-header-title.art.{id}.html" */
    public function articleMethod(int $id): void
    {
        $queryArticle = $this->model->getJoinArticle($id);

        $this->render('blog/article.phtml', [
            'meta' => $this->service->getMetaArticle($queryArticle),
            'article' => $queryArticle,
        ]);
    }
}
