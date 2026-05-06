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

namespace Dbm\Views\Extension;

use Dbm\Core\Paths;
use Dbm\Infrastructure\Cookie\CookieManager;
use Dbm\Localization\LanguageHelper;
use Psr\Http\Message\ServerRequestInterface;

class ViewExtension
{
    public function __construct(
        private readonly ServerRequestInterface $request,
        private readonly CookieManager $cookie
    ) {}

    /*
     * Visit counter
     *
     * @INFO Metodę można przenieść do osobnej klasy i tylko załadować.
     */
    public function counterVisits(): string
    {
        $result = '1';
        $length = 16;

        $file = 'counter_visits.txt';

        $basePath = Paths::joinPaths(Paths::basePath(), 'data', 'txt');
        $pathFile = Paths::joinPaths($basePath, $file);

        if (!is_dir($basePath)) {
            mkdir($basePath, 0o755, true);
        }

        if (!file_exists($pathFile) || filesize($pathFile) === 0) {
            file_put_contents($pathFile, $result);
            $counterFile = 0;
        } else {
            $handle = fopen($pathFile, "r+");
            $counterFile = (int) fgets($handle, $length);
            $result = (string) ($counterFile + 1);

            fseek($handle, 0);
            fwrite($handle, $result, $length);
            fclose($handle);
        }

        // kopia zapasowa
        $dirCopy = Paths::joinPaths($basePath, 'copies');
        $pathCopy = Paths::joinPaths($dirCopy, $file);

        if (!is_dir($dirCopy)) {
            mkdir($dirCopy, 0o755, true);
        }

        if (!file_exists($pathCopy) || filesize($pathCopy) === 0) {
            file_put_contents($pathCopy, $result);
            $counterCopy = 0;
        } else {
            $handle = fopen($pathCopy, "r");
            $counterCopy = (int) fread($handle, filesize($pathCopy));
            fclose($handle);

            if ($counterFile >= $counterCopy) {
                copy($pathFile, $pathCopy);
            } else {
                copy($pathCopy, $pathFile);
            }
        }

        return $result;
    }

    /**
     * Metoda generująca element <select> z opcjami
     *
     * @param array<string, mixed> $options
     * @param string|array<string>|null $selected
     */
    public function htmlCreateSelect(
        array $options,
        string $name,
        ?string $identifier = null,
        ?string $class = null,
        bool $required = false,
        string|array|null $selected = null,
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
            $isSelected = (is_array($selected) && in_array($key, $selected, true))
                || $selected === (string) $key ? ' selected' : '';

            $html .= $space . "    <option value=\"$key\"$isSelected>$value</option>\n";
        }

        $html .= $space . "</select>\n";

        return $html;
    }

    /**
     * Generuje menu przełącznika języka (domyślnie style Bootstrap 5).
     *
     * @param string $asset Ścieżka do katalogu z obrazkami języków.
     * @param string|null $space Opcjonalne wcięcie dla czytelności HTML.
     * @param string|null $class Opcjonalne dodanie klas szablonu
     * @param string|null $version Opcjonalne dodanie wersji dla nietypowych szablonów
     * @return string HTML przełącznika języka.
     */
    public function htmlLanguage(string $asset, ?string $space = null, ?string $class = null, ?string $version = null): ?string
    {
        $availableLanguages = LanguageHelper::getAvailableLanguages();
        $defaultLanguage = LanguageHelper::getDefaultLanguage();

        if ($defaultLanguage === null) {
            return null;
        }

        $cookieLang = 'dbmLanguage';
        $currentLang = $this->cookie->getCookie($cookieLang) ?? $defaultLanguage;

        // Ustalamy wcięcie dla formatowania HTML
        $space = is_numeric($space) ? str_repeat('    ', (int) $space) : ($space ?? '');
        $switchOne = (!empty($version) && strtoupper($version) === 'ONE');

        // Obsługa zmiany języka
        $selectedLang = strtoupper(
            preg_replace('/[^A-Z]/', '', $this->request->getQueryParams()['lang'] ?? '')
        );

        if ($selectedLang) {
            if ($selectedLang === 'OFF') {
                $this->cookie->unsetCookie($cookieLang);
                $currentLang = $defaultLanguage;
            } elseif (in_array($selectedLang, $availableLanguages, true)) {
                $this->cookie->setCookie($cookieLang, $selectedLang, 365 * 24 * 60 * 60);
                $currentLang = $selectedLang;
            }
        }

        // Normalizacja ścieżki
        $asset = rtrim($asset, '/');
        $imgBase = $asset . '/images/lang/';
        $imgLang = $imgBase . strtolower($currentLang) . '.png';

        // Tworzymy HTML
        $html = "<!-- htmlLanguage -->" . PHP_EOL;

        if (!$switchOne) {
            $html .= $space . "<ul class=\"list-unstyled " . $class . "\">" . PHP_EOL;
        }

        $html .= $space . "    <li class=\"dropdown\">" . PHP_EOL;
        $html .= $space . "        <a href=\"#\" role=\"button\"" . ($switchOne ? "" : " class=\"dropdown-toggle link-dark\" data-bs-toggle=\"dropdown\" aria-expanded=\"false\"") . ">";
        $html .= "<img src=\"" . $imgLang . "\" alt=\"" . $currentLang . "\">";

        if ($switchOne) {
            $html .= " <i class=\"bi bi-chevron-down toggle-dropdown\"></i>";
        }

        $html .= "</a>" . PHP_EOL;

        $html .= $space . "        <ul class=\"" . ($switchOne ? "dbm-list-language-one" : "dropdown-menu dropdown-menu-end") . "\">" . PHP_EOL;

        foreach ($availableLanguages as $lang) {
            $queryParams = array_merge($this->request->getQueryParams(), ['lang' => $lang]);
            $queryString = http_build_query($queryParams);
            $classActive = (strtolower($currentLang) === strtolower($lang)) ? " active" : "";
            $imgSrc = $imgBase . strtolower($lang) . '.png';

            $html .= $space . "            <li class=\"dropdown-item" . $classActive . "\">";
            $html .= "<a href=\"?" . $queryString . "\" class=\"d-block\">";
            $html .= "<img src=\"" . $imgSrc . "\" alt=\"" . strtoupper($lang) . "\" class=\"me-2\">";
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
}
