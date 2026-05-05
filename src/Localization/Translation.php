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

namespace Dbm\Localization;

use Dbm\Localization\Contracts\TranslationInterface;

final class Translation implements TranslationInterface
{
    /** @var array<string, string> */
    private array $messages;

    /**
     * @param array<string, string> $messages
     */
    public function __construct(array $messages = [])
    {
        $this->messages = $messages;
    }

    /**
     * @param array<string, mixed>|null $data
     */
    public function trans(string $key, ?array $data = null): string
    {
        $value = $this->messages[$key] ?? $key;

        if (!$data) {
            return $value;
        }

        /**
         * Named placeholders: {key}
         */
        foreach ($data as $k => $v) {
            $value = str_replace(
                '{' . $k . '}',
                (string) ($v ?? ''),
                $value
            );
        }

        /**
         * Classic sprintf placeholders: %s, %1$s, etc.
         */
        if (str_contains($value, '%')) {
            try {
                $value = vsprintf($value, array_values($data));
            } catch (\Throwable) {
                // silent fail – translation must not crash app
            }
        }

        return $value;
    }
}
