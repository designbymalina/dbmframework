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

namespace Mod\Installer\Controller;

use Dbm\Database\Contracts\DatabaseInterface;
use Dbm\Http\Controller\BaseController;
use Mod\Installer\InstallerKernel;
use Mod\Installer\InstallerState;
use Mod\Installer\Resolver\InstallerSteps;
use Psr\Http\Message\ResponseInterface;

final class InstallerController extends BaseController
{
    public function __construct(
        ?DatabaseInterface $database = null
    ) {
        parent::__construct($database);
    }

    public function index(): ResponseInterface
    {
        $resolver = $this->container()->get(InstallerSteps::class);

        $kernel = new InstallerKernel(
            $this->container()->get(InstallerState::class),
            $resolver->resolve($this->container())
        );

        $kernel->boot();

        if ($this->request()->isPost()) {
            $kernel->handle($this->request()->getParsedBody() ?? []);
        }

        return $this->render('installer/index.phtml', [
            'progress' => $kernel->progress(),
            'steps' => $kernel->steps(),
            'payload' => $kernel->payload(),
            'currentStep' => $kernel->currentStep(),
            'currentIndex' => $kernel->currentIndex(),
        ]);
    }
}
