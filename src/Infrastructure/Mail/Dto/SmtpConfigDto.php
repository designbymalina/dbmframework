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

namespace Dbm\Infrastructure\Mail\Dto;

use Dbm\Infrastructure\Mail\Exception\MailConfigurationException;
use Dbm\Infrastructure\Mail\MailConfig;

final readonly class SmtpConfigDto
{
    public function __construct(
        public string $host,
        public int $port = 587,
        public ?string $username = null,
        public ?string $password = null,
        public ?string $encryption = 'tls',
        public int $timeout = MailConfig::DEFAULT_TIMEOUT,
        public ?string $defaultFrom = null,
        public ?string $defaultFromName = null,
    ) {
        if (trim($this->host) === '') {
            throw new MailConfigurationException(
                'SMTP host must not be empty.',
            );
        }

        if ($this->port < 1 || $this->port > 65535) {
            throw new MailConfigurationException(
                'SMTP port must be between 1 and 65535.',
            );
        }

        if ($this->timeout < 1) {
            throw new MailConfigurationException(
                'SMTP timeout must be greater than zero.',
            );
        }

        if (!in_array($this->encryption, [null, 'tls', 'ssl'], true)) {
            throw new MailConfigurationException(
                'SMTP encryption must be null, "tls" or "ssl".',
            );
        }

        if (($this->username === null) !== ($this->password === null)) {
            throw new MailConfigurationException(
                'SMTP username and password must be configured together.',
            );
        }

        if (
            $this->username !== null
            && trim($this->username) === ''
        ) {
            throw new MailConfigurationException(
                'SMTP username must not be empty.',
            );
        }

        if (
            $this->password !== null
            && $this->password === ''
        ) {
            throw new MailConfigurationException(
                'SMTP password must not be empty.',
            );
        }

        if ($this->username !== null && $this->encryption === null) {
            throw new MailConfigurationException(
                'SMTP authentication requires TLS or SSL encryption.',
            );
        }
    }
}
