<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 * Module: DbMPayments
 */

declare(strict_types=1);

namespace App\Service;

use Dbm\Interfaces\TranslationInterface;

class BlogService
{
    private $translation;

    public function __construct(TranslationInterface $translation)
    {
        $this->translation = $translation;
    }

    public function getMetaIndex(array $sections): array
    {
        return [
            'meta.title' => $this->translation->trans('blog.sections.title'),
            'meta.description' => $this->translation->trans('blog.sections.description'),
            'meta.keywords' => $this->translation->trans('blog.sections.keywords'),
            'sections' => $sections,
        ];
    }

    public function getMetaSection(array $section): array
    {
        return [
            'meta.title' => $section['section_name'],
            'meta.description' => $section['section_description'],
            'meta.keywords' => $section['section_keywords'],
        ];
    }

    public function getMetaArticle(object $article): array
    {
        return [
            'meta.title' => $article->meta_title,
            'meta.description' => $article->meta_description,
            'meta.keywords' => $article->meta_keywords,
        ];
    }
}
