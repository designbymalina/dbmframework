<?php

declare(strict_types=1);

namespace Dbm\Routing\Exceptions;

use RuntimeException;

final class RouteNotFoundException extends RuntimeException
{
    public function __construct(string $method, string $uri)
    {
        parent::__construct(
            sprintf('Route not found for %s %s', $method, $uri),
            404
        );
    }
}
