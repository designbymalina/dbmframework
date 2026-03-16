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
 *
 * INFO! Wyszukiwarkę można rozbudować o Elastiocsearch,
 * enterprise ranking + weight + scoring, fuzzy search, multi-tenant, itp.
 */

declare(strict_types=1);

namespace Dbm\SearchEngine;

use Dbm\SearchEngine\Classes\AggregateResult;
use Dbm\SearchEngine\Interfaces\SearchProviderInterface;

final class SearchService
{
    /**
     * @param iterable<SearchProviderInterface> $providers
     */
    public function __construct(
        private iterable $providers = []
    ) {}

    public function search(SearchContext $context): AggregateResult
    {
        $sections = [];
        $total = 0;

        foreach ($this->providers as $provider) {
            if (!$this->providerSupportsFilters($provider, $context->filters)) {
                continue;
            }

            $name = $provider->getName();

            if ($context->sections !== []
                && !in_array($name, $context->sections, true)) {
                continue;
            }

            $section = $provider->search($context);

            if ($section->total <= 0) {
                continue;
            }

            $sections[$name] = $section;
            $total += $section->total;
        }

        return new AggregateResult($sections, $total);
    }

    /**
     * Jeśli użytkownik użył filtra, którego provider nie obsługuje
     * wówczas pomijamy provider
     *
     * @param array<string, mixed> $filters
     */
    private function providerSupportsFilters(
        SearchProviderInterface $provider,
        array $filters
    ): bool {
        $supported = $provider->getSupportedFilters();

        foreach ($filters as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if (!in_array($key, $supported, true)) {
                return false;
            }
        }

        return true;
    }
}
