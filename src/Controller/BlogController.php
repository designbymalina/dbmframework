<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Controller;

use App\Model\BlogModel;
use Dbm\Classes\BaseController;
use Dbm\Interfaces\DatabaseInterface;

class BlogController extends BaseController
{
    private $model;

    public function __construct(DatabaseInterface $database)
    {
        parent::__construct($database);

        $model = new BlogModel($database);
        $this->model = $model;
    }

    /* @Route: "/blog" */
    public function index()
    {
        $this->redirect("./");
    }

    /* @Route: "/blog/sections" */
    public function sectionsMethod(): void
    {
        $translation = $this->translation;
        $allSections = $this->model->getSections();

        $meta = [
            'meta.title' => $translation->trans('blog.sections.title'),
            'meta.description' => $translation->trans('blog.sections.description'),
            'meta.keywords' => $translation->trans('blog.sections.keywords'),
            'sections' => $allSections,
        ];

        $this->render('blog/sections.phtml', [
            'meta' => $meta,
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

        $meta = [
            'meta.title' => $querySection['section_name'],
            'meta.description' => $querySection['section_description'],
            'meta.keywords' => $querySection['section_keywords'],
        ];

        $this->render('blog/section.phtml', [
            'meta' => $meta,
            'section' => $querySection,
            'articles' => $querySectionArticles,
        ]);
    }

    /* @Route: "/article-header-title.art.{id}.html" */
    public function articleMethod(int $id): void
    {
        $queryArticle = $this->model->getJoinArticle($id);

        $meta = [
            'meta.title' => $queryArticle->meta_title,
            'meta.description' => $queryArticle->meta_description,
            'meta.keywords' => $queryArticle->meta_keywords,
        ];

        $this->render('blog/article.phtml', [
            'meta' => $meta,
            'article' => $queryArticle,
        ]);
    }
}
