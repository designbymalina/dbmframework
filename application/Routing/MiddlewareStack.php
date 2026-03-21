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

use Dbm\Http\Message\Request;
use Psr\Http\Message\ResponseInterface;

final class MiddlewareStack
{
    /** @var array<int, array{middleware: callable, prefix: string|null}> */
    private array $stack = [];

    public function add(callable $middleware, ?string $prefix = null): void
    {
        $this->stack[] = [
            'middleware' => $middleware,
            'prefix' => $prefix,
        ];
    }

    public function handle(Request $request, Route $route): void // ResponseInterface
    {
        $uri = $request->getUri()->getPath();

        foreach ($this->stack as $item) {
            if (
                $item['prefix'] === null
                || str_starts_with($uri, $item['prefix'])
            ) {
                $response = ($item['middleware'])($request, $route);

                if ($response) {
                    $response->send();
                    exit;
                }

                /* TODO! if ($response instanceof ResponseInterface) {
                    return $response;
                } */
            }
        }
    }
}
