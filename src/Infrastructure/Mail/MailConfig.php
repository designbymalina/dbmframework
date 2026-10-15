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

namespace Dbm\Infrastructure\Mail;

use Dbm\Core\Config\AppConfig;
use Dbm\Environment\Environment;
use Dbm\Infrastructure\Mail\Exception\MailConfigurationException;

final readonly class MailConfig
{
    public const DEFAULT_TIMEOUT = 10;

    /** @var string[] */
    private const SMTP_DRIVERS = ['dbm-smtp', 'phpmailer-smtp'];

    public function __construct(
        public string $driver,
        public ?string $host,
        public int $port,
        public ?string $username,
        public ?string $password,
        public ?string $encryption,
        public string $fromEmail,
        public string $fromName,
    ) {
        if (!in_array($this->driver, self::SMTP_DRIVERS, true)) {
            return;
        }

        if (!in_array($this->encryption, [null, 'tls', 'ssl'], true)) {
            throw new MailConfigurationException(
                'SMTP encryption must be "tls" or "ssl".',
            );
        }

        if (($this->username === null) !== ($this->password === null)) {
            throw new MailConfigurationException(
                'SMTP username and password must be configured together.',
            );
        }

        if ($this->username !== null && $this->encryption === null) {
            throw new MailConfigurationException(
                'SMTP authentication requires TLS or SSL encryption.',
            );
        }
    }

    public static function fromEnvironment(): self
    {
        $defaultDriver = 'dbm-mail';

        $driver = strtolower(trim(
            (string) Environment::get('MAIL_DRIVER', $defaultDriver),
        ));

        $host = trim(Environment::get('MAIL_HOST'));
        $port = trim(Environment::get('MAIL_PORT'));
        $username = trim(Environment::get('MAIL_USERNAME'));
        $password = Environment::get('MAIL_PASSWORD');
        $encryption = strtolower(trim(Environment::get('MAIL_SECURE')));

        if ($driver === '') {
            $driver = $defaultDriver;
        }

        if ($port !== '' && !ctype_digit($port)) {
            throw new \InvalidArgumentException(
                'MAIL_PORT must be a valid integer.',
            );
        }

        $resolvedPort = $port !== '' ? (int) $port : 587;

        if (
            $encryption === ''
            && in_array($driver, self::SMTP_DRIVERS, true)
        ) {
            $encryption = match ($resolvedPort) {
                465 => 'ssl',
                587 => 'tls',
                default => '',
            };
        }

        return new self(
            driver: $driver,
            host: $host !== '' ? $host : null,
            port: $resolvedPort,
            username: $username !== '' ? $username : null,
            password: $password !== '' ? $password : null,
            encryption: $encryption !== '' ? $encryption : null,
            fromEmail: Environment::get(
                'MAIL_FROM_EMAIL',
                AppConfig::env('APP_EMAIL', 'noreply@localhost'),
            ),
            fromName: Environment::get(
                'MAIL_FROM_NAME',
                AppConfig::env('APP_NAME', 'DBM Application'),
            ),
        );
    }
}
