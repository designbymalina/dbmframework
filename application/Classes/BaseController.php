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

namespace Dbm\Classes;

use App\Config\ConstantConfig;
use Dbm\Classes\Helpers\TranslationLoader;
use Dbm\Classes\Http\Request;
use Dbm\Classes\Http\Response;
use Dbm\Classes\Managers\CookieManager;
use Dbm\Classes\Managers\SessionManager;
use Dbm\Classes\Services\RememberMeService;
use Dbm\Classes\TemplateEngine;
use Dbm\Interfaces\BaseInterface;
use Dbm\Interfaces\DatabaseInterface;
use Psr\Http\Message\ResponseInterface;

abstract class BaseController extends TemplateEngine implements BaseInterface
{
    private ?DatabaseInterface $database = null;
    protected ?Translation $translation = null;
    protected ?Request $request = null;
    protected static ?DependencyContainer $diContainer = null;
    protected SessionManager $session;
    protected CookieManager $cookie;

    public function __construct(?DatabaseInterface $database = null)
    {
        $this->database = $database;

        $this->request = new Request();
        $this->session = $session ?? new SessionManager();
        $this->cookie = $cookie ?? new CookieManager();
        $this->translation = (new TranslationLoader())->load();

        if ($this->database) {
            (new RememberMeService($this->database, $this))->getRememberMe();
        }

        if (self::$diContainer === null) {
            self::$diContainer = new DependencyContainer();
        }
    }

    /**
     * Get Database
     */
    public function getDatabase(): DatabaseInterface
    {
        return $this->database;
    }

    /**
     * Get Dependency Injection
     */
    public function getDIContainer(): DependencyContainer
    {
        return self::$diContainer;
    }

    /**
     * Alias - Proxy method to SessionManager::setSession().
     */
    public function setSession(string $sessionName, mixed $sessionValue): void
    {
        $this->session->setSession($sessionName, $sessionValue);
    }

    /**
     * Alias - Proxy method to SessionManager::getSession().
     */
    public function getSession(string $sessionName): mixed
    {
        return $this->session->getSession($sessionName);
    }

    /**
     * Alias - Proxy method to SessionManager::unsetSession().
     */
    public function unsetSession(string $sessionName): void
    {
        $this->session->unsetSession($sessionName);
    }

    /**
     * Alias - Proxy method to SessionManager::destroySession().
     */
    public function destroySession(): void
    {
        $this->session->destroySession();
    }

    /**
     * Alias - Proxy method to SessionManager::getSessionByReference().
     */
    public function &getSessionByReference(string $sessionName): mixed
    {
        return $this->session->getSessionByReference($sessionName);
    }

    /**
     * Alias - Proxy method to CookieManager::setCookie().
     */
    public function setCookie(string $cookieName, string $cookieValue, int $expiry = 86400, bool $secure = true, bool $httpOnly = true): void
    {
        $this->cookie->setCookie($cookieName, $cookieValue, $expiry, $secure, $httpOnly);
    }

    /**
     * Alias - Proxy method to CookieManager::getCookie().
     */
    public function getCookie(string $cookieName): ?string
    {
        return $this->cookie->getCookie($cookieName);
    }

    /**
     * Alias - Proxy method to CookieManager::unsetCookie().
     */
    public function unsetCookie(string $cookieName): void
    {
        $this->cookie->unsetCookie($cookieName);
    }

    /**
     * Set flash message
     */
    public function setFlash(string $sessionName, string $message): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!empty($sessionName) && !empty($message)) {
            $this->setSession($sessionName, $message);
        }
    }

    /**
     * Show flash message
     */
    public function flash(?string $sessionName = null): ?array
    {
        if ($sessionName !== null) {
            $message = $this->getSession($sessionName);
            if ($message !== null) {
                $this->unsetSession($sessionName);
                return ['type' => $sessionName, 'message' => $message];
            }
        }

        foreach (ConstantConfig::ARRAY_FLASH_MESSAGE as $type => $label) {
            $message = $this->getSession($type);
            if ($message !== null) {
                $this->unsetSession($type);
                return ['type' => $type, 'message' => $message];
            }
        }

        return null;
    }

    /**
     * Redirect to location
     */
    public function redirect(string $path, array $params = []): ResponseInterface
    {
        $server = $this->request->getServerParams();

        $dir = dirname($server['PHP_SELF'] ?? '/');
        $public = '/';

        if (str_contains($dir, 'public')) {
            $public = strstr($dir, 'public', true);
        }

        $scheme = empty($server['HTTPS']) ? 'http' : 'https';
        $host = $server['HTTP_HOST'] ?? 'localhost';

        if (!empty($params)) {
            $path .= '?' . http_build_query($params);
        }

        $url = "$scheme://$host$public$path";
        return new Response(302, ['Location' => $url]);
    }

    /**
     * CSRF Token for forms, generowanie tokena CSRF z ograniczeniem czasu życia.
     */
    public function getCsrfToken(): string
    {
        // Pobierz istniejący token i czas jego utworzenia
        $csrfToken = $this->getSession('csrf_token');
        $tokenTime = $this->getSession('csrf_token_time');

        // Jeśli token jest pusty lub minęło więcej niż 15 minut, wygeneruj nowy
        if (empty($csrfToken) || empty($tokenTime) || (time() - $tokenTime > 900)) {
            $csrfToken = bin2hex(random_bytes(32));
            $this->setSession('csrf_token', $csrfToken);
            $this->setSession('csrf_token_time', time());
        }

        return $csrfToken;
    }
}
