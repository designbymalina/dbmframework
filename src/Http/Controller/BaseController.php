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
use Dbm\Http\Contracts\BaseInterface;
use Dbm\Http\Contracts\RequestAwareInterface;
use Dbm\Http\Message\Response;
use Dbm\Http\Psr\Message\ExtendedRequestInterface;
use Dbm\Infrastructure\Cookie\CookieManager;
use Dbm\Infrastructure\Session\SessionManager;
use Dbm\Routing\Contracts\UrlGeneratorInterface;
use Dbm\Views\TemplateEngine;
use Psr\Http\Message\ResponseInterface;

/**
 * Base controller for all HTTP (web) controllers..
 */
abstract class BaseController implements BaseInterface, RequestAwareInterface
{
    /* ===== Internal - The method should only be used by the framework, not by controllers ===== */
    protected ?DependencyContainer $container = null;

    /* ===== Core infrastructure (injected by framework) ===== */
    protected ?ExtendedRequestInterface $request = null;
    protected ?SessionManager $session = null;
    protected ?CookieManager $cookie = null;
    protected ?TemplateEngine $view = null;
    protected ?UrlGeneratorInterface $url = null;

    /* ===== Framework injection hooks (called by router/kernel) ===== */

    final public function setContainer(DependencyContainer $container): void
    {
        $this->container = $container;
    }

    final public function setRequest(ExtendedRequestInterface $request): void
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

    final public function setView(TemplateEngine $view): void
    {
        $this->view = $view;
    }

    final public function setUrlGenerator(UrlGeneratorInterface $url): void
    {
        $this->url = $url;
    }

    /* ===== Protected accessors (fail-fast) ===== */

    protected function container(): DependencyContainer
    {
        if (!$this->container) {
            throw new \RuntimeException('DependencyContainer not available in controller.');
        }

        return $this->container;
    }

    protected function request(): ExtendedRequestInterface
    {
        return $this->request
            ?? throw new \LogicException('Request not injected into controller.');
    }

    protected function session(): SessionManager
    {
        return $this->session
            ?? throw new \LogicException('SessionManager not injected into controller.');
    }

    protected function cookie(): CookieManager
    {
        return $this->cookie
            ?? throw new \LogicException('CookieManager not injected into controller.');
    }

    protected function view(): TemplateEngine
    {
        return $this->view
            ?? throw new \LogicException('View not injected into controller.');
    }

    protected function url(): UrlGeneratorInterface
    {
        return $this->url
            ?? throw new \LogicException('UrlGenerator not injected into controller.');
    }

    /* ===== Session & flash helpers ===== */

    public function setSession(string $key, mixed $value): void
    {
        $this->session()->setSession($key, $value);
    }

    public function getSession(string $key): mixed
    {
        return $this->session()->getSession($key);
    }

    public function unsetSession(string $key): void
    {
        $this->session()->unsetSession($key);
    }

    public function destroySession(): void
    {
        $this->session()->destroySession();
    }

    public function &getSessionByReference(string $key): mixed
    {
        return $this->session()->getSessionByReference($key);
    }

    /* ===== Cookie helpers ===== */

    public function setCookie(
        string $name,
        string $value,
        int $expiry = 86400,
        bool $secure = true,
        bool $httpOnly = true
    ): void {
        $this->cookie()->setCookie($name, $value, $expiry, $secure, $httpOnly);
    }

    public function getCookie(string $name): ?string
    {
        return $this->cookie()->getCookie($name);
    }

    public function unsetCookie(string $name): void
    {
        $this->cookie()->unsetCookie($name);
    }

    /* ===== User helpers ===== */

    protected function getUserId(): int
    {
        return (int) ($this->getSession(getenv('APP_SESSION_KEY')) ?? 0);
    }

    /* ===== Response helpers ===== */

    /**
     * @param array<string, scalar> $params
     */
    protected function path(string $name, array $params = []): string
    {
        if (!$this->url) {
            throw new \RuntimeException('UrlGenerator not set in controller');
        }

        foreach ($params as $key => $value) {
            if (is_string($value) && str_contains($value, ' ')) {
                $params[$key] = $this->url->generateSeoFriendlyUrl($value);
            }
        }

        return $this->url->path($name, $params);
    }

    /**
     * @param array<string, scalar> $params
     */
    protected function redirect(string $route, array $params = []): ResponseInterface
    {
        if (!$this->url) {
            throw new \RuntimeException('UrlGenerator not set in controller');
        }

        return new Response(302, [
            'Location' => $this->url->path($route, $params),
        ]);
    }

    /**
     * Render for InstallerModule
     *
     * @param array<string, mixed> $data
     */
    protected function render(string $template, array $data = []): ResponseInterface
    {
        return $this->view->render($template, $data);
    }
}
