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

namespace Dbm\Kernel\Contracts;

use Dbm\Http\Psr\Message\ExtendedRequestInterface;
use Psr\Http\Message\ResponseInterface;

interface KernelInterface
{
    /** @inheritDoc */
    public function handle(ExtendedRequestInterface $request): ResponseInterface;
}
