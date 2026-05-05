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

namespace Dbm\Http;

use Dbm\Http\Contracts\HttpClientInterface;
use Dbm\Http\Contracts\HttpResponseInterface;
use Psr\Log\LoggerInterface;

final class CurlHttpClient implements HttpClientInterface
{
    public function __construct(
        private ?LoggerInterface $logger = null
    ) {}

    public function request(string $method, string $url, array $options = []): HttpResponseInterface
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $options['timeout'] ?? 30); // @INFO or getenv('HTTP_TIMEOUT')

        $headers = [];

        if (!empty($options['headers'])) {
            foreach ($options['headers'] as $k => $v) {
                $headers[] = "$k: $v";
            }
        }

        if (!empty($options['json'])) {
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($options['json'], JSON_THROW_ON_ERROR));
        }

        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        // --- Log request ---
        $this->logger?->info('HTTP request', [
            'method' => $method,
            'url' => $url,
        ]);

        $body = curl_exec($ch);

        if ($body === false) {
            $error = curl_error($ch);

            $this->logger?->error('HTTP request failed', [
                'method' => $method,
                'url' => $url,
                'error' => $error,
            ]);

            curl_close($ch);

            return new HttpResponse(0, '', []);
        }

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        // --- Log response ---
        $this->logger?->info('HTTP response', [
            'method' => $method,
            'url' => $url,
            'status' => $status,
        ]);

        // @INFO Można rozszerzać i parsować CURLOPT_HEADER.
        return new HttpResponse($status, $body, []);
    }

    public function get(string $url, array $options = []): HttpResponseInterface
    {
        return $this->request('GET', $url, $options);
    }

    public function post(string $url, array $options = []): HttpResponseInterface
    {
        return $this->request('POST', $url, $options);
    }

    public function put(string $url, array $options = []): HttpResponseInterface
    {
        return $this->request('PUT', $url, $options);
    }

    public function delete(string $url, array $options = []): HttpResponseInterface
    {
        return $this->request('DELETE', $url, $options);
    }
}
