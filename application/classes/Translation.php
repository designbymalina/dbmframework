<?php
/*
 * Application: DbM Framework v2.1
 * Author: Arthur Malinowsky (Design by Malina)
 * License: MIT
 * Web page: www.dbm.org.pl
 * Contact: biuro@dbm.org.pl
*/

declare(strict_types=1);

namespace Dbm\Classes;

use Dbm\Interfaces\TranslationInterface;

class Translation implements TranslationInterface
{
    public $arrayTranslation;

    public function __construct()
    {
        $this->arrayTranslation = $this->translation();
    }

    /* Language translation */
    public function trans(string $key, array $data = null, array $sprint = null): string
    {
        $translation = $this->arrayTranslation;

        if (!empty($data) && array_key_exists($key, $data)) {
            (!empty($sprint)) ? $value = vsprintf($data[$key], $sprint) : $value = $data[$key];
            return $value;
        } elseif (array_key_exists($key, $translation)) {
            (!empty($sprint)) ? $value = vsprintf($translation[$key], $sprint) : $value = $translation[$key];
            return $value;
        }

        return $key;
    }

    /* Switch translation */
    private function translation(): array
    {
        $language = $this->language();
        $pathTranslation = BASE_DIRECTORY . 'translations' . DS . 'language.' . strtolower($language) . '.php';

        if (file_exists($pathTranslation)) {
            return require($pathTranslation);
        }

        return array();
    }

    /* Get language. You can use ?lang=off to clear cookie. */
    private function language(): string
    {
        $cookieName = 'DbmLanguage';
        $appLanguages = getenv('APP_LANGUAGES');

        if ($appLanguages === false || !is_string($appLanguages)) {
            $appLanguages = 'PL';
        }

        $arrayLanguages = explode('|', $appLanguages);
        $language = !empty($arrayLanguages[0]) ? $arrayLanguages[0] : 'PL';

        if (isset($_GET['lang'])) {
            $lang = strtolower($_GET['lang']);

            if ($lang === 'off') {
                setcookie($cookieName, '', time() - 3600);
            } else {
                $language = $_GET['lang'];
                setcookie($cookieName, $language, time() + 24 * 3600);
            }
        } elseif (isset($_COOKIE[$cookieName])) {
            $language = $_COOKIE[$cookieName];
        }

        return $language;
    }
}
