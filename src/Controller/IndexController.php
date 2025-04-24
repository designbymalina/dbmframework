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

namespace App\Controller;

use App\Service\IndexService;
use App\Utility\InstallerUtility;
use Dbm\Classes\BaseController;
use Dbm\Classes\Http\Request;
use Dbm\Interfaces\DatabaseInterface;
use Psr\Http\Message\ResponseInterface;

class IndexController extends BaseController
{
    private readonly InstallerUtility $installer;

    public function __construct(
        IndexService $indexService,
        ?DatabaseInterface $database = null
    ) {
        parent::__construct($database);

        $this->installer = new InstallerUtility();
    }

    /**
     * @param IndexService $indexService
     * @return ResponseInterface
     *
     * @Route: "/index"
     */
    public function index(IndexService $indexService): ResponseInterface
    {
        // Create a New Project (templates/index/index.phtml)!
        $this->setFlash('messageInfo', $this->translation->trans('install.alert.application_is_ready'));

        return $this->render('index/start.phtml', [
            'meta' => $indexService->getMetaIndex(),
        ]);
    }

    /**
     * @param IndexService $indexService
     * @return ResponseInterface
     *
     * @Route: "/start"
     */
    public function start(IndexService $indexService): ResponseInterface
    {
        return $this->render('index/start.phtml', [
            'meta' => $indexService->getMetaStart(),
        ]);
    }

    /**
     * @param IndexService $indexService
     * @param Request $request
     * @return ResponseInterface
     *
     * @Route: "/installer"
     */
    public function installer(IndexService $indexService, Request $request): ResponseInterface
    {
        $pathManifest = BASE_DIRECTORY . '_Documents' . DS . 'install' . DS . 'module.json';

        if (class_exists('\\App\\Controller\\InstallController')) {
            $action = $request->getQuery('action');

            if ($action === 'remove') {
                $msg = $this->installer->uninstallModule($pathManifest);

                if (!empty($msg)) {
                    $alert = $indexService->alertMessage($msg);
                    $this->setFlash($alert['type'], $alert['message']);
                }

                $this->installer->waitForModuleState($pathManifest, false);

                return $this->redirect('./start');
            } else {
                $this->setFlash('messageInfo', $this->translation->trans('install.alert.installer_prepared'));
            }
        } else {
            $dirModule = BASE_DIRECTORY . '_Documents' . DS . 'install';
            $pathZip = BASE_DIRECTORY . '_Documents' . DS . 'install.zip';

            $msg = $this->installer->installModule($dirModule, $pathZip, $pathManifest);

            if (!empty($msg)) {
                $alert = $indexService->alertMessage($msg);
                $this->setFlash($alert['type'], $alert['message']);
            }

            $this->installer->waitForModuleState($pathManifest, true);
        }

        return $this->render('index/start.phtml', [
            'meta' => $indexService->getMetaInstaller(),
        ]);
    }
}
