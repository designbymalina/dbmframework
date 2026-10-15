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

namespace Dbm\Infrastructure\Mail\Transport;

use Dbm\Infrastructure\Mail\Contract\MailTransportInterface;
use Dbm\Infrastructure\Mail\Dto\EmailMessageDto;
use Dbm\Infrastructure\Mail\Dto\SmtpConfigDto;
use Dbm\Infrastructure\Mail\Exception\MailConfigurationException;
use Dbm\Infrastructure\Mail\MimeMessageBuilder;

final class SmtpTransport implements MailTransportInterface
{
    public function __construct(
        private readonly SmtpConfigDto $config,
        private readonly MimeMessageBuilder $builder,
    ) {}

    public function send(EmailMessageDto $message): bool
    {
        $sender = $message->from ?? $this->config->defaultFrom;

        if ($sender === null || trim($sender) === '') {
            throw new MailConfigurationException(
                'A sender address must be configured in the message or SMTP config.',
            );
        }

        $effectiveMessage = new EmailMessageDto(
            subject: $message->subject,
            to: $message->to,
            from: $sender,
            fromName: $message->fromName ?? $this->config->defaultFromName,
            text: $message->text,
            html: $message->html,
            cc: $message->cc,
            bcc: $message->bcc,
            replyTo: $message->replyTo,
            replyToName: $message->replyToName,
            attachments: $message->attachments,
            headers: $message->headers,
        );

        $recipients = [];

        foreach ([
            ...$effectiveMessage->to,
            ...$effectiveMessage->cc,
            ...$effectiveMessage->bcc,
        ] as $recipient) {
            $recipients[] = $recipient->email;
        }

        $recipients = array_values(array_unique($recipients));

        if ($recipients === []) {
            throw new MailConfigurationException(
                'At least one recipient is required.',
            );
        }

        $mime = $this->builder->build($effectiveMessage);

        $data = rtrim($mime->headers, "\r\n")
            . "\r\n\r\n"
            . $mime->body;

        $client = new SmtpClient($this->config);

        $client->send(
            $sender,
            $recipients,
            $data,
        );

        return true;
    }
}
