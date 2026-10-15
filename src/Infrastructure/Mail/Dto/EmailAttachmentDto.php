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

namespace Dbm\Infrastructure\Mail\Dto;

final readonly class EmailAttachmentDto
{
    public function __construct(
        public string $path,
        public ?string $name = null,
        public ?string $mimeType = null,
    ) {}
}
