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
use Dbm\Routing\Contracts\UrlGeneratorInterface;
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

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator
    ) {}

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

        $logoPath = $this->urlGenerator->asset('images/logo.png');
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
            <!-- Debug Toolbar --><div id="dbmToolbar" class="dbm-toolbar"><div id="panel_app" class="dbm-toolbar-panel tb-w-25"><h4>Application Info</h4><div class="tb-info-grid"><div class="tb-info-col"><p>Environment: <strong>{$env}</strong></p><p>Cache: <strong>{$cache}</strong></p></div><div class="tb-info-col"><p>Method: <strong>{$method}</strong></p><p>Route: <strong>{$route}</strong></p></div></div><div class="tb-info-grid tb-mt-1"><div class="tb-info-col"><p class="tb-break-all">URI: <strong>{$uri}</strong></p></div></div></div>{$this->renderSqlPanel($sql, $sqlCount)}<div class="dbm-toolbar-main"><div class="dbm-toolbar-left"><div class="dbm-toolbar-item {$statusClass}" data-panel="panel_app"><span>{$status}</span></div><div class="dbm-toolbar-item {$timeClass}"><span>{$time}</span></div><div class="dbm-toolbar-item {$memoryClass}"><span>{$memory}</span></div>{$this->renderSqlItem($sqlCount, $sqlTime)}</div><div class="dbm-toolbar-right"><div class="dbm-toolbar-item"><img src="{$logoPath}" width="16" class="dbm-toolbar-img" alt="Logo"><span>{$version}</span></div><div id="dbmClose" class="dbm-toolbar-item dbm-close">&times;</div></div></div></div>
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

        foreach ([
            $base . '/libraries' . self::PATH_COMPOSER,
            $base . '/vendor' . self::PATH_COMPOSER,
        ] as $path) {
            if (is_file($path)) {
                $content = file_get_contents($path);
                if ($content === false) {
                    continue;
                }

                $json = json_decode($content, true);

                if (is_array($json) && isset($json['version']) && is_string($json['version'])) {
                    return $json['version'];
                }
            }
        }

        return 'dev';
    }
}
