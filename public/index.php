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
define('BASE_DIRECTORY', str_replace('public' . DS, '', realpath(dirname(__FILE__)) . DS));
define('BASE_FILE', $file_basename);

### ERROR HANDLING, logging error handling ###
require(BASE_DIRECTORY . 'library' . DS . 'dbmframework/methods/StartMethod.php');

function reportingErrorHandler($errLevel, $errMessage, $errFile, $errLine)
{
    $date = date('Y-m-d H:i:s');
    $file = date('Ymd') . '_' . BASE_FILE . '.log';
    $path = BASE_DIRECTORY . 'var' . DS . 'log' . DS . $file;

    $errorHandler = "DATE: $date, level: $errLevel\n File: $errFile on line $errLine\n Message: $errMessage\n";

    $handle = fopen($path, 'a');
    fwrite($handle, $errorHandler);
    fclose($handle);

    if (!empty($errLine)) {
        htmlErrorHandler($errMessage, $errFile, $errLine);
    }
}

/* Time zone; Info: Not every server needed! */
@date_default_timezone_set('Europe/Warsaw');
/* Error handler */
error_reporting(E_ALL);
ini_set('display_errors', '1');
set_error_handler('reportingErrorHandler');

### START, configuration files ###
require(BASE_DIRECTORY . 'config' . DS . 'config.php');
require(BASE_DIRECTORY . 'vendor' . DS . 'autoload.php');
/* Template methods */
require(BASE_DIRECTORY . 'library' . DS . 'dbmframework/methods/TemplateMethod.php');

### RENDER PAGE ###
use Dbm\Classes\RoutClass;

new RoutClass();
