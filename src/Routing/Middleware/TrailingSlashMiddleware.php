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

namespace Dbm\Routing\Middleware;

use Dbm\Routing\Exceptions\RedirectException;
use Dbm\Routing\UriNormalizer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TrailingSlashMiddleware
{
    public function __construct(
        private UriNormalizer $normalizer
    ) {}

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $original = $request->getUri()->getPath();
        $normalized = $this->normalizer->normalize($original, $request);

        if ($original !== $normalized) {
            throw new RedirectException($normalized);
        }

        return $handler->handle($request);
    }
}
