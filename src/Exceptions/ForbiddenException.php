<?php

declare(strict_types=1);

namespace Dbm\Exceptions;

final class ForbiddenException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('Forbidden', 403);
    }
}
