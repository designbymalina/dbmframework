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

namespace Dbm\Http\Message;

use Dbm\Http\Psr\Message\ExtendedResponseInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * A class representing an HTTP response compliant with PSR-7 (ResponseInterface),
 * extended with DbM framework functions.
 */
final class Response extends Message implements ExtendedResponseInterface
{
    private int $statusCode;
    private string $reasonPhrase = '';

    /**
     * Constructs a new Response
     *
     * @param int $statusCode Kod statusu HTTP.
     * @param array<string, string|string[]> $headers Tablica nagłówków.
     * @param StreamInterface|null $body Treść odpowiedzi (stream).
     */
    public function __construct(int $statusCode = 200, array $headers = [], ?StreamInterface $body = null)
    {
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->body = $body ?? new Stream('');
        $this->reasonPhrase = $this->getDefaultReasonPhrase($statusCode);

        // @INFO: Normalize headers to ensure all values are arrays
        $this->headers = [];
        foreach ($headers as $name => $value) {
            $this->headers[$name] = is_array($value) ? $value : [$value];
        }
    }

    /** {@inheritdoc} */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /** {@inheritdoc} */
    public function withStatus(int $code, string $reasonPhrase = ''): static
    {
        $clone = clone $this;
        $clone->statusCode = $code;
        $clone->reasonPhrase = $reasonPhrase ?: $this->getDefaultReasonPhrase($code);
        return $clone;
    }

    /** {@inheritdoc} */
    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    // --- ADDED methods - Extended Functionality ---

    /** {@inheritdoc} */
    public function send(): void
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $values) {
            foreach ($values as $value) {
                header("$name: $value", false);
            }
        }

        if (!$this->body) {
            return;
        }

        $this->body->rewind();

        while (!$this->body->eof()) {
            echo $this->body->read(8192);

            if (function_exists('ob_flush')) {
                @ob_flush();
            }

            flush();
        }
    }

    /** @param array<string, string|string[]> $headers */
    public static function text(string $content, int $statusCode = 200, array $headers = []): Response
    {
        $headers = array_merge(['Content-Type' => 'text/plain; charset=UTF-8'], $headers);
        return new self($statusCode, $headers, new Stream($content));
    }

    /** @param array<string, string|string[]> $headers */
    public static function html(string $content, int $statusCode = 200, array $headers = []): Response
    {
        $headers = array_merge(['Content-Type' => 'text/html; charset=UTF-8'], $headers);
        return new self($statusCode, $headers, new Stream($content));
    }

    /** @param array<string, mixed> $data */
    public static function json(array $data, int $statusCode = 200): static
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $stream = new Stream($json);
        return new self($statusCode, ['Content-Type' => 'application/json; charset=UTF-8'], $stream);
    }

    /**
     * Download idzie strumieniowo.
     *
     * Plik nie jest wczytywany do pamięci i może obsługiwać
     * zarówno małe pliki, jak i duże pliki rzędu setek MB.
     *
     * {@inheritdoc}
     */
    public static function download(
        string $filePath,
        ?string $fileName = null,
        ?string $contentType = 'application/octet-stream'
    ): self {
        if (!is_file($filePath)) {
            return new self(404);
        }

        $resource = fopen($filePath, 'rb');

        if ($resource === false) {
            return new self(500);
        }

        $fileName = self::normalizeDownloadName($filePath, $fileName);

        return new self(
            200,
            [
                'Content-Type' => $contentType ?? 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Content-Length' => (string) filesize($filePath),
            ],
            Stream::createFromResource($resource)
        );
    }

    /** {@inheritdoc} */
    public static function redirect(string $url, int $status = 302): ResponseInterface
    {
        return new self($status, [
            'Location' => $url,
        ]);
    }

    /**
     * Zwraca domyślną nazwę statusu HTTP (reason phrase).
     *
     * @param int $code
     * @return string
     */
    private function getDefaultReasonPhrase(int $code): string
    {
        return match ($code) {
            200 => 'OK',
            201 => 'Created',
            204 => 'No Content',
            301 => 'Moved Permanently',
            302 => 'Found',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            409 => 'Conflict',
            422 => 'Unprocessable Entity',
            500 => 'Internal Server Error',
            503 => 'Service Unavailable',
            default => '',
        };
    }

    private static function normalizeDownloadName(string $filePath, ?string $fileName): string
    {
        $fileName = $fileName !== null ? trim($fileName) : '';

        if ($fileName === '') {
            $fileName = basename($filePath);
        }

        return basename($fileName);
    }

    /** {@inheritdoc} */
    public function debug(): void
    {
        echo "Status Code: {$this->statusCode}\n";
        echo "Headers:\n";

        foreach ($this->headers as $name => $values) {
            echo "$name: " . implode(', ', (array) $values) . "\n";
        }

        echo "Body:\n" . (string) $this->body . "\n";
        exit;
    }
}
