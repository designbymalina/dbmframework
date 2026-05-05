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

use Psr\Http\Message\ServerRequestInterface;

final class UriNormalizer
{
    public function normalize(string $uri, ServerRequestInterface $request): string
    {
        $server = $request->getServerParams();

        $scriptName = $server['SCRIPT_NAME'] ?? '';

        // --- path only ---
        $cleanUri = parse_url($uri, PHP_URL_PATH) ?? '/';

        // --- strip base path (np. /public) ---
        $basePath = strstr($scriptName, 'public', true) ?: '';

        if ($basePath !== '') {
            $cleanUri = '/' . ltrim(str_replace($basePath, '', $cleanUri), '/');
        }

        // --- trailing slash normalization ---
        if ($cleanUri !== '/' && str_ends_with($cleanUri, '/')) {
            $cleanUri = rtrim($cleanUri, '/');
        }

        return '/' . trim($cleanUri, '/');
    }
}
