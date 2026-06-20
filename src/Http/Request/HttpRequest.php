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
 * @INFO Klasa nie używana, raczej do usunięcia - brak adaptera = dobrze.
 *
 * Opcjonalne: Czyste PSR nie koniecznie dobre dla frameworka (brak rozszerzeń, itp.).
 * HttpKernel - public function handle(ServerRequestInterface $request): ResponseInterface
 * if (!$request instanceof ExtendedRequestInterface) {
 *     $request = new HttpRequest($request); // adapter
 * }
 *
 * Próba połączenia PSR z framework requestem (adapter).
 * Można uprościć HttpRequest, żeby nie miał +50 metod,
 * poza tym na ten czas dużo metod jest "pustych" do opracowania, itd.,
 * to jest overengineering, trudne w utrzymaniu, łamie kontrakty.
 *
 * Opcjonalnie można całkowicie porzucić PSR-7 wewnętrznie i adapter tylko na wejściu/wyjściu.
 */

declare(strict_types=1);

namespace Dbm\Http\Request;

use Dbm\Http\Psr\Message\ExtendedRequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\StreamInterface;
use SimpleXMLElement;

final class HttpRequest implements ExtendedRequestInterface
{
    public function __construct(private ServerRequestInterface $request) {}

    // ===== PSR-7 (delegacja) =====

    public function getProtocolVersion(): string
    {
        return $this->request->getProtocolVersion();
    }

    public function withProtocolVersion($version): static
    {
        return new self($this->request->withProtocolVersion($version));
    }

    public function getHeaders(): array
    {
        return $this->request->getHeaders();
    }

    public function hasHeader($name): bool
    {
        return $this->request->hasHeader($name);
    }

    public function getHeader($name): array
    {
        return $this->request->getHeader($name);
    }

    public function getHeaderLine($name): string
    {
        return $this->request->getHeaderLine($name);
    }

    public function withHeader($name, $value): static
    {
        return new self($this->request->withHeader($name, $value));
    }

    public function withAddedHeader($name, $value): static
    {
        return new self($this->request->withAddedHeader($name, $value));
    }

    public function withoutHeader($name): static
    {
        return new self($this->request->withoutHeader($name));
    }

    public function getBody(): StreamInterface
    {
        return $this->request->getBody();
    }

    public function withBody(StreamInterface $body): static
    {
        return new self($this->request->withBody($body));
    }

    public function getRequestTarget(): string
    {
        return $this->request->getRequestTarget();
    }

    public function withRequestTarget($requestTarget): static
    {
        return new self($this->request->withRequestTarget($requestTarget));
    }

    public function getMethod(): string
    {
        return $this->request->getMethod();
    }

    public function withMethod($method): static
    {
        return new self($this->request->withMethod($method));
    }

    public function getUri(): UriInterface
    {
        return $this->request->getUri();
    }

    public function withUri(UriInterface $uri, $preserveHost = false): static
    {
        return new self($this->request->withUri($uri, $preserveHost));
    }

    public function getServerParams(): array
    {
        return $this->request->getServerParams();
    }

    /** @return array<string, mixed> */
    public function getCookieParams(): array
    {
        return $this->request->getCookieParams();
    }

    public function withCookieParams(array $cookies): static
    {
        return new self($this->request->withCookieParams($cookies));
    }

    public function getQueryParams(): array
    {
        return $this->request->getQueryParams();
    }

    public function withQueryParams(array $query): static
    {
        return new self($this->request->withQueryParams($query));
    }

    public function getUploadedFiles(): array
    {
        return $this->request->getUploadedFiles();
    }

    public function withUploadedFiles(array $uploadedFiles): static
    {
        return new self($this->request->withUploadedFiles($uploadedFiles));
    }

    public function getParsedBody(): ?array // [?] mixed
    {
        return $this->request->getParsedBody();
    }

    public function withParsedBody($data): static
    {
        return new self($this->request->withParsedBody($data));
    }

    /** @return array<string, mixed> */
    public function getAttributes(): array
    {
        return $this->request->getAttributes();
    }

    public function getAttribute($name, $default = null): mixed
    {
        return $this->request->getAttribute($name, $default);
    }

    public function withAttribute($name, $value): static
    {
        return new self($this->request->withAttribute($name, $value));
    }

    public function withoutAttribute($name): static
    {
        return new self($this->request->withoutAttribute($name));
    }

    // ===== EXTENSIONS (minimal fallback) =====

    public function getJsonBody(): ?array
    {
        $data = $this->getParsedBody();
        return is_array($data) ? $data : null;
    }

    public function isJson(): bool
    {
        return str_contains($this->getHeaderLine('Content-Type'), 'application/json');
    }

    public function getXmlBody(): ?SimpleXMLElement
    {
        return null;
    }

    public function isFormUrlEncoded(): bool
    {
        return str_contains($this->getHeaderLine('Content-Type'), 'application/x-www-form-urlencoded');
    }

    public function getClientIp(): string
    {
        return '0.0.0.0';
    }

    public function getClientPort(): ?int
    {
        return null;
    }

    public function getPutParams(): ?array
    {
        return null;
    }

    public function getPreferredLanguage(array $availableLanguages): ?string
    {
        return null;
    }

    public function getUserAgent(): ?string
    {
        return null;
    }

    public function getReferer(): ?string
    {
        return null;
    }

    public function isSecure(): bool
    {
        return false;
    }

    public function getQuery(string $key, $default = null): mixed
    {
        return $this->getQueryParams()[$key] ?? $default;
    }

    public function getPost(string $key, $default = null): mixed
    {
        $data = $this->getParsedBody();
        return is_array($data) ? ($data[$key] ?? $default) : $default;
    }

    public function getAllQuery(): array
    {
        return $this->getQueryParams();
    }

    public function getAllPost(): array
    {
        return (array) $this->getParsedBody();
    }

    public function get(string $key, $default = null): mixed
    {
        return $this->getPost($key, $this->getQuery($key, $default));
    }

    public function setParams(array $params): void {}

    public function getParam(string $key): ?string
    {
        return null;
    }

    public function getParams(): array
    {
        return [];
    }

    public function getAllServerParams(): array
    {
        return [];
    }

    public function isMethod(string $method): bool
    {
        return strtoupper($this->getMethod()) === strtoupper($method);
    }

    public function isGet(): bool
    {
        return $this->isMethod('GET');
    }

    public function isPost(): bool
    {
        return $this->isMethod('POST');
    }

    public function isPut(): bool
    {
        return $this->isMethod('PUT');
    }

    public function isDelete(): bool
    {
        return $this->isMethod('DELETE');
    }

    public static function fromGlobals(): static
    {
        throw new \RuntimeException('Not supported in adapter');
    }

    public static function capture(): static
    {
        throw new \RuntimeException('Not supported in adapter');
    }

    // --- @TODO

    public function setQueryParams(array $queryParams): void
    {
        // Code
    }

    public function hasParsedBody(): bool
    {
        return false;
    }

    public function getContentType(): ?string
    {
        return null;
    }

    public function getAuthorizationHeader(): ?string
    {
        return null;
    }

    public function getUploadedFile(string $key): ?array
    {
        return null;
    }

    public function hasUploadedFile(string $key): bool
    {
        return false;
    }
}
