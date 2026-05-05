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

final class StringStream implements StreamInterface
{
    private string $content;
    private int $position = 0;

    public function __construct(string $content = '')
    {
        $this->content = $content;
    }

    public function __toString(): string
    {
        return $this->content;
    }

    public function getContents(): string
    {
        return substr($this->content, $this->position);
    }

    public function write($string): int
    {
        $this->content .= $string;
        return strlen($string);
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    // @INFO Can be extended

    public function close(): void {}

    public function detach()
    {
        return null;
    }

    public function getSize(): int
    {
        return strlen($this->content);
    }

    public function tell(): int
    {
        return $this->position;
    }

    public function eof(): bool
    {
        return $this->position >= strlen($this->content);
    }

    public function isSeekable(): bool
    {
        return false;
    }

    public function seek($offset, $whence = SEEK_SET): void {}

    public function isWritable(): bool
    {
        return true;
    }

    public function isReadable(): bool
    {
        return true;
    }

    public function read($length): string
    {
        return substr($this->content, 0, $length);
    }

    public function getMetadata($key = null): mixed
    {
        return null;
    }
}
