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

namespace Dbm\Http;

use Dbm\Core\DependencyContainer;
use Dbm\Http\Contracts\HttpClientInterface;

final class HttpServiceProvider
{
    public static function register(DependencyContainer $container): void
    {
        $container->singleton(
            HttpClientInterface::class,
            fn() => new CurlHttpClient()
        );
    }
}
