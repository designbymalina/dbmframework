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

namespace Dbm\Exceptions;

use Dbm\Core\Config\AppConfig;
use Dbm\Database\Exceptions\QueryException;
use Dbm\Http\Message\Response;
use Dbm\Http\Message\StringStream;
use Dbm\Routing\Contracts\UrlGeneratorInterface;
use Psr\Http\Message\ResponseInterface;
use Exception;
use Throwable;

class ExceptionHandler
{
    private const REDIRECT_UNAUTHORIZED = '/login';

    /** @var string[] */
    private array $appDirectories = [
        DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR,
        DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR,
    ];

    /** @var string[] */
    private array $skipDirectories = [
        DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR,
        DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR,
        // DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR,
    ];

    /** @var string[] */
    private array $frameworkSkipFiles = [
        'index.php',
        'Router.php',
        'DependencyContainer.php',
        'HttpKernel.php',
        'ExceptionHandler.php',
    ];

    /** @var array<string, string> */
    private array $namespaceMap = [
        'Mod\\' => '/modules/',
        'App\\' => '/src/',
        'Dbm\\' => '/libraries/designbymalina/dbmframework/src/',
    ];

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {}

    public function handle(Throwable $exception, string $env): ResponseInterface
    {
        if ($exception instanceof UnauthorizedWebException) {
            return $this->handleUnauthorized();
        }

        if ($exception instanceof QueryException) {
            return $this->renderQueryException($exception, $env);
        }

        $status = $this->resolveStatusCode($exception);

        if ($env === AppConfig::ENV_DEVELOPMENT) {
            return $this->renderDevelopmentError($exception, $status);
        }

        return $this->renderProductionError($status);
    }

    // ====== Private Methods =====

    private function renderDevelopmentError(
        Throwable $e,
        int $status,
        ?string $title = null,
        ?string $content = null
    ): ResponseInterface {
        return $this->respondByType(
            $status,
            fn($status) => $this->renderDevHtmlError(
                $e,
                $status,
                $title,
                $content
            ),
            fn($status) => $this->renderApiError($status)
        );
    }

    private function renderProductionError(int $status): ResponseInterface
    {
        return $this->respondByType(
            $status,
            fn($status) => $this->renderProdHtmlError($status),
            fn($status) => $this->renderApiError($status)
        );
    }

    private function renderProdHtmlError(int $status): ResponseInterface
    {
        try {
            $base = $this->urlGenerator->base();
        } catch (Throwable) {
            $base = '';
        }

        if ($status === 404) {
            return new Response(
                302,
                ['Location' => "{$base}/errors/error-404.html"],
                new StringStream('')
            );
        }

        return new Response(
            302,
            ['Location' => "{$base}/errors/error.html?code={$status}"],
            new StringStream('')
        );
    }

    private function renderDevHtmlError(
        Throwable $e,
        int $status,
        ?string $messageTitle = null,
        ?string $messageContent = null
    ): ResponseInterface {
        $messageContent ??= $e->getMessage();
        $messageTitle ??= 'Message:';

        if ($this->hasHtml($messageContent)) { // @INFO For render SQL
            $message = "<p><strong>{$messageTitle}</strong></p>{$messageContent}";
        } else {
            $messageContent = nl2br(htmlspecialchars($messageContent, ENT_QUOTES, 'UTF-8'));
            $message = "<p><strong>{$messageTitle}</strong> {$messageContent}</p>";
        }

        $trace = htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8');

        $exception = $e->getPrevious() ?? $e;
        [$bestFile, $bestLine] = $this->resolveBestFrame($exception);

        $codePreview = $this->getCodePreview($bestFile, $bestLine);

        $html = <<<HTML
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <title>DbM Framework - Error {$status}</title>
                <link href="data:," rel="icon">
                <style>
                    .dbm-ex-root, .dbm-ex-root * { all: revert; box-sizing: border-box; }
                    .dbm-ex-root { font-family: monospace; font-size: 16px; background: #f4f4f4; color: #333; padding: 2rem; }
                    .dbm-ex-container { max-width: 992px; margin: auto; background: #fff; border-radius: 6px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); overflow: hidden; }
                    .dbm-ex-header { display: flex; justify-content: space-between; padding: 1rem; background: #e11d48; color: #fff; font-weight: normal; }
                    .dbm-ex-page { font-size: 1.4rem; font-weight: bold; }
                    .dbm-ex-nav { font-size: 1rem; }
                    .dbm-ex-title { font-size: 0.8rem; color: #e2e8f0; }
                    .dbm-ex-description { text-align: right; }
                    .dbm-ex-description a { font-size: 0.7rem; color: #e2e8f0; text-decoration: none; text-transform: uppercase; }
                    .dbm-ex-main { padding: 2rem; font-weight: normal; }
                    .dbm-ex-main p { margin-top: 0.5rem; margin-bottom: 0.5rem; }
                    .dbm-ex-main fieldset { border: 1px solid #d1d5db; }
                    .dbm-ex-main fieldset legend { margin-left: 10px; padding: 3px 12px; background: #334155; color: #fff; border: 1px solid #111827; border-radius: 5px; }
                    .dbm-ex-message { font-family: "Lucida Console", monospace; padding: 1rem; border: 1px solid #111827; background: #334155; color: #fff; border-radius: 5px; word-break: break-all; }
                    .dbm-ex-pre { margin-top: 0.5rem; padding: 1rem; border-radius: 5px; overflow-x: auto; font-family: monospace; background: #1e293b; color: #e5e7eb; white-space: pre-wrap; word-break: break-all; }
                    .dbm-ex-info { padding: 1rem; background: #f3f4f6; border: 1px solid #d1d5db; color: #000; margin-top: 1rem; border-radius: 5px; word-break: break-all; }
                    .dbm-ex-details { margin-top: 1rem; border-radius: 5px; border: 1px solid #d1d5db; background: #f9fafb; color: #000; }
                    .dbm-ex-details summary { cursor: pointer; padding: 0.75rem 1rem; font-weight: bold; font-size: 14px; background: #f3f4f6; }
                    .dbm-ex-details pre { max-height: 400px; overflow: auto; font-size: 12px; padding: 1rem; margin: 0; font-family: monospace; }
                    .dbm-ex-details .ex-highlight { background: #fee2e2; font-weight: bold; }
                    .dbm-ex-sql-block { margin-top: 1rem; }
                    .dbm-ex-label { font-weight: bold; font-size: 14px; color: #e5e7eb; }
                    .dbm-ex-root .ex-blue { color: #1299da; }
                    .dbm-ex-root .ex-red { color: #ff0000; }
                    .dbm-ex-root .ex-yellow { color: #ffff00; }
                    .dbm-ex-root .sql-keyword { color: #1299da; font-weight: bold; }
                    .dbm-ex-root .sql-string { color: #35d43a; }
                    .dbm-ex-root .sql-number { color: #b229d9; }
                    .dbm-ex-root .sql-param { color: #00ffff; }
                    .dbm-ex-root .sql-special { color: #ff8403; }
                    .dbm-ex-root .sql-text { color: #aba8a8; }
                </style>
            </head>
            <body class="dbm-ex-root">
                <div class="dbm-ex-container">
                    <div class="dbm-ex-header">
                        <div class="dbm-ex-page">Error: {$status}</div>
                        <div class="dbm-ex-nav">
                            <div class="dbm-ex-title">DbM Framework Exception</div>
                            <div class="dbm-ex-description"><a href="https://dbm.org.pl/">Go To Project</a></div>
                        </div>
                    </div>
                    <div class="dbm-ex-main">
                        <div class="dbm-ex-message">
                            {$message}
                        </div>
                        <fieldset class="dbm-ex-info">
                            <legend>Primary location</legend>
                            <p><strong>File:</strong> {$bestFile}</p>
                            <p><strong>Line:</strong> {$bestLine}</p>
                        </fieldset>
                        <details class="dbm-ex-details">
                            <summary>Code</summary>
                            <pre>{$codePreview}</pre>
                        </details>
                        <details class="dbm-ex-details">
                            <summary>Trace</summary>
                            <pre>{$trace}</pre>
                        </details>
                    </div>
                </div>
            </body>
            </html>
            HTML;

        return new Response(
            $status,
            ['Content-Type' => 'text/html; charset=utf-8'],
            new StringStream($html)
        );
    }

    private function renderQueryException(QueryException $e, string $env): ResponseInterface
    {
        if ($env === AppConfig::ENV_DEVELOPMENT) {
            $sql = $this->formatSql($e->sql ?? '');
            $params = $this->formatParams($e->params ?? []);
            $message = $this->formatMessage($e->getMessage());

            $content = '
                <div class="dbm-ex-sql-block">
                    <div class="dbm-ex-label">Message:</div>
                    <pre class="dbm-ex-pre">' . $message . '</pre>
                    <div class="dbm-ex-label">SQL:</div>
                    <pre class="dbm-ex-pre">' . $sql . '</pre>
                    <div class="dbm-ex-label">Params:</div>
                    <pre class="dbm-ex-pre">' . $params . '</pre>
                </div>';

            return $this->renderDevelopmentError(
                new Exception('', 500, $e),
                500,
                'Database Query Error',
                $content
            );
        }

        return $this->renderProductionError(500);
    }

    private function isApiRequest(): bool
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $path = $this->urlGenerator->stripBasePath(parse_url($uri, PHP_URL_PATH) ?? '');

        return str_starts_with($path, '/api');
    }

    private function respondByType(int $status, callable $webCallback, callable $apiCallback): ResponseInterface
    {
        return $this->isApiRequest() ? $apiCallback($status) : $webCallback($status);
    }

    private function renderApiError(int $status): ResponseInterface
    {
        $data = [
            'status' => $status,
            'error' => match ($status) {
                404 => 'Not Found',
                403 => 'Forbidden',
                default => 'Server Error',
            },
        ];

        return new Response(
            $status,
            ['Content-Type' => 'application/json'],
            new StringStream(json_encode($data))
        );
    }

    private function resolveStatusCode(Throwable $e): int
    {
        $code = $e->getCode();

        if ($code >= 100 && $code <= 599) {
            return $code;
        }

        if ($e instanceof NotFoundException) {
            return 404;
        }

        return 500;
    }

    private function formatMessage(string $message): string
    {
        $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

        // SQLSTATE
        $message = preg_replace(
            '/SQLSTATE(\[[A-Z0-9]+\])?/',
            '<span class="sql-keyword">$0</span>',
            $message
        );

        // Numery błędów
        $message = preg_replace(
            '/(?<=\s|:)\d{3,5}(?=\s|$)/',
            '<span class="sql-number">$0</span>',
            $message
        );

        // Nazwy tabel, kolumn itp. w apostrofach
        $message = preg_replace(
            '/&#039;(.*?)&#039;/',
            '<span class="sql-string">&#039;$1&#039;</span>',
            $message
        );

        return $message;
    }

    private function formatSql(string $sql): string
    {
        $sql = $this->normalizeSql($sql);
        $sql = htmlspecialchars($sql, ENT_QUOTES, 'UTF-8');

        $placeholders = [];

        // Stringi
        $sql = preg_replace_callback("/'(.*?)'/", function ($m) use (&$placeholders) {
            $key = '__STR_' . count($placeholders) . '__';
            $placeholders[$key] = '<span class="sql-string">\'' . $m[1] . '\'</span>';
            return $key;
        }, $sql);

        // Parametry (:id, :name)
        $sql = preg_replace('/:\w+/', '<span class="sql-param">$0</span>', $sql);

        // Keywords
        $keywords = [
            'SELECT','FROM','WHERE','AND','OR','INSERT','INTO','VALUES',
            'UPDATE','SET','DELETE','JOIN','LEFT','RIGHT','INNER','OUTER',
            'ON','GROUP','BY','ORDER','LIMIT','OFFSET',
        ];

        $sql = preg_replace_callback('/\b(' . implode('|', $keywords) . ')\b/i', function ($m) {
            return '<span class="sql-keyword">' . strtoupper($m[1]) . '</span>';
        }, $sql);

        // Liczby (bez stringów)
        $sql = preg_replace('/\b\d+\b/', '<span class="sql-number">$0</span>', $sql);

        // Przywróć stringi
        $sql = strtr($sql, $placeholders);

        return $sql;
    }

    /**
     * @param array<string, mixed> $params
     */
    private function formatParams(array $params): string
    {
        $json = json_encode($params, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $json = htmlspecialchars($json, ENT_QUOTES, 'UTF-8');

        return preg_replace('/"(.+?)":/', '<span class="sql-param">"$1"</span>:', $json);
    }

    private function normalizeSql(string $sql): string
    {
        $sql = preg_replace('/[ \t]+/', ' ', $sql);
        $sql = preg_replace('/^\s+/m', '', $sql);
        $sql = preg_replace('/\n\s*\n/', "\n", $sql);

        return trim($sql);
    }

    private function hasHtml(string $text): bool
    {
        return preg_match('/<\/?(a|b|i|p|div|span|strong|em|ul|ol|li|br|hr|table|tr|td)[\s>]/i', $text) === 1;
    }

    private function handleUnauthorized(): ResponseInterface
    {
        return new Response(
            302,
            ['Location' => $this->urlGenerator->base() . self::REDIRECT_UNAUTHORIZED],
            new StringStream('')
        );
    }

    /**
     * @return array{string, int}
     */
    private function resolveBestFrame(Throwable $e): array
    {
        // Spróbuj wyciągnąć klasę z błędem
        $class = $this->extractClassFromError($e);

        if ($class) {
            $file = $this->classToFile($class);

            if ($file) {
                $parts = explode('\\', $class);
                $constant = end($parts);

                $line = $this->findConstantLine($file, $constant);

                return [$file, $line];
            }
        }

        // fallback
        $bestFrame = null;

        foreach ($e->getTrace() as $frame) {
            if (!isset($frame['file'])) {
                continue;
            }

            $file = $frame['file'];
            $line = $frame['line'] ?? 0;

            // pierwszy app frame (natychmiast)
            if ($this->isAppFile($file)) {
                return [$file, $line];
            }

            if ($this->isSkipped($file)) {
                continue;
            }

            // fallback
            if ($bestFrame === null) {
                $bestFrame = [$file, $line];
            }
        }

        return $bestFrame ?? [$e->getFile(), $e->getLine()];
    }

    private function isAppFile(string $file): bool
    {
        foreach ($this->appDirectories as $dir) {
            if (str_contains($file, $dir)) {
                return true;
            }
        }
        return false;
    }

    private function isSkipped(string $file): bool
    {
        // Katalogi
        foreach ($this->skipDirectories as $dir) {
            if (str_contains($file, $dir)) {
                return true;
            }
        }

        // Konkretne pliki (basename!)
        $basename = basename($file);

        foreach ($this->frameworkSkipFiles as $skipFile) {
            if ($basename === $skipFile) {
                return true;
            }
        }

        return false;
    }

    private function extractClassFromError(Throwable $e): ?string
    {
        if (preg_match('/Undefined constant "([^"]+)"/', $e->getMessage(), $m)) {
            return $m[1];
        }

        if (preg_match('/Call to undefined method ([^:]+)::/', $e->getMessage(), $m)) {
            return $m[1];
        }

        return null;
    }

    private function classToFile(string $class): ?string
    {
        $parts = explode('\\', $class);
        $last = array_pop($parts);

        $namespace = implode('\\', $parts);

        $base = realpath(dirname(__DIR__, 4));

        foreach ($this->namespaceMap as $prefix => $dir) {
            if (str_starts_with($namespace, $prefix)) {

                $relative = substr($namespace, strlen($prefix));
                $path = $base . $dir . str_replace('\\', DIRECTORY_SEPARATOR, $relative);

                // jeśli to dokładna klasa
                $file = $path . '.php';
                if (is_file($file)) {
                    return $file;
                }

                // jeśli to namespace → szukaj plików
                if (is_dir($path)) {
                    foreach (glob($path . '/*.php') as $candidate) {
                        // możesz filtrować po zawartości
                        if (strpos(file_get_contents($candidate), $last) !== false) {
                            return $candidate;
                        }
                    }
                }
            }
        }

        return null;
    }

    private function findConstantLine(string $file, string $constant): int
    {
        $lines = file($file);

        foreach ($lines as $i => $line) {
            if (str_contains($line, $constant)) {
                return $i + 1;
            }
        }

        return 0;
    }

    /**
     * @param array<string> $lines
     * @return array<string>
     */
    private function normalizeIndentation(array $lines): array
    {
        $minIndent = null;

        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }

            preg_match('/^(\\s*)/', $line, $matches);
            $indent = strlen($matches[1]);

            if ($minIndent === null || $indent < $minIndent) {
                $minIndent = $indent;
            }
        }

        if ($minIndent === null || $minIndent === 0) {
            return $lines;
        }

        return array_map(function ($line) use ($minIndent) {
            return preg_replace('/^\\s{0,' . $minIndent . '}/', '', $line);
        }, $lines);
    }

    private function getCodePreview(string $file, int $line, int $padding = 3): string
    {
        if (!is_file($file)) {
            return '';
        }

        $lines = file($file);
        $total = count($lines);

        if ($line > $total) {
            return 'Line mismatch: showing compiled template instead';
        }

        $start = max($line - $padding - 1, 0);
        $end = min($line + $padding - 1, count($lines));

        $snippet = array_slice($lines, $start, $end - $start);
        $normalized = $this->normalizeIndentation($snippet);

        $output = '';

        foreach ($normalized as $index => $codeLine) {
            $currentLine = $start + $index + 1;
            $code = rtrim(htmlspecialchars($codeLine));

            if ($currentLine === $line) {
                $output .= '<div class="ex-highlight">' . $currentLine . ': ' . $code . '</div>';
            } else {
                $output .= '<div>' . $currentLine . ': ' . $code . '</div>';
            }
        }

        return $output;
    }
}
