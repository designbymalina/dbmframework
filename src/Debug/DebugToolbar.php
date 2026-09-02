<?php

/**
 * Application: DbM Framework
 * A lightweight PHP framework for building web applications.
 *
 * @author Artur Malinowski
 * @copyright Design by Malina (All Rights Reserved)
 * @license MIT
 * @link https://www.dbm.org.pl
 */

declare(strict_types=1);

namespace Dbm\Debug;

use Dbm\Core\Config\AppConfig;
use Dbm\Core\Paths;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DebugToolbar
{
    private const PATH_COMPOSER = '/designbymalina/dbmframework/composer.json';

    private ?ServerRequestInterface $request = null;
    private ?ResponseInterface $response = null;

    private static ?string $cachedCss = null;
    private static ?string $cachedJs = null;

    /**
     * @var array{
     *     System?: array{Time: string, Memory: string},
     *     Request?: array{Status: int, Method: string, URI: string, Route: string},
     *     App?: array{Environment: string, Cache: string},
     *     SQL?: array{
     *         queries: array<int, array{sql: string, time: float}>,
     *         total_time: float
     *     }
     * }
     */
    private array $collectors = [];

    // ===== RENDER =====

    public function render(): string
    {
        $data = [
            'html' => $this->renderToolbar(),
            'css' => $this->getStyle(),
            'js' => $this->getScript(),
        ];

        return <<<HTML
                <div id="dbmToolbarRoot"></div>
                <script id="dbmDebugData" type="application/json">
                    {$this->json($data)}
                </script>
                <script>
                    (function () { const el = document.getElementById('dbmDebugData'); if (!el) return; let data; try { data = JSON.parse(el.textContent); } catch (e) { console.error('Debug JSON parse error', e); return; } if (data.css) { const style = document.createElement('style'); style.textContent = data.css; document.head.appendChild(style); } const root = document.getElementById('dbmToolbarRoot'); if (root && data.html) { root.innerHTML = data.html; } if (data.js) { const script = document.createElement('script'); script.textContent = data.js; document.body.appendChild(script); } })();
                </script>
            HTML;
    }

    // ===== SETTERS =====

    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }

    public function setResponse(ResponseInterface $response): void
    {
        $this->response = $response;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function addCollector(string $name, array $data): void
    {
        $this->collectors[$name] = $data;
    }

    // ===== COLLECTORS =====

    public function collectSQL(string $sql, float $timeMs): void
    {
        if (!isset($this->collectors['SQL'])) {
            $this->collectors['SQL'] = [
                'queries' => [],
                'total_time' => 0.0,
            ];
        }

        $this->collectors['SQL']['queries'][] = [
            'sql' => $sql,
            'time' => $timeMs,
        ];

        $this->collectors['SQL']['total_time'] += $timeMs;
    }

    private function collectSystem(): void
    {
        $start = $this->request?->getAttribute('start_time');
        if (!$start) {
            return;
        }

        $this->collectors['System'] = [
            'Time' => round((microtime(true) - $start) * 1000, 2) . ' ms',
            'Memory' => round(memory_get_peak_usage(false) / 1024 / 1024, 2) . ' MB',
        ];
    }

    private function collectRequest(): void
    {
        $route = $this->request?->getAttribute('route');

        $method = $this->request?->getMethod() ?? 'CLI';
        $routeName = 'N/A';

        if (is_object($route)) {
            $method = $route->httpMethod ?? $method;
            $routeName = $route->name ?? 'N/A';
        }

        $this->collectors['Request'] = [
            'Status' => $this->response?->getStatusCode() ?? 0,
            'Method' => $method,
            'URI' => $this->request ? (string) $this->request->getUri() : '/',
            'Route' => $routeName,
        ];
    }

    private function collectApp(): void
    {
        $this->collectors['App'] = [
            'Environment' => AppConfig::getEnv(),
            'Cache' => AppConfig::isCacheEnabled() ? 'enabled' : 'disabled',
        ];
    }

    // ===== RENDERERS =====

    private function json(mixed $data): string
    {
        $json = json_encode(
            $data,
            JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
            | JSON_HEX_TAG
            | JSON_HEX_AMP
            | JSON_HEX_APOS
            | JSON_HEX_QUOT
        );

        if ($json === false) {
            return '""';
        }

        return $json;
    }

    private function renderToolbar(): string
    {
        $this->collectSystem();
        $this->collectRequest();
        $this->collectApp();

        // ===== DATA =====

        $logoSvg = $this->renderLogo();
        $version = $this->getVersion();

        $system = $this->collectors['System'] ?? [];
        $time = $system['Time'] ?? '-';
        $memory = $system['Memory'] ?? '-';

        $request = $this->collectors['Request'] ?? [];
        $status = $request['Status'] ?? 0;
        $method = $request['Method'] ?? '-';
        $uri = $request['URI'] ?? '-';
        $route = $request['Route'] ?? '-';

        $app = $this->collectors['App'] ?? [];
        $env = $app['Environment'] ?? '-';
        $cache = $app['Cache'] ?? '-';

        $sql = $this->collectors['SQL'] ?? [];
        $sqlCount = count($sql['queries'] ?? []);
        $sqlTime = round($sql['total_time'] ?? 0, 2);

        // ===== CLASSES =====

        $statusClass = $this->resolveStatusClass($status);
        $timeClass = $this->resolveSignalClass($time, 'ms', 70, 200);
        $memoryClass = $this->resolveSignalClass($memory, 'MB', 5, 15);

        // ===== HTML =====

        return <<<HTML
            <!-- Debug Toolbar --><div id="dbmToolbar" class="dbm-toolbar"><div id="panel_app" class="dbm-toolbar-panel tb-w-25"><h4>Application Info</h4><div class="tb-info-grid"><div class="tb-info-col"><p>Environment: <strong>{$env}</strong></p><p>Cache: <strong>{$cache}</strong></p></div><div class="tb-info-col"><p>Method: <strong>{$method}</strong></p><p>Route: <strong>{$route}</strong></p></div></div><div class="tb-info-grid tb-mt-1"><div class="tb-info-col"><p class="tb-break-all">URI: <strong>{$uri}</strong></p></div></div></div>{$this->renderSqlPanel($sql, $sqlCount)}<div class="dbm-toolbar-main"><div class="dbm-toolbar-left"><div class="dbm-toolbar-item {$statusClass}" data-panel="panel_app"><span>{$status}</span></div><div class="dbm-toolbar-item {$timeClass}"><span>{$time}</span></div><div class="dbm-toolbar-item {$memoryClass}"><span>{$memory}</span></div>{$this->renderSqlItem($sqlCount, $sqlTime)}</div><div class="dbm-toolbar-right"><div class="dbm-toolbar-item">{$logoSvg}<span>{$version}</span></div><div id="dbmClose" class="dbm-toolbar-item dbm-close">&times;</div></div></div></div>
            HTML;
    }

    // ===== RENDER HELPERS =====

    /**
     * @param array<string, mixed> $sql
     */
    private function renderSqlPanel(array $sql, int $count): string
    {
        if ($count === 0) {
            return '';
        }

        $rows = '';

        foreach ($sql['queries'] as $k => $q) {
            if (!is_array($q)) {
                continue;
            }

            $query = isset($q['sql']) ? preg_replace('/\s+/', ' ', (string) $q['sql']) : '';
            $time = isset($q['time']) ? (float) $q['time'] : 0.0;

            $rows .= '<tr>';
            $rows .= '<td>' . round($time, 2) . '</td>';
            $rows .= '<td>' . htmlspecialchars($query) . '</td>';
            $rows .= '</tr>';

            if ($k !== array_key_last($sql['queries'])) {
                $rows .= PHP_EOL . '                ';
            }
        }

        return <<<HTML
            <div id="panel_sql" class="dbm-toolbar-panel"><h4>SQL Queries</h4><div class="dbm-table-wrapper"><table class="tb-sql-table"><thead><tr><th>Time (ms)</th><th>Query</th></tr></thead><tbody>{$rows}</tbody></table></div></div>
            HTML;
    }

    private function renderSqlItem(int $count, float $time): string
    {
        if ($count === 0) {
            return '';
        }

        $class = $this->resolveSqlClass($time);

        return <<<HTML
            <div class="dbm-toolbar-item {$class}" data-panel="panel_sql"><span>{$count} queries ({$time} ms)</span></div>
            HTML;
    }

    private function getStyle(): string
    {
        if (self::$cachedCss !== null) {
            return self::$cachedCss;
        }

        $file = file_get_contents(__DIR__ . '/../../resources/debug/toolbar.min.css');

        return self::$cachedCss = $file !== false ? trim($file) : '';
    }

    private function getScript(): string
    {
        if (self::$cachedJs !== null) {
            return self::$cachedJs;
        }

        $file = file_get_contents(__DIR__ . '/../../resources/debug/toolbar.min.js');

        return self::$cachedJs = $file !== false ? trim($file) : '';
    }

    // ===== HELPERS =====

    private function resolveStatusClass(int $status): string
    {
        return match (true) {
            $status >= 500 => 'dbm-status-error',
            $status >= 400 => 'dbm-status-warning',
            $status >= 300 => 'dbm-status-info',
            $status >= 200 => 'dbm-status-ok',
            default => 'dbm-status-unknown',
        };
    }

    private function resolveSignalClass(string $value, string $unit, float $warn, float $danger): string
    {
        $num = (float) str_replace(" {$unit}", '', (string) $value);

        return match (true) {
            $num >= $danger => 'dbm-signal-danger',
            $num >= $warn => 'dbm-signal-warning',
            default => '',
        };
    }

    private function resolveSqlClass(float $time): string
    {
        return match (true) {
            $time > 50 => 'dbm-signal-danger',
            $time > 20 => 'dbm-signal-warning',
            default => '',
        };
    }

    private function getVersion(): string
    {
        $base = Paths::basePath();

        // Composer-installed package
        $installed = $base . '/vendor/composer/installed.php';

        if (is_file($installed)) {
            $data = require $installed;

            $package = $data['versions']['designbymalina/dbmframework'] ?? null;

            if (is_array($package)) {
                $version = $package['pretty_version']
                    ?? $package['version']
                    ?? 'dev';

                return $this->normalizeVersion($version);
            }
        }

        // Manual libraries fallback
        $manualComposer = $base . '/libraries' . self::PATH_COMPOSER;

        if (is_file($manualComposer)) {
            $content = file_get_contents($manualComposer);

            if ($content !== false) {
                $json = json_decode($content, true);

                if (
                    is_array($json)
                    && isset($json['version'])
                    && is_string($json['version'])
                ) {
                    return $this->normalizeVersion($json['version']);
                }
            }
        }

        return 'dev';
    }

    private function normalizeVersion(string $version): string
    {
        $version = ltrim(trim($version), 'v');

        if (preg_match('/^(\d+)(?:\.(\d+))?/', $version, $m)) {
            $major = $m[1];
            $minor = $m[2] ?? '0';

            return $major . '.' . $minor;
        }

        return $version;
    }

    private function renderLogo(): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" id="DBM_Logo_Icon" width="24" height="24" preserveAspectRatio="xMidYMid meet" class="dbm-toolbar-img">
<style type="text/css">
	.st1{fill:#0865D7;} .st2{fill:#048AF7;} .st3{fill:#138BF4;} .st4{fill:#0A73E5;} .st5{fill:#E9F1FC;}
</style>
<g>
	<g>
		<path class="st1" d="M43.6,34.96c-0.38,2.93-3.03,3.59-4.98,4.91c-4.13,2.81-8.94,4.54-12.62,8.05c-1.33,0-2.66,0-3.99,0
			c-3.79-3.92-8.89-5.83-13.46-8.51c-3.51-2.06-4.99-4.48-3.89-8.4c2.11,1.17,4.46,4.1,5.75-0.78c4.53,6.46,9.56,6.56,15.4,0.31
			c0.17,1.37,0.75,2.36,2.29,2.45c-2.39,2-5.77,3.49-3.78,7.91c4.35-2.88,9.4-4.7,12.55-9.19C37.1,36.97,41.17,34.28,43.6,34.96z"/>
		<path class="st2" d="M26,0.05c3.73,3.76,8.66,5.63,13.11,8.24c3.51,2.06,5.56,4.46,4.49,8.69c-3.2,0.51-6.61-0.98-9.64,1.04
			c-2.31-0.38-4.62-0.95-6.93,0.02c-0.3-0.33-0.61-0.67-0.91-1c-0.05-1.82,0.1-3.69-0.22-5.46c-0.27-1.49,1.48-3.78-1.47-4.37
			c-1.4-0.18-2.31,0.3-2.42,1.83l0,0c-1.7,0.51-3.37,1.1-5.09,1.52c-2.8,0.68-5.4,0.63-6.87-2.47c3.68-3.14,8.71-4.26,11.96-8.03
			C23.34,0.05,24.67,0.05,26,0.05z"/>
		<path class="st3" d="M10.05,8.08c1.47,3.1,4.07,3.16,6.87,2.47c1.72-0.42,3.39-1.01,5.09-1.52c0,2.94,0,5.88,0,9.18
			c-5.33-1.56-9.34-0.7-11.74,4.15c-1.46-2.12-3.7-1.1-5.61-1.41C4.12,15.68,2.86,10.11,10.05,8.08z"/>
		<path class="st4" d="M4.66,20.95c1.91,0.3,4.15-0.71,5.61,1.41c0.05,2.63,0.09,5.26,0.14,7.89c-1.29,4.87-3.64,1.94-5.75,0.78
			C4.04,27.66,4.38,24.3,4.66,20.95z"/>
		<path class="st4" d="M43.6,34.96c-2.43-0.69-6.5,2.01-6.72-3.25c-0.04-0.27,0.03-0.51,0.2-0.73c2.68-1.3,2.7-2.63,0.05-4
			c-0.01-1.66-0.02-3.33-0.02-4.99c2.16,1.01,3.4,4.03,6.5,3.01C43.6,28.32,43.6,31.64,43.6,34.96z"/>
		<path class="st3" d="M43.61,25c-3.1,1.02-4.34-2-6.5-3.01c0.05-2.19-2.16-2.6-3.15-3.98c3.03-2.02,6.45-0.53,9.64-1.04
			C43.6,19.65,43.6,22.33,43.61,25z"/>
		<path class="st5" d="M10.41,30.24c-0.05-2.63-0.09-5.26-0.14-7.89c2.4-4.85,6.4-5.7,11.74-4.15c0-3.3,0-6.24,0-9.18c0,0,0,0,0,0
			c0.81-0.61,1.61-1.22,2.42-1.83c2.95,0.6,1.2,2.89,1.47,4.37c0.32,1.78,0.17,3.64,0.22,5.46c-0.1,4.51-0.2,9.01-0.3,13.52
			C19.98,36.8,14.95,36.7,10.41,30.24z M20.01,30.02c1.79-2.83,2.14-5.82-0.78-7.84c-0.98-0.68-3.93-1.17-4.76,1.04
			c-0.77,2.06-1.27,4.58,1.44,6.05C17.12,30.44,18.64,29.83,20.01,30.02z"/>
		<path class="st5" d="M33.96,18.02c0.99,1.37,3.19,1.78,3.15,3.98c0.01,1.66,0.02,3.33,0.02,4.99c-0.02,1.33-0.04,2.67-0.05,4
			c-0.17,0.21-0.24,0.46-0.2,0.73c-3.15,4.5-8.19,6.31-12.55,9.19c-1.99-4.42,1.39-5.91,3.78-7.91c0.29-0.15,0.57-0.4,0.87-0.44
			c4.28-0.57,4.26-3.63,4.33-6.94c0.07-3.23-1.23-4.49-4.38-4.36c-2.38,0.1-1.7-1.88-1.9-3.23C29.34,17.07,31.65,17.63,33.96,18.02z
			"/>
		<path class="st4" d="M27.03,18.03c0.2,1.34-0.47,3.33,1.9,3.23c3.15-0.13,4.45,1.13,4.38,4.36c-0.07,3.3-0.06,6.37-4.33,6.94
			c-0.31,0.04-0.58,0.29-0.87,0.44c-1.54-0.09-2.12-1.07-2.29-2.45c0.1-4.51,0.2-9.01,0.3-13.52C26.42,17.37,26.73,17.7,27.03,18.03
			z"/>
		<path class="st3" d="M24.43,7.2c-0.81,0.61-1.61,1.22-2.42,1.83C22.12,7.49,23.03,7.02,24.43,7.2z"/>
		<path class="st1" d="M37.08,30.98c0.02-1.33,0.04-2.67,0.05-4C39.77,28.35,39.76,29.69,37.08,30.98z"/>
		<path class="st4" d="M20.01,30.02c-1.37-0.2-2.9,0.41-4.09-0.75c0.88-2-1.57-6.07,2.22-5.9c3.46,0.15,1.23,4.03,1.87,6.16
			C20.05,29.68,20.01,29.86,20.01,30.02z"/>
		<path class="st1" d="M20.01,30.02c0-0.17,0.04-0.34,0-0.5c-0.64-2.13,1.59-6.01-1.87-6.16c-3.79-0.17-1.34,3.9-2.22,5.9
			c-2.72-1.47-2.21-3.99-1.45-6.05c0.83-2.21,3.78-1.72,4.76-1.04C22.15,24.21,21.8,27.19,20.01,30.02z"/>
	</g>
</g>
</svg>';
    }
}
