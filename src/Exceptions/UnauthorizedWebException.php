<?php

declare(strict_types=1);

namespace Dbm\Exceptions;

final class UnauthorizedWebException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('Unauthorized access', 401);
    }
}
