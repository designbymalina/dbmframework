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

use Psr\Http\Message\StreamInterface;
use RuntimeException;

class Stream implements StreamInterface
{
    /** @var resource|null */
    private $stream;

    public function __construct(string $content)
    {
        $stream = fopen('php://temp', 'r+');

        if ($stream === false) {
            throw new RuntimeException('Unable to create temporary stream.');
        }

        $this->stream = $stream;

        if ($content !== '') {
            fwrite($this->stream, $content);
        }

        rewind($this->stream);
    }

    public static function create(string $content): self
    {
        return new self($content);
    }

    /**
     * @param resource $resource
     */
    public static function createFromResource($resource): self
    {
        if (!is_resource($resource)) {
            throw new RuntimeException('Invalid stream resource.');
        }

        $stream = new self('');

        $stream->close();
        $stream->stream = $resource;

        return $stream;
    }

    public function __toString(): string
    {
        if ($this->stream === null) {
            return '';
        }

        try {
            $this->rewind();

            return stream_get_contents($this->stream) ?: '';
        } catch (\Throwable) {
            return '';
        }
    }

    public function getContents(): string
    {
        if ($this->stream === null) {
            return '';
        }

        return stream_get_contents($this->stream) ?: '';
    }

    public function close(): void
    {
        if ($this->stream !== null) {
            fclose($this->stream);
            $this->stream = null;
        }
    }

    public function detach()
    {
        $resource = $this->stream;
        $this->stream = null;

        return $resource;
    }

    public function getSize(): ?int
    {
        if ($this->stream === null) {
            return null;
        }

        $stats = fstat($this->stream);

        return $stats !== false ? (int) $stats['size'] : null;
    }

    public function tell(): int
    {
        if ($this->stream === null) {
            throw new RuntimeException('Stream is detached.');
        }

        $position = ftell($this->stream);

        if ($position === false) {
            throw new RuntimeException('Unable to determine stream position.');
        }

        return $position;
    }

    public function eof(): bool
    {
        return $this->stream === null || feof($this->stream);
    }

    public function isSeekable(): bool
    {
        if ($this->stream === null) {
            return false;
        }

        return (bool) stream_get_meta_data($this->stream)['seekable'];
    }

    public function seek($offset, $whence = SEEK_SET): void
    {
        if ($this->stream === null) {
            throw new RuntimeException('Stream is detached.');
        }

        if (fseek($this->stream, $offset, $whence) !== 0) {
            throw new RuntimeException('Unable to seek stream.');
        }
    }

    public function rewind(): void
    {
        $this->seek(0);
    }

    public function isWritable(): bool
    {
        if ($this->stream === null) {
            return false;
        }

        $mode = stream_get_meta_data($this->stream)['mode'];

        return strpbrk($mode, 'waxc+') !== false;
    }

    public function write($string): int
    {
        if ($this->stream === null) {
            throw new RuntimeException('Stream is detached.');
        }

        $written = fwrite($this->stream, $string);

        if ($written === false) {
            throw new RuntimeException('Unable to write to stream.');
        }

        return $written;
    }

    public function isReadable(): bool
    {
        if ($this->stream === null) {
            return false;
        }

        $mode = stream_get_meta_data($this->stream)['mode'];

        return strpbrk($mode, 'r+') !== false;
    }

    public function read($length): string
    {
        if ($this->stream === null) {
            throw new RuntimeException('Stream is detached.');
        }

        $content = fread($this->stream, $length);

        if ($content === false) {
            throw new RuntimeException('Unable to read from stream.');
        }

        return $content;
    }

    public function getMetadata($key = null): mixed
    {
        if ($this->stream === null) {
            return $key === null ? [] : null;
        }

        $metadata = stream_get_meta_data($this->stream);

        return $key !== null
            ? ($metadata[$key] ?? null)
            : $metadata;
    }
}
