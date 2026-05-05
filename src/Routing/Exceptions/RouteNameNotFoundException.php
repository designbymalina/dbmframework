<?php

declare(strict_types=1);

namespace Dbm\Routing\Exceptions;

use RuntimeException;

final class RouteNameNotFoundException extends RuntimeException
{
    public function __construct(string $routeName)
    {
        parent::__construct(
            sprintf('Route not found: %s', $routeName),
            404
        );
    }
}
