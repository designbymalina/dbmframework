<?php

/**
 * Library: DbM Search Engine
 * Advanced full-text & keyword search module for efficient data retrieval with Elasticsearch support.
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

namespace Dbm\SearchEngine;

use DateTime;

class SearchForm
{
    /**
     * @var array<string, list<string>>
     */
    private array $errors = [];

    /**
     * @return array<string, mixed>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    /**
     * Query
     */
    public function sanitizeQuery(string $query): string
    {
        $query = trim($query);

        if (mb_strlen($query) < 3) {
            $this->addError('q', 'Wyszukiwana fraza musi mieć co najmniej 3 znaki.');
            return '';
        }

        if (mb_strlen($query) > 200) {
            $this->addError('q', 'Wyszukiwana fraza jest za długa (max 200 znaków).');
        }

        return mb_substr($query, 0, 200);
    }

    /**
     * Dostępne filtry
     * @param array<string, string|null> $queries
     * @return array<string, string>
     */
    public function extractFilters(array $queries): array
    {
        $filters = [];

        foreach ($queries as $key => $value) {
            if (in_array($key, ['q', 'page', 'section', 'providers'], true)) {
                continue;
            }

            if ($value === '' || $value === null) {
                continue;
            }

            $filters[$key] = $value;
        }

        // date validation
        if (isset($filters['date_from'])
            && !DateTime::createFromFormat('Y-m-d', $filters['date_from'])) {
            unset($filters['date_from']);
        }

        if (isset($filters['date_to'])
            && !DateTime::createFromFormat('Y-m-d', $filters['date_to'])) {
            unset($filters['date_to']);
        }

        if (!empty($filters['date_from'])
            && !empty($filters['date_to'])
            && $filters['date_from'] > $filters['date_to']) {
            unset($filters['date_to']);
        }

        if (isset($filters['status'])
            && !in_array($filters['status'], ['active', 'inactive', 'new'], true)) {
            unset($filters['status']);
        }

        return $filters;
    }
}
