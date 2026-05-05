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

use Dbm\Http\Message\Request;

final class RequestContextFactory
{
    public static function fromRequest(Request $request): RequestContext
    {
        $server = $request->getServerParams();

        $scriptName = $server['SCRIPT_NAME'] ?? '';
        $basePath = str_replace('\\', '/', dirname($scriptName));

        if (str_ends_with($basePath, '/public')) {
            $basePath = substr($basePath, 0, -7);
        }

        $scheme = (!empty($server['HTTPS']) && $server['HTTPS'] !== 'off')
            ? 'https'
            : 'http';

        $host = $server['HTTP_HOST'] ?? 'localhost';

        return new RequestContext($scheme, $host, $basePath);
    }
}
