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
 * Interface InstallerStepInterface
 *
 * Represents a single step in the installer workflow.
 *
 * A step is responsible for:
 * - providing metadata (key, title)
 * - building data for presentation (payload)
 * - handling user input (POST)
 * - determining completion state
 *
 * The step itself MUST NOT render templates or redirect.
 * It only provides data and state to the InstallerKernel.
 */
interface InstallerStepInterface
{
    /**
     * Unique identifier of the step.
     *
     * Used as:
     * - session storage key
     * - step ordering reference
     * - internal installer state
     *
     * Example: "start", "requirements", "database"
     */
    // public function getKey(): string;

    /**
     * Module package name (ZIP file name).
     *
     * Used to identify the installation package.
     *
     * Example: "CmsLite" for "CmsLite.zip"
     * MUST match the actual package file name.
     */
    public function getName(): string;

    /**
     * Translation key for the step title.
     *
     * Displayed in:
     * - step navigation
     * - step header
     *
     * Example: "installer.step.start.title"
     */
    public function getTitle(): ?string;

    /**
     * Translation key for the step description.
     *
     * Displayed in:
     * - step description area
     *
     * Example: "installer.step.start.description"
     */
    public function getDescription(): ?string;

    /**
     * Called before rendering the step.
     *
     * Use this method to:
     * - prepare payload
     * - perform read-only checks
     * - initialize default state
     *
     * This method MUST NOT modify installer progress.
     */
    public function boot(): void;

    /**
     * Handles user input (POST request).
     *
     * This method is called ONLY when the current step
     * is active and a POST request is performed.
     *
     * Typical responsibilities:
     * - validate input
     * - update payload (errors, messages)
     * - persist data to session or config
     * - mark step as completed
     *
     * @param array<string, mixed> $input
     */
    public function handle(array $input): void;

    /**
     * Determines whether the step is completed.
     *
     * Used by InstallerKernel to:
     * - advance to next step
     * - calculate progress
     *
     * MUST be deterministic.
     */
    public function isCompleted(): bool;

    /**
     * Checks if the step is an install step.
     */
    public function isInstallStep(): bool;

    /**
     * Builds payload for the view layer.
     *
     * Payload MUST be:
     * - serializable
     * - language-agnostic (translation keys only)
     *
     * Example payloads:
     *
     * Content:
     * [
     *   'type' => 'content',
     *   'text' => 'installer.step.start.content'
     * ]
     *
     * Messages:
     * [
     *   'type' => 'messages',
     *   'items' => [
     *     [
     *       'type' => 'success',
     *       'key'  => 'installer.requirements.php_ok',
     *       'params' => ['{php}' => '8.2']
     *     ]
     *   ]
     * ]
     *
     * @return array<string, mixed>
     */
    public function getPayload(): array;

    /**
     * Checks if the step has a payload to render.
     */
    public function hasPayload(): bool;
}
