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
    public function path(string $routeName, array $params = []): string;

    public function generateSeoFriendlyUrl(string $text, int $limit = 120): string;
}
