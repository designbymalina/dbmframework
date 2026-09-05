<?php

/**
 * DBM Framework
 *
 * @license MIT
 * @link https://www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Controller;

use Dbm\Http\Controller\BaseController;
use Psr\Http\Message\ResponseInterface;

class WelcomeController extends BaseController
{
    /**
     * Full View Template Rendering
     *
     * This method demonstrates full HTML rendering of a view template.
     * Inheriting from BaseController naturally provides the $this->render() helper method,
     * which invokes the built-in DBM template engine under the hood.
     * This approach is perfect for traditional websites and comprehensive web applications
     * requiring a dynamic user interface.
     */
    public function index(): ResponseInterface
    {
        return $this->render('base.phtml', [
            'title' => 'DBM Framework - Welcome',
            'message' => 'Welcome to your first minimal and modular PHP application!',
        ]);
    }
}
