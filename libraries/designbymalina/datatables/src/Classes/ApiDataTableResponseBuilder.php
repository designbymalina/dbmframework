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

use Dbm\DataTables\Interfaces\ConfigDataTableInterface;
use RuntimeException;

class ApiDataTableResponseBuilder
{
    public function __construct(
        private DataTableRenderer $datatableRender,
        private ConfigDataTableInterface $config
    ) {}

    /**
     * @param array<int, array<string, mixed>> $records
     * @param array<string, mixed> $sider
     * @return array<string, mixed>|string
     */
    public function getResponse(array $records, array $sider): array|string
    {
        return match ($this->config::getMode()) {
            'PHP' => $this->renderPhp($records, $sider),
            'AJAX' => $this->renderAjax($records, $sider),
            'API' => $this->renderApi($records, $sider),
            default => throw new RuntimeException("Unsupported mode"),
        };
    }

    /**
     * Renders the DataTable response in PHP mode.
     *
     * @param array<int,array<string,mixed>> $records Records to render
     * @param array<string,mixed> $sider Sider information
     * @return array<string,mixed> Rendered DataTable response
     */
    private function renderPhp(array $records, array $sider): array
    {
        return $this->datatableRender->renderDataTableJson(
            $records,
            $sider,
            $this->config
        );
    }

    /**
     * Renders the DataTable response in AJAX mode.
     *
     * @param array<int,array<string,mixed>> $records Records to render
     * @param array<string,mixed> $sider Sider information
     * @return array<string,mixed> Rendered DataTable response
     */
    private function renderAjax(array $records, array $sider): array
    {
        return $this->datatableRender->renderDataTableJson(
            $records,
            $sider,
            $this->config
        );
    }

    /**
     * Renders the DataTable response in API mode.
     *
     * @param array<int,array<string,mixed>> $records Records to render
     * @param array<string,mixed> $sider Sider information
     * @return array<string,mixed> Rendered DataTable response
     */
    private function renderApi(array $records, array $sider): array
    {
        return $this->datatableRender->renderDataTableJsonApi(
            records: $records,
            sider: $sider,
            config: $this->config
        );
    }
}
