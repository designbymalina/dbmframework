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

final class SearchSectionResult
{
    /**
     * @param array<string, mixed> $items
     */
    public function __construct(
        public string $name,
        public string $label,
        public array $items,
        public int $total,
        public int $page = 1,
        public int $limit = 20
    ) {}

    public function pages(): int
    {
        return (int) ceil($this->total / $this->limit);
    }

    public function hasPagination(): bool
    {
        return $this->pages() > 1;
    }
}
