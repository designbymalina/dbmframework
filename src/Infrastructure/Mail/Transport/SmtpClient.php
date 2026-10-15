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

use Dbm\Infrastructure\Mail\Dto\SmtpConfigDto;
use Dbm\Infrastructure\Mail\Exception\MailTransportException;

final class SmtpClient
{
    /** @var resource|null */
    private $connection = null;

    public function __construct(
        private readonly SmtpConfigDto $config,
    ) {}

    /**
     * @param string[] $recipients
     */
    public function send(
        string $sender,
        array $recipients,
        string $data,
    ): void {
        try {
            $this->connect();

            $this->expectResponse([220]);

            $this->command(
                'EHLO ' . $this->getHostname(),
                [250],
            );

            if ($this->config->encryption === 'tls') {
                $this->startTls();

                $this->command(
                    'EHLO ' . $this->getHostname(),
                    [250],
                );
            }

            if ($this->config->username !== null) {
                $this->authenticate();
            }

            $this->command(
                'MAIL FROM:<' . $sender . '>',
                [250],
            );

            foreach ($recipients as $recipient) {
                $this->command(
                    'RCPT TO:<' . $recipient . '>',
                    [250, 251],
                );
            }

            $this->command('DATA', [354]);

            $this->writeData($data);

            $this->expectResponse([250]);

            // The server has accepted the message after the final DATA 250.
            // A QUIT failure must not turn that accepted send into a retryable
            // failure (which could deliver the message twice).
            try {
                $this->command('QUIT', [221]);
            } catch (MailTransportException) {
                // Closing the socket in finally is sufficient here.
            }
        } finally {
            $this->disconnect();
        }
    }

    private function connect(): void
    {
        $scheme = $this->config->encryption === 'ssl'
            ? 'ssl://'
            : 'tcp://';

        $errno = 0;
        $error = '';

        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'peer_name' => $this->config->host,
            ],
        ]);

        $connection = @stream_socket_client(
            $scheme . $this->config->host . ':' . $this->config->port,
            $errno,
            $error,
            $this->config->timeout,
            STREAM_CLIENT_CONNECT,
            $context,
        );

        if ($connection === false) {
            throw new MailTransportException(
                sprintf(
                    'SMTP connection failed (%d): %s',
                    $errno,
                    $error,
                ),
            );
        }

        $this->connection = $connection;

        stream_set_timeout(
            $this->connection,
            $this->config->timeout,
        );
    }

    private function startTls(): void
    {
        $this->command('STARTTLS', [220]);

        $result = @stream_socket_enable_crypto(
            $this->connection,
            true,
            STREAM_CRYPTO_METHOD_TLS_CLIENT,
        );

        if ($result !== true) {
            throw new MailTransportException(
                'Unable to establish SMTP TLS encryption.'
            );
        }
    }

    private function authenticate(): void
    {
        $this->command('AUTH LOGIN', [334]);

        $this->command(
            base64_encode($this->config->username ?? ''),
            [334],
        );

        $this->command(
            base64_encode($this->config->password ?? ''),
            [235],
        );
    }

    /**
     * @param int[] $expectedCodes
     */
    private function command(
        string $command,
        array $expectedCodes,
    ): string {
        if (preg_match('/[\r\n]/', $command) === 1) {
            throw new MailTransportException(
                'Invalid SMTP command.'
            );
        }

        $this->write($command . "\r\n");

        return $this->expectResponse($expectedCodes);
    }

    /**
     * @param int[] $expectedCodes
     */
    private function expectResponse(array $expectedCodes): string
    {
        $response = '';
        $code = null;

        do {
            $line = $this->readLine();
            $response .= $line;

            if (preg_match('/^(\d{3})([ -])/', $line, $matches) !== 1) {
                throw new MailTransportException(
                    'Invalid SMTP server response.'
                );
            }

            $lineCode = (int) $matches[1];
            if ($code !== null && $lineCode !== $code) {
                throw new MailTransportException(
                    'Inconsistent SMTP response codes.'
                );
            }
            $code = $lineCode;
            $isLastLine = $matches[2] === ' ';
        } while (!$isLastLine);

        if (!in_array($code, $expectedCodes, true)) {
            throw new MailTransportException(
                sprintf(
                    'Unexpected SMTP response %d: %s',
                    $code,
                    trim($response),
                ),
            );
        }

        return $response;
    }

    private function readLine(): string
    {
        if (!is_resource($this->connection)) {
            throw new MailTransportException(
                'SMTP connection is not available.'
            );
        }

        $line = fgets($this->connection, 515);

        if ($line === false) {
            throw new MailTransportException(
                'Failed to read SMTP server response.'
            );
        }

        $metadata = stream_get_meta_data($this->connection);

        if ($metadata['timed_out']) {
            throw new MailTransportException(
                'SMTP operation timed out.'
            );
        }

        return $line;
    }

    private function write(string $data): void
    {
        if (!is_resource($this->connection)) {
            throw new MailTransportException(
                'SMTP connection is not available.'
            );
        }

        $length = strlen($data);
        $offset = 0;

        while ($offset < $length) {
            $written = fwrite(
                $this->connection,
                substr($data, $offset),
            );

            if ($written === false || $written === 0) {
                throw new MailTransportException(
                    'Failed to write to SMTP server.'
                );
            }

            $offset += $written;
        }
    }

    private function writeData(string $data): void
    {
        $data = str_replace(
            ["\r\n", "\r"],
            "\n",
            $data,
        );

        $data = str_replace("\n", "\r\n", $data);

        // Escape lines beginning with a dot (SMTP DATA transparency).
        $data = preg_replace('/(?m)^\./', '..', $data) ?? $data;

        $data = rtrim($data, "\r\n") . "\r\n";

        $this->write($data . ".\r\n");
    }

    private function getHostname(): string
    {
        $hostname = gethostname();

        return $hostname !== false && $hostname !== ''
            ? $hostname
            : 'localhost';
    }

    private function disconnect(): void
    {
        if (is_resource($this->connection)) {
            fclose($this->connection);
        }

        $this->connection = null;
    }
}
