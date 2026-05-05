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

namespace Dbm\Http\Response;

use Dbm\Http\Message\Response;
use Psr\Http\Message\ResponseInterface;

final class ApiResponder
{
    /**
     * @param array<string,mixed> $data
     */
    public static function success(
        string $message = '',
        array $data = [],
        int $code = 200
    ): ResponseInterface {
        return Response::json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    public static function error(
        string $message = 'An error occurred',
        int $code = 500
    ): ResponseInterface {
        return Response::json([
            'status' => 'error',
            'message' => $message,
        ], $code);
    }

    /**
     * @param array<string,mixed> $errors
     */
    public static function validationError(
        array $errors,
        string $message = 'Validation error',
        int $code = 422
    ): ResponseInterface {
        return Response::json([
            'status' => 'validation_error',
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }
}
