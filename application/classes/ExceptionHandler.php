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

class ExceptionHandler extends Exception
{
    public function __construct(string $message, int $code, Throwable $previous = null)
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
                case 404:
                    header('Location: ' . APP_PATH . 'errors/error-404.html');
                    break;
                default:
                    header("Location: " . APP_PATH . "errors/error.html?code=$code");
            }
        }
    }

    private function exceptionHandler(int $code, string $message, string $file, int $line): void
    {
        $message = str_replace('SQLSTATE', '<span class="color-ss">SQLSTATE</span>', $message);
        $message = preg_replace('/[\[{\(].*?[\]}\)]/', '<span class="color-em">$0</span>', $message);
        $message = preg_replace('/[\'].*?[\']/', '<span class="color-u">$0</span>', $message);
        $message = preg_replace('/[a-z0-9_\-]*\.php/i', '$1<u>$0</u>', $message);
        $message = preg_replace('/[0-9]/i', '$1<em>$0</em>', $message);
        $message = preg_replace('/[\(\)#\[\]\':]/i', '$1<ss>$0</ss>', $message);

        ($code != false) ? $code = 'ERROR: <b>' . $code . '</b>; ' : $code = 'NO CODE! ';

        echo('<!DOCTYPE html>' . "\n"
            . '<html lang="en">' . "\n"
            . '<head>' . "\n"
            . '  <meta charset="utf-8">' . "\n"
            . '  <meta name="author" content="Design by Malina, www.dbm.org.pl">' . "\n"
            . '  <title>DbM Framework - Error Handler</title>' . "\n"
            . '  <style>' . "\n"
            . '    body { margin: 0; background-color: #181818; color: #FBFFFF; } h2 { margin: 0; color: #a97bd3; } u, .color-u { color: #FFF01F; text-decoration: none; } b { color: #00FEFC; } em, .color-em { color: #00FF00; font-style: normal; } ss, .color-ss { color: #FFBF00; } .dbm-container { margin: 0 auto; padding: 10px; max-width: 1280px; } .dbm-header { margin-bottom: 3rem; padding: 5px 10px; background-color: #181818; color: grey; } .dbm-content { padding: 2rem; background-color: rgba(255,255,255,0.1); border-radius: 0.5rem; font-size: 1.0rem; } .dbm-content ul { list-style-type: none; } .dbm-content ul li { padding: 5px 10px; } .dbm-content ul li.msg { background-color: #b1413f; } .dbm-content ul li.file {  background-color: #666; }' . "\n"
            . '  </style>' . "\n"
            . '</head>' . "\n"
            . '<body>' . "\n"
            . '  <div class="dbm-container">' . "\n"
            . '    <div class="dbm-header">DbM Fremwork Exception Handler</div>' . "\n"
            . '    <div class="dbm-content">' . "\n"
            . '      <h2>Fatal error</h2>' . "\n"
            . '      <ul><li class="msg">' . $code . 'Message: ' . nl2br($message) . '</li><li class="file">Uncaught, throwable in: ' . $file . ' on line ' . $line . '</li></ul>' . "\n"
            . '    </div>' . "\n"
            . '  </div>' . "\n"
            . '</body>' . "\n"
            . '</html>');

        exit();
    }
}
