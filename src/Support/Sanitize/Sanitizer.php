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

final class Sanitizer
{
    /* Dozwolone tagi HTML */
    private const HTML_ALLOWED_TAGS = [
        'p', 'strong', 'b', 'em', 'i', 'u', 's', 'del', 'ins', 'mark', 'small',
        'span', 'div', 'main', 'section', 'article', 'aside', 'header', 'footer', 'nav',
        'br', 'hr',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'ul', 'ol', 'li',
        'blockquote', 'pre', 'code', 'kbd',
        'sup', 'sub',
        'a', 'button', 'img',
        'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td',
        'figure', 'figcaption',
        'details', 'summary',
        'iframe', 'video', 'source',
    ];

    /** Dozwolone hosty iframe */
    private const HTML_ALLOWED_IFRAME_HOSTS = [
        'youtube.com', 'www.youtube.com', 'youtube-nocookie.com', 'www.youtube-nocookie.com',
        'vimeo.com', 'www.vimeo.com', 'player.vimeo.com',
    ];

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

    public function sanitizeToken(?string $token): string
    {
        $token = trim((string) $token);

        // Walidacja - tylko małe/duże litery, cyfry, długość 32-128 znaki
        if (!preg_match('/^[a-f0-9]{32,128}$/i', $token)) {
            return '';
        }

        return $token;
    }

    public function sanitizeTags(string $text): string
    {
        $text = strip_tags($text);
        $text = wordwrap($text, 50, ' ', true);

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

    /**
     * Sanitizacja danych przed wstawieniem do bazy danych.
     * Usuwa znaczniki HTML, redukuje białe znaki.
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
        return preg_replace('/\s+/', ' ', trim($text));
    }

    /**
     * Sanitizacja danych z dopuszczaniem HTML przed wstawieniem do bazy danych.
     */
    public function sanitizeHTML(string $text): string
    {
        if ($text === '') {
            return '';
        }

        // Usuwamy potencjalnie niebezpieczne elementy wraz z zawartością.
        $text = preg_replace(
            [
                '@<script\b[^>]*?>.*?</script\s*>@si',
                '@<noscript\b[^>]*?>.*?</noscript\s*>@si',
                '@<style\b[^>]*?>.*?</style\s*>@si',
                '@<object\b[^>]*?>.*?</object\s*>@si',
                '@<embed\b[^>]*?>@si',
                '@<applet\b[^>]*?>.*?</applet\s*>@si',
                '@<form\b[^>]*?>.*?</form\s*>@si',
                '@<base\b[^>]*?>@si',
                '@<meta\b[^>]*?>@si',
                '@<link\b[^>]*?>@si',
            ],
            '',
            $text
        );

        // Chronimy komentarze HTML przed strip_tags().
        $comments = [];

        $text = preg_replace_callback(
            '/<!--.*?-->/s',
            function (array $matches) use (&$comments): string {
                $key = '___DBM_COMMENT_' . count($comments) . '___';

                $comments[$key] = $matches[0];

                return $key;
            },
            $text
        );

        // Usuwamy event handlery: onclick, onload, onerror, onmouseover itd.
        $text = preg_replace(
            '/\s+on[a-z0-9_-]+\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)/i',
            '',
            $text
        );

        // Blokujemy niebezpieczne protokoły w atrybutach.
        $text = preg_replace(
            '/((?:href|src|action|formaction|poster)\s*=\s*)(["\']?)\s*(?:javascript|vbscript|data|file):/i',
            '$1$2',
            $text
        );

        // Dozwolone tagi HTML.
        $allowedTags = '<' . implode('><', self::HTML_ALLOWED_TAGS) . '>';

        // Usuwamy nieznane tagi, ale pozostawiamy ich zawartość.
        $text = strip_tags($text, $allowedTags);

        // Dodatkowa kontrola iframe i video. Dodatkowo: sanitizeInlineStyle()?
        $text = $this->sanitizeIframes($text);
        $text = $this->sanitizeVideos($text);

        // Przywracamy komentarze HTML.
        if ($comments !== []) {
            $text = str_replace(array_keys($comments), array_values($comments), $text);
        }

        return $text;
    }

    // ===== Private =====

    private function sanitizeIframes(string $text): string
    {
        return preg_replace_callback(
            '/<iframe\b[^>]*src=["\']([^"\']+)["\'][^>]*>.*?<\/iframe\s*>/is',
            function (array $matches): string {
                $url = $matches[1];

                $host = parse_url($url, PHP_URL_HOST);

                if ($host === null) {
                    return '';
                }

                $host = strtolower($host);

                if (!in_array($host, self::HTML_ALLOWED_IFRAME_HOSTS, true)) {
                    return '';
                }

                return $matches[0];
            },
            $text
        );
    }

    private function sanitizeVideos(string $text): string
    {
        return preg_replace_callback(
            '/<video\b[^>]*>.*?<\/video\s*>/is',
            function (array $matches): string {
                $video = $matches[0];

                if (preg_match_all('/<source\b[^>]*src=["\']([^"\']+)["\'][^>]*>/is', $video, $sources) === false) {
                    return '';
                }

                foreach ($sources[1] as $src) {
                    if (!filter_var($src, FILTER_VALIDATE_URL)) {
                        return '';
                    }

                    if (preg_match('/^(?:javascript|vbscript|data|file):/i', $src)) {
                        return '';
                    }
                }

                return $video;
            },
            $text
        );
    }
}
