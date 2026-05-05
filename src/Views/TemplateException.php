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

namespace Dbm\Views;

use Exception;

class TemplateException extends Exception
{
    public string $templateFile;
    public int $templateLine;

    public function __construct(
        string $message,
        string $file,
        int $line = 0,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->templateFile = $file;
        $this->templateLine = max(0, $line);
    }
}
