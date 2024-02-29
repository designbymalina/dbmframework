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
    private $translation;

    public function __construct()
    {
        $this->translation = json_encode($this->translation());
    }

    /* Language translation */
    public function trans(string $key, array $data = null, array $sprint = null): string
    {
        $trans = json_decode($this->translation, true);

        if (!empty($data) && array_key_exists($key, $data)) {
            (!empty($sprint)) ? $value = vsprintf($data[$key], $sprint) : $value = $data[$key];
            return $value;
        } elseif (array_key_exists($key, $trans)) {
            (!empty($sprint)) ? $value = vsprintf($trans[$key], $sprint) : $value = $trans[$key];
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

    /* Get language */
    private function language(): string
    {
        $cookieName = 'DbmLanguage';
        $arrayLanguages = explode('|', APP_LANGUAGES);
        
        !empty($arrayLanguages[0]) ? $language = $arrayLanguages[0] : $language = 'PL';

        if (isset($_GET['lang'])) {
            $language = $_GET['lang'];
            setcookie($cookieName, $language, time() + 24 * 3600);

            if (strtolower($_GET['lang']) === 'off') {
                setcookie($cookieName, '', time() - 3600);
            }
        } elseif (isset($_COOKIE[$cookieName])) {
            $language = $_COOKIE[$cookieName];
        }
        
        return $language;
    }
}
