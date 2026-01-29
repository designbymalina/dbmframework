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
 * INFO: Tłumaczenia można rozbudować o cache tłumaczeń.
 */

declare(strict_types=1);

namespace Dbm\Localization;

use SimpleXMLElement;

final class TranslationLoader
{
    private array $paths = [];

    public function __construct(
        private LanguageService $languageService
    ) {}

    public function addPath(string $path): void
    {
        $this->paths[] = rtrim($path, '/');
    }

    public function load(): array
    {
        $language = $this->languageService->detectLanguage();

        if (!$language) {
            return [];
        }

        $messages = [];
        $language = strtolower($language);

        foreach ($this->paths as $path) {
            $messages = array_replace(
                $messages,
                $this->loadPhp($path, $language),
                $this->loadXlf($path, $language)
            );
        }

        return $messages;
    }

    /* ================= PHP ================= */

    private function loadPhp(string $path, string $language): array
    {
        $merged = [];
        $pattern = sprintf('%s/*.%s.php', $path, $language);

        foreach (glob($pattern) as $file) {
            $data = require $file;
            if (is_array($data)) {
                $merged = array_replace($merged, $data);
            }
        }

        return $merged;
    }

    /* ================= XLIFF ================= */

    private function loadXlf(string $path, string $language): array
    {
        $merged = [];
        $pattern = sprintf('%s/*.%s.xlf', $path, $language);

        foreach (glob($pattern) as $file) {
            $xml = simplexml_load_file($file);
            if (!$xml) {
                continue;
            }

            $namespace = (string) ($xml->getNamespaces(true)[''] ?? '');

            if (str_contains($namespace, 'xliff:document:1.2')) {
                $merged = array_replace($merged, $this->parseXliff12($xml));
            } elseif (str_contains($namespace, 'xliff:document:2')) {
                $merged = array_replace($merged, $this->parseXliff2($xml));
            }
        }

        return $merged;
    }

    /* ================= XLIFF 1.2 ================= */

    private function parseXliff12(SimpleXMLElement $xml): array
    {
        $messages = [];

        if (!isset($xml->file->body->{'trans-unit'})) {
            return $messages;
        }

        foreach ($xml->file->body->{'trans-unit'} as $unit) {
            $key = (string) $unit['id'];
            if ($key === '') {
                continue;
            }

            $value = (string) ($unit->target ?: $unit->source);
            $messages[$key] = $value;
        }

        return $messages;
    }

    /* ================= XLIFF 2.x ================= */

    private function parseXliff2(SimpleXMLElement $xml): array
    {
        $messages = [];

        foreach ($xml->file->unit as $unit) {
            $key = (string) $unit['id'];
            if ($key === '') {
                continue;
            }

            $segment = $unit->segment;
            if (!$segment) {
                continue;
            }

            $value = (string) ($segment->target ?: $segment->source);
            $messages[$key] = $value;
        }

        return $messages;
    }
}
