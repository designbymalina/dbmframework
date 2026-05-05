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

use Dbm\Security\Jwt\JwtService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class JwtAuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private JwtService $jwtService,
        private ResponseFactoryInterface $responseFactory
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var \Dbm\Routing\Route|null $route */
        $route = $request->getAttribute('route');

        // Jeśli brak route → puść dalej
        if (!$route) {
            return $handler->handle($request);
        }

        $permission = $route->getPermission();

        // PUBLIC ROUTE - NIE wymagamy JWT
        if ($permission === null) {
            return $handler->handle($request);
        }

        // ROUTE WYMAGA AUTH - sprawdzamy token
        $authHeader = $request->getHeaderLine('Authorization');

        if (!str_starts_with($authHeader, 'Bearer ')) {
            return $this->unauthorized('Missing token');
        }

        $token = substr($authHeader, 7);

        if (!$this->jwtService->validateToken($token)) {
            return $this->unauthorized('Invalid token');
        }

        // @TODO: payload - user
        // $request = $request->withAttribute('user', $payload);

        return $handler->handle($request);
    }

    private function unauthorized(string $message): ResponseInterface
    {
        $response = $this->responseFactory->createResponse(401);

        $response->getBody()->write(json_encode([
            'error' => $message,
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }
}
