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

namespace Dbm\Infrastructure\Mail\Registry;

use Dbm\Infrastructure\Mail\Contract\MailerInterface;
use Dbm\Infrastructure\Mail\Exception\MailConfigurationException;

final class MailerRegistry
{
    /** @var array<string, callable(): MailerInterface> */
    private array $factories = [];

    /**
     * @param callable(): MailerInterface $factory
     */
    public function register(string $driver, callable $factory): void
    {
        $driver = strtolower(trim($driver));

        if ($driver === '') {
            throw new MailConfigurationException(
                'Mailer driver name cannot be empty.',
            );
        }

        if (isset($this->factories[$driver])) {
            throw new MailConfigurationException(
                sprintf('Mailer driver "%s" is already registered.', $driver),
            );
        }

        $this->factories[$driver] = $factory;
    }

    public function create(string $driver): MailerInterface
    {
        $driver = strtolower(trim($driver));
        $factory = $this->factories[$driver] ?? null;

        if ($factory === null) {
            throw new MailConfigurationException(
                sprintf('Mailer driver "%s" is not registered.', $driver),
            );
        }

        return $factory();
    }

    public function has(string $driver): bool
    {
        return isset($this->factories[strtolower(trim($driver))]);
    }
}
