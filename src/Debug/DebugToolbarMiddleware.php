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

namespace Dbm\Debug;

use Dbm\Core\Config\AppConfig;
use Dbm\Http\Message\StringStream;
use Dbm\Routing\Contracts\UrlGeneratorInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DebugToolbarMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $env = AppConfig::getEnv();

        if ($env !== AppConfig::ENV_DEVELOPMENT) {
            return $handler->handle($request);
        }

        $toolbar = new DebugToolbar($this->urlGenerator);
        $toolbar->setRequest($request);

        DebugRegistry::setToolbar($toolbar);

        $response = $handler->handle($request);

        $toolbar->setResponse($response);

        if (!str_contains($response->getHeaderLine('Content-Type'), 'text/html')) {
            return $response;
        }

        $stream = $response->getBody();

        if ($stream->isSeekable()) {
            $stream->rewind();
        }

        $body = $stream->getContents();
        $body = $this->injectToolbar($body, $toolbar->render());

        DebugRegistry::setToolbar(null);

        return $response->withBody(new StringStream($body));
    }

    private function injectToolbar(string $html, string $toolbar): string
    {
        return str_contains($html, '</body>')
            ? str_replace('</body>', $toolbar . PHP_EOL . '</body>', $html)
            : $html . $toolbar;
    }
}
