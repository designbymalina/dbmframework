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
use Dbm\Database\Contracts\DatabaseInterface;
use Dbm\Http\Controller\BaseController;
use Psr\Http\Message\ResponseInterface;

class IndexController extends BaseController
{
    public function __construct(
        ?DatabaseInterface $database = null
    ) {
        parent::__construct($database);
    }

    /**
     * Index page
     * @routing GET '/' name: index
     *
     * @param IndexService $indexService
     * @return ResponseInterface
     */
    public function index(IndexService $indexService): ResponseInterface
    {
        // Create a New Project (templates/index/index.phtml)!
        $this->setFlash('Your application is now ready and you can start working on a new project.');

        return $this->render('index/start.phtml', [
            'meta' => $indexService->getMetaIndex(),
        ]);
    }

    /**
     * Start page
     * @routing GET '/start' name: start
     *
     * @param IndexService $indexService
     * @return ResponseInterface
     */
    public function start(IndexService $indexService): ResponseInterface
    {
        return $this->render('index/start.phtml', [
            'meta' => $indexService->getMetaStart(),
        ]);
    }
}
