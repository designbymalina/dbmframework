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
use Dbm\Database\Contracts\DatabaseInterface;
use Dbm\Http\Contracts\BaseApiInterface;
use Dbm\Http\Message\Request;
use Dbm\Http\Message\Response;
use Dbm\Http\Message\Stream;
use Dbm\Infrastructure\Cookie\CookieManager;
use Dbm\Infrastructure\Session\SessionManager;
use Dbm\Localization\Translation;
use Psr\Http\Message\ResponseInterface;

/**
 * Base controller for API endpoints.
 * JSON-only responses. No view layer. Optional session & translation
 */
abstract class BaseApiController implements BaseApiInterface
{
    protected ?DependencyContainer $container = null;
    protected ?Request $request = null;
    protected ?SessionManager $session = null;
    protected ?CookieManager $cookie = null;
    protected ?DatabaseInterface $database = null;

    public function __construct(?DatabaseInterface $database = null)
    {
        $this->database = $database;
    }

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
        if (!$this->container) {
            throw new \RuntimeException('DependencyContainer not available in controller.');
        }

        return $this->container;
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
            ?? throw new \LogicException('CookieManager not injected into controller.');
    }

    // INFO: Nowa architektura tłumaczeń.
    protected function trans(string $key, ?array $data = null, ?array $sprintf = null): string
    {
        return $this->container()->get(Translation::class)->trans($key, $data, $sprintf);
    }

    /* ===== API helpers ===== */

    protected function jsonResponse(
        array|string|int|float|bool|null $data,
        int $status = 200,
        array $headers = []
    ): ResponseInterface {
        $headers = array_merge(['Content-Type' => 'application/json'], $headers);

        if (!is_string($data)) {
            $data = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return new Response(
            $status,
            $headers,
            new Stream($data ?? '')
        );
    }

    protected function getDatabase(): ?DatabaseInterface
    {
        return $this->database;
    }
}
