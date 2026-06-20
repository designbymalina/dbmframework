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

class LanguageHelper
{
    /**
     * Get raw APP_LANGUAGES string
     */
    private static function rawLanguages(): string
    {
        return getenv('APP_LANGUAGES') ?: '';
    }

    /**
     * Available languages from settings
     *
     * @return array<int, string> List of available language codes (e.g., ['EN', 'PL']).
     */
    public static function getAvailableLanguages(): array
    {
        $raw = trim(self::rawLanguages());

        if ($raw === '') {
            return ['EN']; // default language
        }

        return array_map('strtoupper', explode('|', $raw));
    }

    /**
     * Default language = first in settings
     */
    public static function getDefaultLanguage(): string
    {
        return self::getAvailableLanguages()[0];
    }

    /**
     * Validate language code against available list
     */
    public static function isSupported(string $lang): bool
    {
        return in_array(strtoupper($lang), self::getAvailableLanguages(), true);
    }
}
