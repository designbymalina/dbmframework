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

namespace Dbm\Infrastructure\Sender;

use Dbm\Infrastructure\Sender\Dto\EmailMessageDto;

interface MailerInterface
{
    /**
     * Sends an email message.
     *
     * This interface abstracts the mail transport layer (e.g. PHPMailer, SMTP API, etc.).
     * The framework depends only on this contract, not on any specific implementation.
     *
     * @param EmailMessageDto $params Data required to build and send the email
     *
     * @return bool True on success, false on failure
     */
    public function send(EmailMessageDto $params): bool;
}
