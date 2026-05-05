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

use Dbm\Core\Paths;

class EventServiceProvider
{
    public function register(EventDispatcher $dispatcher): void
    {
        $file = Paths::joinPaths(Paths::basePath(), 'bootstrap', 'events.php');

        if (!file_exists($file)) {
            return;
        }

        $mappings = require $file;

        foreach ($mappings as $event => $listeners) {
            foreach ((array) $listeners as $listener) {
                $dispatcher->listen($event, new $listener());
            }
        }
    }
}
