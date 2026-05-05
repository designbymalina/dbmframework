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

use Dbm\Core\DependencyContainer;
use Dbm\Routing\Contracts\UrlGeneratorInterface;

final class UrlGenerator implements UrlGeneratorInterface
{
    private const ARRAY_SIGNS_TO_REMOVE = [
        'and', 'or', 'to', 'an', 'the', 'is', 'in', 'of', 'on', 'with', 'at',
        'by', 'for', 'etc.', 'a', 'i', 'o', 'u', 'w', 'z', 'na', 'do', 'po',
        'za', 'od', 'dla', 'ku', 'czy', 'by', 'aby', 'oraz', 'lub', 'itp.',
    ];

    // private string $basePath = '';
    private string $scheme = 'http';
    private string $host = 'localhost';

    protected static ?string $currentRouteName = null;

    /** @var array<string, string> */
    protected array $namedRoutes = [];

    public function __construct(
        private DependencyContainer $container,
        private RouteCollection $routes
    ) {}

    /**
     * @param array<string, mixed> $params
     */
    public function path(string $routeName, array $params = []): string
    {
        $route = $this->routes->getByName($routeName);
        $path = $route->path;

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

        $base = rtrim($this->context()->basePath, '/');
        $uri  = '/' . ltrim($path, '/');

        if ($uri === '/') {
            return $base !== '' ? $base : '/';
        }

        return ($base !== '' ? $base : '') . $uri;
    }

    public function base(): string
    {
        return $this->context()->basePath ?: '/';
    }

    /**
     * @param array<string, mixed> $params
     */
    public function absolute(string $routeName, array $params = []): string
    {
        $uri = $this->path($routeName, $params);

        return $this->scheme . '://' . $this->host . $uri;
    }

    public function stripBasePath(string $path): string
    {
        $base = $this->context()->basePath;

        if ($base && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }

        return $path !== '' ? $path : '/';
    }

    /**
     * @INFO Sprawdź metodę po modyfikacji.
     */
    public function generateSeoFriendlyUrl(string $text, int $limit = 120): string
    {
        $hyphen = '-';
        $allowedPattern = "/[^a-zA-Z0-9 ]/";

        // Transliterate text to ASCII
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        $text = strip_tags($text);
        $text = strtolower($text);
        $text = preg_replace($allowedPattern, '', $text);

        // Remove unwanted words
        $removePattern = "/\b(" . implode("|", self::ARRAY_SIGNS_TO_REMOVE) . ")\b/";
        $text = trim(preg_replace($removePattern, '', $text));

        // Limit length of the text
        if (mb_strlen($text) > $limit) {
            $text = trim(preg_replace('~\s+\S+$~', '', substr($text, 0, $limit)));
        }

        // Replace spaces with hyphens
        $text = trim(preg_replace('~\s+~', $hyphen, $text));

        return $text;
    }

    // ===== Private =====

    private function context(): RequestContext
    {
        return $this->container->get(RequestContext::class);
    }
}
