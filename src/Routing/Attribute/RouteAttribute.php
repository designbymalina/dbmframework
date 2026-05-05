<?php

/**
 * Application: DbM Framework
 * A lightweight PHP framework for building web applications.
 *
 * @author Artur Malinowski
 * @copyright Design by Malina (All Rights Reserved)
 * @license MIT
 * @link https://www.dbm.org.pl
 *
 * @INFO Klasa nie używana, do wdrożenia
 */

declare(strict_types=1);

namespace Dbm\Routing\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD)]
class RouteAttribute
{
    /**
     * @param string[] $methods
     */
    public function __construct(
        public string $path,
        public string $name = '',
        public array $methods = ['GET']
    ) {}
}
