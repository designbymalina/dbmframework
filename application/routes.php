<?php
/*
 * Application: DbM Framework v2.1
 * Author: Arthur Malinowsky (Design by Malina)
 * License: MIT
 * Web page: www.dbm.org.pl
 * Contact: biuro@dbm.org.pl
*/

declare(strict_types=1);

use App\Controller\AccountController;
use App\Controller\AuthenticationController;
use App\Controller\BlogController;
use App\Controller\GalleryController;
use App\Controller\IndexController;
use App\Controller\PageController;
use App\Controller\PanelController;
use App\Controller\PanelGalleryController;
use App\Controller\SubpageController;
use App\Controller\UserController;
use Dbm\Classes\Router;
use Dbm\Interfaces\DatabaseInterface;

return function (?DatabaseInterface $database) {
    $uri = $_SERVER['REQUEST_URI'];

    $router = new Router($database);

    $router->addRoute('/', [IndexController::class, 'index']);
    $router->addRoute('/home', [IndexController::class, 'home']);
    $router->addRoute('/link.html', [IndexController::class, 'linkMethod']);
    $router->addRoute('/about.html', [SubpageController::class, 'about']);
    $router->addRoute('/contact.html', [SubpageController::class, 'contact']);
    $router->addRoute('/regulation.html', [SubpageController::class, 'regulation']);
    $router->addRoute('/page', [PageController::class, 'index']);
    $router->addRoute('/page/site', [PageController::class, 'siteMethod']);
    $router->addRoute('/{#}.site.html', [PageController::class, 'siteMethod']);
    $router->addRoute('/{#}.offer.html', [PageController::class, 'offerMethod']);
    $router->addRoute('/blog', [BlogController::class, 'index']);
    $router->addRoute('/blog/sections', [BlogController::class, 'sectionsMethod']);
    $router->addRoute('/blog/{#}.sec.{id}.html', [BlogController::class, 'sectionMethod']);
    $router->addRoute('/{#}.art.{id}.html', [BlogController::class, 'articleMethod']);
    $router->addRoute('/register', [AuthenticationController::class, 'register']);
    $router->addRoute('/register/signup', [AuthenticationController::class, 'signupMethod']);
    $router->addRoute('/register/verified.php', [AuthenticationController::class, 'verifiedMethod']);
    $router->addRoute('/login', [AuthenticationController::class, 'login']);
    $router->addRoute('/login/signin', [AuthenticationController::class, 'signinMethod']);
    $router->addRoute('/login/logout', [AuthenticationController::class, 'logoutMethod']);
    $router->addRoute('/account', [AccountController::class, 'index']);
    $router->addRoute('/account/profileChange', [AccountController::class, 'profileChangeMethod']);
    $router->addRoute('/account/passwordChange', [AccountController::class, 'passwordChangeMethod']);
    $router->addRoute('/user.{id}.html', [UserController::class, 'index']);
    $router->addRoute('/gallery', [GalleryController::class, 'index']);
    $router->addRoute('/gallery/ajaxLoadData', [GalleryController::class, 'ajaxLoadDataMethod']);
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
    $router->addRoute('/panel/ajaxUploadImage', [PanelController::class, 'ajaxUploadImageMethod']);
    $router->addRoute('/panel/ajaxDeleteImage', [PanelController::class, 'ajaxDeleteImageMethod']);
    $router->addRoute('/panel/toolsLogs', [PanelController::class, 'toolsLogsMethod']);
    $router->addRoute('/panel/manageGallery', [PanelGalleryController::class, 'manageGalleryMethod']);
    $router->addRoute('/panel/addOrEditPhoto', [PanelGalleryController::class, 'addOrEditPhotoMethod']);
    $router->addRoute('/panel/addPhoto', [PanelGalleryController::class, 'addPhotoMethod']);
    $router->addRoute('/panel/editPhoto', [PanelGalleryController::class, 'editPhotoMethod']);
    $router->addRoute('/panel/ajaxDeletePhoto', [PanelGalleryController::class, 'ajaxDeletePhotoMethod']);

    $router->dispatch($uri);
};
