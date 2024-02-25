<?php
/*
 * Application: DbM Framework v2.1
 * Author: Arthur Malinowsky (Design by Malina)
 * License: MIT
 * Web page: www.dbm.org.pl
 * Contact: biuro@dbm.org.pl
 *
 * Used Lightweight Template Engine with PHP; Modified for DbM Framework
 * Homepage: https://codeshack.io/lightweight-template-engine-php/
 * Template::view('index.html');
 * Template::clearCache();
 *
 * INFO: You don't have to use the default template engine, but any template engine, e.g.:
 * Twig - https://twig.symfony.com/, example of use in /_Documents/Script/BaseController_for_Twig.php
*/

declare(strict_types=1);

namespace Dbm\Classes;

use Dbm\Classes\TemplateFeature;

class TemplateEngine extends TemplateFeature
{
    private const PATH_VIEW = BASE_DIRECTORY . 'templates'. DS;
    private const PATH_CACHE = BASE_DIRECTORY . 'var' . DS . 'cache' . DS;
    private const CACHE_ENABLED = false;
    public static $blocks = array(); // TODO! static, czy ok?

    protected function render(string $file, array $data = array()): void
    {
        $cachedFile = $this->cache($file);
        extract($data, EXTR_SKIP);

        require($cachedFile);
    }

    protected function clearCache(): void
    {
        foreach (glob(self::PATH_CACHE . '*') as $file) {
            unlink($file);
        }
    }

    private function cache(string $file): string
    {
        if (!file_exists(self::PATH_CACHE)) {
            mkdir(self::PATH_CACHE, 0744);
        }

        $cachedFile = self::PATH_CACHE . str_replace(array('/', '.phtml'), array('_', ''), $file . '.php');

        if (!self::CACHE_ENABLED || !file_exists($cachedFile) || filemtime($cachedFile) < filemtime($file)) {
            $code = $this->includeFiles($file);
            $code = $this->compileCode($code);

            file_put_contents($cachedFile, '<?php class_exists(\'' . __CLASS__ . '\') or exit; ?>' . PHP_EOL . $code); // TODO! PHP_EOL czy /n
        }

        return $cachedFile;
    }

    private function compileCode(string $code): string
    {
        $code = $this->compileBlock($code);
        $code = $this->compileYield($code);
        $code = $this->compileEscapedEchos($code);
        $code = $this->compileEchos($code);
        $code = $this->compilePHP($code);

        $code = $this->extensionPath($code);
        $code = $this->extensionTrans($code);

        return $code;
    }

    private function includeFiles(string $file): string
    {
        $code = file_get_contents(self::PATH_VIEW . $file);

        preg_match_all('/{% ?(extends|include) ?\'?(.*?)\'? ?%}/i', $code, $matches, PREG_SET_ORDER);

        foreach ($matches as $value) {
            $code = str_replace($value[0], $this->includeFiles($value[2]), $code);
        }

        $code = preg_replace('/{% ?(extends|include) ?\'?(.*?)\'? ?%}/i', '', $code);

        return trim($code);
    }

    private function compilePHP(string $code): string
    {
        return trim(preg_replace('~\{%\s*(.+?)\s*\%}~is', '<?php $1 ?>', $code));
    }

    private function compileEchos(string $code): string
    {
        return preg_replace('~\{{\s*(.+?)\s*\}}~is', '<?php echo $1 ?>', $code);
    }

    private function compileEscapedEchos(string $code): string
    {
        return preg_replace('~\{{{\s*(.+?)\s*\}}}~is', '<?php echo htmlentities($1, ENT_QUOTES, \'UTF-8\') ?>', $code);
    }

    private function compileBlock(string $code): string
    {
        preg_match_all('/{% ?block ?(.*?) ?%}(.*?){% ?endblock ?%}/is', $code, $matches, PREG_SET_ORDER);

        foreach ($matches as $value) {
            if (!array_key_exists($value[1], self::$blocks)) {
                self::$blocks[$value[1]] = '';
            }

            if (strpos($value[2], '@parent') === false) {
                self::$blocks[$value[1]] = $value[2];
            } else {
                self::$blocks[$value[1]] = str_replace('@parent', self::$blocks[$value[1]], $value[2]);
            }

            $code = str_replace($value[0], '', $code);
        }

        return $code;
    }

    private function compileYield(string $code): string
    {
        foreach (self::$blocks as $block => $value) {
            $code = preg_replace('/{% ?yield ?' . $block . ' ?%}/', $value, $code);
        }

        $code = preg_replace('/{% ?yield ?(.*?) ?%}/i', '', $code);

        return $code;
    }

    private function extensionPath(string $code): string
    {
        return preg_replace('~\{@\s*path(.+?)\s*\@}~is', '<?php echo $this->path($1) ?>', $code);
    }

    private function extensionTrans(string $code): string
    {
        return preg_replace('~\{@\s*trans/((.+?),(.+?),(.+?)/)\s*\@}~is', '<?php echo $this->trans($1,$2,$3) ?>', $code);
    }
}
