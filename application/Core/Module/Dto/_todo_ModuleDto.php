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

namespace Dbm\Core\Module\DTO;

final class ModuleDto
{
    public function __construct(
        public string $slug,
        public string $name,
        public string $version,
        public string $description,
        public bool $core = false,
        public bool $installed = false,
        public bool $enabled = false,
        public bool $archiveExists = false,
        public ?string $status = null,
        public ?string $updateStatus = null,
    ) {}
}
