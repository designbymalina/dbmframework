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

namespace Dbm\Views;

use Dbm\Http\Message\Request;
use Dbm\Infrastructure\Cookie\CookieManager;
use Dbm\Infrastructure\Session\SessionManager;
use Dbm\Infrastructure\Log\Logger;
use Dbm\Libraries\Adverts\AdvertisementCache;
use Dbm\Localization\LanguageHelper;
use Dbm\Localization\Translation;
use Dbm\Routing\RoutingContext;
use Dbm\Support\Helpers\EnumHelper;
use ReflectionClass;
use Exception;

abstract class TemplateFeature
{
    private ?SessionManager $sessionManager = null;
    private ?Logger $logger = null;
    private ?EnumHelper $enumHelper = null;
    protected array $globals = [];

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
     * Globalne szablony / INFO: Można rozszerzyć o TemplateGlobals?
     */
    public function setGlobal(string $key, mixed $value): void
    {
        $this->globals[$key] = $value;
    }

    public function global(string $key): mixed
    {
        return $this->globals[$key] ?? null;
    }

    public function globals(): array
    {
        return $this->globals;
    }

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

    public function getFlash(?string $key = null): ?array
    {
        $flash = $this->global('flash');

        if (is_callable($flash)) {
            return $flash($key);
        }

        return null;
    }

    public function trans(string $key, ?array $data = null): string
    {
        return $this->translation()?->trans($key, $data) ?? $key;
    }

    public function path(?string $name = null, array $params = []): string
    {
        // TRYB 1: path() - base path (BC)
        if ($name === null || $name === '') {
            return $this->basePath();
        }

        // TRYB 2: path('route_name', [...])
        if (!RoutingContext::hasUrl()) {
            throw new \RuntimeException('ROUTING CONTEXT URL NOT INITIALIZED');
        }

        $url = RoutingContext::url();

        foreach ($params as $key => $value) {
            if (is_string($value) && str_contains($value, ' ')) {
                $params[$key] = $url->generateSeoFriendlyUrl($value);
            }
        }

        return $url->path($name, $params);
    }

    /**
     * Generowanie ścieżki dla zasobów
     */
    public function asset(?string $file = null): string
    {
        // Pobierz bazowy path
        $basePath = $this->basePath();

        // Jeśli podano plik, dołącz go do ścieżki bazowej
        if (!empty($file)) {
            $file = preg_replace('/[\/\\\\]+/', '/', $file); // Zastępuje wielokrotne slashe
            $file = ltrim($file, '/');

            return $basePath . $file;
        }

        return $basePath;
    }

    /**
     * Obsługuje meta tagi strony, korzystając z tłumaczeń i specyficznych reguł.
     */
    public function meta(string $key, array $overwrite = [], ?array $sprint = null): string
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
        $value = $this->trans($key, $meta, $sprint);
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
     * @param array|string|null $constant
     * @return mixed
     */
    public function constConfig(
        array|string|null $constant = null,
        $class = 'App\\Config\\ConstantConfig'
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
     * Generowanie linku canonical - zalecane dla każdej podstrony
     */
    public function linkCanonical(): string
    {
        // Pobierz podstawowy adres aplikacji (bez ukośnika na końcu)
        $appUrl = getenv('APP_URL');
        if ($appUrl === false || !filter_var($appUrl, FILTER_VALIDATE_URL)) {
            throw new Exception('APP_URL is not set or is invalid.');
        }
        $appUrl = rtrim($appUrl, '/');

        // Pobierz ścieżkę z adresu aplikacji
        $appUrlPath = parse_url($appUrl, PHP_URL_PATH);
        if ($appUrlPath === null) {
            $appUrlPath = '';
        }

        // Pobierz bieżący URI strony (bez bazowego folderu aplikacji)
        $currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if ($currentUri === null) {
            $currentUri = '/';
        } else {
            $currentUri = str_replace($appUrlPath, '', $currentUri);
        }

        // Zbuduj pełny adres URL
        return $appUrl . $currentUri;
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

    public function isActive(
        string|array $routeNames,
        string $classActive = 'active',
        ?string $menuActive = 'linkActive'
    ): string {
        $current = RoutingContext::currentRouteName();

        $isActive = is_array($routeNames)
            ? in_array($current, $routeNames, true)
            : $current === $routeNames;

        return $isActive ? trim(" {$classActive} {$menuActive}") : '';
    }

    public function hasRoute(string $name): bool
    {
        return RoutingContext::hasRoute($name);
    }

    public function isCurrentRoute(string $name): bool
    {
        return RoutingContext::currentRouteName() === $name;
    }

    /**
     * Metoda konwertuje zawartość kontentu (space and replace)
     */
    public function replaceContent(string $content, string $space = '', string $searchReplace = '<!--REPLACE_CONTENT-->', string $replaceReplace = ''): ?string
    {
        if (!empty($content)) {
            $space = is_numeric($space) ? str_repeat('    ', (int) $space) : $space ?? '';
            $search = [PHP_EOL, '[URL]', $searchReplace];
            $replace = [PHP_EOL . $space, getenv('APP_URL'), trim($replaceReplace)];

            return trim(str_replace($search, $replace, $content)) . PHP_EOL;
        }

        return null;
    }

    /**
     * Metoda wyświetla reklamy
     */
    public function adverts(string $position, string $space = ''): string
    {
        return AdvertisementCache::getInstance()->getAdvert($position, $space);
    }

    // === Templates Code and HTML elements ===

    /*
     * Visit counter
     */
    public function counterVisits(): string
    {
        $result = '1';
        $length = 16;

        $file = 'counter_visits.txt';
        $path = BASE_DIRECTORY . '/data/txt/';
        $pathFile = $path . $file;

        if (!is_dir($path)) {
            mkdir($path, 0o755, true);
        }

        if (!file_exists($pathFile) || (filesize($pathFile) == 0)) {
            file_put_contents($pathFile, $result);
            $counterFile = 0;
        } else {
            $handle = fopen($pathFile, "r+");
            $counterFile = fgets($handle, $length);
            $result = strval($counterFile + 1);

            fseek($handle, 0);
            fwrite($handle, $result, $length);
            fclose($handle);
        }

        $dirCopy = $path . 'copies/';
        $pathCopy = $dirCopy . $file;

        if (!is_dir($dirCopy)) {
            mkdir($dirCopy, 0o755, true);
        }

        if (!file_exists($pathCopy) || (filesize($pathCopy) == 0)) {
            file_put_contents($pathCopy, $result);
            $counterCopy = 0;
        } else {
            $handle = fopen($pathCopy, "r");
            $counterCopy = fread($handle, filesize($pathCopy));
            fclose($handle);

            if (intval($counterFile) >= intval($counterCopy)) {
                copy($pathFile, $pathCopy);
            } elseif (intval($counterFile) < intval($counterCopy)) {
                copy($pathCopy, $pathFile);
            }
        }

        return $result;
    }

    /**
     * Metoda generująca element <select> z opcjami
     */
    public function htmlCreateSelect(
        array $options,
        string $name,
        ?string $identifier = null,
        ?string $class = null,
        bool $required = false,
        ?string $selected = null,
        ?string $space = null,
        ?string $emptyOption = null,
        string $sortOrder = 'null',
        ?int $size = null,
        bool $multiple = false,
        ?string $style = null,
    ): string {
        // Identyfikator dla pola - jeśli nie jest podany, przyjmujemy nazwę
        $identifier ??= $name;

        // Jeśli pole jest wielokrotnego wyboru, modyfikujemy nazwę jako tablicę
        $selectName = $multiple ? $name . '[]' : $name;

        // Dodanie spacji (liczba powtórzeń lub ciąg spacji)
        $space = is_numeric($space) ? str_repeat('    ', (int) $space) : $space ?? '';

        // Sortowanie opcji, jeśli wymagane
        if (strtolower($sortOrder) === 'asc') {
            asort($options);
        } elseif (strtolower($sortOrder) === 'desc') {
            arsort($options);
        }

        // Generowanie kodu HTML dla elementu <select>
        $html = "<!-- htmlCreateSelect -->\n";
        $html .= $space . "<select name=\"$selectName\" id=\"$identifier\"";

        if ($class) {
            $html .= " class=\"$class\"";
        }

        if ($style) {
            $html .= " style=\"$style\"";
        }

        if ($size) {
            $html .= " size=\"$size\"";
        }

        if ($multiple) {
            $html .= " multiple";
        }

        if ($required) {
            $html .= " required";
        }

        $html .= ">\n";

        // Opcja pusta, jeśli podana
        if ($emptyOption) {
            $html .= $space . "    <option value=\"\">$emptyOption</option>\n";
        }

        // Generowanie opcji
        foreach ($options as $key => $value) {
            $isSelected = (is_array($selected) && in_array($key, $selected)) || $selected == $key ? ' selected' : '';
            $html .= $space . "    <option value=\"$key\"$isSelected>$value</option>\n";
        }

        $html .= $space . "</select>\n";

        return $html;
    }

    /**
     * Generuje menu przełącznika języka.
     *
     * @param string $asset Ścieżka do katalogu z obrazkami języków.
     * @param string|null $space Opcjonalne wcięcie dla czytelności HTML.
     * @param string|null $class Opcjonalne dodanie klas szablonu
     * @param string|null $version Opcjonalne dodanie wersji dla nietypowych szablonów
     * @return string HTML przełącznika języka.
     */
    public function htmlLanguage(string $asset, ?string $space = null, ?string $class = null, ?string $version = null): ?string
    {
        /** @var \Dbm\Http\Controller\BaseController $this */
        $availableLanguages = LanguageHelper::getAvailableLanguages();
        $defaultLanguage = LanguageHelper::getDefaultLanguage();

        if ($defaultLanguage === null) {
            return null;
        }

        $cookieLang = 'dbmLanguage';
        $currentLang = $this->getCookie($cookieLang) ?? $defaultLanguage;

        // Ustalamy wcięcie dla formatowania HTML
        $space = is_numeric($space) ? str_repeat('    ', (int) $space) : ($space ?? '');
        $switchOne = (!empty($version) && strtoupper($version) === 'ONE');

        // Obsługa zmiany języka
        $request = new Request();
        $selectedLang = strtoupper(preg_replace('/[^A-Z]/', '', $request->getQuery('lang', '')));

        if ($selectedLang) {
            if ($selectedLang === 'OFF') {
                $this->unsetCookie($cookieLang);
                $currentLang = $defaultLanguage;
            } elseif (in_array($selectedLang, $availableLanguages, true)) {
                $this->setCookie($cookieLang, $selectedLang, 365 * 24 * 60 * 60);
                $currentLang = $selectedLang;
            }
        }

        // Tworzymy HTML
        $html = "<!-- htmlLanguage -->" . PHP_EOL;

        if (!$switchOne) {
            $html .= $space . "<ul class=\"list-unstyled " . $class . "\">" . PHP_EOL;
        }

        $html .= $space . "    <li class=\"dropdown\">" . PHP_EOL;
        $html .= $space . "        <a href=\"#\" role=\"button\"" . ($switchOne ? "" : " class=\"dropdown-toggle link-dark\" data-bs-toggle=\"dropdown\" aria-expanded=\"false\"") . ">";
        $html .= "<img src=\"" . $asset . "images/lang/" . strtolower($currentLang) . ".png\" alt=\"" . $currentLang . "\">";

        if ($switchOne) {
            $html .= " <i class=\"bi bi-chevron-down toggle-dropdown\"></i>";
        }

        $html .= "</a>" . PHP_EOL;

        $html .= $space . "        <ul class=\"" . ($switchOne ? "dbm-list-language-one" : "dropdown-menu dropdown-menu-end") . "\">" . PHP_EOL;

        foreach ($availableLanguages as $lang) {
            $queryParams = array_merge($request->getQueryParams(), ['lang' => $lang]);
            $queryString = http_build_query($queryParams);
            $classActive = (strtolower($currentLang) === strtolower($lang)) ? " active" : "";

            $html .= $space . "            <li class=\"dropdown-item" . $classActive . "\">";
            $html .= "<a href=\"?" . $queryString . "\" class=\"d-block\">";
            $html .= "<img src=\"" . $asset . "images/lang/" . strtolower($lang) . ".png\" alt=\"" . strtoupper($lang) . "\" class=\"me-2\">";
            $html .= strtoupper($lang) . "</a></li>" . PHP_EOL;
        }

        $html .= $space . "        </ul>" . PHP_EOL;
        $html .= $space . "    </li>" . PHP_EOL;

        if (!$switchOne) {
            $html .= $space . "</ul>" . PHP_EOL;
        }

        return $html;
    }

    /**
     * Heightlightowanie tekstu w zapytaniu
     */
    public function highlight(string $text, string $query): string
    {
        if ($query === '') {
            return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        $escapedText = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $pattern = '/' . preg_quote($query, '/') . '/i';

        return preg_replace(
            $pattern,
            '<mark>$0</mark>',
            $escapedText
        );
    }

    // ===== PRYWATNE METODY =====

    /**
     * Metoda pomocnicza / wspólna dla path() i asset()
     */
    private function basePath(): string
    {
        // Bazowy katalog publiczny
        $dirPublic = 'public';
        $divider = '/';

        // Pełna ścieżka do katalogu public
        $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
        $scriptDir = rtrim(str_replace('\\', '/', $scriptDir), '/'); // Normalizacja separatorów

        // Obsługa przypadku, gdy aplikacja jest w katalogu public (np. localhost)
        if (str_contains($scriptDir, "/{$dirPublic}")) {
            $requestUri = $_SERVER['REQUEST_URI'];
            $dirName = dirname($_SERVER['PHP_SELF']);

            $publicPath = substr($requestUri, strlen(strstr($dirName, $dirPublic, true)));
            $arrayRequestPath = explode($divider, $publicPath);
            $countDir = count($arrayRequestPath) - 1;

            if ($countDir > 0) {
                $basePath = str_repeat('..' . $divider, $countDir);
            } else {
                $basePath = '.' . $divider;
            }
        } else {
            $basePath = $scriptDir . $divider;
        }

        return $basePath;
    }
}
