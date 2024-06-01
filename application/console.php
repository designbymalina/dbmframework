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
use App\Command\ConsoleCommand;
use Dbm\Classes\DotEnv;

// Environment Variables
$dotEnv = new DotEnv($pathConfig);
$dotEnv->load();

### CONSOLE COMMANDS
// Command from folder: application> php console.php ConsoleCommand executeCommand

new ConsoleCommand();
