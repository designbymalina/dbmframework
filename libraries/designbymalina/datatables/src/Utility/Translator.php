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

namespace Dbm\DataTables\Utility;

/**
 * Example of use:
 * Translator::trans('records_info', $from, $to, $pagination['total'])
 */
class Translator
{
    public static string $locale = 'pl'; // Settings: translation locale

    /**
     * @var array<string, array<string, string>>
     */
    public static array $translations = [
        'pl' => [
            'action' => 'Akcja',
            'filter' => 'Filtruj',
            'search' => 'Szukaj',
            'filters' => 'Filtry',
            'reset' => 'Resetuj',
            'empty' => 'Brak',
            'active' => 'Aktywny',
            'inactive' => 'Nieaktywny',
            'new' => 'Nowy',
            'unknown' => 'Nieznany',
            'activate' => 'Aktywuj',
            'deactivate' => 'Dezaktywuj',
            'pagination' => 'Paginacja',
            'previous' => 'Poprzednia',
            'next' => 'Następna',
            'entries_page' => 'wpisów na stronę',
            'search_placeholder' => 'Szukaj...',
            'reset_filters' => 'Zresetuj filtry',
            'records_info' => 'Wyświetlono od %s do %s z %s rekordów',
            'loading_dots' => 'Ładowanie...',
            'no_results' => 'Brak wyników',
            'filter_category' => 'Kategorie',
            'filter_user' => 'Użytkownicy',
            'filter_status' => 'Status',
            'row_total' => 'Razem',
            'row_no_message' => 'Brak komunikatu',
        ],
        'en' => [
            'action' => 'Action',
            'filter' => 'Filter',
            'search' => 'Search',
            'filters' => 'Filters',
            'reset' => 'Reset',
            'empty' => 'Empty',
            'active' => 'Active',
            'inactive' => 'Inactive',
            'new' => 'New',
            'unknown' => 'Unknown',
            'activate' => 'Activate',
            'deactivate' => 'Deactivate',
            'pagination' => 'Pagination',
            'previous' => 'Previous',
            'next' => 'Next',
            'entries_page' => 'entries on the page',
            'search_placeholder' => 'Search...',
            'reset_filters' => 'Reset filters',
            'records_info' => 'Showing %s to %s of %s records',
            'loading_dots' => 'Loading...',
            'no_results' => 'No results',
            'filter_category' => 'Categories',
            'filter_user' => 'Users',
            'filter_status' => 'Status',
            'row_total' => 'Total',
            'row_no_message' => 'No message',
        ],
    ];

    /**
     * @param bool|float|int|string|null ...$params
     */
    public static function trans(string $key, ...$params): string
    {
        $msg = self::$translations[self::$locale][$key] ?? $key;

        return $params !== []
            ? vsprintf($msg, array_values($params))
            : $msg;
    }
}
