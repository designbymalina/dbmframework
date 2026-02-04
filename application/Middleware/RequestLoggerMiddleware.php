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

use Dbm\Api\ApiJwtService;
use Dbm\Infrastructure\Log\Logger;
use Dbm\Routing\Route;
use Psr\Http\Message\RequestInterface;

/**
 * Middleware logujący każde żądanie HTTP.
 *
 * - Loguje metodę, ścieżkę i IP klienta.
 * - Może być rozszerzony o logowanie czasu wykonania requestu.
 *
 * INFO! W przyszłości rozdzielić na RequestLoggerMiddleware i ApiRequestLoggerMiddleware.
 */
class RequestLoggerMiddleware
{
    private ?ApiJwtService $jwtService = null;

    public function __construct(
        private Logger $logger
    ) {}

    public function __invoke(RequestInterface $request, Route $route): null
    {
        $payload = null;

        if (str_starts_with($route->getPath(), '/api')) {
            try {
                $auth = $request->getHeaderLine('Authorization');
                $token = str_starts_with($auth, 'Bearer ')
                    ? substr($auth, 7)
                    : null;

                $payload = $token ? $this->jwt()->decodeToken($token) : null;
            } catch (\Throwable $e) {
                // Ignorujemy – logger nie blokuje requestu
                $this->logger->error(
                    'RequestLoggerMiddleware JWT: ' . $e->getMessage()
                );
            }
        }

        /** @var \Dbm\Http\Message\Request|Psr\Http\Message\RequestInterface $request */
        $server = $request->getServerParams();
        $ip = $request->getClientIp() ?? ($server['REMOTE_ADDR'] ?? 'unknown');
        $method = $request->getMethod() ?? 'GET';
        $uri = $request->getUri()?->getPath() ?? '/';

        $userRole = $payload['role'] ?? 'guest';

        $duration = defined('REQUEST_START_TIME')
            ? round((microtime(true) - REQUEST_START_TIME) * 1000, 2)
            : 0.0;

        $this->logger->info(
            "Request {method} {uri} by {role} from {ip} in {time} ms",
            compact('method', 'uri', 'ip', 'userRole', 'duration')
        );

        return null;
    }

    private function jwt(): ApiJwtService
    {
        if ($this->jwtService === null) {
            $this->jwtService = new ApiJwtService();
        }

        return $this->jwtService;
    }
}
