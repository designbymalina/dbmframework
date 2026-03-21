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

namespace Dbm\SearchEngine\Dto;

class SearchResultDto
{
    /**
     * @param array<string, int> $routeParams
     */
    public function __construct(
        public string $provider,
        public int|string $id,
        public string $title,
        public string $description,
        public ?string $route = null,
        public array $routeParams = [],
        public ?string $createdAt = null
    ) {}
}
