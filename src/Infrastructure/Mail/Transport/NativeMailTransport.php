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
use Dbm\Infrastructure\Mail\Exception\MailConfigurationException;
use Dbm\Infrastructure\Mail\MimeMessageBuilder;

final class NativeMailTransport implements MailTransportInterface
{
    /**
     * @var callable(string, string, string, string): bool
     */
    private $sender;

    /**
     * @param callable(string, string, string, string): bool|null $sender
     */
    public function __construct(
        private readonly MimeMessageBuilder $builder,
        ?callable $sender = null,
        private readonly ?string $defaultFrom = null,
        private readonly ?string $defaultFromName = null,
    ) {
        $this->sender = $sender ?? 'mail';
    }

    public function send(EmailMessageDto $message): bool
    {
        $effectiveMessage = new EmailMessageDto(
            subject: $message->subject,
            to: $message->to,
            from: $message->from ?? $this->defaultFrom,
            fromName: $message->fromName ?? $this->defaultFromName,
            text: $message->text,
            html: $message->html,
            cc: $message->cc,
            bcc: $message->bcc,
            replyTo: $message->replyTo,
            replyToName: $message->replyToName,
            attachments: $message->attachments,
            headers: $message->headers,
        );

        $recipients = $this->extractRecipientEmails($effectiveMessage);

        if ($recipients === []) {
            throw new MailConfigurationException(
                'At least one recipient is required.',
            );
        }

        $mime = $this->builder->build($effectiveMessage);

        return ($this->sender)(
            implode(', ', $recipients),
            $effectiveMessage->subject,
            $mime->body,
            $this->buildHeaders($mime->headers),
        );
    }

    /**
     * @return string[]
     */
    private function extractRecipientEmails(
        EmailMessageDto $message,
    ): array {
        $recipients = [];

        foreach ([
            ...$message->to,
            ...$message->cc,
            ...$message->bcc,
        ] as $recipient) {
            $recipients[] = $recipient->email;
        }

        return array_values(array_unique($recipients));
    }

    private function buildHeaders(string $headers): string
    {
        // PHP mail() receives the subject as a separate argument.
        return preg_replace(
            '/^Subject:.*(?:\r\n|\n|$)/mi',
            '',
            $headers,
        ) ?? $headers;
    }
}
