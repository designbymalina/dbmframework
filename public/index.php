<?php
/*
 * Application: DbM Framework v2.1
 * Author: Arthur Malinowsky (Design by Malina)
 * License: MIT
 * Web page: www.dbm.org.pl
 * Contact: biuro@dbm.org.pl
*/

### DECLARATION strict typing ###
declare(strict_types=1);

### SESSION ###
session_start();
### OUTPUT BUFFERING for htmlErrorHandler()
ob_start();

### BASE PATH ###
define('DS', DIRECTORY_SEPARATOR);
define('BASE_DIRECTORY', dirname(__DIR__) . DS);

### FUNCTIONS - application starting
require(BASE_DIRECTORY . 'application' . DS . 'start.php');

### ERROR HANDLING, logging error handling ###
// Time zone; Info: Not every server needed!
@date_default_timezone_set('Europe/Warsaw');
// Error handler
error_reporting(E_ALL);
ini_set('display_errors', '1');
set_error_handler('reportingErrorHandler');

### STARTING APPLICATION, configuration and autoloading.
$pathConfig = BASE_DIRECTORY . '.env';
$pathAutoload = BASE_DIRECTORY . 'vendor' . DS . 'autoload.php';

// Configuration settings
configurationSettings($pathConfig);

// Autoloading with and without Composer
autoloadingWithWithoutComposer($pathAutoload);

// Routing and database connection
use Dbm\Classes\Database;
use Dbm\Classes\DotEnv;

(new DotEnv($pathConfig))->load();

$database = new Database();

$routes = require(BASE_DIRECTORY . 'application' . DS . 'routes.php');
$routes($database);
