<?php

/**
 * DBM Framework
 *
 * @license MIT
 * @link https://www.dbm.org.pl
 */

declare(strict_types=1);

use Dbm\Core\Paths;

$baseDirectory = realpath(dirname(__DIR__));

require_once $baseDirectory . '/vendor/autoload.php';

Paths::setBasePath($baseDirectory);

$appFactory = require __DIR__ . '/bootstrap/app.php';

$app = $appFactory();

$response = $app->run();

$response->send();
