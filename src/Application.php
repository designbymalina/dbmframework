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

namespace Dbm;

use Dbm\Core\DependencyContainer;
use Dbm\Core\Module\ModuleManager;
use Dbm\Http\Message\Request;
use Dbm\Infrastructure\Session\SessionManager;
use Dbm\Kernel\Contracts\KernelInterface;
use Psr\Http\Message\ResponseInterface;

class Application
{
    public function __construct(
        private DependencyContainer $container
    ) {}

    public function run(): ResponseInterface
    {
        $this->container->get(SessionManager::class)->start();
        $this->container->get(ModuleManager::class)->boot();

        // @INFO Create Request
        $request = Request::fromGlobals();

        // @INFO Run Kernel
        $kernel = $this->container->get(KernelInterface::class);

        return $kernel->handle($request);
    }
}
