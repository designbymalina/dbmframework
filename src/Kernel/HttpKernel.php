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

namespace Dbm\Kernel;

use Dbm\Http\Message\Response;
use Dbm\Http\Psr\Message\ExtendedRequestInterface;
use Dbm\Kernel\Contracts\KernelInterface;
use Dbm\Routing\Contracts\UrlGeneratorInterface;
use Dbm\Routing\Exceptions\RedirectException;
use Dbm\Routing\Router;
use Dbm\Routing\MiddlewareStack;
use Dbm\Routing\RequestHandler;
use Dbm\Views\TemplateEngine;
use Psr\Http\Message\ResponseInterface;

class HttpKernel implements KernelInterface
{
    public function __construct(
        private readonly Router $router,
        private readonly MiddlewareStack $middleware,
        private readonly TemplateEngine $template,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {}

    public function handle(ExtendedRequestInterface $request): ResponseInterface
    {
        $this->template->setUrlGenerator($this->urlGenerator);

        $handler = new RequestHandler(
            fn(ExtendedRequestInterface $req) => $this->dispatch($req)
        );

        try {
            return $this->middleware->process($request, $handler);
        } catch (RedirectException $e) {
            return new Response(
                $e->getStatusCode(),
                ['Location' => $e->getLocation()]
            );
        }
    }

    // ===== Private =====

    private function dispatch(ExtendedRequestInterface $request): ResponseInterface
    {
        $uri = $this->getPath($request);
        $response = $this->router->dispatch($request, $uri);

        return $response;
    }

    // ===== Important Helper (root path) =====

    private function getPath(ExtendedRequestInterface $request): string
    {
        return $this->urlGenerator->stripBasePath(
            $request->getUri()->getPath()
        );
    }
}
