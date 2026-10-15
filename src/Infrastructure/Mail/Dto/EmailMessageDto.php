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

final readonly class EmailMessageDto
{
    /**
     * @param EmailAddressDto[] $to
     * @param EmailAddressDto[] $cc
     * @param EmailAddressDto[] $bcc
     * @param EmailAttachmentDto[] $attachments
     * @param array<string, string> $headers
     */
    public function __construct(
        public string $subject,
        public array $to,
        public ?string $from = null,
        public ?string $fromName = null,
        public ?string $text = null,
        public ?string $html = null,
        public array $cc = [],
        public array $bcc = [],
        public ?string $replyTo = null,
        public ?string $replyToName = null,
        public array $attachments = [],
        public array $headers = [],
    ) {}
}
