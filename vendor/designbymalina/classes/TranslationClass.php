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

class TranslationClass
{
    private const PATH_TRANSLATION = '../translations/language.';

    /* Switch translation */
    public function Translation(): array
    {
        $language = $this->Language();
        $pathTranslation = self::PATH_TRANSLATION . strtolower($language) . '.php';

        if (file_exists($pathTranslation)) {
            return require($pathTranslation);
        } else {
            throw new Exception('No translation file ' . $pathTranslation);
        }
    }

    /* TODO! Method trans() can be used in template and another places (controller, model, service) */
    public function trans(string $key, array $data = null, array $sprint = null): string
    {
        $trans = $this->Translation();

        if (!empty($data) && array_key_exists($key, $data)) {
            (!empty($sprint)) ? $value = vsprintf($data[$key], $sprint) : $value = $data[$key];
            return $value;
        } elseif (array_key_exists($key, $trans)) {
            (!empty($sprint)) ? $value = vsprintf($trans[$key], $sprint) : $value = $trans[$key];
            return $value;
        } else {
            return $key;
        }
    }

    /* Get language */
    private function Language(): string
    {
        $cookieName = 'DbmLanguage';
        $languageDefault = 'EN';

        $arrayLanguages = explode('|', APP_LANGUAGES);

        if (!empty($arrayLanguages[0])) {
            $languageDefault = $arrayLanguages[0];
        }

        if (isset($_GET['lang']) or isset($_COOKIE[$cookieName])) {
            if (isset($_GET['lang'])) {
                $language = $_GET['lang'];
                setcookie($cookieName, $language, time() + 24 * 3600);

                if (strtolower($_GET['lang']) === 'off') {
                    setcookie($cookieName, '', time() - 3600);
                }
            } elseif (isset($_COOKIE[$cookieName])) {
                $language = $_COOKIE[$cookieName];
            }

            if (!in_array($language, $arrayLanguages)) {
                $language = $languageDefault;
            }
        } else {
            $language = $languageDefault;
        }

        return $language;
    }
}
