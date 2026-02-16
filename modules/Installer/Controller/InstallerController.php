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
use Dbm\Database\Contracts\DatabaseInterface;
use Dbm\Http\Controller\BaseController;
use Dbm\Support\Traits\LazyLoaderTrait;
use Mod\Installer\InstallerKernel;
use Mod\Installer\InstallerState;
use Mod\Installer\Resolver\InstallerSteps;
use Psr\Http\Message\ResponseInterface;

final class InstallerController extends BaseController
{
    use LazyLoaderTrait;

    public function __construct(
        ?DatabaseInterface $database = null
    ) {
        parent::__construct($database);
    }

    // === Dependencies (lazy) ===

    protected function state(): InstallerState
    {
        return $this->lazy(
            'state',
            fn() => $this->container()->get(InstallerState::class)
        );
    }

    protected function steps(): InstallerSteps
    {
        return $this->lazy(
            'steps',
            fn() => $this->container()->get(InstallerSteps::class)
        );
    }

    /**
     * Installer
     * @routing GET '/install' name: install
     *
     * @return ResponseInterface
     */
    public function index(): ResponseInterface
    {
        $kernel = new InstallerKernel(
            $this->state(),
            $this->steps()->resolve($this->container())
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
     * Doinstaluj moduly - Manualny reset stanu instalatora.
     *
     * @routing GET '/install/restart' name: install_restart
     */
    public function restart(): ResponseInterface
    {
        $scanner = $this->container()->get(PackageScanner::class);

        if (!$scanner->hasPendingPackages()) {
            return $this->redirect('/');
        }

        $this->state()->clear();

        return $this->redirect('/install');
    }
}
