<?php

declare(strict_types=1);

namespace Dbm\Routing\Exceptions;

use RuntimeException;

final class MethodNotAllowedException extends RuntimeException
{
    /**
     * @param array<string|int, string> $allowedMethods
     */
    public function __construct(string $method, string $uri, array $allowedMethods)
    {
        parent::__construct(
            sprintf(
                'Method %s not allowed for %s. Allowed: %s',
                $method,
                $uri,
                implode(', ', $allowedMethods)
            ),
            405
        );

        header('Allow: ' . implode(', ', $allowedMethods));
    }
}
