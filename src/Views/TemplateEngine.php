<?php

/**
 * Application: DbM Framework + DbM Template Engine (views for framework)
 * A lightweight PHP framework for building web applications.
 *
 * @author Artur Malinowski
 * @copyright Design by Malina (All Rights Reserved)
 * @license MIT
 * @link https://www.dbm.org.pl
 */

declare(strict_types=1);

namespace Dbm\Views;

use Dbm\Core\Paths;
use Dbm\Http\Controller\BaseController;
use Dbm\Http\Message\Response;
use Dbm\Http\Message\Stream;
use Dbm\Localization\Contracts\TranslationInterface;
use Dbm\Routing\Contracts\UrlGeneratorInterface;
use Dbm\Views\Extension\FilterExtension;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * TemplateEngine
 *
 * Klasa odpowiedzialna za renderowanie, kompilację i cache'owanie szablonów.
 * Obsługuje filtry, debugowanie, linting i dziedziczenie layoutów.
 */
class TemplateEngine extends TemplateFeature
{
    private TemplateCompiler $compiler;
    private TemplateCache $cache;
    private FilterExtension $filters;
    private ?TranslationInterface $translation = null;
    private ?BaseController $controllerContext = null;
    private ?UrlGeneratorInterface $urlGenerator = null;
    private ?ServerRequestInterface $request = null;

    /** @var array<string> */
    private array $templatePaths = [];
    /** @var array<string, callable> */
    private array $globalProviders = [];
    /** @var array<string, string> */
    private array $namespaces = [];

    private bool $enableDebugger = false; // Default: false, optionally enable debugger (for tests)
    private bool $enableLint = false; // Default: false for Windows, can be changed to true for Linux
    private bool $cacheEnabled = true;
    private string $cachePath;
    private string $debugPath;
    private ?string $currentTemplate = null;
    private ?string $currentCompiledPath = null;

    /**
     * @param string $templatesPath Ścieżka do katalogu z szablonami.
     * @param string $cachePath Ścieżka do katalogu cache.
     */
    public function __construct(
        ?string $templatesPath = null,
        ?string $cachePath = null,
    ) {
        $templatesPath ??= Paths::templatesPath();
        $cachePath ??= Paths::joinPaths(Paths::varPath(), 'cache');

        $this->templatePaths[] = rtrim($templatesPath, '/') . '/';
        $this->cachePath = rtrim($cachePath, '/') . '/';
        $this->debugPath = rtrim(
            Paths::joinPaths(Paths::varPath(), 'cache', 'debug'),
            '/'
        ) . '/';

        $this->filters = new FilterExtension();
        $this->compiler = new TemplateCompiler($this->filters);
        $this->cache = new TemplateCache($this->cachePath);

        $this->cacheEnabled = filter_var(
            getenv('CACHE_ENABLED') ?: 'true',
            FILTER_VALIDATE_BOOLEAN
        );
    }

    /**
     * Renderuje szablon i zwraca treść lub ResponseInterface.
     *
     * @param string $template Nazwa pliku szablonu.
     * @param array<string, mixed> $data Dane przekazywane do szablonu.
     * @param bool $asResponse Czy zwrócić obiekt ResponseInterface.
     * @return \Dbm\Http\Message\Response|ResponseInterface|string
     */
    public function render(string $template, array $data = [], bool $asResponse = true)
    {
        try {
            ob_start();

            $this->setCurrentTemplate($template);
            $this->resolveGlobals();

            $output = $this->renderContent(
                $template,
                $data,
                $this->controllerContext
            );

            return $asResponse
                ? new Response(200, ['Content-Type' => 'text/html'], new Stream($output))
                : $output;
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
    }

    /**
     * Kompiluje, ładuje i renderuje szablon (z obsługą layoutów).
     *
     * @param string $template
     * @param array<string, mixed> $data
     * @param BaseController|null $controller
     * @return string
     * @throws TemplateException
     */
    public function renderContent(string $template, array $data = [], ?BaseController $controller = null): string
    {
        $templateFile = $this->resolveTemplateFile($template);
        $cachePath = $this->cache->getCachePath($template);

        if (!$this->cacheEnabled || !$this->cache->isFresh($templateFile, $cachePath)) {
            $body = $this->compiler->compile($templateFile);
            $this->enableDebugger($template, $body);
            $this->writeCompiledTemplate($template, $cachePath, $body);
        }

        require_once $cachePath;

        $className = $this->getCompiledClassName($template);
        if (!class_exists($className)) {
            throw new TemplateException(
                "Compiled template class {$className} not found",
                $template
            );
        }

        $tpl = new $className();
        $tpl->engine = $this;
        $tpl->controller = $controller;

        foreach ($this->globals() as $key => $value) {
            $tpl->setGlobal($key, $value);
        }

        if ($this->request) {
            $tpl->setGlobal('request', $this->request);
        }

        // @INFO Zamiast tego rozważyć GLOBAL w TemplateFeature:
        // $tpl->setGlobal('url', $this->urlGenerator);
        // W template: {{ url.generate('route_name') }}
        if ($this->urlGenerator) {
            $tpl->setUrlGenerator($this->urlGenerator);
        }

        $tpl->setData($data);
        $output = $tpl->render($data);

        if (!empty($tpl->parent)) {
            $output = $this->renderParentTemplate(
                $tpl->parent,
                $data,
                $tpl->blocks ?? [],
                $tpl
            );
        }

        return $output;
    }

    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }

    public function setUrlGenerator(UrlGeneratorInterface $urlGenerator): void
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function addGlobalProvider(callable $provider): void
    {
        $this->globalProviders[] = $provider;
    }

    protected function resolveGlobals(): void
    {
        foreach ($this->globalProviders as $provider) {
            $provider($this);
        }
    }

    /** @param bool $enable */
    public function setEnableDebugger(bool $enable): void
    {
        $this->enableDebugger = $enable;
    }

    /** @param bool $enabled */
    public function setEnableLint(bool $enabled): void
    {
        $this->enableLint = $enabled;
    }

    /** @param bool $enabled */
    public function setCacheEnabled(bool $enabled): void
    {
        $this->cacheEnabled = $enabled;
    }

    /** @param string $name @param callable $generator */
    public function addFilter(string $name, callable $generator): void
    {
        $this->filters->register($name, $generator);
    }

    /**
     * @INFO -> param scalar ...$args ?
     * @param array<string, scalar> $args
     */
    public function trans(string $key, ...$args): string
    {
        return $this->translation->trans($key, ...$args);
    }

    public function setTranslation(TranslationInterface $translation): void
    {
        $this->translation = $translation;
    }

    public function addPath(string $path): void
    {
        $this->templatePaths[] = rtrim($path, '/') . '/';
    }

    /**
     * @return array<string>
     */
    public function getPaths(): array
    {
        return $this->templatePaths;
    }

    public function setControllerContext(?BaseController $controller): void
    {
        $this->controllerContext = $controller;
    }

    public function addNamespace(string $name, string $path): void
    {
        $this->namespaces[$name] = rtrim($path, '/\\') . DIRECTORY_SEPARATOR;
    }

    /**
     * Fabryka: Tworzy silnik z gotowych komponentów (przydatne w testach / DI).
     *
     * @param TemplateCompiler $compiler
     * @param TemplateCache $cache
     */
    public static function createFromComponents(
        TemplateCompiler $compiler,
        TemplateCache $cache
    ): self {
        $engine = new self();
        $engine->compiler = $compiler;
        $engine->cache = $cache;
        $engine->filters = $compiler->getFilters();

        return $engine;
    }

    /**
     * Sprawdza czy debugger jest aktywny
     */
    public function isDebuggerEnabled(): bool
    {
        return $this->enableDebugger;
    }

    public function setCurrentTemplate(string $template): void
    {
        $this->currentTemplate = Paths::basePath() . '/templates/' . $template;
    }

    public function getCurrentTemplate(): ?string
    {
        return $this->currentTemplate;
    }

    public function getCurrentCompiledPath(): ?string
    {
        return $this->currentCompiledPath;
    }

    /**
     * Czyszczenie cache
     */
    protected function clearCache(): void
    {
        foreach (glob($this->cachePath . '*') as $file) {
            @unlink($file);
        }
    }

    // ===== PRIVATE =====

    /**
     * Kompiluje i zapisuje plik klasy PHP dla szablonu.
     *
     * @param string $template
     * @param string $cachePath
     * @param string $body
     * @throws TemplateException
     */
    private function writeCompiledTemplate(string $template, string $cachePath, string $body): void
    {
        $className = $this->getCompiledClassName($template);
        $classCode = $this->generateClass($className, $body);
        $tmpFile = $cachePath . '.tmp_' . uniqid('', true);

        file_put_contents($tmpFile, $classCode);

        if ($this->enableLint) {
            $this->runPhpLintOnFile($tmpFile);
        }

        rename($tmpFile, $cachePath);
    }

    /**
     * Renderuje layout rodzica.
     *
     * @param string $template
     * @param array<string, mixed> $data
     * @param array<string, string> $blocks
     * @param TemplateRuntime $child
     * @return string
     */
    private function renderParentTemplate(
        string $template,
        array $data,
        array $blocks,
        TemplateRuntime $child
    ): string {
        $templateFile = $this->resolveTemplateFile($template);
        $cachePath = $this->cache->getCachePath($template);

        if (!$this->cacheEnabled || !$this->cache->isFresh($templateFile, $cachePath)) {
            $body = $this->compiler->compile($templateFile);
            $this->writeCompiledTemplate($template, $cachePath, $body);
        }

        $this->currentCompiledPath = $cachePath;

        require_once $cachePath;

        $className = $this->getCompiledClassName($template);

        /** @var TemplateRuntime $parentTpl */
        $parentTpl = new $className();

        $child->inheritContextTo($parentTpl);

        $parentTpl->blocks = $blocks;

        return $parentTpl->render($data);
    }

    /**
     * Zwraca plik szablonu.
     */
    private function resolveTemplateFile(string $template): string
    {
        // Namespace: @installer/... (optional for modules)
        if (str_starts_with($template, '@')) {
            [$ns, $path] = explode('/', substr($template, 1), 2);

            if (!isset($this->namespaces[$ns])) {
                throw new TemplateException(
                    "Unknown view namespace: {$ns}",
                    $template
                );
            }

            $candidate = $this->namespaces[$ns] . ltrim($path, '/');

            if (is_file($candidate)) {
                return $candidate;
            }

            throw new TemplateException(
                "Template not found in namespace '{$ns}': {$path}",
                $template
            );
        }

        // Standard: templates/
        foreach ($this->templatePaths as $basePath) {
            $candidate = $basePath . ltrim(str_replace('\\', '/', $template), '/');

            if (is_file($candidate)) {
                return $candidate;
            }
        }

        throw new TemplateException(
            "Template not found: {$template}",
            $template
        );
    }

    /**
     * Generuje klasę PHP dla szablonu.
     */
    private function generateClass(string $className, string $body): string
    {
        return <<<PHP
            <?php
            if (!class_exists('{$className}')) {
                class {$className} extends \\Dbm\\Views\\TemplateRuntime {
                    public function render(array \$data = []): string {
                        extract((array)\$data, EXTR_SKIP);
                        ob_start(); ?>
            {$body}
            <?php
                        return (string) ob_get_clean();
                    }
                }
            }
            PHP;
    }

    /**
     * Sprawdza poprawność składni PHP w pliku.
     *
     * @throws TemplateException
     */
    private function runPhpLintOnFile(string $filePath): void
    {
        $php = defined('PHP_BINARY') ? PHP_BINARY : null;

        if (!$php || !is_executable($php)) {
            throw new TemplateException(
                'Unable to locate PHP binary for linting.',
                $filePath
            );
        }

        $cmd = escapeshellarg($php) . ' -l ' . escapeshellarg($filePath) . ' 2>&1';
        exec($cmd, $output, $retval);

        if ($retval !== 0) {
            @unlink($filePath);
            throw new TemplateException(
                "PHP syntax check failed:\n" . implode("\n", $output),
                $filePath
            );
        }
    }

    /**
     * Zapisuje plik debug w katalogu PATH_DEBUG (jeśli aktywny debugger).
     */
    private function enableDebugger(string $template, string $body): void
    {
        if (!$this->enableDebugger) {
            return;
        }

        if (!is_dir($this->debugPath)) {
            mkdir($this->debugPath, 0o777, true);
        }

        $key = str_replace(['/', '\\', '.'], '_', ltrim($template, '/'));
        $debugFile = $this->debugPath . '/' . $key . '_debug.php';

        if (file_put_contents($debugFile, $body) === false) {
            throw new TemplateException(
                "Failed to write debug file: {$debugFile}",
                $template
            );
        }
    }

    /**
     * Generuje nazwy klas
     */
    private function getCompiledClassName(string $template): string
    {
        return '__Tpl_' . sha1($template);
    }
}
