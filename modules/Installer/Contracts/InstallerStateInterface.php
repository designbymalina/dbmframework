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

namespace Mod\Installer\Contracts;

/**
 * Represents persistent state of the installer process.
 *
 * Implementations are responsible for storing and restoring
 * installer progress between requests (e.g. session, cache, database).
 *
 * The state tracks:
 * - arbitrary key-value data
 * - current step index
 * - completion status
 */
interface InstallerStateInterface
{
    /**
     * Retrieve value from installer state.
     *
     * @param string $key State key
     * @param mixed $default Default value if key does not exist
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Store value in installer state.
     *
     * @param string $key
     * @param mixed $value
     */
    public function set(string $key, mixed $value): void;

    /**
     * Remove value from installer state.
     */
    public function remove(string $key): void;

    /**
     * Clear entire installer state.
     * Resets progress and stored data.
     */
    public function clear(): void;

    /**
     * Get current step index.
     */
    public function currentIndex(): int;

    /**
     * Set current step index manually.
     */
    public function setCurrentIndex(int $index): void;

    /**
     * Clamp current index to maximum allowed value.
     * Prevents index overflow.
     */
    public function clampIndex(int $max): void;

    /**
     * Move installer to next step.
     */
    public function advance(): void;

    /**
     * Mark installer as finished.
     */
    public function finish(): void;

    /**
     * Determine whether installer process is completed.
     */
    public function isFinished(): bool;
}
