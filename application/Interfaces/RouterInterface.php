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

namespace Dbm\Interfaces;

interface RouterInterface
{
    public function addRoute(string $route, array $controllerAction, ?string $name = null): void;

    public function dispatch(string $uri): void;
}
