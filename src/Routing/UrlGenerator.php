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
use Dbm\Localization\CurrentLanguage;
use Dbm\Localization\LanguageHelper;
use Dbm\Routing\Contracts\UrlGeneratorInterface;

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

        $base = rtrim($this->context()->basePath ?: '', '/');

        $uri = '/' . ltrim($path, '/');

        $language = $this->currentLanguage->get();

        // $language = strtoupper(
        //     $this->request->getAttribute(
        //         'language',
        //         LanguageHelper::getDefaultLanguage()
        //     )
        // );

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

    /**
     * @INFO Można dodać $port -> RequestContext
     *
     * @param array<string, mixed> $params
     */
    public function absolute(string $routeName, array $params = []): string
    {
        $ctx = $this->context();

        return $ctx->scheme . '://' . rtrim($ctx->host, '/')
            . $this->path($routeName, $params);
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
     * @param array<string, mixed> $params
     */
    public function absoluteRouteLanguage(
        string $routeName,
        string $language,
        array $params = []
    ): string {
        $ctx = $this->context();

        return $ctx->scheme . '://' . rtrim($ctx->host, '/')
            . $this->routeLanguage(
                $routeName,
                $language,
                $params
            );
    }

    // ===== Private =====

    private function context(): RequestContext
    {
        return $this->container->get(RequestContext::class);
    }
}
