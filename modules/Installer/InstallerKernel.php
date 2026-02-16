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
use Mod\Installer\Contracts\InstallerStateInterface;
use Mod\Installer\Contracts\InstallerStepInterface;
use Mod\Installer\Steps\Helper\CacheHelper;

final class InstallerKernel
{
    /**
     * @param InstallerStepInterface[] $steps
     */
    public function __construct(
        private InstallerStateInterface $state,
        private array $steps
    ) {}

    /* ===== Navigation ===== */

    public function state(): InstallerStateInterface
    {
        return $this->state;
    }

    /**
     * @return array<int, InstallerStepInterface>
     */
    public function steps(): array
    {
        return $this->steps;
    }

    /**
     * Indeks biezacego kroku
     */
    public function currentIndex(): int
    {
        return $this->state->currentIndex();
    }

    /**
     * Bieżacy krok
     */
    public function currentStep(): InstallerStepInterface
    {
        if (empty($this->steps)) {
            throw new \RuntimeException('Installer has no steps.');
        }

        $this->state->clampIndex(count($this->steps) - 1);

        return $this->steps[$this->state->currentIndex()];
    }

    /* ===== Lifecycle ===== */

    public function boot(): void
    {
        $this->currentStep()->boot();
    }

    /**
     * @param array<string, mixed> $input
     */
    public function handle(array $input): void
    {
        $step = $this->currentStep();

        $step->handle($input);

        if (!$step->isCompleted()) {
            return;
        }

        $lastIndex = count($this->steps) - 1;
        $isLast = $this->currentIndex() === $lastIndex;

        if ($isLast) {
            $this->state->finish();

            register_shutdown_function(static function (): void {
                CacheHelper::clearCache();
            });
        } else {
            $this->state->advance();
        }
    }

    /* ===== View ===== */

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        $step = $this->currentStep();

        if ($step->hasPayload()) {
            return $step->getPayload();
        }

        $this->state->clear(); // INFO! Opcjonalne.

        return [
            'type' => InstallerConstant::ALERT,
            'class' => 'info',
            'text' => 'installer.alert.installation_process', // optional: installer.alert.installation_success
            'actions' => [
                [
                    'label' => 'installer.button.home_page',
                    'class' => 'dbm-btn-gradient',
                    'path' => 'home',
                ],
            ],
        ];
    }

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
            if ($step->isCompleted()) {
                $completed++;
            }
        }

        return (int) round(($completed / $total) * 100);
    }
}
