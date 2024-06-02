<?php

declare(strict_types=1);

// TODO! Maybe remove the definition from the entire project?
define('DS', DIRECTORY_SEPARATOR);
define('BASE_DIRECTORY', str_replace('application' . DS, '', __DIR__ . DS));

require(BASE_DIRECTORY . 'application' . DS . 'start.php');

$pathConfig = BASE_DIRECTORY . '.env';
$pathAutoload = BASE_DIRECTORY . 'vendor' . DS . 'autoload.php';

configurationSettings($pathConfig);
autoloadingWithWithoutComposer($pathAutoload);

### START
use Dbm\Classes\DotEnv;

// Environment Variables
$dotEnv = new DotEnv($pathConfig);
$dotEnv->load();

### CONSOLE COMMANDS
// Example command from folder: application> php console.php ConsoleCommand executeCommand

if (!empty($argv) && (count($argv) > 1)) {
    $argvClass = $argv[1];
    !empty($argv[2]) ? $argvMethod = $argv[2] : $argvMethod = 'null';

    $fileClass = BASE_DIRECTORY . 'src' . DS . 'Command' . DS . $argvClass. '.php';
    $class = "App\\Command\\" . $argvClass;

    if (file_exists($fileClass) && method_exists($class, $argvMethod)) {
        require_once($fileClass);

        /* if (count($argv) > 3) { // method params
            $param = $argv[3];
        } */

        $method = new $class();
        $method->$argvMethod();
    } else {
        echo "\033[43mNot found class '$argvClass' or method '$argvMethod'\033[0m \n";
    }
} else {
    echo "\033[43mINFO! Provide the call parameters. Example: php console.php execute\033[0m \n";
}
