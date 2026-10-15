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
 * Example usage:
 * $container->singleton(
 *     MailerInterface::class,
 *     static fn (): MailerInterface => (
 *         new MailerFactory(MailConfig::fromEnvironment())
 *     )->create(),
 * );
 */

declare(strict_types=1);

namespace Dbm\Infrastructure\Mail;

use Dbm\Infrastructure\Mail\Contract\MailerInterface;
use Dbm\Infrastructure\Mail\Dto\SmtpConfigDto;
use Dbm\Infrastructure\Mail\Exception\MailConfigurationException;
use Dbm\Infrastructure\Mail\Transport\NativeMailTransport;
use Dbm\Infrastructure\Mail\Transport\SmtpTransport;

final readonly class MailerFactory
{
    public function __construct(
        private MailConfig $config,
    ) {}

    /**
     * Creates the built-in mailer using PHP mail().
     */
    public function createNative(): MailerInterface
    {
        $transport = new NativeMailTransport(
            builder: new MimeMessageBuilder(),
            defaultFrom: $this->config->fromEmail,
            defaultFromName: $this->config->fromName,
        );

        return new Mailer($transport);
    }

    /**
     * Creates the built-in SMTP mailer.
     */
    public function createSmtp(): MailerInterface
    {
        if ($this->config->host === null) {
            throw new MailConfigurationException(
                'MAIL_HOST must be configured when MAIL_DRIVER=dbm-smtp.',
            );
        }

        $smtpConfig = new SmtpConfigDto(
            host: $this->config->host,
            port: $this->config->port,
            username: $this->config->username,
            password: $this->config->password,
            encryption: $this->config->encryption,
            defaultFrom: $this->config->fromEmail,
            defaultFromName: $this->config->fromName,
        );

        $transport = new SmtpTransport(
            $smtpConfig,
            new MimeMessageBuilder(),
        );

        return new Mailer($transport);
    }

    /**
     * Creates a built-in mailer based on MAIL_DRIVER.
     */
    public function create(): MailerInterface
    {
        return match ($this->config->driver) {
            'dbm-mail' => $this->createNative(),
            'dbm-smtp' => $this->createSmtp(),
            default => throw new MailConfigurationException(
                sprintf(
                    'Driver "%s" is not supported by the built-in MailerFactory.',
                    $this->config->driver,
                ),
            ),
        };
    }
}
