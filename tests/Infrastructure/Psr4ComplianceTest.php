<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;

final class Psr4ComplianceTest extends TestCase
{
    public function testComposerAutoloadHasNoPsr4Violations(): void
    {
        exec('composer dump-autoload -o 2>&1', $output);

        $warnings = array_filter(
            $output,
            fn($line)
                => str_contains($line, 'does not comply with psr-4')
                && !str_contains($line, 'application/Libraries')
        );

        $this->assertCount(
            0,
            $warnings,
            "PSR-4 violations detected:\n" . implode("\n", $warnings)
        );
    }
}
