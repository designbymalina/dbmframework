<?php
/*
 * Application: DbM Framework v2.1
 * Author: Arthur Malinowsky (Design by Malina)
 * License: MIT
 * Web page: www.dbm.org.pl
 * Contact: biuro@dbm.org.pl
*/

declare(strict_types=1);

namespace Dbm\Interfaces;

interface DataFlatfileInterface
{
    public function dataFlatFile(string $type = 'content', string $sign = ''): string;
}
