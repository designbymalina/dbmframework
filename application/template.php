<?php
/*
 * Application: DbM Framework v2.1
 * Author: Arthur Malinowsky (Design by Malina)
 * License: MIT
 * Web page: www.dbm.org.pl
 * Contact: biuro@dbm.org.pl
 *
 * TEMPLATE UNDER EXPANSION: function htmlUser(DatabaseInterface $database) doesn't look great in this file!
 * INFO: You don't have to use the default template engine, but any template engine, e.g.:
 * Twig - https://twig.symfony.com/, example of use in /_Documents/Script/BaseController_for_Twig.php
 * Lightweight - https://codeshack.io/lightweight-template-engine-php/ and others.
 * If you decide to use a different template engine, remove this file and the filename line in public/index.php.
 */

/*
 * Path generator
 */
function path(string $file = null): string
{
    $pathResult = '';
    $divider = '/';
    $requestUri = $_SERVER['REQUEST_URI'];
    $dir = dirname($_SERVER['PHP_SELF']);
    $pathFile = !empty($file) ? trim($file) : '';

    if (strpos($dir, 'public')) { // for localhost (application in catalog)
        $pathPublic = substr($requestUri, strlen(strstr($dir, 'public', true)));
    } else {
        $pathPublic = $requestUri;
    }

    $arrayRequestPath = explode($divider, $pathPublic);
    $countDir = (int) count($arrayRequestPath) - 1;

    if ($countDir > 0) {
        for ($i = 0; $i < $countDir; $i++) {
            $pathResult .= '..' . $divider;
        }
    } else {
        $pathResult .= '.' . $divider;
    }

    return $pathResult . $pathFile;
}

/*
 * Translations
 */
function trans(string $key, array $overwrite = [], array $sprint = null): string
{
    $cookieName = 'DbmLanguage';
    $lang = 'pl';
    $languages = array($lang);

    if (!empty($overwrite['meta'])) {
        $overwrite = $overwrite['meta'];
    }

    if (!empty(APP_LANGUAGES)) {
        $languages = explode('|', APP_LANGUAGES);
        $lang = $languages[0];
    }

    if (!empty($_GET['lang'])) {
        $lang = $_GET['lang'];
    } elseif (isset($_COOKIE[$cookieName])) {
        $lang = $_COOKIE[$cookieName];
    }

    $translation = include(BASE_DIRECTORY . "translations/language.$lang.php");

    if (array_key_exists($key, $overwrite) && array_key_exists($key, $translation)) {
        (!empty($sprint)) ? $value = vsprintf($overwrite[$key], $sprint) : $value = $overwrite[$key];

        return $value;
    } elseif (array_key_exists($key, $translation)) {
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
 *
 * @return string
 */
function truncate(string $content, int $limit = 250, string $ending = '...'): string
{
    $content = htmlspecialchars_decode($content, ENT_QUOTES);
    $content = trim(strip_tags($content));

    return mb_strlen($content) > $limit
        ? trim(preg_replace('~\s+\S+$~', '', substr($content, 0, $limit))) . $ending
        : $content;
}

/*
 * Link generator
 */
function linkSEO(string $rule, int $id, string $text = null, int $limit = 65): string
{
    $hyphen = '-';
    $divider = '.';
    $extension = '.html';

    if ($text != null) {
        $arrayRemove = ['a', 'i', 'o', 'u', 'w', 'z', 'r.', 'itp.', 'and', 'or', 'to', 'an', 'etc.']; // words to remove from url
        $allowedPattern = "/[^a-zA-Z0-9 ]/";

        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        $text = strip_tags($text);
        $text = strtolower($text);
        $text = str_replace($hyphen, '', $text);
        $text = preg_replace($allowedPattern, '', $text);

        if (!empty($arrayRemove)) {
            $removePattern = "/\b(" . implode("|", $arrayRemove) . ")\b/";
            $text = trim(preg_replace($removePattern, '', $text));
        }

        if (mb_strlen($text) > $limit) {
            $text = trim(preg_replace('~\s+\S+$~', '', substr($text, 0, $limit)));
        }

        $text = trim(preg_replace('~\s+~', $hyphen, $text));

        $text = $text . $divider;
    }

    return $text . $rule . $divider . $id . $extension;
}

/*
 * String output
 */
function output(string $data): string
{
    $search = array('@<script[^>]*?>.*?</script>@si', '@<style[^>]*?>.*?</style>@si');

    $data = preg_replace($search, '', $data);
    $data = wordwrap($data, 50, ' ', true);

    return $data;
}

/*
 * HTML output
 */
function outputHTML(string $data, $sign = ''): string
{
    $data = htmlspecialchars_decode($data, ENT_QUOTES);
    $data = wordwrap($data, 50, ' ', true);
    $data = str_replace('{{url}}', APP_PATH, $data);
    $data = trim(str_replace("\n", "\n" . $sign, $data)) . "\n";
    return $data;
}

/*
 * Visit counter
 */
function counterVisits(): string
{
    $result = 1;
    $length = 16;

    $file = 'counter_visits.txt';
    $path = '../data/txt/';
    $pathFile = $path . $file;

    if (!file_exists($pathFile) || (filesize($pathFile) == 0)) {
        file_put_contents($pathFile, $result);
        $counterFile = 0;
    } else {
        $handle = fopen($pathFile, "r+");
        $counterFile = fgets($handle, $length);
        $result = $counterFile + 1;

        fseek($handle, 0);
        fwrite($handle, $result, $length);
        fclose($handle);
    }

    $pathCopy = $path . 'copies' . DS . $file;

    if (!file_exists($pathCopy) || (filesize($pathCopy) == 0)) {
        file_put_contents($pathCopy, $result);
        $counterCopy = 0;
    } else {
        $handle = fopen($pathCopy, "r");
        $counterCopy = fread($handle, filesize($pathCopy));
        fclose($handle);

        if (intval($counterFile) >= intval($counterCopy)) {
            copy($pathFile, $pathCopy);
        } elseif (intval($counterFile) < intval($counterCopy)) {
            copy($pathCopy, $pathFile);
        }
    }

    return $result;
}

/*
 * Language switcher
 */
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

            if ($key === 0) {
                $active = ' active';

                $html .= '    <button type="button" class="btn btn-sm btn-link text-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="' . $value . '"><img src="' . $path . 'images/lang/' . strtolower($value) . '.png" alt="' . $value . '"></button>' . "\n";
                $html .= '    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark">' . "\n";
            } else {
                $active = '';
            }

            $html .= '        <li><a href="?' . http_build_query($param) . '" class="dropdown-item' . $active . '"><img src="' . $path . 'images/lang/' . strtolower($value) . '.png" alt="' . $value . '"><span>' . $value . '</span></a></li>' . "\n";

            if (($count > 1) && ($key === ($count - 1))) {
                $html .= '    </ul>'. "\n";
                $html .= '</div>' . "\n";
            }
        }
    }

    return $html;
}

/*
 * Creating address for the website, used in the panel: create_edit_page.phtml
 */
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
        $result = str_replace(['page-', '.html'], ['', '.site.html'], $result);
    } else {
        $result = $result . ' / INFO: For a page with this address you need to create a controller: ' . str_replace('.txt', '', ucfirst($address)) . 'Controller.php';
    }

    return $result;
}

/*
 * Creating field "select"
 */
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

/*
 * Creating a list
 */
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

/*
 * Creating a user item
 */
function htmlUser($database, $sessionUserId, $module = null): string
{
    $userId = (int) $sessionUserId;
    $path = path();

    $query = "SELECT user.login, user.avatar, user_details.fullname FROM dbm_user user"
        . " INNER JOIN dbm_user_details user_details ON user_details.user_id = user.id"
        . " WHERE user.id = :uid";

    if ($database->queryExecute($query, [':uid' => $userId])) {
        $data = $database->fetchObject();

        !empty($data->avatar) ? $avatar = $data->avatar : $avatar = 'no-avatar.png';
        !empty($data->login) ? $login = $data->login : $login = 'NoName';
        !empty($data->fullname) ? $name = $data->fullname : $name = 'NoName';

        if ($module == 'PANEL') {
            $html = '<span class="mr-2 d-none d-lg-inline text-gray-600 small">' . $name . '</span>';
            $html .= '<img class="img-profile rounded-circle" src="' . $path . 'images/avatar/' . $avatar . '">' . "\n";
        } else {
            $html = '<span class="d-none d-lg-inline me-2">' . $login . '</span>';
            $html .= '<img class="dbm-img-profile rounded-circle" src="' . $path . 'images/avatar/' . $avatar . '">' . "\n";
        }

        return $html;
    } else {
        return 'Error: htmlUser()' . "\n";
    }
}
