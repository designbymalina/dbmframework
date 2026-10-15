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

use Dbm\Infrastructure\Mail\Contract\MailerInterface;
use Dbm\Infrastructure\Mail\Contract\MailTransportInterface;
use Dbm\Infrastructure\Mail\Dto\EmailMessageDto;

final class Mailer implements MailerInterface
{
    public function __construct(
        private MailTransportInterface $transport
    ) {}

    public function send(EmailMessageDto $params): bool
    {
        return $this->transport->send($params);
    }
}
