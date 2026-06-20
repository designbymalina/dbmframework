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

namespace Dbm\Localization\Dto;

final readonly class LanguageMatch
{
    public function __construct(
        public string $language,
        public string $path,
        public bool $isDefault
    ) {}
}
