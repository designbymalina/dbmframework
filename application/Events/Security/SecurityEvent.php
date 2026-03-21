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

namespace Dbm\Events\Security;

final class SecurityEvent
{
    public const UNAUTHORIZED = 'unauthorized';

    public function __construct(
        public readonly string $type,
        public readonly ?int $userId,
        public readonly string $ip,
        public readonly string $uri,
        public readonly int $timestamp
    ) {}
}
