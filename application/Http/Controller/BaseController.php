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
use Dbm\Http\Contracts\BaseInterface;
use Dbm\Http\Message\Request;
use Dbm\Http\Message\Response;
use Dbm\Infrastructure\Cookie\CookieManager;
use Dbm\Infrastructure\Session\SessionManager;
use Dbm\Localization\Contracts\TranslationInterface;
use Dbm\Localization\Translation;
use Dbm\Security\CsrfTokenManager;
use Dbm\Support\Traits\LazyLoaderTrait;
use Dbm\Views\Flash\FlashBag;
use Dbm\Views\TemplateEngine;
use Psr\Http\Message\ResponseInterface;

/**
 * Base controller for all HTTP (web) controllers.
 *
 * Provides optional access to: Request, Session, Cookies, Translation, View engine
 * All dependencies are injected by the framework at runtime.
 * Controllers may use only what they need.
 */
abstract class BaseController implements BaseInterface
{
    /* ===== Lazy loading of dependencies ===== */
    use LazyLoaderTrait;

    /* ===== Infrastructure ===== */
    protected ?DependencyContainer $container = null;

    /* ===== Request lifecycle ===== */
    protected ?Request $request = null;
    protected ?SessionManager $session = null;
    protected ?CookieManager $cookie = null;

    /* ===== Persistence ===== */
    protected ?DatabaseInterface $database = null;
    protected ?TranslationInterface $translation = null;

    /* ===== Presentation ===== */
    protected ?TemplateEngine $view = null;
    protected ?FlashBag $flash = null;

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

    final public function setView(TemplateEngine $view): void
    {
        $this->view = $view;
    }

    final public function setTranslation(TranslationInterface $translation): void
    {
        $this->translation = $translation;
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

    public function view(): TemplateEngine
    {
        return $this->view
            ?? throw new \LogicException('View not injected into controller.');
    }

    protected function translation(): TranslationInterface
    {
        return $this->lazy(
            'translation',
            fn() => $this->container()->get(Translation::class)
        );
    }

    protected function trans(string $key, ?array $data = null, ?array $sprintf = null): string
    {
        return $this->translation()->trans($key, $data, $sprintf);
    }

    /* ===== View helpers ===== */

    protected function render(string $template, array $data = []): ResponseInterface
    {
        return $this->view()->render($template, $data);
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

    /* ===== Flash helpers ===== */

    protected function flash(): FlashBag
    {
        return $this->flash ??= new FlashBag($this->session()); // INFO: new FlashBag() - kompromis architektoniczny.
    }

    public function setFlash(string $message, string $type = 'messageInfo'): void
    {
        $this->flash()->set($message, $type);
    }

    public function getFlash(?string $type = null): ?array
    {
        return $this->flash()->get($type);
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

    /* ===== Infrastructure helpers ===== */

    public function getDatabase(): ?DatabaseInterface
    {
        return $this->database;
    }

    protected function redirect(string $path, array $params = []): ResponseInterface
    {
        $server = $this->request()->getServerParams();

        $dir = dirname($server['PHP_SELF'] ?? '/');
        $base = '/';

        if (str_contains($dir, 'public')) {
            $base = rtrim(strstr($dir, 'public', true), '/');
        }

        $scheme = empty($server['HTTPS']) ? 'http' : 'https';
        $host = $server['HTTP_HOST'] ?? 'localhost';

        if ($params) {
            $path .= '?' . http_build_query($params);
        }

        $path = '/' . ltrim($path, '/');

        return new Response(302, [
            'Location' => "{$scheme}://{$host}{$base}{$path}",
        ]);
    }

    /* ===== Security helpers ===== */

    protected function csrfToken(): CsrfTokenManager
    {
        return $this->lazy('csrfToken', fn() => new CsrfTokenManager($this->session()));
    }
}
