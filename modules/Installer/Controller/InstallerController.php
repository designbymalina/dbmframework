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

use Dbm\Core\Module\Package\PackageScanner;
use Dbm\Http\Controller\BaseController;
use Mod\Installer\InstallerKernel;
use Mod\Installer\InstallerState;
use Mod\Installer\Resolver\InstallerSteps;
use Psr\Http\Message\ResponseInterface;

final class InstallerController extends BaseController
{
    public function __construct(
        private readonly InstallerState $state,
        private readonly InstallerSteps $steps,
        private readonly PackageScanner $scanner,
    ) {}

    /**
     * Installer
     * @routing GET '/install' name: install
     *
     * @return ResponseInterface
     */
    public function index(): ResponseInterface
    {
        $kernel = new InstallerKernel(
            $this->state,
            $this->steps->resolve($this->container())
        );

        if ($this->request()->isPost()) {
            $kernel->handle($this->request()->getParsedBody() ?? []);
        }

        $kernel->boot();

        return $this->render('installer/index.phtml', [
            'progress' => $kernel->progress(),
            'steps' => $kernel->steps(),
            'payload' => $kernel->payload(),
            'currentStep' => $kernel->currentStep(),
            'currentIndex' => $kernel->currentIndex(),
        ]);
    }

    /**
     * @routing GET '/install/restart' name: install_restart
     */
    public function restart(): ResponseInterface
    {
        if (!$this->scanner->hasPendingPackages()) {
            return $this->redirect('home');
        }

        $this->state->clear();

        return $this->redirect('install');
    }
}
