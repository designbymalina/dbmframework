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

namespace Dbm\Events;

use Dbm\Events\Security\SecurityEvent;
use Dbm\Infrastructure\Log\Logger;

final class EventSecurityLogger
{
    private string $dirTmp;
    private string $logFile;

    private int $limit = 5;  // max forbidden access attempts
    private int $windowSeconds = 600; // 10 min.

    public function __construct(
        private readonly Logger $logger
    ) {
        $this->dirTmp = BASE_DIRECTORY . '/var/tmp/';
        $this->logFile = $this->dirTmp . 'access_attempt.log';

        if (!is_dir($this->dirTmp)) {
            mkdir($this->dirTmp, 0o775, true);
        }
    }

    public function handle(SecurityEvent $event): void
    {
        if ($event->type !== SecurityEvent::UNAUTHORIZED) {
            return;
        }

        $logs = $this->loadLogs();
        $logs = array_filter(
            $logs,
            fn($entry) => $entry['timestamp'] > ($event->timestamp - $this->windowSeconds)
        );

        $logs[] = [
            'ip' => $event->ip,
            'timestamp' => $event->timestamp,
            'uri' => $event->uri,
        ];

        $this->saveLogs($logs);

        $count = count(array_filter(
            $logs,
            fn($entry) => $entry['ip'] === $event->ip
        ));

        if ($count > $this->limit) {
            $this->logger->alert(
                "Too many access violations from IP: {$event->ip}"
            );
            $this->addToBlacklist($event->ip);
        } else {
            $user = $event->userId ? "User ID: {$event->userId}" : "Guest";
            $this->logger->alert(
                "Forbidden access attempt - {$user}, IP: {$event->ip}, URI: {$event->uri}"
            );
        }
    }

    private function loadLogs(): array
    {
        if (!file_exists($this->logFile)) {
            return [];
        }

        return json_decode(file_get_contents($this->logFile), true) ?? [];
    }

    private function saveLogs(array $logs): void
    {
        file_put_contents($this->logFile, json_encode($logs), LOCK_EX);
    }

    // INFO! Można rozbudować, dodać blokadę itp. (obecnie dla testu tylko zapisuje dane)
    private function addToBlacklist(string $ip): void
    {
        $file = $this->dirTmp . 'access_blacklist.txt';

        $current = file_exists($file)
            ? file($file, FILE_IGNORE_NEW_LINES)
            : [];

        if (!in_array($ip, $current, true)) {
            file_put_contents($file, $ip . PHP_EOL, FILE_APPEND | LOCK_EX);
        }
    }
}
