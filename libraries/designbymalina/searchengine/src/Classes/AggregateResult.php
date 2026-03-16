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

namespace Dbm\SearchEngine\Classes;

final class AggregateResult
{
    /**
     * @param array<string, \Dbm\SearchEngine\SearchSectionResult> $sections
     */
    public function __construct(
        public array $sections,
        public int $total
    ) {}
}
