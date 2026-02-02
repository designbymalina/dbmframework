<?php

declare(strict_types=1);

namespace Tests\App;

use PHPUnit\Framework\TestCase;

final class AutoloadTest extends TestCase
{
    public function testDbmClassLoads(): void
    {
        $this->assertTrue(
            class_exists(\Dbm\Exceptions\ExceptionHandler::class)
        );
    }

    public function testPsrInterfaceLoads(): void
    {
        $this->assertTrue(
            interface_exists(\Psr\Http\Message\MessageInterface::class)
        );
    }

    public function testTraitLoads(): void
    {
        $this->assertTrue(
            trait_exists(\Dbm\Support\Traits\LazyLoaderTrait::class)
        );
    }
}
