<?php
declare(strict_types=1);

// TODO! Remove the definition from the entire project
define('DS', DIRECTORY_SEPARATOR);
define('BASE_DIRECTORY', __DIR__ . DS);

require(BASE_DIRECTORY . 'application' . DS . 'start.php');

$pathConfig = BASE_DIRECTORY . '.env';
$pathAutoload = BASE_DIRECTORY . 'vendor' . DS . 'autoload.php';

configurationSettings($pathConfig);
autoloadingWithWithoutComposer($pathAutoload);

use Dbm\Classes\DotEnv;

$dotEnv = new DotEnv($pathConfig);
$dotEnv->load();

### CONSOLE COMMANDS
include(__DIR__ . DIRECTORY_SEPARATOR . 'commands' . DIRECTORY_SEPARATOR . 'action_show_me_code.php');
include(__DIR__ . DIRECTORY_SEPARATOR . 'commands' . DIRECTORY_SEPARATOR . 'action_show_me_text.php');

// INFO! Wywolanie kody pliku actionShowMeCode.php w konsoli
// $ php console.php [file_name] // Pytanie: Wykonuje wszystkie includowane pliki, a pojedynczo?
?>
