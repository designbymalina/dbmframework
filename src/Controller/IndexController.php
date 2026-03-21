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
use Dbm\Http\Controller\BaseController;
use Dbm\Views\Flash\FlashBag;
use Psr\Http\Message\ResponseInterface;

class IndexController extends BaseController
{
    public function __construct(
        private readonly IndexService $service,
        private readonly FlashBag $flash
    ) {}

    /**
     * Index page
     * @routing GET '/' name: home
     *
     * @return ResponseInterface
     */
    public function index(): ResponseInterface
    {
        // Create a New Project (templates/index/index.phtml)!
        $this->flash->set('Your application is now ready and you can start working on a new project.');

        return $this->render('index/start.phtml', [
            'meta' => $this->service->getMetaIndex(),
        ]);
    }

    /**
     * Start page
     * @routing GET '/start' name: start
     *
     * @return ResponseInterface
     */
    public function start(): ResponseInterface
    {
        return $this->render('index/start.phtml', [
            'meta' => $this->service->getMetaStart(),
        ]);
    }
}
