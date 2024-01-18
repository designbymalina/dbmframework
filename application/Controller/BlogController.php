<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Controller;

use Dbm\Classes\FrameworkClass;
use Dbm\Classes\TranslationClass;

class BlogController extends FrameworkClass
{
    private $blogModel;
    private $translation;

    // TODO! Poprawic kod, dodac wstrzykiwanie do kontruktora public function __construct(TranslationClass $translation)
    public function __construct()
    {
        $this->blogModel = $this->model('BlogModel'); // TODO! Zmienic sposob ladowania modeli -> class FrameworkClass -> public function model()

        $translation = new TranslationClass();
        $this->translation = $translation;
    }

    /*
     * TODO! Wstrzykiwanie do metody -> public function index(TranslationClass $translation)
     *
     * @Route: "/blog"
    */
    public function index()
    {
        $this->redirect("./");
    }

    /* @Route: "/blog/sections" */
    public function sectionsMethod(): void
    {
        $translation = $this->translation;
        $allSections = $this->blogModel->getSections();

        $data = [
            'meta.title' => $translation->trans('blog.sections.title'),
            'meta.description' => $translation->trans('blog.sections.description'),
            'meta.keywords' => $translation->trans('blog.sections.keywords'),
            'sections' => $allSections,
        ];

        $this->view("blog/sections.html.php", $data);
    }

    /* @Route: "/section-name,sec,{id}.html" */
    public function sectionMethod(int $id): void
    {
        $translation = $this->translation;
        $querySectionArticles = $this->blogModel->getJoinSectionArticles($id);
        $querySection = $this->blogModel->getSection($id);

        if (empty($querySectionArticles)) {
            $querySectionArticles = $translation->trans('alert.no_content');
        }

        $data = [
            'meta.title' => $querySection['section_name'],
            'meta.description' => $querySection['section_description'],
            'meta.keywords' => $querySection['section_keywords'],
            'section' => $querySection,
            'articles' => $querySectionArticles,
        ];

        $this->view("blog/section.html.php", $data);
    }

    /* @Route: "/article-header-title,art,{id}.html" */
    public function articleMethod(int $id): void
    {
        $queryArticle = $this->blogModel->getJoinArticle($id);

        $data = [
            'meta.title' => $queryArticle->meta_title,
            'meta.description' => $queryArticle->meta_description,
            'meta.keywords' => $queryArticle->meta_keywords,
            'article' => $queryArticle,
        ];

        $this->view("blog/article.html.php", $data);
    }
}
