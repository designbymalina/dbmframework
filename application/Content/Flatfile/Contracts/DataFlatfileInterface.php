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

namespace Dbm\Content\Flatfile\Contracts;

use Dbm\Content\Flatfile\Dto\FileOperationDto;

interface DataFlatfileInterface
{
    public function dataFlatFile(string $type, string $slug, string|int $space = '', ?string $path = null): FileOperationDto;

    public function buildFileName(string $slug, string $ext = 'txt'): string;
}
