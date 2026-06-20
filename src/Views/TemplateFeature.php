<?php

/**
 * Application: DbM Framework
 * A lightweight PHP framework for building web applications.
 *
 * @author Artur Malinowski
 * @copyright Design by Malina (All Rights Reserved)
 * @license MIT
 * @link https://www.dbm.org.pl
 *
 * @TODO Framework refactor
 *
 * TemplateFeature currently does not operate on the same object instance
 * that receives runtime configuration from controllers/modules.
 *
 * Symptoms:
 * - custom properties set via setters may be lost during rendering
 * - request attributes may differ from controller request
 * - callbacks registered on TemplateEngine may be unavailable in templates
 *
 * Example:
 * - setLanguageUrlResolver() called in module boot()
 * - resolver available on TemplateEngine instance
 * - resolver becomes NULL inside template execution context
 *
 * Temporary workaround:
 * - pass data through globals:
 *   $view->setGlobal(...)
 *
 * Long-term solution:
 * - render templates on the same TemplateEngine instance
 *   OR
 * - copy runtime state during template compilation/render preparation
 *   OR
 * - inject RequestAwareTemplateInterface directly instead of globals.
 *
 * @INFO Problem ze sposobem przekazywania Request do warstwy widoku.
 * Po refaktoryzacji przełącznik getLanguagesData() można ulepszyć.
 * Zmienne globalne $this->global() muszą zostać usunięte.
 *
 * Alternatywny sposób użycia:
 * @ var callable|null
 * private $languageUrlResolver = null;
 * W Module:
 * $view->setLanguageUrlResolver(
 *  fn(string $pageKey, string $toLanguage)
 *   => CmsLiteLanguageUrlResolver::resolve($pageKey, $toLanguage)
 * );
 * W metodzie kontrolera:
 * $this->view->setGlobal('cmslitePage', $page);
 * $this->view->setGlobal('cmsliteLanguageResolver',
 *  fn(string $pageKey, string $language)
 *   => CmsLiteLanguageUrlResolver::resolve($pageKey, $language)
 * );
 */

declare(strict_types=1);

namespace Dbm\Views;

use Dbm\Infrastructure\Cookie\CookieManager;
use Dbm\Infrastructure\Session\SessionManager;
use Dbm\Infrastructure\Log\Logger;
use Dbm\Localization\LanguageHelper;
use Dbm\Localization\Translation;
use Dbm\Routing\Contracts\UrlGeneratorInterface;
use Dbm\Routing\Route;
use Dbm\Support\Helpers\EnumHelper;
use Dbm\Views\Extension\ViewExtension;
use Psr\Http\Message\ServerRequestInterface;

abstract class TemplateFeature
{
    private ?SessionManager $sessionManager = null;
    private ?Logger $logger = null;
    private ?EnumHelper $enumHelper = null;
    private ?UrlGeneratorInterface $urlGenerator = null;
    private ?ViewExtension $viewExtension = null;

    /** @var array<string, mixed> */
    protected array $globals = [];

    protected function request(): ServerRequestInterface
    {
        /*
        * @TODO Refaktoryzacja Request w widokach.
        *
        * Aktualnie obiekt Request przekazywany do widoku przez:
        * ControllerResolver -> injectDependencies()
        * -> view->setGlobal('request', $request)
        *
        * W praktyce może to być inna instancja niż aktualny Request
        * używany przez kontroler. Powoduje to problemy z:
        *
        * - withAttribute()
        * - getAttribute()
        * - danymi dodawanymi dynamicznie w kontrolerach
        *
        * Działa w kontrolerze, ale może być niewidoczne w widoku.
        *
        * Sprawdzenie spl_object_id() w kontrolerze i widoku.
        * Docelowo widok powinien implementować
        * RequestAwareTemplateInterface i pobierać Request
        * bezpośrednio z aktualnego kontekstu żądania,
        * zamiast korzystać z global('request').
        *
        * Do wdrożenia:
        *
        * return $this->getRequest();
        */

        return $this->global('request');
    }

    protected function route(): ?Route
    {
        return $this->request()->getAttribute('route');
    }

    protected function sessionManager(): SessionManager
    {
        if (!$this->sessionManager) {
            $this->sessionManager = new SessionManager();
        }
        return $this->sessionManager;
    }

    protected function logger(): Logger
    {
        if (!$this->logger) {
            $this->logger = new Logger();
        }
        return $this->logger;
    }

    protected function enumHelper(): EnumHelper
    {
        if (!$this->enumHelper) {
            $this->enumHelper = new EnumHelper();
        }
        return $this->enumHelper;
    }

    /**
     * Globalne szablony / @INFO: Można rozszerzyć o TemplateGlobals?
     */
    public function setGlobal(string $key, mixed $value): void
    {
        $this->globals[$key] = $value;
    }

    public function global(string $key): mixed
    {
        return $this->globals[$key] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    public function globals(): array
    {
        return $this->globals;
    }

    /**
     * @return array<string, mixed>
     */
    public function debugGlobals(): array
    {
        return $this->globals();
    }

    ### ADAPTERY ###

    protected function session(): ?SessionManager
    {
        return $this->global('session');
    }

    protected function translation(): ?Translation
    {
        return $this->global('translation');
    }

    ### PUBLIC API DLA SZABLONÓW ###

    public function getSession(?string $key = null): mixed
    {
        $session = $this->global('session');

        if (!$session instanceof SessionManager) {
            return null;
        }

        return $key === null
            ? $session
            : $session->getSession($key);
    }

    public function getCookie(?string $key = null): mixed
    {
        $cookie = $this->global('cookie');

        if (!$cookie instanceof CookieManager) {
            return null;
        }

        return $key === null
            ? $cookie
            : $cookie->getCookie($key);
    }

    public function setCookie(string $cookieName, string $cookieValue, int $expiry = 86400, bool $secure = true, bool $httpOnly = true): void
    {
        $cookie = $this->global('cookie');

        if (!$cookie instanceof CookieManager) {
            return;
        }

        $cookie->setCookie($cookieName, $cookieValue, $expiry, $secure, $httpOnly);
    }

    public function unsetCookie(string $cookieName): void
    {
        $cookie = $this->global('cookie');

        if (!$cookie instanceof CookieManager) {
            return;
        }

        $cookie->unsetCookie($cookieName);
    }

    /**
     * @return array<string, string>|null
     */
    public function getFlash(?string $key = null): ?array
    {
        $flash = $this->global('flash');

        if (is_callable($flash)) {
            return $flash($key);
        }

        return null;
    }

    public function getUrlGenerator(): ?UrlGeneratorInterface
    {
        return $this->urlGenerator;
    }

    public function setUrlGenerator(UrlGeneratorInterface $urlGenerator): void
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param array<string, mixed>|null $data
     */
    public function trans(string $key, ?array $data = null): string
    {
        return $this->translation()?->trans($key, $data) ?? $key;
    }

    /**
     * Generowanie ścieżki dla zasobów na podstawie Route nazwy i parametrów
     *
     * @param array<string, scalar> $params
     */
    public function path(?string $name = null, array $params = []): string
    {
        if ($name === null || $name === '') {
            return $this->urlGenerator?->base() ?? '/';
        }

        if (!$this->urlGenerator) {
            throw new \RuntimeException('UrlGenerator not set in Template');
        }

        foreach ($params as $key => $value) {
            if (is_string($value) && str_contains($value, ' ')) {
                $params[$key] = $this->urlGenerator->generateSeoFriendlyUrl($value);
            }
        }

        return $this->urlGenerator->path($name, $params);
    }

    /**
     * Generowanie ścieżki dla zasobów URL
     */
    public function asset(?string $file = null): string
    {
        $base = rtrim($this->urlGenerator?->base() ?? '', '/');

        if ($file === null || $file === '') {
            return $base !== '' ? $base : '/';
        }

        $file = preg_replace('/[\/\\\\]+/', '/', $file);
        $file = ltrim($file, '/');

        return ($base !== '' ? $base : '') . '/' . $file;
    }

    /**
     * Obsługuje meta tagi strony, korzystając z tłumaczeń i specyficznych reguł.
     *
     * @param array<string, mixed> $overwrite
     */
    public function meta(string $key, array $overwrite = []): string
    {
        $meta = $overwrite['meta'] ?? [];

        // Obsługa meta.robots z domyślną wartością 'index,follow'
        if ($key === 'meta.robots') {
            return $meta['meta.robots'] ?? 'index,follow';
        }

        // Obsługa meta.title z domyślną wartością getenv('APP_NAME')
        if ($key === 'meta.title') {
            return $meta['meta.title'] ?? getenv('APP_NAME');
        }

        if ($key === 'meta.description') {
            return $meta['meta.description'] ?? '';
        }

        if ($key === 'meta.keywords') {
            return $meta['meta.keywords'] ?? '';
        }

        // Sprawdzamy czy meta istnieje, jeśli nie – zwracamy pusty string
        $value = $this->trans($key, $meta);
        return $value !== $key ? $value : '';
    }

    /**
     * Truncates the text to the specified number and adds an ending
     *
     * @param string $content
     * @param int $limit, default 250 characters
     * @param string $ending, default ellipsis
     *
     * @return string
     */
    public function truncate(string $content, int $limit = 250, string $ending = '...'): string
    {
        $content = htmlspecialchars_decode($content, ENT_QUOTES);
        $content = trim(strip_tags($content));

        return mb_strlen($content) > $limit
            ? trim(preg_replace('~\s+\S+$~u', '', mb_substr($content, 0, $limit))) . $ending
            : $content;
    }

    /**
     * Get application constant config (optional).
     *
     * @param array<int, mixed>|string|null $constant
     * @return mixed
     */
    public function constConfig(
        array|string|null $constant = null,
        string $class = 'App\\Config\\ConstantConfig'
    ): mixed {
        if (!class_exists($class)) {
            return null;
        }

        $reflection = new \ReflectionClass($class);

        if ($constant !== null) {
            if (is_array($constant) && !empty($constant[0]) && !empty($constant[1])) {
                $arrayConstant = $reflection->getConstant($constant[0]);

                foreach ($arrayConstant as $item) {
                    if (is_array($item)) {
                        if (array_key_exists($constant[1], $item)) {
                            return $item[$constant[1]];
                        }
                    }
                }

                if (array_key_exists($constant[1], $arrayConstant)) {
                    return $arrayConstant[$constant[1]];
                }
            } else {
                return $reflection->getConstant($constant);
            }

            return 'check->parameters';
        }

        return $reflection->getConstants();
    }

    /**
     * Generowanie linku canonical.
     */
    public function canonicalLink(): string
    {
        $appUrl = $this->applicationUrl();

        $path = $this->request()->getUri()->getPath();

        $basePath = parse_url($appUrl, PHP_URL_PATH) ?: '';

        if ($basePath !== '' && str_starts_with($path, $basePath)) {
            $path = substr($path, strlen($basePath));
        }

        return rtrim($appUrl, '/') . ($path ?: '/');
    }

    /**
     * Generowanie linków hreflang.
     *
     * @return array<array{lang: string, url: string}>
     */
    public function hreflangLinks(): array
    {
        $route = $this->route();

        if ($route === null) {
            return [];
        }

        $urlGenerator = $this->urlGenerator;

        if ($urlGenerator === null) {
            return [];
        }

        $params = $this->request()->getAttribute(
            'route_params',
            []
        );

        $links = [];

        foreach (LanguageHelper::getAvailableLanguages() as $language) {
            $links[] = [
                'lang' => strtolower($language),
                'url' => $urlGenerator->absoluteRouteLanguage(
                    $route->name,
                    $language,
                    $params
                ),
            ];
        }

        $links[] = [
            'lang' => 'x-default',
            'url' => $urlGenerator->absoluteRouteLanguage(
                $route->name,
                LanguageHelper::getDefaultLanguage(),
                $params
            ),
        ];

        return $links;
    }

    /**
     * Zwraca dane przełącznika języka.
     *
     * @param string $asset Ścieżka do assetów.
     * @return array<string, mixed>|null
     */
    public function getLanguagesData(string $asset): ?array
    {
        $availableLanguages = LanguageHelper::getAvailableLanguages();
        $defaultLanguage = LanguageHelper::getDefaultLanguage();

        // @TODO Patrz do dokumentacji na wstępie klasy.
        // $pageKey = $this->global('cmslitePage')->page_key ?? null;
        // $resolver = $this->global('cmsliteLanguageResolver');

        $currentLanguage = strtoupper(
            $this->request()->getAttribute('language', $defaultLanguage)
        );

        $asset = rtrim($asset, '/');
        $imgBase = $asset . '/images/lang/';

        $route = $this->request()->getAttribute('route');

        $languages = [];

        foreach ($availableLanguages as $language) {
            $url = '/';

            // @TODO
            // if ($pageKey && is_callable($resolver)) {
            //     $url = $resolver($pageKey, $language);
            // }

            if ($route !== null && $route->getName() !== null) {
                $params = $this->request()->getAttribute(
                    'route_params',
                    []
                );

                $url = $this->urlGenerator->routeLanguage(
                    $route->getName(),
                    $language,
                    $params
                );
            }

            $languages[] = [
                'code' => strtoupper($language),
                'active' => strtoupper($language) === strtoupper($currentLanguage)
                    ? 'active'
                    : '',
                'image' => $imgBase . strtolower($language) . '.png',
                'url' => $url,
            ];
        }

        return [
            'current' => [
                'code' => strtoupper($currentLanguage),
                'image' => $imgBase . strtolower($currentLanguage) . '.png',
            ],
            'languages' => $languages,
        ];
    }

    public function getCsrfToken(): string
    {
        $session = $this->session();

        // Pobierz istniejący token i czas jego utworzenia
        $csrfToken = $session->getSession('csrf_token');
        $tokenTime = $session->getSession('csrf_token_time');

        // Jeśli token jest pusty lub minęło więcej niż 15 minut, wygeneruj nowy
        if (empty($csrfToken) || empty($tokenTime) || (time() - $tokenTime > 900)) {
            $csrfToken = bin2hex(random_bytes(32));
            $session->setSession('csrf_token', $csrfToken);
            $session->setSession('csrf_token_time', time());
        }

        return $csrfToken;
    }

    /**
     * Zwraca parametr z GET lub POST (POST ma priorytet).
     * Dane są automatycznie konwertowane do stringa i oczyszczane z niebezpiecznych znaków.
     * Nie używa klasy Request - brak narzutu wydajnościowego.
     */
    public function getRequestValue(string $key, bool $escape = true): string
    {
        $default = '';
        $value = $_POST[$key] ?? $_GET[$key] ?? $default;

        if (is_array($value) || is_object($value)) {
            return $default;
        }

        $value = trim((string) $value);
        $value = filter_var($value, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_BACKTICK);

        if ($escape) {
            return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        return $value;
    }

    /**
     * Zwraca parametr z GET lub POST (POST ma priorytet).
     *
     * @return array<string, mixed>
     */
    public function getRequestArray(string $key): array
    {
        $value = $_POST[$key] ?? $_GET[$key] ?? [];

        return is_array($value) ? $value : [];
    }

    /**
     * Get single enum value by name.
     *
     * Template example:
     * $adminRole = $enumHelper->getEnumValue('App\Shared\Security\Enum', 'ADMIN');
     *
     * @param string $enumClass
     * @param string $caseName
     */
    public function getEnumValue(string $enumClass, string $caseName): mixed
    {
        return $this->enumHelper()->getEnumValue($enumClass, $caseName);
    }

    /**
     * @param list<string> $routeNames
     */
    public function isActive(
        string|array $routeNames,
        string $classActive = 'active',
        ?string $menuActive = 'linkActive'
    ): string {
        $route = $this->request()->getAttribute('route');
        $current = $route?->name;

        $isActive = is_array($routeNames)
            ? in_array($current, $routeNames, true)
            : $current === $routeNames;

        return $isActive ? trim(" {$classActive} {$menuActive}") : '';
    }

    public function hasRoute(string $name): bool
    {
        if (!$this->urlGenerator) {
            return false;
        }

        try {
            $this->urlGenerator->path($name);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function isCurrentRoute(string $name): bool
    {
        $route = $this->request()->getAttribute('route');

        return $route && $route->name === $name;
    }

    /**
     * Metoda konwertuje zawartość kontentu (space and replace)
     */
    public function replaceContent(
        string $content,
        int|string $indent = 0,
        string $placeholder = '<!--REPLACE_CONTENT-->',
        string $replacement = ''
    ): ?string {
        if ($content === '') {
            return null;
        }

        // normalize new lines
        $content = str_replace(["\r\n", "\r"], "\n", $content);

        $space = is_numeric($indent) ? str_repeat('    ', (int) $indent) : $indent;

        $content = preg_replace('/^/m', $space, $content);

        $content = str_replace(
            ['[URL]', $placeholder],
            [getenv('APP_URL'), trim($replacement)],
            $content
        );

        return trim($content) . PHP_EOL;
    }

    // ===== Templates Code and HTML elements =====

    /* @INFO Dla modułów można utwórzyć WidgetManager
    protected function widgets(): WidgetManager
    {
        return $this->global('widgetManager');
    } */

    protected function viewExtension(): ViewExtension
    {
        if ($this->viewExtension) {
            return $this->viewExtension;
        }

        return $this->viewExtension = new ViewExtension();
    }

    // ===== Private methods =====

    private function applicationUrl(): string
    {
        $appUrl = getenv('APP_URL');

        if (is_string($appUrl) && filter_var($appUrl, FILTER_VALIDATE_URL)) {
            return rtrim($appUrl, '/');
        }

        $uri = $this->request()->getUri();

        return sprintf('%s://%s', $uri->getScheme(), $uri->getHost());
    }
}
