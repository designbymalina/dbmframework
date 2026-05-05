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

namespace Dbm\Core\Module\Events;

final class ModulesChangedEvent
{
    public function __construct(
        public readonly string $action, // install | uninstall (enable | disable)
        public readonly string $moduleKey
    ) {}
}
