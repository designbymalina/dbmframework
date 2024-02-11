<?php
/*
 * Application: DbM Framework v2.1
 * Author: Arthur Malinowsky (Design by Malina)
 * License: MIT
 * Web page: www.dbm.org.pl
 * Contact: biuro@dbm.org.pl
*/

declare(strict_types=1);

use App\Controller\AboutController;
use App\Controller\AccountController;
use App\Controller\BlogController;
use App\Controller\ContactController;
use App\Controller\HomeController;
use App\Controller\IndexController;
use App\Controller\LoginController;
use App\Controller\PageController;
use App\Controller\PanelController;
use App\Controller\RegisterController;
use App\Controller\RegulationController;
use App\Controller\UserController;
use Dbm\Classes\Router;
use Dbm\Interfaces\DatabaseInterface;

return function (DatabaseInterface $database) {
    $uri = $_SERVER['REQUEST_URI'];

    $router = new Router($database);

    $router->addRoute('/', [IndexController::class, 'index']);
    $router->addRoute('/link.html', [IndexController::class, 'linkMethod']);
    $router->addRoute('/home', [HomeController::class, 'index']);
    $router->addRoute('/about.html', [AboutController::class, 'index']);
    $router->addRoute('/contact.html', [ContactController::class, 'index']);
    $router->addRoute('/regulation.html', [RegulationController::class, 'index']);
    $router->addRoute('/page', [PageController::class, 'index']);
    $router->addRoute('/page/site', [PageController::class, 'siteMethod']);
    $router->addRoute('/{#},site.html', [PageController::class, 'siteMethod']);
    $router->addRoute('/{#},offer.html', [PageController::class, 'offerMethod']);
    $router->addRoute('/blog', [BlogController::class, 'index']);
    $router->addRoute('/blog/sections', [BlogController::class, 'sectionsMethod']);
    $router->addRoute('/blog/{#},sec,{id}.html', [BlogController::class, 'sectionMethod']);
    $router->addRoute('/{#},art,{id}.html', [BlogController::class, 'articleMethod']);
    $router->addRoute('/login', [LoginController::class, 'index']);
    $router->addRoute('/login/signin', [LoginController::class, 'signinMethod']);
    $router->addRoute('/login/logout', [LoginController::class, 'logoutMethod']);
    $router->addRoute('/account', [AccountController::class, 'index']);
    $router->addRoute('/register', [RegisterController::class, 'index']);
    $router->addRoute('/register/signup', [RegisterController::class, 'signupMethod']);
    $router->addRoute('/register/verified.php', [RegisterController::class, 'verifiedMethod']);
    $router->addRoute('/user,{id}.html', [UserController::class, 'index']);
    $router->addRoute('/panel', [PanelController::class, 'index']);
    $router->addRoute('/panel/managePage', [PanelController::class, 'managePageMethod']);
    $router->addRoute('/panel/createPage', [PanelController::class, 'createPageMethod']);
    $router->addRoute('/panel/createOrEditPage', [PanelController::class, 'createOrEditPageMethod']);
    $router->addRoute('/panel/editPage', [PanelController::class, 'editPageMethod']);
    $router->addRoute('/panel/ajaxDeleteFile', [PanelController::class, 'ajaxDeleteFileMethod']);
    $router->addRoute('/panel/manageBlog', [PanelController::class, 'manageBlogMethod']);
    $router->addRoute('/panel/createBlog', [PanelController::class, 'createBlogMethod']);
    $router->addRoute('/panel/createOrEditBlog', [PanelController::class, 'createOrEditBlogMethod']);
    $router->addRoute('/panel/editBlog', [PanelController::class, 'editBlogMethod']);
    $router->addRoute('/panel/ajaxDeleteArticle', [PanelController::class, 'ajaxDeleteArticleMethod']);
    $router->addRoute('/panel/manageBlogSections', [PanelController::class, 'manageBlogSectionsMethod']);
    $router->addRoute('/panel/createSection', [PanelController::class, 'createSectionMethod']);
    $router->addRoute('/panel/createOrEditBlogSection', [PanelController::class, 'createOrEditBlogSectionMethod']);
    $router->addRoute('/panel/editSection', [PanelController::class, 'editSectionMethod']);
    $router->addRoute('/panel/ajaxDeleteSection', [PanelController::class, 'ajaxDeleteSectionMethod']);
    $router->addRoute('/panel/tabels.html', [PanelController::class, 'tabelsMethod']);
    $router->addRoute('/panel/ajaxUploadImage', [PanelController::class, 'ajaxUploadImageMethod']);
    $router->addRoute('/panel/ajaxDeleteImage', [PanelController::class, 'ajaxDeleteImageMethod']);

    $router->dispatch($uri);
};
