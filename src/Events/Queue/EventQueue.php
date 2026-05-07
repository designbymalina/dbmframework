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
 * @INFO Kolejka async do późniejszego wykonania, klasa przydatna przy:
 * mail queue, webhooki, retry jobs, async processing, scheduler, CLI workers.
 *
 * Przykład użycia:
 *
 * final class TestEvent
 * {
 *     public function __construct(
 *         public string $message
 *     ) {}
 * }
 *
 * $queue = new EventQueue();
 * $queue->push(
 *     new TestEvent('Hello Queue')
 * );
 *
 * $events = $queue->pullAll();
 */

declare(strict_types=1);

namespace Dbm\Events\Queue;

use Dbm\Core\Paths;

final class EventQueue
{
    private const EVENT_FILE = 'dbm_event_queue.json';

    private string $file;

    public function __construct(?string $file = null)
    {
        $path = Paths::joinPaths(
            Paths::basePath(),
            'storage',
            'events'
        );

        if (!is_dir($path)) {
            mkdir($path, 0o775, true);
        }

        if (!is_writable($path)) {
            throw new \RuntimeException(
                "Directory '{$path}' is not writable."
            );
        }

        $this->file = $file ?? Paths::joinPaths(
            $path,
            self::EVENT_FILE
        );
    }

    public function push(object $event): void
    {
        $queue = $this->read();

        $queue[] = [
            'class' => $event::class,
            'data' => serialize($event),
        ];

        $this->write($queue);
    }

    /**
     * @return array<int, mixed>
     */
    public function pullAll(): array
    {
        $queue = $this->read();

        // czyścimy kolejkę
        if (is_file($this->file)) {
            unlink($this->file);
        }

        return array_map(
            static fn(array $event): mixed => unserialize($event['data']),
            $queue
        );
    }

    public function setFile(string $file): void
    {
        $this->file = $file;
    }

    /**
     * @return array<int, array{class: string, data: string}>
     */
    private function read(): array
    {
        if (!is_file($this->file)) {
            return [];
        }

        $content = file_get_contents($this->file);

        if ($content === false || trim($content) === '') {
            return [];
        }

        $data = json_decode($content, true);

        return is_array($data) ? $data : [];
    }

    /**
     * @param array<int, array{class: string, data: string}> $queue
     */
    private function write(array $queue): void
    {
        // nie zapisujemy pustej kolejki
        if ($queue === []) {
            if (is_file($this->file)) {
                unlink($this->file);
            }

            return;
        }

        file_put_contents(
            $this->file,
            json_encode($queue, JSON_THROW_ON_ERROR),
            LOCK_EX
        );
    }
}
