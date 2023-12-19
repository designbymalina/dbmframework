<?php
/*
 * Application: DbM Framework v1.2
 * Author: Arthur Malinowsky (Design by Malina)
 * License: MIT
 * Web page: www.dbm.org.pl
 * Contact: biuro@dbm.org.pl
*/

declare(strict_types=1);

namespace Dbm\Classes;

use Exception;
use Throwable;

class ExceptionClass extends Exception
{
    public function __construct($message, $code = 0, Throwable $previous = null)
    {
        if (APP_ENV === 'development') {
            parent::__construct($message, $code, $previous);

            if (!empty($this->getTrace()[0])) {
                $file = $this->getTrace()[0]['file'];
                $line = $this->getTrace()[0]['line'];
            } else {
                $file = $this->file;
                $line = $this->line;
            }

            $this->exceptionHandler($this->code, $this->message, $file, $line);
        } else {
            switch ($code) {
                case 401:
                    header('Location: errors/error-401.html');
                    break;
                case 404:
                    header('Location: errors/error-404.html');
                    break;
                case 500:
                    header('Location: errors/error-500.html');
                    break;
                default:
                    header("Location: index.html");
            }
        }
    }

    private function exceptionHandler(int $code, string $message, string $file, int $line): void
    {
        $message = preg_replace('/[a-z0-9_\-]*\.php/i', '$1<u>$0</u>', $message);
        $message = preg_replace('/[0-9]/i', '$1<em>$0</em>', $message);
        $message = preg_replace('/[\(\)#\[\]\':]/i', '$1<ss>$0</ss>', $message);

        echo '<!DOCTYPE html>' . "\n"
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
            . '    <div class="dbm-header">DbM Fremwork Exception</div>' . "\n"
            . '    <div class="dbm-content">' . "\n"
            . '      <h2>Fatal error</h2>' . "\n"
            . '      <ul><li class="msg">Code: ' . $code . '; Message: ' . nl2br($message) . '</li><li class="file">Uncaught, throwable in: ' . $file . ' on line ' . $line . '</li></ul>' . "\n"
            . '    </div>' . "\n"
            . '  </div>' . "\n"
            . '</body>' . "\n"
            . '</html>';

        exit();
    }
}
