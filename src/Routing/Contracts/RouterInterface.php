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

namespace Dbm\Routing\Contracts;

use Dbm\Http\Message\Response;
use Dbm\Http\Psr\Message\ExtendedRequestInterface;

interface RouterInterface
{
    public function dispatch(ExtendedRequestInterface $request, string $uri): Response;
}
