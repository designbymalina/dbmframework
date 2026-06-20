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

namespace Dbm\Routing\Contracts;

interface UrlGeneratorInterface
{
    /**
     * @param array<string, scalar> $params
     */
    public function path(string $name, array $params = []): string;

    /**
     * @return string
     */
    public function base(): string;

    /**
     * @param string $path
     * @return string
     */
    public function asset(string $path): string;

    /**
     * @param array<string, scalar> $params
     */
    public function absolute(string $routeName, array $params = []): string;

    /**
     * @param string $path
     * @return string
     */
    public function stripBasePath(string $path): string;

    /**
     * @param string $text
     * @param int $limit
     * @return string
     */
    public function generateSeoFriendlyUrl(string $text, int $limit = 120): string;

    /**
     * @param string $routeName
     * @param string $language
     * @param array<string, scalar> $params
     * @return string
     */
    public function routeLanguage(string $routeName, string $language, array $params = []): string;

    /**
     * @return string
     */
    public function currentLanguage(): string;

    /**
     * @param string $path
     * @return string
     */
    public function localizedPath(string $path): string;

    /**
     * @param string $routeName
     * @param string $language
     * @param array<string, mixed> $params
     * @return string
     */
    public function absoluteRouteLanguage(string $routeName, string $language, array $params = []): string;
}
