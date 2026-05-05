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

namespace Dbm\Http;

use Dbm\Http\Contracts\HttpResponseInterface;

final class HttpResponse implements HttpResponseInterface
{
    /**
     * @param array<string, array<int, string>> $headers
     */
    public function __construct(
        private int $status,
        private string $body,
        private array $headers = []
    ) {}

    public function statusCode(): int
    {
        return $this->status;
    }

    public function body(): string
    {
        return $this->body;
    }

    /**
     * @return array<string, mixed>
     */
    public function json(): array
    {
        return json_decode($this->body, true) ?? [];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function headers(): array
    {
        return $this->headers;
    }
}
