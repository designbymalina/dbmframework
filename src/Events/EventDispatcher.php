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
 * Usage:
 * ------------
 * // 1. Register events in bootstrap/events.php:
 * return [
 *     UserLoggedIn::class => [LogUserLogin::class],
 *     UserRegistered::class => [SendWelcomeEmail::class],
 * ];
 *
 * // 2. Dispatch events in code:
 * $dispatcher = new EventDispatcher();
 * $provider = new EventServiceProvider();
 * $provider->register($dispatcher);
 *
 * // Fire immediately
 * $dispatcher->dispatch(new UserLoggedIn($userId, $email));
 *
 * // Add to async queue
 * $dispatcher->dispatchAsync(new UserRegistered($userId, $email));
 *
 * // Process queue (manually or by worker)
 * $dispatcher->processQueue();
 *
 * Implementation notes:
 * ---------------------
 * - dispatch() executes listeners immediately (sync)
 * - dispatchAsync() stores serialized events in /data/events/dbm_event_queue.json
 * - processQueue() reads and clears the queue
 * - The queue directory is auto-created and validated for write access
 *
 * Recommended usage:
 * ------------------
 * - Use sync events for in-process logic (logging, metrics)
 * - Use async events for background tasks (email sending, notifications)
 * - Run processQueue() from CRON or a long-running worker
 *
 * - - - EXAMPLE Event & Listener - - -
 *
 * namespace App\Events;
 *
 * class UserLoggedIn
 * {
 *     public function __construct(public int $userId, public string $email) {}
 * }
 *
 * - - -
 * namespace App\Listeners;
 *
 * class LogUserLogin implements ListenerInterface
 * {
 *     public function handle(object $event): void
 *     {
 *         if ($event instanceof UserLoggedIn) {
 *             $logger = new Logger();
 *             $logger->info("User {$event->email} logged in (ID {$event->userId})");
 *         }
 *     }
 * }
 */

declare(strict_types=1);

namespace Dbm\Events;

use Dbm\Core\DependencyContainer;
use Dbm\Events\Queue\EventQueue;

class EventDispatcher
{
    /** @var array<string, array<callable|object|string>> */
    protected array $listeners = [];

    protected EventQueue $queue;

    public function __construct(
        protected ?DependencyContainer $container = null, // @INFO Docelowo można usunąć null, ale to wymaga zmian w starszym kodzie.
        ?EventQueue $queue = null
    ) {
        $this->queue = $queue ?? new EventQueue();
    }

    /**
     * Register a listener for an event.
     *
     * Supports listener objects, callables and class names.
     *
     * Class-name listeners are resolved through the dependency container
     * when the event is dispatched.
     */
    public function listen(string $event, callable|object|string $listener): void
    {
        $this->listeners[$event][] = $listener;
    }

    /**
     * [SYNC] Natychmiast wywołuje listener (nic nie zapisuje)
     */
    public function dispatch(object $event): void
    {
        $eventClass = $event::class;

        foreach ($this->listeners[$eventClass] ?? [] as $listener) {
            if (is_string($listener)) {
                if ($this->container === null) {
                    throw new \RuntimeException(
                        'DependencyContainer is required to resolve event listeners.'
                    );
                }

                $listener = $this->container->get($listener);
            }


            if (is_callable($listener)) {
                $listener($event);
                continue;
            }

            if (method_exists($listener, 'handle')) {
                $listener->handle($event);
            }
        }
    }

    /**
     * [ASYNC] Dodaje event do kolejki (zapisuje do JSON)
     */
    public function dispatchAsync(object $event): void
    {
        $this->queue->push($event);
    }

    /**
     * [WORKER] Czyta plik, wykonuje eventy, a następnie czyści plik
     */
    public function processQueue(): void
    {
        foreach ($this->queue->pullAll() as $event) {
            $this->dispatch($event);
        }
    }
}
