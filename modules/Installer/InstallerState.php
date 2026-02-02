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

use Dbm\Infrastructure\Session\SessionManager;

final class InstallerState
{
    private const KEY = 'dbm_installer';

    public function __construct(
        private SessionManager $session
    ) {}

    /* ===== low-level ===== */

    private function data(): array
    {
        return $this->session->getSession(self::KEY) ?? [];
    }

    private function save(array $data): void
    {
        $this->session->setSession(self::KEY, $data);
    }

    /* ===== generic ===== */

    public function get(string $key, mixed $default = null): mixed
    {
        $data = $this->data();
        return $data[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $data = $this->data();
        $data[$key] = $value;
        $this->save($data);
    }

    public function reset(): void
    {
        $this->session->unsetSession(self::KEY);
    }

    /* ===== installer flow ===== */

    public function currentIndex(): int
    {
        return (int) ($this->get('index', 0));
    }

    public function advance(): void
    {
        $this->set('index', $this->currentIndex() + 1);
    }

    public function completedSteps(): array
    {
        return (array) ($this->get('steps', []));
    }

    public function isDone(string $stepName): bool
    {
        return (bool) $this->get('installer.step.' . strtolower($stepName) . '.done', false);
    }

    public function markDone(string $stepName): void
    {
        $this->set('installer.step.' . strtolower($stepName) . '.done', true);
    }

    public function setPayload(string $stepKey, array $payload): void
    {
        $this->set('payload_' . $stepKey, $payload);
    }

    public function getPayload(string $stepKey): array
    {
        return $this->get('payload_' . $stepKey, []);
    }
}
