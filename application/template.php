<?php
/*
 * Application: DbM Framework v1.2
 * Author: Arthur Malinowsky (Design by Malina)
 * License: MIT
 * Web page: www.dbm.org.pl
 * Contact: biuro@dbm.org.pl
 */

/**
 * Path generator - Przetestuj na serwerze nie lokalnym
 * TODO! Czy i jak przeniesc w inne miejsce, aby moc uzywac w szablonach?
 *
 * @param string $file
 */
function path(string $file = null): string
{
    $seperator = '/';

    if (APP_PATH === $seperator) {
        return APP_PATH . trim($file);
    }

    $requestUri = $_SERVER['REQUEST_URI'];
    $httpHost = $_SERVER['HTTP_HOST'];

    $requestPath = substr($requestUri, strpos($requestUri, $httpHost) + strlen($httpHost));
    $arrayRequestPath = explode($seperator, ltrim($requestPath, $seperator));

    $countDir = (int) count($arrayRequestPath);

    if ($countDir > 0) {
        switch ($countDir) {
            case 2:
                $pathResult = '.' . $seperator;
                break;
            case 3:
                $pathResult = '..' . $seperator;
                break;
            default:
                $pathResult = $seperator;
        }
    } else {
        $pathResult = '#pathError:';
    }

    return $pathResult . trim($file);
}

function trans(string $key, array $sprint = null): string
{
    $cookieName = 'DbmLanguage';
    $lang = 'pl';
    $languages = array($lang);

    if (!empty(APP_LANGUAGES)) {
        $languages = explode('|', APP_LANGUAGES);
        $lang = $languages[0];
    }

    if (!empty($_GET['lang'])) {
        $lang = $_GET['lang'];
    } elseif (isset($_COOKIE[$cookieName])) {
        $lang = $_COOKIE[$cookieName];
    }

    $translation = include("../translations/language.$lang.php");

    if (array_key_exists($key, $translation)) {
        (!empty($sprint)) ? $value = vsprintf($translation[$key], $sprint) : $value = $translation[$key];

        return $value;
    } else {
        return $key;
    }
}

/**
 * Truncates the text to the specified number and adds an ending
 *
 * @param string $content
 * @param int $limit, default 250 characters
 * @param string $ending, default ellipsis
 */
function truncate(string $content, int $limit = 250, string $ending = '...'): string
{
    $content = htmlspecialchars_decode($content, ENT_QUOTES);
    $content = trim(strip_tags($content));

    return mb_strlen($content) > $limit
        ? trim(preg_replace('~\s+\S+$~', '', substr($content, 0, $limit))) . $ending
        : $content;
}

function linkSEO(string $rule, int $id, string $text = null, int $limit = 120, string $separator = '-'): string
{
    if ($text != null) {
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        $text = str_replace('-', '', $text);
        $text = strip_tags($text);
        $text = preg_replace('/[^a-zA-Z0-9 -]/', '', $text);
        $text = strtolower($text);
        $text = substr($text, 0, $limit);
        $text = trim($text);
        $text = preg_replace('/\s+/', $separator, $text);

        $text = $text . ',';
    }

    return $text . $rule . ',' . $id . '.html';
}

function output(string $data): string
{
    $search = array('@<script[^>]*?>.*?</script>@si', '@<style[^>]*?>.*?</style>@si');

    $data = preg_replace($search, '', $data);
    $data = wordwrap($data, 50, ' ', true);

    return $data;
}

function outputHTML(string $data, $sign = ''): string
{
    $data = htmlspecialchars_decode($data, ENT_QUOTES);
    $data = wordwrap($data, 50, ' ', true);
    $data = str_replace('{{url}}', APP_PATH, $data);
    $data = trim(str_replace("\n", "\n" . $sign, $data)) . "\n";
    return $data;
}

function htmlLanguage(string $path): string
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

    return $html;
}

function htmlPageAddress(?string $address): ?string
{
    $search = 'txt';
    $replace = 'html';

    if (($pos = strrpos($address, $search)) !== false) {
        $length = strlen($search);
        $result = substr_replace($address, $replace, $pos, $length);
    }

    $first = substr($address, 0, strpos($address, '-'));

    if ($first == 'page') {
        $result = str_replace(['page-', '.html'], ['', ',site.html'], $result);
    } else {
        $result = $result . ' / INFO: For a page with this address you need to create a controller: ' . str_replace('.txt', '', ucfirst($address)) . 'Controller.php';
    }

    return $result;
}

function htmlSelect(array $options, string $name, int $item = null, string $sort = null, string $style = null): string
{
    if (strtolower($sort) === 'asc') {
        asort($options);
    }

    if ($style !== null) {
        $style = ' ' . $style;
    }

    $html = '<!-- htmlSelect -->' . "\n";
    $html .= '<select name="' . $name . '" id="form_' . $name . '" class="form-control"' . $style. '>' . "\n";
    $html .= '    <option value="">- select ' . $name . ' -</option>' . "\n";

    foreach ($options as $key => $value) {
        $html .= '    <option value="' . $key . '"';

        if ($item === $key) {
            $html .= ' selected';
        }

        $html .= '>' . $value . '</option>' . "\n";
    }

    $html .= '</select>' . "\n";

    return $html;
}

function htmlList(array $list, ?string $sign = '', ?string $class = ''): string
{
    $html = '<!-- htmlList -->' . "\n";

    $html .= $sign . '<ul';
    ($class != '') ? $html .= ' class="' . $class . '"' : null;
    $html .= '>' . "\n";

    foreach ($list as $value) {
        $html .= $sign . '    <li>' . $value . '</li>' . "\n";
    }

    $html .= $sign . '</ul>' . "\n";

    return $html;
}

// TODO! Temporary function
use Dbm\Classes\DatabaseClass;

function temp_htmlUser($sessionUserId, $module = null): string
{
    $database = new DatabaseClass();
    $userId = (int) $sessionUserId;

    $query = "SELECT user.login, user.avatar, user_details.fullname FROM dbm_user user"
        . " INNER JOIN dbm_user_details user_details ON user_details.user_id = user.id"
        . " WHERE user.id = :uid";

    if ($database->queryExecute($query, [':uid' => $userId])) {
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

        return $html;
    } else {
        return 'Error: temp_htmlUser()' . "\n";
    }
}
