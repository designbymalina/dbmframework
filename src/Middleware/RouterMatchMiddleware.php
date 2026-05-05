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

namespace Dbm\Middleware;

use Dbm\Http\Psr\Message\ExtendedRequestInterface;
use Dbm\Routing\RouteMatcher;
use Dbm\Routing\UriNormalizer;
use Dbm\Routing\Exceptions\RouteNotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class RouterMatchMiddleware implements MiddlewareInterface
{
    public function __construct(
        private RouteMatcher $matcher,
        private UriNormalizer $normalizer
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        assert($request instanceof ExtendedRequestInterface);

        $uri = $this->normalizer->normalize(
            $request->getUri()->getPath(),
            $request
        );

        $method = $request->getMethod();

        $result = $this->matcher->match($uri, $method);

        if (!$result) {
            throw new RouteNotFoundException($method, $uri);
        }

        [$route, $params] = $result;

        $request = $request
            ->withAttribute('route', $route)
            ->withAttribute('route_params', $params);

        return $handler->handle($request);
    }
}
