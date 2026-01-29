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

/**
 * Globalny kontekst routingu dla aktualnego requestu.
 *
 * Odpowiedzialności:
 * - przechowuje aktualnie dopasowaną trasę
 * - udostępnia nazwę bieżącej trasy (dla widoków, middleware, ACL)
 * - przechowuje wszystkie nazwane trasy (do sprawdzania istnienia)
 * - przechowuje parametry tras dynamicznych
 * - udostępnia generator URL
 *
 * Uwaga:
 * Klasa celowo używa statycznego stanu - żyje dokładnie tyle, ile jeden cykl request -> response.
 */
final class RoutingContext
{
    /** Aktualnie dopasowana trasa */
    private static ?Route $currentRoute = null;

    /** Nazwa aktualnej trasy (cache dla wygody) */
    private static ?string $currentRouteName = null;

    /** Wszystkie nazwane trasy w aplikacji */
    private static array $namedRoutes = [];

    /** Parametry z trasy dynamicznej */
    private static array $params = [];

    /** Generator URL */
    private static ?UrlGenerator $urlGenerator = null;

    /**
     * Rejestruje wszystkie nazwane trasy.
     * Wywoływane raz na początku dispatch().
     */
    public static function setNamedRoutes(array $routes): void
    {
        self::$namedRoutes = $routes;
    }

    /**
     * Sprawdza, czy trasa o podanej nazwie istnieje w aplikacji.
     */
    public static function hasRoute(string $name): bool
    {
        return isset(self::$namedRoutes[$name]);
    }

    /**
     * Ustawia aktualnie dopasowaną trasę.
     */
    public static function setCurrentRoute(Route $route): void
    {
        self::$currentRoute = $route;
        self::$currentRouteName = $route->name;
    }

    /**
     * Zwraca aktualną trasę (jeśli istnieje).
     */
    public static function currentRoute(): ?Route
    {
        return self::$currentRoute;
    }

    /**
     * Zwraca nazwę aktualnej trasy.
     */
    public static function currentRouteName(): ?string
    {
        return self::$currentRouteName;
    }

    /**
     * Sprawdza, czy aktualna trasa ma podaną nazwę.
     * (Pomocnicze, głównie dla widoków / ACL)
     */
    public static function routeIs(string $name): bool
    {
        return self::$currentRouteName === $name;
    }

    /**
     * Ustawia parametry trasy dynamicznej.
     */
    public static function setRouteParams(array $params): void
    {
        self::$params = $params;
    }

    /**
     * Zwraca parametry trasy.
     */
    public static function getRouteParams(): array
    {
        return self::$params;
    }

    /**
     * Rejestruje generator URL.
     */
    public static function setUrlGenerator(UrlGenerator $generator): void
    {
        self::$urlGenerator = $generator;
    }

    /**
     * Zwraca generator URL lub rzuca wyjątek, jeśli nie został ustawiony.
     */
    public static function url(): UrlGenerator
    {
        if (!self::$urlGenerator) {
            throw new \RuntimeException('UrlGenerator not initialized');
        }

        return self::$urlGenerator;
    }

    /**
     * Sprawdza, czy generator URL jest dostępny.
     */
    public static function hasUrl(): bool
    {
        return self::$urlGenerator !== null;
    }
}
