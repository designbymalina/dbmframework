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

namespace Mod\Installer;

use Mod\Installer\Constants\InstallerConstant;
use Mod\Installer\Contracts\InstallerStepInterface;
use Mod\Installer\Steps\Helper\CacheHelper;
use Mod\Installer\Steps\NullInstallerStep;

final class InstallerKernel
{
    private bool $clearCacheAfter = false;

    public function __construct(
        private InstallerState $state,
        private array $steps
    ) {}

    /* ===== Navigation ===== */

    public function state(): InstallerState
    {
        return $this->state;
    }

    public function currentIndex(): int
    {
        return $this->state->currentIndex();
    }

    public function steps(): array
    {
        return $this->steps;
    }

    public function currentStep(): InstallerStepInterface
    {
        $index = $this->state->currentIndex();
        $step = $this->steps[$index] ?? null;

        if (!$step) {
            return new NullInstallerStep();
        }

        $step->boot();
        return $step;
    }

    /* ===== Lifecycle ===== */

    public function boot(): void
    {
        $step = $this->currentStep();

        if ($step && method_exists($step, 'boot')) {
            $step->boot();
        }
    }

    public function handle(array $input): void
    {
        $step = $this->currentStep();
        if (!$step) {
            return;
        }

        $step->handle($input);

        if ($step->isCompleted()) {
            $this->state->markDone($step->getName());
            $this->state->advance();
        }
    }


    /* ===== View ===== */

    public function payload(): array
    {
        $step = $this->currentStep();

        if (!$step) {
            return [
                'type' => InstallerConstant::ALERT,
                'text' => 'installer.alert.no_step',
            ];
        }

        $payload = $step->getPayload();

        if (!$step->hasPayload()) {
            return []; // default payload
        }

        return !empty($payload)
            ? $payload
            : [
                'type' => InstallerConstant::ALERT,
                'class' => 'warning',
                'text' => 'installer.alert.no_payload',
                'placeholder' => [],
            ];
    }

    /* ===== Cache ====
    INFO! Obecnie nie używane, patrz do CmsLiteStep -> CacheHelper::clearCache()
    public function requestClearCache(): void
    {
        $this->clearCacheAfter = true;
    }

    public function terminate(): void
    {
        if ($this->clearCacheAfter) {
            CacheHelper::clearCache();
        }
    } */

    /* ===== Progress ===== */

    public function progress(): int
    {
        $progressSteps = array_filter($this->steps, fn($step) => $step->isInstallStep());

        $total = count($progressSteps);

        if ($total === 0) {
            return 0;
        }

        $completed = 0;

        foreach ($progressSteps as $step) {
            if ($step->isDone()) {
                $completed++;
            }
        }

        return (int) round(($completed / $total) * 100);
    }
}
