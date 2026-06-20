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

namespace Dbm\Localization;

use Dbm\Localization\LanguageHelper;
use Dbm\Localization\Dto\LanguageMatch;

final class LanguageResolver
{
    public function resolve(string $uri): LanguageMatch
    {
        $languages = LanguageHelper::getAvailableLanguages();
        $default = LanguageHelper::getDefaultLanguage();

        $segments = explode('/', trim($uri, '/'));
        $first = strtoupper($segments[0]);

        if ($first !== '' && in_array($first, $languages, true) && $first !== $default) {
            array_shift($segments);

            $path = '/' . trim(implode('/', $segments), '/');

            return new LanguageMatch(
                $first,
                $path,
                false
            );
        }

        return new LanguageMatch(
            $default,
            $uri ?: '/',
            true
        );
    }
}
