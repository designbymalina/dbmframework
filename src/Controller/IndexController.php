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
use Dbm\Classes\BaseController;
use Dbm\Interfaces\DatabaseInterface;
use Psr\Http\Message\ResponseInterface;

class IndexController extends BaseController
{
    public function __construct(
        IndexService $indexService,
        ?DatabaseInterface $database = null
    ) {
        parent::__construct($database);
    }

    /**
     * @param IndexService $indexService
     * @return ResponseInterface
     *
     * @Route: "/index"
     */
    public function index(IndexService $indexService): ResponseInterface
    {
        // TEMP: Remove flash and redirect.
        $this->setFlash('messageInfo', 'Your application is now ready and you can start working on a new project. Optionally, proceed to installing the DbM CMS content management system.');
        return $this->redirect('./start');

        // START: Unselect the code and create a New Project.
        /* return $this->render('index/index.phtml', [
            'meta' => $indexService->getMetaIndex(),
        ]); */
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
     * @return ResponseInterface
     *
     * @Route: "/step"
     */
    public function step(IndexService $indexService): ResponseInterface
    {
        $filePath = BASE_DIRECTORY . 'modules' . DS . 'CMSLite' . DS . 'module.json';
        // TODO! $indexService->prepareCMSLiteModule()

        $this->setFlash('messageInfo', sprintf(
            'DbM CMS Lite module is not available. Download it from the official website or GitHub and place it in: <span class="text-danger">%s</span>',
            str_replace(BASE_DIRECTORY, '', dirname($filePath))
        ));

        return $this->render('index/start.phtml', [
            'meta' => $indexService->getMetaStep(),
        ]);
    }
}
