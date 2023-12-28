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

use Dbm\Classes\TranslationClass;
use Dbm\Classes\DatabaseClass;

/*
 * TODO! Metody static?
 */
class TemplateClass
{
    public static function trans(string $key, array $data = null, array $sprint = null): void
    {
        $translation = new TranslationClass();
        $trans = $translation->translation();

        if (!empty($data) && array_key_exists($key, $data)) {
            (!empty($sprint)) ? $value = vsprintf($data[$key], $sprint) : $value = $data[$key];
            echo $value;
        } elseif (array_key_exists($key, $trans)) {
            (!empty($sprint)) ? $value = vsprintf($trans[$key], $sprint) : $value = $trans[$key];
            echo $value;
        } else {
            echo $key;
        }
    }

    public static function htmlLanguage(string $path): void
    {
        $html = '<!-- htmlLanguage -->';
        $param = array();
        $languages = array();
        $cookieName = 'DbmLanguage';

        if (!empty(APP_LANGUAGES)) {
            $languages = explode('|', APP_LANGUAGES);
        }

        if (!empty($_GET['id'])) {
            $id = $_GET['id'];
            $param['id'] = $id;
        }

        if (!empty($_GET['lang'])) {
            $lang = $_GET['lang'];
        } elseif (isset($_COOKIE[$cookieName])) {
            $lang = $_COOKIE[$cookieName];
        }

        if (isset($lang)) {
            $keySearch = array_search($lang, $languages);
            unset($languages[$keySearch]);
            array_unshift($languages, $lang);
        }

        $count = count($languages);

        if ($count > 0) {
            $html .= "\n" . '<div class="dropdown dbm-dropdown-language">' . "\n";

            foreach ($languages as $key => $value) {
                unset($param['lang']);
                $param['lang'] = $value;

                if ($key === 0) { // for first key
                    $active = ' active';

                    $html .= '    <button type="button" class="btn btn-sm btn-link text-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="' . $value . '"><img src="' . $path . 'images/lang/' . strtolower($value) . '.png" alt="' . $value . '"></button>' . "\n";
                    $html .= '    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark">' . "\n";
                } else {
                    $active = '';
                }

                $html .= '        <li><a href="?' . http_build_query($param) . '" class="dropdown-item' . $active . '"><img src="' . $path . 'images/lang/' . strtolower($value) . '.png" alt="' . $value . '"><span>' . $value . '</span></a></li>' . "\n";

                if (($count > 1) && ($key === ($count - 1))) { // for last key
                    $html .= '    </ul>'. "\n";
                    $html .= '</div>' . "\n";
                }
            }
        }

        echo $html;
    }

    /* TODO! Temporary. Change to full htmlUserNavigation() ? */
    public static function temp_htmlUser($sessionUserId, $module = null): void
    {
        $database = new DatabaseClass();
        $userId = (int) $sessionUserId;

        $query = "SELECT user.login, user.avatar, user_details.fullname FROM dbm_user user"
            . " INNER JOIN dbm_user_details user_details ON user_details.user_id = user.id"
            . " WHERE user.id = '$userId'";

        if ($database->queryExecute($query)) {
            $data = $database->fetchObject();

            !empty($data->avatar) ? $avatar = $data->avatar : $avatar = 'no-avatar.png';
            !empty($data->login) ? $login = $data->login : $login = 'NoName';
            !empty($data->fullname) ? $name = $data->fullname : $name = 'NoName';

            if ($module === 'panel') {
                $html = '<span class="mr-2 d-none d-lg-inline text-gray-600 small">' . $name . '</span>';
                $html .= '<img class="img-profile rounded-circle" src="' . APP_PATH . 'images/avatar/' . $avatar . '">' . "\n";
            } else {
                $html = '<span class="d-none d-lg-inline me-2">' . $login . '</span>';
                $html .= '<img class="dbm-img-profile rounded-circle" src="' . APP_PATH . 'images/avatar/' . $avatar . '">' . "\n";
            }

            echo $html;
        } else {
            echo 'Error: temp_htmlUser()' . "\n";
        }
    }
}
