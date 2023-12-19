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
