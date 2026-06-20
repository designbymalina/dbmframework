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

class ViewExtension
{
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

    /**
     * Element walidacji formularzy - wyświetlenie błędów walidacji
     *
     * @param string $field
     * @param array<string, string>|null $errors
     * @param string|null $defaultMessage
     * @param string $class
     * @param bool $alwaysRender
     * @return string
     */
    public function htmlFieldError(
        string $field,
        ?array $errors = null,
        ?string $defaultMessage = null,
        string $class = 'invalid-feedback',
        bool $alwaysRender = true
    ): string {
        $message = $errors[$this->validationKey($field)] ?? $defaultMessage;

        if (!$alwaysRender && empty($message)) {
            return '';
        }

        return sprintf(
            '<div class="%s">%s</div>',
            htmlspecialchars($class, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars((string) $message, ENT_QUOTES, 'UTF-8')
        );
    }

    /**
     * Element walidacji formularzy - dodanie klasy bledu walidacji
     *
     * @param string $field
     * @param array<string, string>|null $errors
     * @return string
     */
    public function fieldInvalid(string $field, ?array $errors = null): string
    {
        return !empty($errors[$this->validationKey($field)])
            ? ' is-invalid'
            : '';
    }

    // ===== Private =====

    private function validationKey(string $field): string
    {
        return 'error_' . $field;
    }
}
