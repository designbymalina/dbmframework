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

namespace Dbm\SearchEngine\Interfaces;

use Dbm\SearchEngine\SearchContext;
use Dbm\SearchEngine\SearchSectionResult;

interface SearchProviderInterface
{
    /**
     * Unique provider name (used as key in aggregated results).
     */
    public function getName(): string;

    /**
     * Provider label.
     */
    public function getLabel(): string;

    /**
     * Check if provider supports given filters.
     * @return list<string>
     */
    public function getSupportedFilters(): array;

    /**
     * Execute provider-specific search.
     */
    public function search(SearchContext $context): SearchSectionResult;
}
