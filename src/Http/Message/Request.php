<?php

/**
 * Application: DbM Framework
 * A lightweight PHP framework for building web applications.
 *
 * @author Artur Malinowski
 * @copyright Design by Malina (All Rights Reserved)
 * @license MIT
 * @link https://www.dbm.org.pl
 *
 * Concrete implementation of ExtendedRequestInterface.
 * Provides extended PSR-7 compliant HTTP request handling.
 *
 * ────────────────────────────────────────────────
 * REQUEST USAGE GUIDE (DbM Framework)
 * ────────────────────────────────────────────────
 *
 * getParsedBody(): ?array
 * ───────────────────────────
 * Standard PSR-7 method. Safely parses and returns request body
 * for HTML forms, JSON requests, and multipart uploads.
 *
 * Supports:
 *  - application/x-www-form-urlencoded (HTML form)
 *  - application/json (API requests)
 *  - multipart/form-data (file upload)
 *
 * Example:
 *   $data = $request->getParsedBody();
 *   $email = $data['email'] ?? null;
 *
 * Used in:
 *   Controllers, Services, API endpoints
 *
 * ────────────────────────────────────────────────
 * getAllPost(): array
 * ───────────────────────────
 * Shortcut for accessing sanitized $_POST.
 * Does not parse raw body or JSON.
 *
 * Example:
 *   $data = $request->getAllPost();
 *
 * Used in:
 *   Internal legacy modules, form helpers, BaseController
 *
 * ────────────────────────────────────────────────
 * getAllQuery(): array
 * ───────────────────────────
 * Returns $_GET parameters (query string).
 *
 * Example:
 *   $page = (int) ($request->getAllQuery()['page'] ?? 1);
 *
 * Used in:
 *   Pagination, filters, search forms
 *
 * ────────────────────────────────────────────────
 * getBody(): StreamInterface
 * ───────────────────────────
 * Returns raw request body as stream.
 *
 * Example:
 *   $json = $request->getBody()->__toString();
 *
 * Used in:
 *   Debugging, logging, file uploads
 *
 * ────────────────────────────────────────────────
 * getHeaders() / hasHeader() / getHeaderLine()
 * ───────────────────────────
 * Access to HTTP headers.
 *
 * Example:
 *   $contentType = $request->getHeaderLine('Content-Type');
 *
 * ────────────────────────────────────────────────
 * getServerParams(): array
 * ───────────────────────────
 * Returns $_SERVER variables (scheme, host, method, etc.)
 *
 * ────────────────────────────────────────────────
 * getUploadedFiles(): array
 * ───────────────────────────
 * Returns $_FILES information for multipart requests.
 *
 * ────────────────────────────────────────────────
 * Summary:
 *  - Controllers:       → getParsedBody()
 *  - Form processors:   → getParsedBody() / getAllPost()
 *  - Search pages:      → getAllQuery()
 *  - Low-level tools:   → getBody(), getHeaders()
 *
 * @INFO Można dopisać RequestDecorator / AppRequest
 * Poziom wyżej niż większość frameworków (bardzo czyste rozwiązanie).
 */

declare(strict_types=1);

namespace Dbm\Http\Message;

use Dbm\Http\Psr\Message\ExtendedRequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Exception;
use JsonException;
use SimpleXMLElement;

/**
 * Class Request
 *
 * Implements the ExtendedRequestInterface, providing convenient
 * access to HTTP request data (headers, query params, POST, JSON,
 * files, and client/server information). Fully compatible with PSR-7.
 */
final class Request extends Message implements ExtendedRequestInterface
{
    private string $method;

    /** @var array<string, mixed> */
    private array $params = [];

    /** @var array<string, mixed> */
    private array $queryParams = [];

    /** @var array<string, mixed> */
    private array $postParams = [];

    /** @var array<string, array<string, mixed>> */
    private array $filesParams = [];

    /** @var array<string, mixed> */
    private array $cookies = [];

    /** @var array<string, mixed> */
    private array $attributes = [];

    /** @var array<string, mixed> */
    private array $serverParams = [];

    /** @var UriInterface */
    private UriInterface $uri;

    /**
     * Constructs a new Request from PHP globals.
     *
     * Automatically reads headers, body, query, post, and files.
     */
    public function __construct()
    {
        $this->headers = function_exists('getallheaders') ? getallheaders() : [];

        try {
            $this->body = new Stream(file_get_contents('php://input') ?: '');
        } catch (Exception $e) {
            $this->body = new Stream('');
        }

        $this->queryParams = $_GET;
        $this->postParams = $_POST;
        $this->filesParams = $_FILES;
        $this->serverParams = $_SERVER;
        $this->method = $this->detectMethod();

        // Build URI from server params
        $scheme = (!empty($this->serverParams['HTTPS'])
            && $this->serverParams['HTTPS'] !== 'off')
                ? 'https'
                : 'http';

        $host = $this->serverParams['HTTP_HOST'] ?? 'localhost';

        $port = isset($this->serverParams['SERVER_PORT'])
            ? (int) $this->serverParams['SERVER_PORT']
            : null;

        $requestUri = $this->serverParams['REQUEST_URI'] ?? '/';
        $path = parse_url($requestUri, PHP_URL_PATH) ?? '/';
        $query = parse_url($requestUri, PHP_URL_QUERY) ?? '';

        $this->uri = new Uri($path, $scheme, $host, $port, $query);
    }

    /**
     * Returns the request target (path component).
     */
    public function getRequestTarget(): string
    {
        return $this->uri->getPath();
    }

    /**
     * Returns a new instance with the provided request target.
     */
    public function withRequestTarget(string $requestTarget): static
    {
        $new = clone $this;
        $new->uri = $new->uri->withPath($requestTarget);
        return $new;
    }

    /**
     * Returns HTTP method (GET, POST, etc.).
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Returns a new instance with the provided HTTP method.
     */
    public function withMethod(string $method): static
    {
        $new = clone $this;
        $new->method = strtoupper($method);
        return $new;
    }

    /**
     * Returns the request URI.
     */
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * Returns a new instance with the provided URI.
     */
    public function withUri(UriInterface $uri, bool $preserveHost = false): static
    {
        $new = clone $this;
        $new->uri = $uri;

        if (!$preserveHost) {
            $new->headers['Host'] = [$uri->getHost()];
        }

        return $new;
    }

    // ===== Methods that extend functionality =====

    /** @return array<string, mixed> */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    /** @param array<string, mixed> $queryParams */
    public function setQueryParams(array $queryParams): void
    {
        $this->queryParams = $queryParams;
    }

    /** @param array<string, mixed> $query */
    public function withQueryParams(array $query): static
    {
        $new = clone $this;
        $new->queryParams = $query;
        return $new;
    }

    /** @return array<string, mixed>|null */
    public function getParsedBody(): ?array
    {
        $contentType = strtolower($this->getContentType() ?? '');
        $bodyContent = trim($this->body->__toString());

        if ($bodyContent === '' && !empty($this->postParams)) {
            return $this->postParams;
        }

        if (str_contains($contentType, 'application/json')) {
            try {
                $decoded = json_decode($bodyContent, true, 512, JSON_THROW_ON_ERROR);
                return is_array($decoded) ? $decoded : null;
            } catch (JsonException) {
                return null; // Invalid JSON
            }
        }

        if (str_contains($contentType, 'application/x-www-form-urlencoded')) {
            if ($bodyContent !== '') {
                parse_str($bodyContent, $parsed);
                return $parsed;
            }
            return $this->postParams ?: null;
        }

        if (str_contains($contentType, 'multipart/form-data')) {
            return !empty($this->postParams) ? $this->postParams : ($_POST ?: null);
        }

        return !empty($this->postParams) ? $this->postParams : null;
    }

    /** @inheritdoc */
    public function hasParsedBody(): bool
    {
        $parsedBody = $this->getParsedBody();
        return !empty($parsedBody);
    }

    /** @return array<string, mixed>|null */
    public function getJsonBody(): ?array
    {
        return $this->isJson() ? $this->getParsedBody() : null;
    }

    /** @inheritdoc */
    public function getXmlBody(): ?SimpleXMLElement
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($this->body->__toString(), SimpleXMLElement::class, LIBXML_NOENT | LIBXML_NOCDATA);
        libxml_clear_errors();
        return $xml ?: null;
    }

    /** @inheritdoc */
    public function getContentType(): ?string
    {
        $header = $this->headers['Content-Type'] ?? null;
        return is_array($header) ? $header[0] : $header;
    }

    /** @inheritdoc */
    public function getAuthorizationHeader(): ?string
    {
        return $this->headers['Authorization'][0] ?? null;
    }

    /** @inheritdoc */
    public function isJson(): bool
    {
        return str_contains($this->getContentType() ?? '', 'application/json')  ;
    }

    /** @inheritdoc */
    public function isFormUrlEncoded(): bool
    {
        return str_contains($this->getContentType() ?? '', 'application/x-www-form-urlencoded')  ;
    }

    /** @inheritdoc */
    public function getClientIp(): string
    {
        $ipKeys = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR',
        ];

        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {

                $ipList = explode(',', (string) $_SERVER[$key]);
                $ip = trim($ipList[0]);

                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }

    /** @inheritdoc */
    public function getClientPort(): ?int
    {
        return isset($_SERVER['REMOTE_PORT']) ? (int) $_SERVER['REMOTE_PORT'] : null;
    }

    /** @return array<string, mixed> */
    public function getServerParams(): array
    {
        return [
            'PHP_SELF' => $_SERVER['PHP_SELF'] ?? null,
            'SERVER_NAME' => $_SERVER['SERVER_NAME'] ?? null,
            'DOCUMENT_ROOT' => $_SERVER['DOCUMENT_ROOT'] ?? null,
            'HTTPS' => $_SERVER['HTTPS'] ?? null,
            'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'] ?? null,
            'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'] ?? null,
            'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? null,
            'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? null,
            'HTTP_REFERER' => $_SERVER['HTTP_REFERER'] ?? null,
            'HTTP_USER_AGENT' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? null,
            'HTTP_AUTHORIZATION' => $_SERVER['HTTP_AUTHORIZATION'] ?? null,
            'HTTP_X_FORWARDED_FOR' => $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null,
            'HTTP_CLIENT_IP' => $_SERVER['HTTP_CLIENT_IP'] ?? null,
        ];
    }

    /** @return array<string, mixed>|null */
    public function getPutParams(): ?array
    {
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            parse_str($this->body->__toString(), $putParams);
            return $putParams;
        }
        return null;
    }

    /** @param string[] $availableLanguages */
    public function getPreferredLanguage(array $availableLanguages): ?string
    {
        $acceptLanguage = $this->headers['Accept-Language'][0] ?? '';

        if (!$acceptLanguage) {
            return null;
        }

        foreach (explode(',', $acceptLanguage) as $lang) {
            $lang = trim(strtolower(explode(';', $lang)[0]));
            foreach ($availableLanguages as $available) {
                if (str_starts_with($lang, strtolower($available))) {
                    return $available;
                }
            }
        }

        return null;
    }

    /** @inheritdoc */
    public function getUserAgent(): ?string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? null;
    }

    /** @inheritdoc */
    public function getReferer(): ?string
    {
        return $_SERVER['HTTP_REFERER'] ?? null;
    }

    /** @inheritdoc */
    public function isSecure(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
    }

    // ===== Additional methods (PSR, PSR 7) =====

    /** @return array<string, mixed> */
    public function getCookieParams(): array
    {
        return $this->cookies ?: $_COOKIE;
    }

    /** @param array<string, mixed> $cookies */
    public function withCookieParams(array $cookies): ServerRequestInterface
    {
        $new = clone $this;
        $new->cookies = $cookies;
        return $new;
    }

    /** @param array<string, mixed>|object|null $data */
    public function withParsedBody($data): ServerRequestInterface
    {
        $new = clone $this;
        $new->postParams = is_array($data) ? $data : [];
        return $new;
    }

    /** @return array<string, mixed> */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /** @inheritdoc */
    public function getAttribute($name, $default = null)
    {
        return $this->attributes[$name] ?? $default;
    }

    /** @inheritdoc */
    public function withAttribute(string $name, $value): ServerRequestInterface
    {
        $new = clone $this;
        $new->attributes[$name] = $value;
        return $new;
    }

    /** @inheritdoc */
    public function withoutAttribute(string $name): ServerRequestInterface
    {
        $new = clone $this;
        unset($new->attributes[$name]);
        return $new;
    }

    /** @return array<string, array<string, mixed>> */
    public function getUploadedFiles(): array
    {
        return $this->filesParams;
    }

    /** @return array<string, mixed>|null */
    public function getUploadedFile(string $key): ?array
    {
        return $this->filesParams[$key] ?? null;
    }

    /** @inheritdoc */
    public function hasUploadedFile(string $key): bool
    {
        return isset($this->filesParams[$key])
            && ($this->filesParams[$key]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK;
    }

    /** @param array<string, array<string, mixed>> $uploadedFiles */
    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface
    {
        $new = clone $this;
        $new->filesParams = $uploadedFiles;
        return $new;
    }

    // ===== Framework methods =====

    /** @inheritdoc */
    public function getQuery(string $key, $default = null): mixed
    {
        return $this->queryParams[$key] ?? $default;
    }

    /** @inheritdoc */
    public function getPost(string $key, $default = null): mixed
    {
        $data = $this->getParsedBody();

        return $data[$key] ?? $default;
    }

    /** @return array<string, mixed> */
    public function getAllQuery(): array
    {
        return $this->queryParams;
    }

    /** @return array<string, mixed> */
    public function getAllPost(): array
    {
        return $this->postParams;
    }

    /** @inheritdoc */
    public function get(string $key, $default = null): mixed
    {
        $postValue = $this->getPost($key);
        return $postValue !== null ? $postValue : $this->getQuery($key, $default);
    }

    /** @param array<string, mixed> $params */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    /** @inheritdoc */
    public function getParam(string $key): ?string
    {
        return $this->params[$key] ?? null;
    }

    /** @return array<string, mixed> */
    public function getParams(): array
    {
        return $this->params;
    }

    /** @return array<string, mixed> */
    public function getAllServerParams(): array
    {
        return $this->serverParams;
    }

    /** @inheritdoc */
    public function isMethod(string $method): bool
    {
        return $this->method === strtoupper($method);
    }

    /** @inheritdoc */
    public function isGet(): bool
    {
        return $this->isMethod('GET');
    }

    /** @inheritdoc */
    public function isPost(): bool
    {
        return $this->isMethod('POST');
    }

    /** @inheritdoc */
    public function isPut(): bool
    {
        return $this->isMethod('PUT');
    }

    /** @inheritdoc */
    public function isDelete(): bool
    {
        return $this->isMethod('DELETE');
    }

    /** @inheritdoc */
    public static function fromGlobals(): static
    {
        return new self();
    }

    /** @inheritdoc */
    public static function capture(): static
    {
        return self::fromGlobals();
    }

    // ===== Extension for Tests - Factory methods =====

    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $post
     * @param array<string, mixed> $server
     * @param array<string, mixed> $cookies
     * @param array<string, array<string, mixed>> $files
    */
    public static function create(
        string $method,
        string $uri,
        array $query = [],
        array $post = [],
        array $server = [],
        array $cookies = [],
        array $files = [],
        ?string $body = null
    ): static {
        $instance = new self();

        $instance->method = strtoupper($method);
        $instance->queryParams = $query;
        $instance->postParams = $post;
        $instance->cookies = $cookies;
        $instance->filesParams = $files;

        $server = array_merge([
            'REQUEST_METHOD' => $method,
            'REQUEST_URI' => $uri,
        ], $server);

        $instance->headers = [];

        $instance->body = new Stream($body ?? '');

        $instance->uri = new Uri(
            parse_url($uri, PHP_URL_PATH) ?? '/',
            'http',
            'localhost',
            null,
            parse_url($uri, PHP_URL_QUERY) ?? ''
        );

        return $instance;
    }

    // ===== Private =====

    /**
     * Detects the effective HTTP method.
     *
     * Supports method overriding via:
     * - X-HTTP-Method-Override header (API standard)
     * - _method POST parameter (HTML form spoofing)
     *
     * Keeps original method for non-POST requests.
     */
    private function detectMethod(): string
    {
        // Base method from server
        $serverParams = $this->getServerParams();
        $method = strtoupper($serverParams['REQUEST_METHOD'] ?? 'GET');

        if ($method !== 'POST') {
            return $method;
        }

        // Header override (REST API standard)
        $headerOverride = $this->headers['X-HTTP-Method-Override'][0] ?? null;

        if ($headerOverride) {
            $override = strtoupper($headerOverride);
            if (in_array($override, ['PUT', 'PATCH', 'DELETE'], true)) {
                return $override;
            }
        }

        // Form override (_method)
        $spoofed = $this->postParams['_method'] ?? null;

        if ($spoofed) {
            $override = strtoupper($spoofed);
            if (in_array($override, ['PUT', 'PATCH', 'DELETE'], true)) {
                return $override;
            }
        }

        return $method;
    }
}
