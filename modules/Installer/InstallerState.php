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
 * INFO! Być może warto zmienić InstallerState i Instalator
 * na wersję bez indeksów - opartą wyłącznie o nazwy kroków?
 * Albo jeszcze inaczej, aby pozbyć się indeksów.
 */

declare(strict_types=1);

namespace Mod\Installer;

use Dbm\Infrastructure\Session\SessionManager;
use Mod\Installer\Contracts\InstallerStateInterface;

final class InstallerState implements InstallerStateInterface
{
    /**
     * Klucz sesji, pod którym przechowywany jest stan instalatora.
     */
    private const KEY = 'dbmInstaller';

    public function __construct(
        private SessionManager $session
    ) {}

    /**
     * Pobiera pojedynczą wartość ze stanu.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $data = $this->data();
        return $data[$key] ?? $default;
    }

    /**
     * Ustawia pojedynczą wartość w stanie.
     */
    public function set(string $key, mixed $value): void
    {
        $data = $this->data();
        $data[$key] = $value;
        $this->save($data);
    }

    /**
     * Usuwa pojedynczą wartość z stanu.
     */
    public function remove(string $key): void
    {
        $data = $this->data();

        if (array_key_exists($key, $data)) {
            unset($data[$key]);
            $this->save($data);
        }
    }

    /**
     * Usuwa stan instalatora z sesji.
     */
    public function clear(): void
    {
        $this->session->unsetSession(self::KEY);
    }

    /**
     * Zwraca aktualny indeks kroku (domyślnie 0).
     */
    public function currentIndex(): int
    {
        return (int) $this->get('index', 0);
    }

    /**
     * Ustawia aktualny indeks kroku.
     *
     * Zabezpiecza przed wartościami ujemnymi.
     */
    public function setCurrentIndex(int $index): void
    {
        $this->set('index', max(0, $index));
    }

    /**
     * J.w. Ustawia index (czytelniejsze)
     */
    public function clampIndex(int $max): void
    {
        if ($this->currentIndex() > $max) {
            $this->setCurrentIndex($max);
        }
    }

    /**
     * Przechodzi do kolejnego kroku.
     */
    public function advance(): void
    {
        $this->setCurrentIndex($this->currentIndex() + 1);
    }

    /**
     * Oznacza instalator jako zakończony.
     */
    public function finish(): void
    {
        $this->set('finished', true);
    }

    /**
     * Sprawdza, czy instalator został zakończony.
     */
    public function isFinished(): bool
    {
        return (bool) $this->get('finished', false);
    }

    // ===== Helpers =====

    /**
     * Pobiera cały zapisany stan instalatora z sesji.
     *
     * @return array<string, mixed>
     */
    private function data(): array
    {
        return $this->session->getSession(self::KEY) ?? [];
    }

    /**
     * Zapisuje stan instalatora do sesji.
     *
     * @param array<string, mixed> $data
     */
    private function save(array $data): void
    {
        $this->session->setSession(self::KEY, $data);
    }
}
