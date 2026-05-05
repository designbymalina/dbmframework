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

namespace Dbm\Http\Contracts;

interface HttpClientInterface
{
    /**
     * @param array<string, mixed> $options
     */
    public function request(string $method, string $url, array $options = []): HttpResponseInterface;

    /**
     * @param array<string, mixed> $options
     */
    public function get(string $url, array $options = []): HttpResponseInterface;

    /**
     * @param array<string, mixed> $options
     */
    public function post(string $url, array $options = []): HttpResponseInterface;

    /**
     * @param array<string, mixed> $options
     */
    public function put(string $url, array $options = []): HttpResponseInterface;

    /**
     * @param array<string, mixed> $options
     */
    public function delete(string $url, array $options = []): HttpResponseInterface;
}
