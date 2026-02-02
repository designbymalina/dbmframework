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

namespace Mod\Installer\Steps;

use Dbm\Core\DependencyContainer;
use Dbm\Infrastructure\Log\Logger;
use Mod\Installer\InstallerState;
use Mod\Installer\Contracts\InstallerStepInterface;

abstract class AbstractInstallerStep implements InstallerStepInterface
{
    protected DependencyContainer $container;
    protected InstallerState $state;
    protected Logger $logger;

    protected array $payload = [];

    public function __construct(DependencyContainer $container)
    {
        $this->container = $container;
        $this->state = $container->get(InstallerState::class);
        $this->logger = $container->get(Logger::class);
    }

    /**
     * Nazwa kroku (ID logiczne, nie klucz modułu)
     */
    abstract public function getName(): string;

    /**
     * Tytuł kroku
     */
    public function getTitle(): ?string
    {
        return null;
    }

    /**
     * Opis kroku
     */
    public function getDescription(): ?string
    {
        return null;
    }

    protected function setDescription(?string $description): void
    {
        $this->state->set($this->getStepKey() . '.description', $description);
    }

    /**
     * Nazwa pliku ZIP
     */
    public function getZipFile(): string
    {
        return $this->getName() . '.zip';
    }

    /**
     * Ścieżka do pliku ZIP
     */
    public function getZipPath(): string
    {
        return BASE_DIRECTORY . '/_Documents/packages/' . $this->getZipFile();
    }

    /**
     * Inicjalizacja kroku
     */
    public function boot(): void
    {
        // default null
    }

    /**
     * Obsługa danych wejściowych kroku
     */
    public function handle(array $input): void
    {
        // default null
    }

    /* ===== PAYLOAD ===== */

    /**
     * Ustawia danye do renderowania.
     */
    public function setPayload(array $payload): void
    {
        $this->payload = $payload;
    }

    /**
     * Pobiera dane renderowania.
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * Sprawdza, czy krok ma dane do renderowania.
     */
    public function hasPayload(): bool
    {
        return !empty($this->payload);
    }

    /* ===== STATE ===== */

    /**
     * Sprawdza, czy krok jest ukończony.
     */
    public function isDone(): bool
    {
        return (bool) $this->state->get($this->getStepKey() . '.done', false);
    }

    /**
     * Sprawdza, czy krok został ukończony.
     */
    public function isCompleted(): bool
    {
        return $this->isDone();
    }

    /**
     * Oznacza krok jako ukończony.
     */
    public function markCompleted(): void
    {
        $this->state->set($this->getStepKey() . '.done', true);
    }

    /**
     * Ustawia flagę, czy krok jest krokiem instalacyjnym.
     * Domyślnie większość to kroki instalacyjne inne tylko sprawdzające lub koniec.
     */
    public function isInstallStep(): bool
    {
        return true;
    }

    /**
     * Klucz techniczny kroku (do InstallerState)
     */
    protected function getStepKey(): string
    {
        return 'installer.step.' . strtolower($this->getName());
    }

    /**
     * Ustawia fazę kroku.
     */
    protected function setPhase(string $phase): void
    {
        $this->state->set($this->getStepKey() . '.phase', $phase);
    }

    /**
     * Pobiera fazę kroku.
     */
    protected function getPhase(): string
    {
        return $this->state->get($this->getStepKey() . '.phase', 'check');
    }
}
