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

namespace Dbm\Http\Controller;

use Dbm\Core\DependencyContainer;
use Dbm\Http\Contracts\BaseApiInterface;
use Dbm\Http\Message\Request;
use Dbm\Http\Message\Response;
use Dbm\Http\Message\Stream;
use Dbm\Infrastructure\Cookie\CookieManager;
use Dbm\Infrastructure\Session\SessionManager;
use Psr\Http\Message\ResponseInterface;

/**
 * Base controller for API endpoints.
 * JSON-only responses. No view layer. Optional session & translation
 */
abstract class BaseApiController implements BaseApiInterface
{
    /* ===== Internal (framework only) ===== */

    protected ?DependencyContainer $container = null;

    /* ===== Core infrastructure (injected by kernel/router) ===== */

    protected ?Request $request = null;
    protected ?SessionManager $session = null;
    protected ?CookieManager $cookie = null;

    /* ===== Framework injection hooks ===== */

    final public function setContainer(DependencyContainer $container): void
    {
        $this->container = $container;
    }

    final public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    final public function setSessionManager(SessionManager $session): void
    {
        $this->session = $session;
    }

    final public function setCookieManager(CookieManager $cookie): void
    {
        $this->cookie = $cookie;
    }

    /* ===== Protected accessors (fail-fast) ===== */

    protected function container(): DependencyContainer
    {
        return $this->container
            ?? throw new \RuntimeException('DependencyContainer not available in API controller.');
    }

    protected function request(): Request
    {
        return $this->request
            ?? throw new \LogicException('Request not injected into API controller.');
    }

    protected function session(): SessionManager
    {
        return $this->session
            ?? throw new \LogicException('SessionManager not injected into API controller.');
    }

    protected function cookie(): CookieManager
    {
        return $this->cookie
            ?? throw new \LogicException('CookieManager not injected into API controller.');
    }

    /* ===== Session helpers (optional - compatibility) ===== */

    protected function getUserId(): int
    {
        return (int) $this->session()->getSession(getenv('APP_SESSION_KEY'));
    }

    /* ===== JSON Response helper ===== */

    protected function jsonResponse(
        array|string|int|float|bool|null $data,
        int $status = 200,
        array $headers = []
    ): ResponseInterface {
        $headers = array_merge(
            ['Content-Type' => 'application/json'],
            $headers
        );

        if (!is_string($data)) {
            $data = json_encode(
                $data,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
        }

        return new Response(
            $status,
            $headers,
            new Stream($data)
        );
    }
}
