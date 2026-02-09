<?php

declare(strict_types=1);

namespace Dbm\Exceptions;

use Dbm\Events\EventSecurityLogger;
use Dbm\Events\Security\SecurityEvent;
use Dbm\Http\Message\Request;
use Dbm\Infrastructure\Log\Logger;
use Dbm\Infrastructure\Session\SessionManager;
use Exception;

/**
 * INFO! Exception nie powinien robić logiki aplikacyjnej.
 * Docelowo można zmienić na HttpKernel i EventSecurityLogger
 * Rejestracja w kontenerze services.php
 * W start.php -> dispatchRequest()
 * $kernel = $container->get(\Dbm\Http\HttpKernel::class);
 * $kernel->handle();
 */
final class UnauthorizedWebException extends Exception
{
    public function __construct(?Exception $previous = null)
    {
        parent::__construct('Unauthorized access!', 401, $previous);

        // INFO! Rzadko uruchamiany kod tyczasowy, docelowo HttpKernel.
        $request = new Request();
        $session = new SessionManager();
        $securityLogger = new EventSecurityLogger(new Logger());

        $event = new SecurityEvent(
            type: SecurityEvent::UNAUTHORIZED,
            userId: (int) $session->getSession(getenv('APP_SESSION_KEY')),
            ip: $request->getClientIp() ?? 'unknown',
            uri: (string) $request->getUri(),
            timestamp: time()
        );

        $securityLogger->handle($event);
    }
}
