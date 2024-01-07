<?php
/*
 * Application: DbM Framework v1.2
 * Author: Arthur Malinowsky (Design by Malina)
 * License: MIT
 * Web page: www.dbm.org.pl
 * Contact: biuro@dbm.org.pl
*/

function htmlErrorHandler(string $message, string $file, int $line): void
{
    ob_end_clean();

    echo('<!DOCTYPE html>' . "\n"
        . '<html lang="en">' . "\n"
        . '<head>' . "\n"
        . '  <meta charset="utf-8">' . "\n"
        . '  <meta name="author" content="Design by Malina, www.dbm.org.pl">' . "\n"
        . '  <title>DbM Framework - Error Handler</title>' . "\n"
        . '  <style>' . "\n"
        . '    body { margin: 0; background-color: #181818; color: white; } h2 { margin: 0; color: #a97bd3; } u { color: yellow; text-decoration: none; } b { color: red; } em { color: blue; font-style: normal; } ss { color: orange; } .dbm-container { margin: 0 auto; max-width: 1000px; } .dbm-header { margin-bottom: 3rem; padding: 5px 10px; background-color: #181818; color: grey; } .dbm-content { padding: 2rem; background-color: rgba(255,255,255,0.1); border-radius: 0.5rem; font-size: 1.0rem; } .dbm-content ul { list-style-type: none; } .dbm-content ul li { padding: 5px 10px; } .dbm-content ul li.msg { background-color: #b1413f; } .dbm-content ul li.file {  background-color: #666; }' . "\n"
        . '  </style>' . "\n"
        . '</head>' . "\n"
        . '<body>' . "\n"
        . '  <div class="dbm-container">' . "\n"
        . '    <div class="dbm-header">DbM Fremwork Handler Reporting</div>' . "\n"
        . '    <div class="dbm-content">' . "\n"
        . '      <h2>Oops, something went wrong!</h2>' . "\n"
        . '      <ul><li class="msg">Message: ' . nl2br($message) . '</li><li class="file">File: ' . basename(dirname($file)) . DS . basename($file)  . ' on line ' . $line . '</li></ul>' . "\n"
        . '    </div>' . "\n"
        . '  </div>' . "\n"
        . '</body>' . "\n"
        . '</html>');

    exit();
}
