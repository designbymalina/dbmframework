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

namespace Dbm\Http;

use Dbm\Routing\Router;
use Dbm\Exceptions\UnauthorizedWebException;
use Dbm\Events\EventSecurityLogger;
use Dbm\Events\Security\SecurityEvent;
use Dbm\Http\Message\Request;
use Dbm\Infrastructure\Session\SessionManager;
use Psr\Http\Message\UriInterface;

/**
 * INFO: KLasa nie używana, ale do wdrożenia.
 */
final class HttpKernel
{
    public function __construct(
        private readonly Router $router,
        private readonly EventSecurityLogger $securityLogger,
        private readonly Request $request,
        private readonly SessionManager $session
    ) {}

    public function handle(): void
    {
        try {
            $uri = $_SERVER['REQUEST_URI'] ?? '/';
            $this->router->dispatch($uri);
        } catch (UnauthorizedWebException $e) {

            $event = new SecurityEvent(
                type: SecurityEvent::UNAUTHORIZED,
                userId: (int) $this->session->getSession(getenv('APP_SESSION_KEY')),
                ip: $this->request->getClientIp() ?? 'unknown',
                uri: (string) $this->request->getUri(), // UriInterface::__toString() - nie jest static ?
                timestamp: time()
            );

            $this->securityLogger->handle($event);

            header('Location: /login', true, 302); // TODO! Nie może być redirect '/login'.
            exit;
        }
    }
}
