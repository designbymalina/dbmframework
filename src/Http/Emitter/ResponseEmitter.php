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
 * Example of use in index.php:
 * $emitter = new ResponseEmitter();
 * $emitter->emit($response);
 * or: (new ResponseEmitter())->emit($response);
 * and renove: $response->send() - optional use
 */

declare(strict_types=1);

namespace Dbm\Http\Emitter;

use Psr\Http\Message\ResponseInterface;

final class ResponseEmitter
{
    public function emit(ResponseInterface $response): void
    {
        // CLI safety (np. testy, cron, phpunit)
        if (PHP_SAPI === 'cli') {
            echo (string) $response->getBody();
            return;
        }

        $this->emitStatusLine($response);
        $this->emitHeaders($response);
        $this->emitBody($response);
    }

    private function emitStatusLine(ResponseInterface $response): void
    {
        http_response_code($response->getStatusCode());
    }

    private function emitHeaders(ResponseInterface $response): void
    {
        if (headers_sent()) {
            return; // or throw if strict mode
        }

        foreach ($response->getHeaders() as $name => $values) {
            $normalizedName = $this->normalizeHeaderName((string) $name);

            foreach ($values as $value) {
                header($normalizedName . ': ' . $value, false);
            }
        }
    }

    private function emitBody(ResponseInterface $response): void
    {
        echo (string) $response->getBody();
    }

    private function normalizeHeaderName(string $name): string
    {
        // Content-type => Content-Type
        return str_replace(' ', '-', ucwords(str_replace('-', ' ', $name)));
    }

    // @INFO Opcjonalnie można dopisać Safe Stream System.
    // Obecnie Body nie jest prawdziwym streamem, a string streamem.
    // private function emitBody(ResponseInterface $response): void
    // {
    //     $body = $response->getBody();

    //     if ($body->isSeekable()) {
    //         $body->rewind();
    //     }

    //     $maxIterations = 100000; // safety net
    //     $i = 0;

    //     while (!$body->eof()) {
    //         echo $body->read(8192);

    //         if (++$i > $maxIterations) {
    //             throw new \RuntimeException('Body stream overflow detected (possible infinite stream).');
    //         }
    //     }
    // }
}
