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
 * EmailMessageDto
 *
 * Defines a single email message configuration.
 *
 * CONTENT RULES:
 * - If "template" is provided, it will be loaded from file system
 * - If template is missing, "body" is used as fallback content
 * - "params" are injected into template/body placeholders {key}
 *
 * RECIPIENT RULES:
 * - If "recipients" is NOT empty - it overrides "toEmail"
 * - If "recipients" is empty - "toEmail" is used
 *
 * IMPORTANT:
 * - Either template OR body is required (one must be non-null)
 */

declare(strict_types=1);

namespace Dbm\Infrastructure\Sender\Dto;

final class EmailMessageDto
{
    /**
     * @param array<int, array{email: string, name?: string}> $recipients List of primary recipients (alternative to toEmail)
     * @param array<int, array{email: string, name?: string}> $cc Carbon Copy recipients
     * @param array<int, array{email: string, name?: string}> $bcc Blind Carbon Copy recipients
     * @param array<string, mixed> $params Template variables for email rendering (e.g. {token}, {name})
     */
    public function __construct(
        public string $subject,

        // sender
        public string $fromEmail,
        public string $fromName,

        // content layer
        public ?string $template = null, // template file from /data/mailer/
        public ?string $body = null, // raw HTML/text fallback
        public array $params = [],

        // recipients (single mode OR fallback)
        public ?string $toEmail = null, // primary recipient email
        public ?string $toName = null, // display name (optional)

        public array $recipients = [], // multi-recipient mode (overrides toEmail)
        public array $cc = [], // CC recipients
        public array $bcc = [], // BCC recipients

        // extras
        public ?string $messageType = null, // html | text | null (auto)
        public ?string $attachmentPath = null, // file key in /data/attachment/
        public ?string $attachmentName = null, // visible filename
    ) {
        if ($this->template === null && $this->body === null) {
            throw new \InvalidArgumentException(
                'Either template or body must be provided'
            );
        }
    }
}
