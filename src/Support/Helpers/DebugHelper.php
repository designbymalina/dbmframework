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

namespace Dbm\Support\Helpers;

class DebugHelper
{
    public static function dump(mixed $var): void
    {
        ob_start();
        var_dump($var);
        $output = ob_get_clean();

        // ===== NORMALIZACJA (RAW) =====

        // array(0) { \n } array(0) {}
        $output = preg_replace('/array\((\d+)\) \{\s*\}/', 'array($1) {}', $output);

        // => \n NULL => NULL
        $output = preg_replace('/=>\s*\n\s*NULL/', '=> NULL', $output);

        // Normalizacja znaku => (spacja przed/po, i/lub usnięcie)
        $output = preg_replace('/\s*=>\s*/', ' ', $output);

        // ===== HTML ESCAPE =====

        $output = htmlspecialchars(trim($output), ENT_QUOTES, 'UTF-8');

        // ===== FORMATOWANIE STRUKTURY =====

        // Usuń łamanie po =>
        $output = str_replace('=&gt;' . PHP_EOL, '=&gt; ', $output);

        // Sspacing typów
        $output = preg_replace_callback(
            '/\s+(\b(?:object|array|string|int|bool|float|double|null|uninitialized|resource)\b\()/',
            fn($m) => ' ' . $m[1],
            $output
        );

        // ===== KOLORY =====

        // object(...)
        $output = preg_replace(
            '/object\((.*?)\)#(\d+) \((\d+)\)/',
            '<span class="item">object($1)#$2 ($3)</span>',
            $output
        );

        // array(...)
        $output = preg_replace(
            '/\b(array)\((\d+)\)/',
            '<span class="item">$1($2)</span>',
            $output
        );

        // Klucze: ["key"]
        $output = preg_replace(
            '/\[&quot;(.*?)&quot;\]/',
            '<span class="key"><span class="special">#</span>$1<span class="special">:</span></span>',
            $output
        );

        // Klucze prywatne: ["x":"Class":private]
        $output = preg_replace(
            '/\[&quot;(.*?)&quot;:&quot;(.*?)&quot;:(private|protected|public)\]/',
            '<span class="key"><span class="special">#</span>$1<span class="special">:</span></span><span class="modifier">$3</span><span class="item">($2)</span>',
            $output
        );

        $output = preg_replace(
            '/\[&quot;(.*?)&quot;:(private|protected|public)\]/',
            '<span class="key"><span class="special">#</span>$1<span class="special">:</span></span><span class="modifier">$2</span>',
            $output
        );

        // Stringi
        $output = preg_replace(
            '/&quot;(.*?)&quot;/',
            '<span class="string"><span class="special">&quot;</span>$1<span class="special">&quot;</span></span>',
            $output
        );

        // Typy
        $output = preg_replace(
            '/\bstring\((\d+)\)/',
            '<span class="type">string($1)</span>',
            $output
        );

        // NULL (po escape!)
        $output = preg_replace(
            '/\bNULL\b/',
            '<span class="text">null</span>',
            $output
        );

        // Nawiasy
        $output = preg_replace(
            '/([{}\[\]])/',
            '<span class="special">$1</span>',
            $output
        );

        // Integer
        $output = preg_replace_callback(
            '/\bint\((\-?\d+)\)/',
            function ($m) {
                return '<span class="type">int</span> <span class="number">' . $m[1] . '</span>';
            },
            $output
        );

        // Float
        $output = preg_replace_callback(
            '/\b(float|double)\((-?\d+(?:\.\d+)?)\)/',
            function ($m) {
                return '<span class="type">' . $m[1] . '</span> <span class="number">' . $m[2] . '</span>';
            },
            $output
        );

        // Boolean
        $output = preg_replace_callback(
            '/\bbool\((true|false)\)/',
            function ($m) {
                return '<span class="type">bool</span> <span class="text">' . $m[1] . '</span>';
            },
            $output
        );

        // Placeholdery
        $placeholders = [];

        // String
        $output = preg_replace_callback('/&quot;.*?&quot;/', function ($m) use (&$placeholders) {
            $key = '__STR_' . count($placeholders) . '__';
            $placeholders[$key] = $m[0];
            return $key;
        }, $output);

        // Resource
        $output = preg_replace_callback('/resource\((\d+)\) of type \((.*?)\)/', function ($m) use (&$placeholders) {
            $key = '__RES_' . count($placeholders) . '__';
            $placeholders[$key] = '<span class="text">resource(' . $m[1] . ') of type (' . $m[2] . ')</span>';
            return $key;
        }, $output);

        // Przywróć placeholdery
        $output = strtr($output, $placeholders);

        // Czyszczenie białych znaków / spacje

        $output = preg_replace_callback('/^( +)/m', function ($m) {
            $level = intdiv(strlen($m[1]), 2);
            return str_repeat(' ', $level * 2);
        }, $output);

        // ===== WIDOK =====

        echo <<<HTML
            <!DOCTYPE html>
            <html lang="en">
                <head>
                <meta charset="utf-8">
                <title>DBM Framework - Output Debugger</title>
                    <style>
                        .dbm-dg-root, .dbm-dg-root * { all: revert; box-sizing: border-box; }
                        .dbm-dg-root { margin: 0; padding: 2rem; font-family: monospace; font-size: 14px; background: #23241f; color: #f8f8f2; }
                        .dbm-dg-container { background: #272822; padding: 1rem; border-radius: 8px; }
                        .dbm-dg-header { margin: 0 0 5px 5px; float: right; color: #aaaaaa; font-size: 12px; }
                        .dbm-dg-output { font-family: "Liberation Mono", monospace; white-space: pre-wrap; overflow-wrap: break-word; word-break: break-all; }
                        .dbm-dg-output .text { color: #f8f8f2; }
                        .dbm-dg-output .item { color: #1299da; }
                        .dbm-dg-output .key { color: #aba8a8; }
                        .dbm-dg-output .string { color: #35d43a; }
                        .dbm-dg-output .type { color: #ffff00; }
                        .dbm-dg-output .special { color: #ff8403; }
                        .dbm-dg-output .number { color: #00ffff; }
                        .dbm-dg-output .modifier { color: #b229d9; }
                        .dbm-dg-output .other { color: #ff0000; }
                    </style>
                </head>
                <body class="dbm-dg-root">
                    <div class="dbm-dg-container">
                        <div class="dbm-dg-header">DBM Debug</div>
                        <pre class="dbm-dg-output">$output</pre>
                    </div>
                </body>
            </html>
            HTML;

        exit;
    }
}
