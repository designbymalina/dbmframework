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

namespace Dbm\Support\Sanitize;

class Sanitizer
{
    /**
     * Oczyszczanie tekstu przed wyświetleniem w widoku.
     * Domyślnie pełna ochrona (usunięcie tagów + encodowanie HTML).
     * Opcjonalnie można wyłączyć usuwanie tagów poprzez `$mode = 'tags'`.
     *
     * @param string $text Tekst do oczyszczenia
     * @param string|null $mode Tryb działania (null = pełna ochrona, 'tags' = pozwala na tagi)
     * @return string Zabezpieczony tekst
     */
    public function sanitizeView(string $text, ?string $mode = null): string
    {
        if ($mode !== 'tags') {
            $text = strip_tags($text);
        }

        return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Sanitizacja danych przed wstawieniem do bazy danych.
     * Usuwa znaczniki HTML, redukuje białe znaki i encoduje znaki specjalne.
     *
     * @param string $text Tekst do sanitizacji
     * @return string Zabezpieczony tekst
     */
    public function sanitizeInsert(string $text): string
    {
        // Usuń tagi HTML
        $text = strip_tags($text);

        // Usuń niewidoczne znaki kontrolne
        $text = preg_replace('/[\x00-\x1F\x7F]/u', '', $text);

        // Redukcja wielokrotnych spacji i białych znaków
        $text = preg_replace('/\s+/', ' ', trim($text));

        // Zamiana potencjalnych znaków specjalnych na HTML entities
        return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public function sanitizeToken(?string $token): string
    {
        $token = trim((string) $token);

        // Walidacja - tylko małe/duże litery, cyfry, długość 40-64 znaki
        if (!preg_match('/^[a-f0-9]{40,64}$/i', $token)) {
            return '';
        }

        // Opcjonalnie: zabezpieczenie przed XSS (w razie błędów w szablonie)
        $token = htmlspecialchars($token, ENT_QUOTES, 'UTF-8');

        return $token;
    }

    public function sanitizeTags(string $text): string
    {
        $text = strip_tags($text);
        $text = wordwrap($text, 50, ' ', true);

        return $text;
    }

    public function sanitizeHTML(string $text): string
    {
        // Usuwanie potencjalnie niebezpiecznych tagów
        $search = [
            '@<script[^>]*?>.*?</script>@si', // Usuwanie tagów <script>
            '@<noscript[^>]*?>.*?</noscript>@si', // Usuwanie tagów <noscript>
            '@<style[^>]*?>.*?</style>@si', // Usuwanie tagów <style>
            '@<object[^>]*?>.*?</object>@si', // Usuwanie tagów <object>
            '@<embed[^>]*?>.*?</embed>@si', // Usuwanie tagów <embed>
            '@<applet[^>]*?>.*?</applet>@si', // Usuwanie tagów <applet>
            '@<form[^>]*?>.*?</form>@si', // Usuwanie tagów <form>
        ];
        $replace = ['', '', '', '', '', '', ''];
        $text = preg_replace($search, $replace, $text);

        // Usuwanie potencjalnie niebezpiecznych atrybutów
        $text = preg_replace('/on\w+="[^"]*"/i', '', $text); // Usuwanie zdarzeń jak onclick
        $text = preg_replace('/javascript:[^"]*/i', '', $text); // Usuwanie javascript: w href itp.
        $text = preg_replace('/style="[^"]*"/i', '', $text); // Usuwanie inline CSS

        // Tymczasowe zastąpienie komentarzy specjalnymi znacznikami, aby strip_tags ich nie usunął
        $text = preg_replace_callback(
            '/<!--(.*?)-->/s',
            fn($matches) => '###COMMENT_START###' . $matches[1] . '###COMMENT_END###',
            $text
        );

        // Lista dozwolonych tagów
        $allowedTags = '<p><strong><b><em><span><br><h1><h2><h3><h4><h5><h6><ul><ol><li><img><a><table><thead><tbody><tfoot><tr><th><td><blockquote><pre><code><div><iframe><video><source>';

        // Usuwanie wszystkich innych tagów oprócz dozwolonych
        $text = strip_tags($text, $allowedTags);

        // Przywracanie komentarzy do oryginalnej postaci
        $text = str_replace(['###COMMENT_START###', '###COMMENT_END###'], ['<!--', '-->'], $text);

        // Dodatkowe sprawdzanie tagów <iframe> i <video>
        $text = $this->sanitizeIframes($text);
        $text = $this->sanitizeVideos($text);

        return $text;
    }

    /**
     * Zabezpieczenia scieżki plikow przed manipulowaniem w sposób niebezpieczny
     */
    public function sanitizePath(?string $path): string
    {
        if (is_null($path)) {
            return '';
        }

        $path = str_replace(['../', '..\\'], '', $path); // Usuwanie "directory traversal"
        $path = preg_replace('/[\x00-\x1F\x7F]/', '', $path); // Usuniecie znakow kontrolnych oraz null byte

        return $path;
    }

    private function sanitizeIframes(string $text): string
    {
        return preg_replace_callback(
            '/<iframe.*?src=["\']([^"\']+)["\'].*?>.*?<\/iframe>/i',
            function ($matches) {
                $allowedDomains = ['youtube.com', 'vimeo.com']; // Dozwolone domeny
                foreach ($allowedDomains as $domain) {
                    if (str_contains($matches[1], $domain)) {
                        // Jeśli domena pasuje, pozostaw iframe
                        return $matches[0];
                    }
                }
                // Usuń iframe z niedozwolonych domen
                return '';
            },
            $text
        );
    }

    private function sanitizeVideos(string $text): string
    {
        return preg_replace_callback(
            '/<video[^>]*>.*?<source.*?src=["\']([^"\']+)["\'].*?>.*?<\/video>/i',
            function ($matches) {
                $videoSrc = $matches[1];
                // Sprawdź, czy URL źródła wideo jest prawidłowy (możesz dodać dodatkowe warunki)
                if (filter_var($videoSrc, FILTER_VALIDATE_URL)) {
                    // Jeśli źródło jest poprawnym URL-em, pozostaw wideo
                    return $matches[0];
                }
                // Usuń wideo z nieprawidłowym źródłem
                return '';
            },
            $text
        );
    }
}
