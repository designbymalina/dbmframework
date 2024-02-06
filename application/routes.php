<?php
/*
 * Application: DbM Framework v1.2
 * Author: Arthur Malinowsky (Design by Malina)
 * License: MIT
 * Web page: www.dbm.org.pl
 * Contact: biuro@dbm.org.pl
*/

declare(strict_types=1);

use App\Controller\AboutController;
use App\Controller\BlogController;
use App\Controller\ContactController;
use App\Controller\HomeController;
use App\Controller\IndexController;
use App\Controller\PageController;
use App\Controller\RegulationController;
use Dbm\Classes\Database;
use Dbm\Classes\Router;

return function (Database $database) {
    $uri = $_SERVER['REQUEST_URI'];

    $router = new Router($database);

    $router->addRoute('/', [IndexController::class, 'index']);
    $router->addRoute('/link.html', [IndexController::class, 'linkMethod']);
    $router->addRoute('/home.html', [HomeController::class, 'index']);
    $router->addRoute('/about.html', [AboutController::class, 'index']);
    $router->addRoute('/contact.html', [ContactController::class, 'index']);
    $router->addRoute('/regulation.html', [RegulationController::class, 'index']);
    $router->addRoute('/page', [PageController::class, 'index']);
    $router->addRoute('/page/site', [PageController::class, 'siteMethod']); // ? /page/site.html
    $router->addRoute('/site.html', [PageController::class, 'siteMethod']); // ?
    $router->addRoute('/offer.html', [PageController::class, 'offerMethod']); // /{#},offer.html
    $router->addRoute('/blog', [BlogController::class, 'index']);
    $router->addRoute('/blog/sections', [BlogController::class, 'sectionsMethod']);
    $router->addRoute('/blog/{#},sec,{id}.html', [BlogController::class, 'sectionMethod']); // TODO!
    $router->addRoute('/{#},art,{$}.html', [BlogController::class, 'articleMethod']); // TODO!
    
    $router->dispatch($uri);
};
