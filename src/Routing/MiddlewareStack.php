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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class MiddlewareStack implements MiddlewareInterface
{
    /**
     * @var array<int, array{middleware: MiddlewareInterface, prefix: string|null}>
     */
    private array $stack = [];

    public function add(MiddlewareInterface $middleware, ?string $prefix = null): void
    {
        $this->stack[] = [
            'middleware' => $middleware,
            'prefix' => $prefix,
        ];
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $middleware = $this->resolve($request);

        $runner = array_reduce(
            array_reverse($middleware),
            fn($next, $mw) => new class ($mw, $next) implements RequestHandlerInterface {
                public function __construct(
                    private MiddlewareInterface $mw,
                    private RequestHandlerInterface $next
                ) {}

                public function handle(ServerRequestInterface $request): ResponseInterface
                {
                    return $this->mw->process($request, $this->next);
                }
            },
            $handler
        );

        return $runner->handle($request);
    }

    /**
     * @return MiddlewareInterface[]
     */
    private function resolve(ServerRequestInterface $request): array
    {
        $uri = $request->getUri()->getPath() ?: '/';

        return array_values(array_map(
            fn($item) => $item['middleware'],
            array_filter(
                $this->stack,
                fn($item)
                => $item['prefix'] === null || str_starts_with($uri, $item['prefix'])
            )
        ));
    }
}
