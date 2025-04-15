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
 * TODO! Filtry oraz metody specjane szablonów można przenieść do osobnych klas.
 */

declare(strict_types=1);

namespace Dbm\Classes;

use Dbm\Classes\Helpers\LanguageHelper;
use Dbm\Classes\Http\Request;
use Dbm\Classes\Log\Logger;
use Lib\Adverts\AdvertisementCache;
use Exception;
use ReflectionClass;

class TemplateFeature
{
    private Logger $logger;

    public function __construct()
    {
        $this->logger = new Logger();
    }

    /**
     * Generowanuie ścieżki adresu strony
     */
    public function path(string $routeName = 'index', array $params = []): string
    {
        try {
            $router = RouterSingleton::getInstance();

            // Rozpoznaj parametry tekstowe do zmiany na SEO-friendly
            foreach ($params as $key => $value) {
                if (is_string($value) && strpos($value, ' ') !== false) {
                    $params[$key] = $router->generateSeoFriendlyUrl($value);
                }
            }

            $routePath = $router->generatePath($routeName, $params);

            return $this->basePath() . ltrim($routePath, '/');
        } catch (Exception $e) {
            throw new Exception("Błąd generowania ścieżki dla trasy '{$routeName}': " . $e->getMessage());
        }
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
     * Obsługuje wyszukiwanie i formatowanie kluczy w tłumaczeniach.
     */
    public function trans(string $key, array $overwrite = [], ?array $sprint = null): string
    {
        if (array_key_exists($key, $overwrite)) {
            return !empty($sprint) ? vsprintf($overwrite[$key], $sprint) : $overwrite[$key];
        }

        $translation = $this->translation->arrayTranslation ?? null;

        if ($translation && array_key_exists($key, $translation)) {
            return !empty($sprint) ? vsprintf($translation[$key], $sprint) : $translation[$key];
        }

        // return ''; // TODO! Sprawdź to?
        return $key;
    }

    /**
     * Obsługuje meta tagi strony, korzystając z tłumaczeń i specyficznych reguł.
     */
    public function meta(string $key, array $overwrite = [], ?array $sprint = null): string
    {
        $meta = $overwrite['meta'] ?? [];

        // Obsługa meta.robots z domyślną wartością 'index, follow'
        if ($key === 'meta.robots') {
            return $meta['meta.robots'] ?? 'index,follow';
        }

        // Obsługa meta.title z domyślną wartością getenv('APP_NAME')
        if ($key === 'meta.title') {
            return $meta['meta.title'] ?? getenv('APP_NAME');
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
            ? trim(preg_replace('~\s+\S+$~', '', substr($content, 0, $limit))) . $ending
            : $content;
    }

    /*
     * Visit counter
     */
    public function counterVisits(): string
    {
        $result = '1';
        $length = 16;

        $file = 'counter_visits.txt';
        $path = BASE_DIRECTORY . 'data' . DS . 'txt' . DS;
        $pathFile = $path . $file;

        if (!is_dir($path)) {
            mkdir($path, 0755, true);
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

        $dirCopy = $path . 'copies' . DS;
        $pathCopy = $dirCopy . $file;

        if (!is_dir($dirCopy)) {
            mkdir($dirCopy, 0755, true);
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
     * Get constants config.
     *
     * @param array|string|null $constant data to get.
     * @return array|string Array or string result.
     */
    public function constConfig($constant = null)
    {
        $reflection = new ReflectionClass('App\Config\ConstantConfig');

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
     * TODO! Sprawdź parse_url() na serwerze, a localhost.
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

    /**
     * Rozpoznaje aktywny link w nawigacji dla wybranej strony
     */
    public function isActive(string $link, string $class = 'dbm-active', ?string $active = 'linkActive'): string
    {
        // Pobierz instancję routera
        $router = RouterSingleton::getInstance();

        // Pobierz listę tras z routera
        $arrayRoutes = $router->getRoutes();

        // Pobierz bieżący URI bez parametrów i fragmentów
        $currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Usuń skrypt (index.php) z URI i przygotuj bazową ścieżkę
        $baseUri = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        $dirPublic = 'public';

        // Usuń bazową ścieżkę z URI, jeśli zawiera katalog 'public'
        if (strpos($baseUri, $dirPublic) !== false) {
            $basePath = strstr($baseUri, $dirPublic, true);
            $currentUri = '/' . ltrim(str_replace($basePath, '', $currentUri), '/');
        }

        // Normalizacja URI dla aplikacji w katalogu głównym
        $currentUri = '/' . ltrim($currentUri, '/');

        // Znajdź bieżącą trasę na podstawie URI
        $matchedRouteName = null;

        foreach ($arrayRoutes as $routePattern => $routeData) {
            // Zamień dynamiczne parametry w trasach na wyrażenia regularne
            $regexPattern = preg_replace('/\{[a-zA-Z_]+\}/', '[^/]+', $routePattern);
            $regexPattern = str_replace('.', '\.', $regexPattern);
            $regexPattern = '#^' . $regexPattern . '$#';

            // Dopasuj bieżące URI do wzorca
            if (preg_match($regexPattern, $currentUri)) {
                $matchedRouteName = $routeData['name'] ?? null;
                break;
            }
        }

        // Porównaj nazwę dopasowanej trasy z podanym linkiem
        return $matchedRouteName === $link ? rtrim(" {$class} {$active}") : '';
    }

    /**
     * Metoda pomocnicza dla isActive() do wyciągania parametru z aktualnego URL
     */
    public function extractParameter()
    {
        // Pobranie bieżącej ścieżki URI
        $currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Wyrażenie regularne do dopasowania liczby na końcu URL
        $pattern = '/(\d+)(?:\.html)?$/';

        // Sprawdź, czy jest dopasowanie
        if (preg_match($pattern, $currentUri, $matches)) {
            return $matches[1]; // Zwracamy dopasowaną liczbę
        }

        return null;
    }

    /**
     * Metoda konwertuje zawartość kontentu (space and replace)
     */
    public function replaceContent(string $content, string $space = '', string $searchReplace = '<!--REPLACE_CONTENT-->', string $replaceReplace = ''): ?string
    {
        if (!empty($content)) {
            $space = is_numeric($space) ? str_repeat('    ', (int)$space) : $space ?? '';
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
        if (strpos($scriptDir, "/{$dirPublic}") !== false) {
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

    /**
     * Zabezpieczenia scieżki plikow przed manipulowaniem w sposób niebezpieczny
     */
    private function sanitizePath(?string $path): string
    {
        if (is_null($path)) {
            return '';
        }

        $path = str_replace(['../', '..\\'], '', $path); // Usuwanie "directory traversal"
        $path = preg_replace('/[\x00-\x1F\x7F]/', '', $path); // Usuniecie znakow kontrolnych oraz null byte

        return $path;
    }

    /**
     * ### HTML Methods - TODO! Czy utworzyć osobną klasę dla metod HTML?
     */

    /**
     * Metoda generująca element <select> z opcjami
     */
    public function htmlCreateSelect(
        array $options,
        string $name,
        ?string $identifier = null,
        ?string $class = null,
        bool $required = false,
        ?string $space = null,
        ?string $selected = null,
        ?string $emptyOption = null,
        string $sortOrder = 'null',
        ?int $size = null,
        bool $multiple = false,
        ?string $style = null,
    ): string {
        // Identyfikator dla pola - jeśli nie jest podany, przyjmujemy nazwę
        $identifier = $identifier ?? $name;

        // Jeśli pole jest wielokrotnego wyboru, modyfikujemy nazwę jako tablicę
        $selectName = $multiple ? $name . '[]' : $name;

        // Dodanie spacji (liczba powtórzeń lub ciąg spacji)
        $space = is_numeric($space) ? str_repeat('    ', (int)$space) : $space ?? '';

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
        /** @var BaseController $this */
        $availableLanguages = LanguageHelper::getAvailableLanguages();
        $defaultLanguage = LanguageHelper::getDefaultLanguage();

        if ($defaultLanguage === null) {
            return null;
        }

        $cookieLang = 'dbmLanguage';
        $currentLang = $this->getCookie($cookieLang) ?? $defaultLanguage;

        // Ustalamy wcięcie dla formatowania HTML
        $space = is_numeric($space) ? str_repeat('    ', (int)$space) : ($space ?? '');
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
     * ### DATABASE Methods - TODO! Czy utworzyć osobną klasę dla metod DataBase?
     */

    /**
     * Pobiera dane użytkownika
     */
    public function userData(object $that): ?object
    {
        $query = "SELECT user.id AS uid, user.login, user.email, user.roles, 
                user_details.fullname, user_details.profession, user_details.avatar 
            FROM dbm_user user 
            INNER JOIN dbm_user_details user_details ON user_details.user_id = user.id 
            WHERE user.id = :uid";

        $sessionUserId = (int) $that->getSession(getenv('APP_SESSION_KEY'));

        if (!$that->database->queryExecute($query, [':uid' => $sessionUserId])) {
            $this->logger->error("Database query failed for user ID: $sessionUserId");
            return null;
        }

        $data = $that->database->fetchObject();

        if (!$data) {
            return null;
        }

        $data->avatar = $data->avatar ?: 'no-avatar.png';

        return $data;
    }
}
