<?php

/**
 * Library: DbM DataTables PHP
 * Efficient backend CRUD system for easy database record management and table handling.
 *
 * This software is proprietary and licensed.
 * Use of this software is subject to the terms of the DbM Platform License.
 *
 * @author Artur Malinowski
 * @copyright Design by Malina
 * @license Proprietary
 *
 * @see /LICENSE_DBM_PLATFORM.txt
 * @link https://www.dbm.org.pl
 */

declare(strict_types=1);

namespace Dbm\DataTables\Classes;

/**
 * Helper dla operacji na tekście w wynikach wyszukiwania.
 */
class RenderHelper
{
    // Stałe
    private const LIMIT = 250; // default 250 characters
    private const ENDING = '...'; // default ellipsis

    /**
     * Przycinanie treści do określonej długości, z zachowaniem tagów <mark>.
     *
     * - Usuwa wszystkie inne znaczniki HTML, zostawiając jedynie <mark>
     * - Dekoduje encje HTML (&amp; -> &)
     * - Przycięcie odbywa się do pełnego słowa (nie urywa w środku wyrazu)
     * - Jeśli tekst przekracza limit, dodaje końcówkę (np. "...")
     *
     * @param string $content Treść do skrócenia
     * @param int $limit Maksymalna długość (domyślnie 250)
     * @param string $ending Końcówka dodawana po skróceniu (domyślnie "…")
     * @return string Skrócony i oczyszczony tekst
     *
     * Przykład:
     * $short = SearchHelper::safeTruncate($content, 160);
     */
    public static function safeTruncate(string $content, int $limit = self::LIMIT, string $ending = self::ENDING): string
    {
        $content = htmlspecialchars_decode($content, ENT_QUOTES);
        $content = trim(strip_tags($content, '<mark>'));

        // Przycięcie do pełnego słowa
        if (mb_strlen($content) > $limit) {
            $content = trim(preg_replace('~\s+\S+$~u', '', mb_substr($content, 0, $limit)));
            $content .= $ending;
        }

        return $content;
    }
}
