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

namespace Dbm\Views\Flash;

use Dbm\Infrastructure\Session\SessionManager;

final class FlashBag
{
    public const TYPES = [
        'messageInfo' => 'alert-info',
        'messageWarning' => 'alert-warning',
        'messageDanger' => 'alert-danger',
        'messageSuccess' => 'alert-success',
    ];

    public function __construct(
        private SessionManager $session
    ) {}

    public function set(string $message, string $type = 'messageInfo'): void
    {
        if (!isset(self::TYPES[$type])) {
            $type = 'messageInfo';
        }

        $this->session->setSession($type, $message);
    }

    public function get(?string $type = null): ?array
    {
        if ($type !== null) {
            $message = $this->session->pop($type);

            if ($message !== null) {
                return ['type' => $type, 'message' => $message];
            }
        }

        foreach (self::TYPES as $type => $_) {
            $message = $this->session->pop($type);

            if ($message !== null) {
                return ['type' => $type, 'message' => $message];
            }
        }

        return null;
    }

    public static function cssClass(string $type): string
    {
        return self::TYPES[$type] ?? self::TYPES['messageInfo'];
    }
}
