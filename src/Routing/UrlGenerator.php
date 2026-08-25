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
use Dbm\Environment\Environment;
use Dbm\Localization\CurrentLanguage;
use Dbm\Localization\LanguageHelper;
use Dbm\Routing\Contracts\UrlGeneratorInterface;
use Throwable;

final class UrlGenerator implements UrlGeneratorInterface
{
    private const ARRAY_SIGNS_TO_REMOVE = [
        'and', 'or', 'to', 'an', 'the', 'is', 'in', 'of', 'on', 'with', 'at',
        'by', 'for', 'etc.', 'a', 'i', 'o', 'u', 'w', 'z', 'na', 'do', 'po',
        'za', 'od', 'dla', 'ku', 'czy', 'by', 'aby', 'oraz', 'lub', 'itp.',
    ];

    protected static ?string $currentRouteName = null;

    /** @var array<string, string> */
    protected array $namedRoutes = [];

    public function __construct(
        private readonly DependencyContainer $container,
        private readonly RouteCollection $routes,
        private readonly CurrentLanguage $currentLanguage
    ) {}

    /**
     * @param array<string, mixed> $params
     */
    public function path(string $routeName, array $params = []): string
    {
        $route = $this->routes->getByName($routeName);

        $path = $route->path;

        $routeParams = $route->getParamNames();

        foreach ($routeParams as $param) {
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

            unset($params[$param]);
        }

        if ($params !== []) {
            $query = http_build_query($params, '', '&', PHP_QUERY_RFC3986);

            if ($query !== '') {
                $path .= '?' . $query;
            }
        }

        $base = rtrim($this->context()->basePath ?: '', '/');

        $uri = '/' . ltrim($path, '/');

        $language = $this->currentLanguage->get();
        $default = LanguageHelper::getDefaultLanguage();

        if ($language !== $default) {
            $uri = '/' . strtolower($language) . $uri;
        }

        return ($base !== '' ? $base : '') . $uri;
    }

    public function base(): string
    {
        $base = rtrim($this->context()->basePath ?: '', '/');

        return $base !== '' ? $base : '/';
    }

    public function asset(string $path): string
    {
        return rtrim($this->base(), '/') . '/' . ltrim($path, '/');
    }

    public function hasRoute(string $routeName): bool
    {
        try {
            $this->routes->getByName($routeName);

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @INFO Można dodać $port -> RequestContext
     *
     * @param array<string, mixed> $params
     */
    public function absolute(string $routeName, array $params = []): string
    {
        return $this->applicationUrl(
            $this->path($routeName, $params)
        );
    }

    public function absolutePath(string $path): string
    {
        return $this->applicationUrl(
            $this->asset($path)
        );
    }

    /**
     * @param array<string, mixed> $params
     */
    public function absoluteRouteLanguage(
        string $routeName,
        string $language,
        array $params = []
    ): string {
        return $this->applicationUrl(
            $this->routeLanguage($routeName, $language, $params)
        );
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
     * @param array<string, mixed> $params
     */
    public function routeLanguage(string $routeName, string $language, array $params = []): string
    {
        $current = $this->currentLanguage->get();

        $this->currentLanguage->set($language);

        try {
            return $this->path($routeName, $params);
        } finally {
            $this->currentLanguage->set($current);
        }
    }

    public function currentLanguage(): string
    {
        return strtoupper($this->currentLanguage->get());
    }

    public function localizedPath(string $path): string
    {
        $path = '/' . trim($path, '/');

        $language = $this->currentLanguage->get();
        $default = LanguageHelper::getDefaultLanguage();

        if ($language !== $default) {
            $path = '/' . strtolower($language) . $path;
        }

        return rtrim($this->base(), '/') . $path;
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

    /**
     * Generate an absolute application URL.
     *
     * APP_URL is the canonical application address and must be used
     * instead of the current HTTP request host.
     *
     * This is especially important for webhooks and other external
     * callbacks, where the request may arrive through a proxy,
     * tunnel (e.g. ngrok) or another public endpoint.
     *
     * Example:
     *
     * APP_URL=https://example.com
     *
     * Request host:
     * https://temporary-tunnel.example
     *
     * Generated application URL:
     * https://example.com/...
     *
     * If APP_URL is not configured, the current request context is
     * used as a backwards-compatible fallback.
     */
    private function applicationUrl(string $path): string
    {
        $appUrl = trim(Environment::get('APP_URL'));

        if ($appUrl !== '') {
            $appUrl = rtrim($appUrl, '/');

            $basePath = rtrim(
                (string) $this->context()->basePath,
                '/'
            );

            if ($basePath !== '' && str_starts_with($path, $basePath)) {
                $path = substr($path, strlen($basePath));
            }

            return $appUrl . '/' . ltrim($path, '/');
        }

        $ctx = $this->context();

        return $ctx->scheme
            . '://'
            . rtrim($ctx->host, '/')
            . $path;
    }
}
