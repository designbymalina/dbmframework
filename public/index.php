<?php
/*
 * Application: DbM Framework v1.2
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

### BASE PATH AND FILE NAME ###
isset($_GET['url']) ? $file_basename = basename(str_replace('/', ',', $_GET['url']), '.html') : $file_basename = 'index';

define('DS', DIRECTORY_SEPARATOR);
define('BASE_DIRECTORY', str_replace('public' . DS, '', __DIR__ . DS));
define('BASE_FILE', $file_basename);

### FUNCTIONS - application starting, template engine, etc
require(BASE_DIRECTORY . 'library' . DS . 'dbmframework/methods/StartMethod.php');
require(BASE_DIRECTORY . 'library' . DS . 'dbmframework/methods/TemplateMethod.php'); // Template methods *TODO! Do poprawki, czy jest Ok?

### ERROR HANDLING, logging error handling ###
/* Time zone; Info: Not every server needed! */
@date_default_timezone_set('Europe/Warsaw');
/* Error handler */
error_reporting(E_ALL);
ini_set('display_errors', '1');
set_error_handler('reportingErrorHandler');

### STARTING APPLICATION, configuration and autoloading ###
$pathConfig = BASE_DIRECTORY . 'config' . DS . 'config.php';
$pathAutoload = BASE_DIRECTORY . 'vendor' . DS . 'autoload.php';

// Configuration settings
configurationSettings($pathConfig);

// Autoloading with and without Composer
autoloadingWithWithoutComposer($pathAutoload);

### RENDER PAGE ###
use Dbm\Classes\RoutClass;

new RoutClass();
