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

use Dbm\Infrastructure\Mail\Dto\EmailAddressDto;
use Dbm\Infrastructure\Mail\Dto\EmailAttachmentDto;
use Dbm\Infrastructure\Mail\Dto\EmailMessageDto;
use Dbm\Infrastructure\Mail\Dto\MimeMessageDto;
use Dbm\Infrastructure\Mail\Exception\MailException;

final class MimeMessageBuilder
{
    public function build(EmailMessageDto $message): MimeMessageDto
    {
        if ($message->text === null && $message->html === null) {
            throw new MailException(
                'Email message must contain text or HTML body.',
            );
        }

        $this->validateRecipients($message->to);
        $this->validateRecipients($message->cc);
        $this->validateRecipients($message->bcc);

        $headers = $this->buildHeaders($message);
        $content = $this->buildBody($message);

        return new MimeMessageDto(
            headers: $headers . "\r\n" . $content['headers'],
            body: $content['body'],
        );
    }

    private function buildHeaders(EmailMessageDto $message): string
    {
        $headers = [];

        if ($message->from !== null && trim($message->from) !== '') {
            $this->validateEmailAddress($message->from);
            $this->validateOptionalHeaderValue($message->fromName);

            $headers[] = 'From: ' . $this->formatAddress(
                $message->from,
                $message->fromName,
            );
        }

        if ($message->to !== []) {
            $headers[] = 'To: ' . $this->formatAddresses($message->to);
        }

        if ($message->cc !== []) {
            $headers[] = 'Cc: ' . $this->formatAddresses($message->cc);
        }

        // Bcc is intentionally not included in MIME headers.
        // BCC recipients are passed separately to the transport.

        if ($message->replyTo !== null) {
            $this->validateEmailAddress($message->replyTo);
            $this->validateOptionalHeaderValue($message->replyToName);

            $headers[] = 'Reply-To: ' . $this->formatAddress(
                $message->replyTo,
                $message->replyToName,
            );
        }

        $this->validateHeaderValue($message->subject);
        $headers[] = 'Subject: ' . $this->encodeHeader($message->subject);
        $headers[] = 'MIME-Version: 1.0';

        foreach ($message->headers as $name => $value) {
            $this->validateHeader($name, $value);
            if (in_array(strtolower($name), [
                'from', 'to', 'cc', 'bcc', 'reply-to', 'subject',
                'mime-version', 'content-type', 'content-transfer-encoding',
            ], true)) {
                throw new MailException(
                    sprintf('Mail header is managed by the message builder: "%s".', $name),
                );
            }
            $headers[] = $name . ': ' . $value;
        }

        return implode("\r\n", $headers);
    }

    /**
     * @return array{headers: string, body: string}
     */
    private function buildBody(EmailMessageDto $message): array
    {
        if ($message->text === null && $message->html === null) {
            throw new MailException(
                'Email message must contain text or HTML content.',
            );
        }

        $text = $message->text;

        if ($message->html !== null && $text === null) {
            $text = $this->htmlToPlainText($message->html);
        }

        if ($message->attachments !== []) {
            return $this->buildMultipartMixed($message, $text);
        }

        if ($text !== null && $message->html !== null) {
            return $this->buildMultipartAlternative(
                $text,
                $message->html,
            );
        }

        if ($message->html !== null) {
            return $this->buildHtmlBody($message->html);
        }

        if ($text !== null) {
            return $this->buildTextBody($text);
        }

        throw new MailException(
            'Email message must contain text or HTML content.',
        );
    }

    /**
     * @return array{headers: string, body: string}
     */
    private function buildTextBody(string $text): array
    {
        return [
            'headers' => implode("\r\n", [
                'Content-Type: text/plain; charset=UTF-8',
                'Content-Transfer-Encoding: quoted-printable',
            ]),
            'body' => quoted_printable_encode($text),
        ];
    }

    /**
     * @return array{headers: string, body: string}
     */
    private function buildHtmlBody(string $html): array
    {
        return [
            'headers' => implode("\r\n", [
                'Content-Type: text/html; charset=UTF-8',
                'Content-Transfer-Encoding: quoted-printable',
            ]),
            'body' => quoted_printable_encode($html),
        ];
    }

    /**
     * @return array{headers: string, body: string}
     */
    private function buildMultipartAlternative(
        string $text,
        string $html,
    ): array {
        $boundary = $this->boundary();

        $body = [
            '--' . $boundary,
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: quoted-printable',
            '',
            quoted_printable_encode($text),
            '',
            '--' . $boundary,
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: quoted-printable',
            '',
            quoted_printable_encode($html),
            '',
            '--' . $boundary . '--',
        ];

        return [
            'headers' => 'Content-Type: multipart/alternative; boundary="'
                . $boundary . '"',
            'body' => implode("\r\n", $body),
        ];
    }

    /**
     * @return array{headers: string, body: string}
     */
    private function buildMultipartMixed(
        EmailMessageDto $message,
        ?string $text,
    ): array {
        $boundary = $this->boundary();
        $body = [];

        if ($text !== null && $message->html !== null) {
            $alternativeBoundary = $this->boundary();

            $body[] = '--' . $boundary;
            $body[] = 'Content-Type: multipart/alternative; boundary="'
                . $alternativeBoundary . '"';
            $body[] = '';

            $body[] = '--' . $alternativeBoundary;
            $body[] = 'Content-Type: text/plain; charset=UTF-8';
            $body[] = 'Content-Transfer-Encoding: quoted-printable';
            $body[] = '';
            $body[] = quoted_printable_encode($text);
            $body[] = '';

            $body[] = '--' . $alternativeBoundary;
            $body[] = 'Content-Type: text/html; charset=UTF-8';
            $body[] = 'Content-Transfer-Encoding: quoted-printable';
            $body[] = '';
            $body[] = quoted_printable_encode($message->html);
            $body[] = '';

            $body[] = '--' . $alternativeBoundary . '--';
        } elseif ($message->html !== null) {
            $body[] = '--' . $boundary;
            $body[] = 'Content-Type: text/html; charset=UTF-8';
            $body[] = 'Content-Transfer-Encoding: quoted-printable';
            $body[] = '';
            $body[] = quoted_printable_encode($message->html);
        } elseif ($text !== null) {
            $body[] = '--' . $boundary;
            $body[] = 'Content-Type: text/plain; charset=UTF-8';
            $body[] = 'Content-Transfer-Encoding: quoted-printable';
            $body[] = '';
            $body[] = quoted_printable_encode($text);
        } else {
            throw new MailException(
                'Email message must contain text or HTML content.',
            );
        }

        foreach ($message->attachments as $attachment) {
            $body[] = '';
            $body[] = $this->buildAttachment($boundary, $attachment);
        }

        $body[] = '';
        $body[] = '--' . $boundary . '--';

        return [
            'headers' => 'Content-Type: multipart/mixed; boundary="'
                . $boundary . '"',
            'body' => implode("\r\n", $body),
        ];
    }

    private function buildAttachment(
        string $boundary,
        EmailAttachmentDto $attachment,
    ): string {
        if (!is_file($attachment->path)) {
            throw new MailException(
                sprintf(
                    'Attachment file does not exist: %s',
                    $attachment->path,
                ),
            );
        }

        $content = file_get_contents($attachment->path);

        if ($content === false) {
            throw new MailException(
                sprintf(
                    'Unable to read attachment file: %s',
                    $attachment->path,
                ),
            );
        }

        $name = $attachment->name ?? basename($attachment->path);
        $this->validateHeaderValue($name);

        $mimeType = $attachment->mimeType
            ?? $this->detectMimeType($attachment->path);

        if (
            preg_match(
                '~^[a-zA-Z0-9][a-zA-Z0-9!#$&^_.+-]*/[a-zA-Z0-9][a-zA-Z0-9!#$&^_.+-]*$~D',
                $mimeType,
            ) !== 1
        ) {
            throw new MailException('Invalid attachment MIME type.');
        }

        $encodedName = $this->encodeHeader($name);

        return implode("\r\n", [
            '--' . $boundary,
            'Content-Type: ' . $mimeType . '; name="' . $this->escape($encodedName) . '"',
            'Content-Disposition: attachment; filename="' . $this->escape($encodedName) . '"',
            'Content-Transfer-Encoding: base64',
            '',
            rtrim(chunk_split(base64_encode($content), 76, "\r\n")),
        ]);
    }

    private function detectMimeType(string $path): string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($path);

        return $mimeType ?: 'application/octet-stream';
    }

    private function formatAddress(
        string $email,
        ?string $name,
    ): string {
        if ($name === null || $name === '') {
            return $email;
        }

        $this->validateHeaderValue($name);

        // Quote ASCII display names containing special phrase characters.
        if (
            preg_match('/^[\x20-\x7E]*$/', $name) === 1
            && preg_match('/[(),.:;<>@\[\\\\\]"]/', $name) === 1
        ) {
            $displayName = '"' . addcslashes($name, "\\\"") . '"';
        } else {
            $displayName = $this->encodeHeader($name);
        }

        return $displayName . ' <' . $email . '>';
    }

    /**
     * @param EmailAddressDto[] $addresses
     */
    private function formatAddresses(array $addresses): string
    {
        return implode(', ', array_map(
            fn(EmailAddressDto $address): string => $this->formatAddress(
                $address->email,
                $address->name,
            ),
            $addresses,
        ));
    }

    private function encodeHeader(string $value): string
    {
        if (preg_match('/^[\x20-\x7E]*$/', $value) === 1) {
            return $value;
        }

        // RFC 2047 limits each encoded word to 75 characters. Split by Unicode
        // characters so no UTF-8 sequence is cut between encoded words.
        $characters = preg_split('//u', $value, -1, PREG_SPLIT_NO_EMPTY);
        if ($characters === false) {
            throw new MailException('Header value must contain valid UTF-8.');
        }

        $words = [];
        $chunk = '';
        foreach ($characters as $character) {
            if (strlen($chunk . $character) > 45) {
                $words[] = '=?UTF-8?B?' . base64_encode($chunk) . '?=';
                $chunk = '';
            }
            $chunk .= $character;
        }
        if ($chunk !== '') {
            $words[] = '=?UTF-8?B?' . base64_encode($chunk) . '?=';
        }

        return implode("\r\n ", $words);
    }

    private function boundary(): string
    {
        return '=_DBM_' . bin2hex(random_bytes(16));
    }

    private function escape(string $value): string
    {
        return addcslashes($value, "\\\"");
    }

    private function validateEmailAddress(string $address): void
    {
        if (
            preg_match('/[\r\n]/', $address) === 1
            || filter_var($address, FILTER_VALIDATE_EMAIL) === false
        ) {
            throw new MailException(
                sprintf('Invalid email address: "%s".', $address),
            );
        }
    }

    /**
     * @param EmailAddressDto[] $recipients
     */
    private function validateRecipients(array $recipients): void
    {
        foreach ($recipients as $recipient) {
            $this->validateEmailAddress($recipient->email);

            if ($recipient->name !== null) {
                $this->validateHeaderValue($recipient->name);
            }
        }
    }

    private function validateHeader(string $name, string $value): void
    {
        if (
            preg_match("/^[!#$%&'*+.^_`|~0-9A-Za-z-]+$/D", $name) !== 1
            || preg_match('/[\r\n]/', $value) === 1
        ) {
            throw new MailException(
                sprintf('Invalid mail header: "%s".', $name),
            );
        }
    }

    private function validateHeaderValue(string $value): void
    {
        if (preg_match('/[\r\n]/', $value) === 1) {
            throw new MailException('Invalid mail header value.');
        }
    }

    private function validateOptionalHeaderValue(?string $value): void
    {
        if ($value !== null) {
            $this->validateHeaderValue($value);
        }
    }

    private function htmlToPlainText(string $html): string
    {
        $html = preg_replace(
            '~<(script|style)\b[^>]*>.*?</\1\s*>~is',
            '',
            $html,
        ) ?? $html;

        $html = preg_replace(
            '~<\s*br\s*/?\s*>|</\s*(p|div|h[1-6]|li|tr)\s*>~i',
            "\n",
            $html,
        ) ?? $html;

        $text = html_entity_decode(
            strip_tags($html),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8',
        );

        $text = preg_replace('/[ \t]+/', ' ', $text) ?? $text;
        $text = preg_replace('/ *\n */', "\n", $text) ?? $text;
        $text = preg_replace('/\n{3,}/', "\n\n", $text) ?? $text;

        return trim($text);
    }
}
