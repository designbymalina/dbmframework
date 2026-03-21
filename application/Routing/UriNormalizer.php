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

final class UriNormalizer
{
    public function normalize(string $uri, Request $request): string
    {
        // Przekierowanie, jeśli adres nie jest katalogiem/plikiem i kończy się ukośnikiem
        $server = $request->getServerParams();
        $potentialFile = $server['DOCUMENT_ROOT'] . rtrim($uri, '/');

        if (!is_dir($potentialFile) && !is_file($potentialFile)) {
            if ($uri !== '/' && substr($uri, -1) === '/') {
                $normalizedUri = rtrim($uri, '/');
                header("Location: {$normalizedUri}", true, 301);
                exit;
            }
        }

        // Usuwa skrypt (index.php) z URI
        $scriptName = dirname($server['SCRIPT_NAME']);
        $baseUri = str_replace('\\', '/', $scriptName);

        // Usuwa fragmenty i parametry z URI
        $cleanUri = parse_url($uri, PHP_URL_PATH);

        // Usuwa bazową ścieżkę z URI (np. /public)
        if (count(explode('/', trim($baseUri, '/'))) > 1) {
            $basePath = strstr($scriptName, 'public', true);
            $cleanUri = '/' . ltrim(str_replace($basePath, '', $cleanUri), '/');
        }

        // Zwraca znormalizowany URI
        return '/' . trim($cleanUri, '/');
    }
}
