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

use Dbm\Routing\Contracts\UrlGeneratorInterface;

final class UrlGenerator implements UrlGeneratorInterface
{
    protected array $namedRoutes = [];
    protected static ?string $currentRouteName = null;
    private string $basePath = '';
    private string $scheme = 'http';
    private string $host = 'localhost';

    public function __construct(
        public RouteCollection $routes
    ) {}

    public function path(string $routeName, array $params = []): string
    {
        $route = $this->routes->getByName($routeName);
        $path = $route->path;

        // Wypełnij wszystkie parametry
        if (str_contains($path, '{')) {
            foreach ($route->getParamNames() as $param) {
                if (!array_key_exists($param, $params)) {
                    throw new \RuntimeException(
                        "Missing parameter '{$param}' for route '{$routeName}'"
                    );
                }

                $path = str_replace(
                    '{' . $param . '}',
                    rawurlencode((string) $params[$param]),
                    $path
                );
            }
        }

        $uri = '/' . trim($path, '/');

        if ($this->basePath !== '') {
            $uri = rtrim($this->basePath, '/') . $uri;
        }

        return $uri;
    }

    public function absolute(string $routeName, array $params = []): string
    {
        $uri = $this->path($routeName, $params);

        return $this->scheme . '://' . $this->host . $uri;
    }

    public function setBasePath(string $basePath): void
    {
        $basePath = trim($basePath, '/');
        $this->basePath = $basePath === '' ? '' : '/' . $basePath;
    }

    public function setBaseUrl(string $scheme, string $host): void
    {
        $this->scheme = $scheme;
        $this->host = $host;
    }

    /**
     * INFO! Sprawdź metodę po modyfikacji.
     */
    public function generateSeoFriendlyUrl(string $text, int $limit = 120): string
    {
        $hyphen = '-';
        $allowedPattern = "/[^a-zA-Z0-9 ]/";
        $arrayRemove = ['and', 'or', 'to', 'an', 'the', 'is', 'in', 'of', 'on', 'with',
            'at', 'by', 'for', 'etc.', 'a', 'i', 'o', 'u', 'w', 'z', 'na', 'do', 'po',
            'za', 'od', 'dla', 'ku', 'czy', 'by', 'aby', 'oraz', 'lub', 'itp.',
        ];

        // Transliterate text to ASCII
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        $text = strip_tags($text);
        $text = strtolower($text);
        $text = preg_replace($allowedPattern, '', $text);

        // Remove unwanted words
        if (!empty($arrayRemove)) {
            $removePattern = "/\b(" . implode("|", $arrayRemove) . ")\b/";
            $text = trim(preg_replace($removePattern, '', $text));
        }

        // Limit length of the text
        if (mb_strlen($text) > $limit) {
            $text = trim(preg_replace('~\s+\S+$~', '', substr($text, 0, $limit)));
        }

        // Replace spaces with hyphens
        $text = trim(preg_replace('~\s+~', $hyphen, $text));

        return $text;
    }
}
