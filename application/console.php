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
use App\Command\ExampleCommand;
use Dbm\Classes\DotEnv;

// Environment Variables
$dotEnv = new DotEnv($pathConfig);
$dotEnv->load();

### CONSOLE COMMANDS
// INFO! Ladujesz/includujesz tylko te Command (jedno), ktore chcesz wywolac.
// Komenda: $ php console.php [ClassName?]
// include(BASE_DIRECTORY . 'src' . DIRECTORY_SEPARATOR . 'Command' . DIRECTORY_SEPARATOR . 'ExampleCommand.php');

$command = new ExampleCommand();
$command->executeCommand();
