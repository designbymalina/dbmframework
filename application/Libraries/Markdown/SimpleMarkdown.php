<?php

/**
 * Library: Simple Markdown
 * A class designed for the DbM Framework and for use in any PHP application.
 *
 * @package Dbm\Libraries\Markdown\SimpleMarkdown
 * @author Artur Malinowski
 * @copyright Design by Malina (All Rights Reserved)
 * @license MIT
 * @link https://www.dbm.org.pl
 *
 * INFO! Dla bardziej rozbudowanych plików użyj biblioteki:
 * use League\CommonMark\CommonMarkConverter -> composer require league/commonmark
 * Dodaj tylko: "commonmark" i adapter -> wóczas jest PRO bez zmiany API.
 */

declare(strict_types=1);

namespace Dbm\Libraries\Markdown;

final class SimpleMarkdown
{
    private const MARKDOWN_CACHE_PATH = BASE_DIRECTORY . '/storage/cache/markdown/';

    /** @var array<string, int> */
    private array $usedIds = [];

    public function markdownToHtml(string $text): string
    {
        $blocks = [];

        // --- CODE BLOCKS ``` ---
        $text = preg_replace_callback('/```(\w+)?(.*?)```/s', function ($m) use (&$blocks) {
            $lang = !empty($m[1]) ? $m[1] : '';
            $code = htmlspecialchars(trim($m[2]), ENT_QUOTES);

            $key = '%%BLOCK_' . count($blocks) . '%%';
            $blocks[$key] = "\n<pre><code class=\"language-{$lang}\">{$code}</code></pre>\n";

            return $key;
        }, $text);

        // --- INLINE CODE ---
        $text = preg_replace('/`(.*?)`/', '<code>$1</code>', $text);

        // --- IMAGES ---
        $text = preg_replace('/!\[(.*?)\]\((.*?)\)/', '<img src="$2" alt="$1" class="img-fluid">', $text);

        // --- LINKS ---
        $text = preg_replace('/\[(.*?)\]\((.*?)\)/', '<a href="$2" target="_blank">$1</a>', $text);

        // --- BOLD / ITALIC ---
        $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);
        $text = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $text);

        // --- HR ---
        $text = preg_replace('/^\s*-{3,}\s*$/m', "\n<hr>\n", $text);

        // --- HEADINGS ---
        $text = preg_replace_callback('/^\s*(#{1,6})\s+(.*)$/m', function ($m) {
            $level = strlen($m[1]);
            return $this->heading($m[2], $level);
        }, $text);

        // --- LISTS ---
        $text = $this->parseLists($text);

        // --- PROTECT <code> BEFORE TABLES ---
        $codes = [];
        $text = preg_replace_callback('/<code>(.*?)<\/code>/', function ($m) use (&$codes) {
            $key = '%%CODE_' . count($codes) . '%%';
            $codes[$key] = $m[0];
            return $key;
        }, $text);

        // --- TABLES ---
        $text = $this->parseTables($text);

        // restore code
        if (!empty($codes)) {
            $text = strtr($text, $codes);
        }

        // --- PARAGRAPHS ---
        $text = $this->parseParagraphs($text);

        // --- RESTORE BLOCKS ---
        if (!empty($blocks)) {
            $text = strtr($text, $blocks);
        }

        // --- CLEANUP ---
        $text = $this->cleanup($text);

        return $text;
    }

    public function markdownToHtmlCached(string $text, string $cacheKey): string
    {
        $dir = self::MARKDOWN_CACHE_PATH;

        if (!is_dir($dir)) {
            mkdir($dir, 0o777, true);
        }

        $file = $dir . md5($cacheKey) . '.html';

        // cache hit
        if (file_exists($file)) {
            return file_get_contents($file);
        }

        // generate
        $html = $this->markdownToHtml($text);

        file_put_contents($file, $html);

        return $html;
    }

    private function heading(string $text, int $level): string
    {
        $text = trim($text);

        $id = strtolower(trim(preg_replace('/[^\p{L}0-9]+/u', '-', $text), '-'));

        if (isset($this->usedIds[$id])) {
            $this->usedIds[$id]++;
            $id .= '-' . $this->usedIds[$id];
        } else {
            $this->usedIds[$id] = 1;
        }

        return "\n<h{$level} id=\"{$id}\"><a href=\"#{$id}\" class=\"anchor\">#</a> {$text}</h{$level}>\n";
    }

    private function parseLists(string $text): string
    {
        $lines = explode("\n", $text);
        $result = '';
        $type = null;

        foreach ($lines as $line) {
            $trim = trim($line);

            $isUl = preg_match('/^\- (.*)/', $trim, $mUl);
            $isOl = preg_match('/^\d+\.\s+(.*)/', $trim, $mOl);

            if ($isUl || $isOl) {
                $newType = $isUl ? 'ul' : 'ol';
                $content = $isUl ? $mUl[1] : $mOl[1];

                if ($type !== $newType) {
                    if ($type) {
                        $result .= "</$type>\n";
                    }
                    $result .= "<$newType>\n";
                    $type = $newType;
                }

                $result .= "<li>{$content}</li>\n";
            } else {
                if ($type) {
                    $result .= "</$type>\n";
                    $type = null;
                }

                $result .= $line . "\n";
            }
        }

        if ($type) {
            $result .= "</$type>\n";
        }

        return $result;
    }

    private function parseTables(string $text): string
    {
        return preg_replace_callback('/((?:^\|.*\|\s*$\n?)+)/m', function ($m) {

            $rows = preg_split('/\n/', trim($m[1]));
            $parsed = [];

            foreach ($rows as $row) {
                if (preg_match('/^\|\-+/', $row)) {
                    continue;
                }

                $parsed[] = array_map('trim', explode('|', trim($row, '|')));
            }

            $max = 0;
            foreach ($parsed as $r) {
                $max = max($max, count($r));
            }

            // detect valid columns
            $valid = [];
            for ($i = 0; $i < $max; $i++) {
                foreach ($parsed as $row) {
                    if (!empty(trim($row[$i] ?? ''))) {
                        $valid[$i] = true;
                        break;
                    }
                }
            }

            $html = "<table class=\"table table-sm table-bordered\">\n";

            foreach ($parsed as $i => $cells) {
                $tag = $i === 0 ? 'th' : 'td';
                $cells = array_pad($cells, $max, '');

                $html .= "<tr>";

                foreach ($cells as $idx => $cell) {
                    if (!isset($valid[$idx])) {
                        continue;
                    }
                    $html .= "<{$tag}>{$cell}</{$tag}>";
                }

                $html .= "</tr>\n";
            }

            $html .= "</table>\n";

            return "\n{$html}\n";
        }, $text);
    }

    private function parseParagraphs(string $text): string
    {
        $lines = explode("\n", $text);
        $html = '';
        $buffer = '';

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '') {
                if (trim(strip_tags($buffer)) !== '') {
                    $html .= "<p>" . trim($buffer) . "</p>\n";
                }
                $buffer = '';
                continue;
            }

            // BLOCK elements (also inline detection)
            if (
                preg_match('/^<\/?(h[1-6]|ul|ol|li|pre|table|tr|td|th|thead|tbody|blockquote|hr)/', $line)
                || str_contains($line, '<pre')
                || str_contains($line, '<table')
                || str_contains($line, '<ul')
                || str_contains($line, '<ol')
            ) {
                if (trim(strip_tags($buffer)) !== '') {
                    $html .= "<p>" . trim($buffer) . "</p>\n";
                    $buffer = '';
                }

                $html .= $line . "\n";
                continue;
            }

            $buffer .= $line . "\n";
        }

        if (trim(strip_tags($buffer)) !== '') {
            $html .= "<p>" . trim($buffer) . "</p>\n";
        }

        return $html;
    }

    private function cleanup(string $html): string
    {
        // protecting <code> content from parsing
        $html = preg_replace_callback('/<code>(.*?)<\/code>/is', function ($m) {
            return '<code>' . htmlspecialchars($m[1], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</code>';
        }, $html);

        // unwrap invalid <p> around blocks
        $html = preg_replace(
            '/<p>\s*(<(pre|table|ul|ol)[^>]*>.*?<\/\2>)\s*<\/p>/is',
            '$1',
            $html
        );

        // fix <hr>
        $html = preg_replace('/<p>\s*<hr>\s*<\/p>/i', '<hr>', $html);

        return trim($html) . "\n";
    }
}
