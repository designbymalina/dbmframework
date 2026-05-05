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

namespace Dbm\Routing;

final class RequestContext
{
    public function __construct(
        public readonly string $scheme,
        public readonly string $host,
        public readonly string $basePath
    ) {}
}
