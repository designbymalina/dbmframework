<?php

/**
 * DBM Framework
 *
 * @license MIT
 * @link https://www.dbm.org.pl
 */

declare(strict_types=1);

use Dbm\Core\DotEnv;
use Dbm\Core\Paths;
use Dbm\Http\Emitter\ResponseEmitter;

$baseDirectory = realpath(dirname(__DIR__));

if ($baseDirectory === false) {
    http_response_code(500);
    echo 'Cannot resolve base directory.';
    exit;
}

$baseDirectory = rtrim(str_replace('\\', '/', $baseDirectory), '/');

require_once $baseDirectory . '/bootstrap/runtime.php';

initRuntime($baseDirectory);

require_once $baseDirectory . '/../vendor/autoload.php';

require_once $baseDirectory . '/bootstrap/support.php';

Paths::setBasePath($baseDirectory);

$envPath = Paths::basePath() . '/.env';

if (file_exists($envPath)) {
    (new DotEnv($envPath))->load();
}

$appFactory = require Paths::basePath() . '/bootstrap/app.php';

$app = $appFactory();

$response = $app->run();

(new ResponseEmitter())->emit($response);
