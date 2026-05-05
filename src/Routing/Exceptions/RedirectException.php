<?php

declare(strict_types=1);

namespace Dbm\Routing\Exceptions;

use RuntimeException;

class RedirectException extends RuntimeException
{
    public function __construct(
        private readonly string $location,
        private readonly int $statusCode = 301
    ) {
        parent::__construct("Redirect to {$location}", $statusCode);
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
