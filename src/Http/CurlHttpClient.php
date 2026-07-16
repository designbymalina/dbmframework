<?php

/**
 * Application: DbM Framework
 * A lightweight PHP framework for building web applications.
 *
 * @author Artur Malinowski
 * @copyright Design by Malina (All Rights Reserved)
 * @license MIT
 * @link https://www.dbm.org.pl
 *
 * @INFO W przyszłości rozszerzyć o obsługę:
 * application/x-www-form-urlencoded, query string, raw body...
 */

declare(strict_types=1);

namespace Dbm\Http;

use Dbm\Core\Config\AppConfig;
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
        $started = microtime(true);
        $method = strtoupper($method);

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $options['timeout'] ?? 30);

        $headers = [
            'User-Agent: DBM Framework HTTP Client',
        ];

        $contentTypeDefined = false;

        if (!empty($options['headers'])) {
            foreach ($options['headers'] as $k => $v) {
                if (strcasecmp($k, 'Content-Type') === 0) {
                    $contentTypeDefined = true;
                }

                $headers[] = "$k: $v";
            }
        }

        if (array_key_exists('json', $options) && !$contentTypeDefined) {
            $headers[] = 'Content-Type: application/json';
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $responseHeaders = [];

        curl_setopt(
            $ch,
            CURLOPT_HEADERFUNCTION,
            static function ($curl, string $header) use (&$responseHeaders): int {
                $length = strlen($header);
                $header = trim($header);

                if ($header === '' || !str_contains($header, ':')) {
                    return $length;
                }

                [$name, $value] = explode(':', $header, 2);

                $responseHeaders[trim($name)][] = trim($value);

                return $length;
            }
        );

        if (array_key_exists('json', $options)) {
            curl_setopt(
                $ch,
                CURLOPT_POSTFIELDS,
                json_encode(
                    $options['json'],
                    JSON_UNESCAPED_UNICODE
                    | JSON_UNESCAPED_SLASHES
                    | JSON_THROW_ON_ERROR
                )
            );
        }

        if (!empty($options['auth'])) {
            if (!is_array($options['auth']) || count($options['auth']) < 2) {
                throw new \InvalidArgumentException(
                    'HTTP client option "auth" must contain [username, password].'
                );
            }

            curl_setopt(
                $ch,
                CURLOPT_USERPWD,
                sprintf('%s:%s', $options['auth'][0], $options['auth'][1])
            );
        }

        $verify = $options['verify'] ?? true;

        curl_setopt(
            $ch,
            CURLOPT_SSL_VERIFYPEER,
            $verify
        );

        curl_setopt(
            $ch,
            CURLOPT_SSL_VERIFYHOST,
            $verify ? 2 : 0
        );

        curl_setopt(
            $ch,
            CURLOPT_FOLLOWLOCATION,
            $options['follow_redirects'] ?? true
        );

        // --- Log request ---
        if (AppConfig::httpClientLog()) {
            $this->logger?->debug('HTTP request (cURL)', [
                'method' => $method,
                'url' => $url,
            ]);
        }

        curl_setopt(
            $ch,
            CURLOPT_ENCODING,
            ''
        );

        $body = curl_exec($ch);

        if ($body === false) {
            $error = curl_error($ch);

            $this->logger?->error('HTTP request failed (cURL)', [
                'method' => $method,
                'url' => $url,
                'error' => $error,
            ]);

            curl_close($ch);

            return new HttpResponse(0, '', []);
        }

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $ms = round((microtime(true) - $started) * 1000, 2);

        curl_close($ch);

        // --- Log response ---
        $context = [
            'method' => $method,
            'url' => $url,
            'status' => $status,
            'ms' => $ms,
        ];

        if ($status >= 500) {
            $this->logger?->error('HTTP response (cURL)', $context);
        } elseif ($status >= 400) {
            $this->logger?->warning('HTTP response (cURL)', $context);
        } elseif (AppConfig::httpClientLog()) {
            $this->logger?->debug('HTTP response (cURL)', $context);
        }

        return new HttpResponse($status, $body, $responseHeaders);
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
