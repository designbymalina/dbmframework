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

use Dbm\Core\Config\AppConfig;
use Dbm\Debug\DebugToolbarMiddleware;
use Dbm\Exceptions\ExceptionHandler;
use Dbm\Exceptions\ForbiddenException;
use Dbm\Exceptions\UnauthorizedWebException;
use Dbm\Http\Message\Response;
use Dbm\Http\Response\ApiResponder;
use Dbm\Infrastructure\Error\ErrorLogger;
use Dbm\Routing\Contracts\UrlGeneratorInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ExceptionMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ExceptionHandler $handler,
        private readonly ErrorLogger $errorLogger,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        if (str_contains($request->getUri()->getPath(), '/_debug/')) {
            return $next->handle($request);
        }

        try {
            return $next->handle($request);
        } catch (UnauthorizedWebException $e) {
            if ($this->isApi($request)) {
                return $this->json('Unauthorized', 401);
            }

            return $this->redirect('login');
        } catch (ForbiddenException $e) {
            if ($this->isApi($request)) {
                return $this->json('Forbidden', 403);
            }

            return $this->redirect('home');
        } catch (\Throwable $e) {
            $env = AppConfig::getEnv();

            // Logger (debug / dev / fast analysis) )
            $this->errorLogger->exception($e, 'EXCEPTION_MIDDLEWARE');

            // @INFO Optional: Logger PSR-3 (system / monitoring / production)
            // $this->logger->error($e->getMessage(), ['exception' => $e]);

            $response = $this->handler->handle($e, $env);

            if (str_contains(strtolower($response->getHeaderLine('Content-Type')), 'text/html')) {
                $toolbar = new DebugToolbarMiddleware($this->urlGenerator);

                return $toolbar->process($request, new class ($response) implements RequestHandlerInterface {
                    public function __construct(private ResponseInterface $response) {}

                    public function handle(ServerRequestInterface $request): ResponseInterface
                    {
                        return $this->response;
                    }
                });
            }

            return $response;
        }
    }

    private function isApi(ServerRequestInterface $request): bool
    {
        return str_starts_with($request->getUri()->getPath(), '/api/');
    }

    private function json(string $message, int $status): ResponseInterface
    {
        return ApiResponder::error($message, $status);
    }

    private function redirect(string $routeName): ResponseInterface
    {
        $url = $this->urlGenerator->path($routeName);

        return Response::redirect($url);
    }
}
