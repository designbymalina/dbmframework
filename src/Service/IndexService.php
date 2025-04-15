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

class IndexService
{
    public function getMetaIndex(): array
    {
        return [
            'meta.title' => "Your Web Application Name",
            'meta.description' => "Web application description...",
            'meta.keywords' => "application keywords",
        ];
    }

    public function getMetaStart(): array
    {
        return [
            'meta.title' => "Welcome to DbM Framework!",
            'meta.description' => "Your lightweight and flexible framework for building powerful web applications.",
            'meta.keywords' => "high performance, easy configuration, comprehensive documentation",
            'meta.robots' => "noindex,nofollow",
        ];
    }

    public function getMetaStep(): array
    {
        return [
            'meta.title' => "Install DbM CMS",
            'meta.description' => "Use a ready-to-go content management system based on the DbM Framework.",
            'meta.keywords' => "dbm cms, cms, content management system",
            'meta.robots' => "noindex,nofollow",
        ];
    }
}
