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

namespace Dbm\Http\Message;

use Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{
    private string $scheme;
    private string $host;
    private ?int $port;
    private string $path;
    private string $query;
    private string $fragment;
    private string $userInfo;

    public function __construct(
        string $path = '/',
        string $scheme = '',
        string $host = '',
        ?int $port = null,
        string $query = '',
        string $fragment = '',
        string $userInfo = ''
    ) {
        $this->scheme = strtolower($scheme);
        $this->host = strtolower($host);
        $this->port = $port;
        $this->path = $path ?: '/';
        $this->query = ltrim($query, '?');
        $this->fragment = ltrim($fragment, '#');
        $this->userInfo = $userInfo;
    }

    // ===== GETTERS =====

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getAuthority(): string
    {
        if ($this->host === '') {
            return '';
        }

        $authority = '';

        if ($this->userInfo !== '') {
            $authority .= $this->userInfo . '@';
        }

        $authority .= $this->host;

        if ($this->isNonStandardPort()) {
            $authority .= ':' . $this->port;
        }

        return $authority;
    }

    public function getUserInfo(): string
    {
        return $this->userInfo;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): ?int
    {
        return $this->isNonStandardPort() ? $this->port : null;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getFragment(): string
    {
        return $this->fragment;
    }

    // ===== IMMUTABILITY =====

    public function withScheme($scheme): static
    {
        $clone = clone $this;
        $clone->scheme = strtolower($scheme);
        return $clone;
    }

    public function withUserInfo($user, $password = null): static
    {
        $clone = clone $this;

        $clone->userInfo = $password !== null
            ? $user . ':' . $password
            : $user;

        return $clone;
    }

    public function withHost($host): static
    {
        $clone = clone $this;
        $clone->host = strtolower($host);
        return $clone;
    }

    public function withPort($port): static
    {
        $clone = clone $this;
        $clone->port = $port;
        return $clone;
    }

    public function withPath($path): static
    {
        $clone = clone $this;
        $clone->path = $path ?: '/';
        return $clone;
    }

    public function withQuery($query): static
    {
        $clone = clone $this;
        $clone->query = ltrim($query, '?');
        return $clone;
    }

    public function withFragment($fragment): static
    {
        $clone = clone $this;
        $clone->fragment = ltrim($fragment, '#');
        return $clone;
    }

    // ===== STRING =====

    public function __toString(): string
    {
        $uri = '';

        if ($this->scheme !== '') {
            $uri .= $this->scheme . ':';
        }

        $authority = $this->getAuthority();

        if ($authority !== '') {
            $uri .= '//' . $authority;
        }

        if ($this->path !== '') {
            if ($authority !== '' && $this->path[0] !== '/') {
                $uri .= '/' . $this->path;
            } else {
                $uri .= $this->path;
            }
        }

        if ($this->query !== '') {
            $uri .= '?' . $this->query;
        }

        if ($this->fragment !== '') {
            $uri .= '#' . $this->fragment;
        }

        return $uri;
    }

    // ===== INTERNAL =====

    private function isNonStandardPort(): bool
    {
        if ($this->port === null) {
            return false;
        }

        return match ($this->scheme) {
            'http' => $this->port !== 80,
            'https' => $this->port !== 443,
            default => true,
        };
    }
}
