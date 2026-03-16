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

use Dbm\DataTables\Utility\Translator;

/**
 * Provides built-in rendering logic for special cell types (action buttons, status, images, etc.).
 *
 * This class is stateless and contains only static helper methods.
 * It is used internally by DataTableRenderer, but can be extended or overridden if needed.
 */
class CellRenderer
{
    private const ARRAY_SPECIAL_CELLS = [
        'cell_action' => 'renderActionCell',
        'cell_status' => 'renderStatusCell',
        'cell_change_status' => 'renderChangeStatusCell',
        'cell_image' => 'renderImageCell',
    ];

    public static function specialCellSupports(string $tag): bool
    {
        return isset(self::ARRAY_SPECIAL_CELLS[$tag]);
    }

    /**
     * Renders a special cell (action buttons, status, images, etc.) based on the provided options.
     *
     * @param array<string,mixed> $col Column data
     * @param array<string,mixed> $record Record data
     *
     * @return string HTML code for the special cell
     */
    public static function renderSpecialCell(array $col, array $record): string
    {
        $options = $col['tag_options'] ?? [];
        $field = $col['field'] ?? null;

        return match ($col['tag']) {
            'cell_action' => self::renderActionCell($record, $options, $field),
            'cell_image' => self::renderImageCell($record, $options, $field),
            'cell_status' => self::renderStatusCell($record, $field),
            'cell_change_status' => self::renderChangeStatusCell($record, $field),
            default => '',
        };
    }

    /**
     * Renders an action cell (dropdown menu) based on the provided options.
     *
     * @param array<string,mixed> $record
     * @param array<string,mixed> $options
     * @param string|null $field
     *
     * @return string HTML code for the action cell
     */
    private static function renderActionCell(
        array $record,
        array $options = [],
        ?string $field = null
    ): string {
        $actions = $options['actions'] ?? [];

        $html = PHP_EOL . '<div class="dropdown">' . PHP_EOL;
        $html .= '    <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">' . PHP_EOL;
        $html .= '        <i class="bi bi-three-dots-vertical"></i>' . PHP_EOL;
        $html .= '    </button>' . PHP_EOL;
        $html .= '    <ul class="dropdown-menu">' . PHP_EOL;

        foreach ($actions as $action) {
            // zamień {id} i inne pola w url/attrs na wartości z $record
            $url = $action['url'] ?? '#';
            $url = preg_replace_callback('/\{(\w+)\}/', fn($m) => $record[$m[1]] ?? '', $url);

            $label = htmlspecialchars($action['label'] ?? '');
            $icon  = htmlspecialchars($action['icon'] ?? '');
            $class = htmlspecialchars($action['class'] ?? '');

            if ($action['type'] === 'link') {
                $html .= sprintf(
                    '        <li><a href="%s" class="dropdown-item %s"><i class="%s me-2"></i>%s</a></li>' . PHP_EOL,
                    htmlspecialchars($url),
                    $class,
                    $icon,
                    $label
                );
            } elseif ($action['type'] === 'button') {
                $attrs = '';
                foreach (($action['attrs'] ?? []) as $k => $v) {
                    $v = preg_replace_callback('/\{(\w+)\}/', fn($m) => $record[$m[1]] ?? '', $v);
                    $attrs .= sprintf(' %s="%s"', $k, htmlspecialchars($v));
                }
                $html .= sprintf(
                    '        <li><button type="button" class="dropdown-item %s"%s><i class="%s me-2"></i>%s</button></li>' . PHP_EOL,
                    $class,
                    $attrs,
                    $icon,
                    $label
                );
            }
        }

        $html .= '    </ul>' . PHP_EOL;
        $html .= '</div>' . PHP_EOL;

        return $html . '    ';
    }

    /**
     * Renders a status cell (badge) based on the provided record and field.
     *
     * @param array<string,mixed> $record Record data
     * @param string|null $field Field name to render the status cell for (default: 'status')
     *
     * @return string HTML code for the status cell
     */
    private static function renderStatusCell(array $record, ?string $field = null): string
    {
        $field ??= 'status';
        $status = strtolower((string) ($record[$field] ?? ''));

        $mapClass = [
            'active'   => 'success',
            'inactive' => 'danger',
            'new'      => 'warning',
        ];
        $mapLabel = [
            'active'   => 'active',
            'inactive' => 'inactive',
            'new'      => 'new',
            'unknown'  => 'unknown',
        ];

        $cls = $mapClass[$status] ?? 'secondary';
        $labelKey = $mapLabel[$status] ?? 'unknown';
        $extraClass = $status === 'new' ? ' text-dark' : '';

        return sprintf(
            '<span class="badge bg-%s%s">%s</span>',
            $cls,
            $extraClass,
            Translator::trans($labelKey)
        );
    }

    /**
     * Renders a cell that allows changing the status of a record (active/inactive/new).
     *
     * @param array<string,mixed> $record Record data
     * @param string|null $field Field name to render the status cell for (default: 'status')
     *
     * @return string HTML code for the status cell
     */
    private static function renderChangeStatusCell(array $record, ?string $field = null): string
    {
        $field ??= 'status';
        $status = $record[$field] ?? 'default';
        $id = $record['id'] ?? null;
        $arrayStatus = ['A' => "active", 'I' => "inactive", 'N' => 'new'];

        if (in_array($status, $arrayStatus) && !empty($id)) {
            $links = [
                'active' => [
                    'url' => "?id=$id&status={$arrayStatus['I']}",
                    'title' => Translator::trans('deactivate'),
                    'class' => 'bg-success',
                ],
                'inactive' => [
                    'url' => "?id=$id&status={$arrayStatus['A']}",
                    'title' => Translator::trans('activate'),
                    'class' => 'bg-danger',
                ],
                'default' => [
                    'url' => "?id=$id&status={$arrayStatus['A']}",
                    'title' => Translator::trans('activate'),
                    'class' => 'bg-warning text-dark',
                ],
            ];

            $state = $links[$status] ?? $links['default'];

            return '<a href="' . $state['url'] . '" title="' . $state['title'] . '" data-bs-toggle="tooltip" data-bs-placement="top"><span class="badge ' . $state['class'] . '">' . Translator::trans($status) . '</span></a>';
        }

        return '<span class="badge bg-secondary">' . Translator::trans('unknown') . '</span>';
    }

    /**
     * Renders an image cell based on the provided record and field.
     *
     * @param array<string,mixed> $record Record data
     * @param array<string,mixed> $options Options for rendering the image cell
     * @param string|null $field Field name to render the image cell for (default: 'image')
     *
     * @return string HTML code for the image cell
     */
    private static function renderImageCell(array $record, array $options = [], ?string $field = null): string
    {
        $field ??= 'image';
        $src = htmlspecialchars($record[$field] ?? '');

        $noimage = $options['noimage'] ?? 'placeholder.png';
        $srcDir = rtrim($options['src_dir'] ?? '', '/') . '/';
        $fullSrc = $src ? $srcDir . $src : $srcDir . $noimage;

        $altField = $options['alt_field'] ?? null;
        $alt = $src
            ? ($altField && isset($record[$altField]) ? htmlspecialchars($record[$altField]) : '')
            : Translator::trans('empty');

        $width = (int) ($options['width'] ?? 20);

        $attTitle = "<img src='{$fullSrc}' class='img-fluid' alt='{$alt}'>";

        $html  = PHP_EOL . '<p class="m-0" data-bs-toggle="tooltip" data-bs-html="true" title="' . $attTitle . '">' . PHP_EOL;
        $html .= '    <img src="' . $fullSrc . '" class="img-fluid" alt="' . $alt . '" style="height:' . $width . 'px;">' . PHP_EOL;
        $html .= '</p>' . PHP_EOL;

        return $html . '    ';
    }
}
