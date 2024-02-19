<?php
/*
 * Twig home page: https://twig.symfony.com/
 *
 * Prerequisites
 * Twig 3.x needs at least PHP 7.2.5 to run.
 * 
 * The recommended way to install Twig is via Composer:
 * composer require "twig/twig:^3.0"
 *
*/

declare(strict_types=1);

namespace Dbm\Classes;

use Dbm\Interfaces\DatabaseInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class BaseController
{
    private const PATH_VIEW = BASE_DIRECTORY . 'templates'. DS;

    private $database;

    public function __construct(DatabaseInterface $database)
    {
        $this->database = $database;
    }

    protected function render(string $fileName, array $data = []): void
    {
        $loader = new FilesystemLoader(self::PATH_VIEW);
        $twig = new Environment($loader);

        echo $twig->render($fileName, $data);
    }
}
