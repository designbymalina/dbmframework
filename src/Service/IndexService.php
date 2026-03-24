<?php

/**
 * Application: DbM Framework
 * A lightweight PHP framework for building web applications.
 *
 * @author Artur Malinowski
 * @copyright Design by Malina (All Rights Reserved)
 * @license MIT
 * @link https://www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Service;

use Dbm\Localization\Translation;

class IndexService
{
    public function __construct(
        private readonly Translation $translation
    ) {}

    /**
     * @return array<string, string>
     */
    public function getMetaIndex(): array
    {
        return [
            'meta.title' => "Your Web Application Name",
            'meta.description' => "Web application description...",
            'meta.keywords' => "application keywords",
        ];
    }

    /**
     * @return array<string, string>
     */
    public function getMetaStart(): array
    {
        return [
            'meta.title' => $this->translation->trans('index.start_meta_title'),
            'meta.description' => $this->translation->trans('index.start_meta_description'),
            'meta.keywords' => $this->translation->trans('index.start_meta_keywords'),
            'meta.robots' => "noindex,nofollow",
        ];
    }
}
