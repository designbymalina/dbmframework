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

final class SearchContext
{
    /**
     * @param array<string, mixed> $filters
     * @param array<string, mixed> $sections
     */
    public function __construct(
        public readonly string $query,
        public readonly int $page = 1,
        public readonly int $limit = 20,
        public readonly array $filters = [],
        public readonly array $sections = []
    ) {}

    public function offset(): int
    {
        return ($this->page - 1) * $this->limit;
    }
}
