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

final class CurrentLanguage
{
    private ?string $language = null;

    public function __construct(?string $language = null)
    {
        // @INFO Language is set in the router
        $this->language = $language !== null
            ? strtoupper($language)
            : null;
    }

    public function get(): ?string
    {
        return $this->language;
    }

    public function set(string $language): void
    {
        $this->language = strtoupper($language);
    }

    public function has(): bool
    {
        return $this->language !== null;
    }
}
