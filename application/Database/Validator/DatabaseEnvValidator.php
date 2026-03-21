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

namespace Dbm\Database\Validator;

final class DatabaseEnvValidator
{
    public static function validate(bool $requireDatabase = true): void
    {
        $required = ['DB_HOST', 'DB_USER'];

        if ($requireDatabase) {
            $required[] = 'DB_NAME';
        }

        $missing = [];

        foreach ($required as $key) {
            if (!getenv($key)) {
                $missing[] = $key;
            }
        }

        if ($missing !== []) {
            throw new \RuntimeException(
                'Missing database environment variables: ' . implode(', ', $missing)
            );
        }
    }

    public static function requireDatabase(): void
    {
        if (!getenv('DB_NAME')) {
            throw new \RuntimeException('DB_NAME is required for this operation.');
        }
    }
}
