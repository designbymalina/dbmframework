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

namespace Dbm\Security\Middleware;

use Dbm\Routing\Route;
use Dbm\Security\AccessGuard;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private AccessGuard $guard
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var Route|null $route */
        $route = $request->getAttribute('route');

        if (!$route) {
            return $handler->handle($request);
        }

        $permission = $route->getPermission();

        // Brak permission = public route
        if ($permission === null) {
            return $handler->handle($request);
        }

        // Sprawdzamy dostęp
        $this->guard->checkPermission($permission);

        return $handler->handle($request);
    }
}
