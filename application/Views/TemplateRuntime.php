<?php

/**
 * Application: DbM Framework
 * A lightweight PHP framework for building web applications.
 *
 * @author Artur Malinowski
 * @copyright Design by Malina (All Rights Reserved)
 * @license MIT
 * @link https://www.dbm.org.pl
 */

declare(strict_types=1);

namespace Dbm\Views;

use Dbm\Http\Controller\BaseController;
use ReflectionProperty;
use BadMethodCallException;
use Throwable;

/**
 * Klasa bazowa dla wszystkich wygenerowanych szablonów (__Tpl_*).
 * Zapewnia obsługę bloków, dziedziczenia layoutów, delegację metod i dostęp do kontrolera.
 */
abstract class TemplateRuntime extends TemplateFeature
{
    /** @var TemplateEngine|null Silnik szablonów (wstrzykiwany przez TemplateEngine) */
    public ?TemplateEngine $engine = null;

    /** @var BaseController|null Instancja kontrolera powiązana z szablonem */
    public ?object $controller = null;

    /** @var string|null Nazwa szablonu nadrzędnego (layoutu) */
    public ?string $parent = null;

    /** @var array Dane przekazywane do szablonu */
    public array $data = [];

    /** @var array Zawartość zdefiniowanych bloków (dziecko → rodzic override) */
    public array $blocks = [];

    /** @var array Stos nazw bloków podczas renderowania (startBlock/endBlock) */
    public array $blockStack = [];

    /**
     * Magiczna metoda wywoływana, gdy brak metody w runtime.
     * Przekierowuje wywołania do kontrolera lub silnika.
     *
     * @param string $method Nazwa metody.
     * @param array $args Argumenty metody.
     * @return mixed
     * @throws BadMethodCallException
     */

    public function __call(string $method, array $args)
    {
        // 1. GLOBALS jako callable
        $global = $this->global($method);

        if ($global instanceof \Closure) {
            return $global(...$args);
        }

        // 2. Controller
        if ($this->controller && method_exists($this->controller, $method)) {
            return $this->controller->$method(...$args);
        }

        // 3. Engine
        if ($this->engine && method_exists($this->engine, $method)) {
            return $this->engine->$method(...$args);
        }

        throw new BadMethodCallException(
            "Undefined method '{$method}' in template context."
        );
    }

    /**
     * Magiczny getter — umożliwia dostęp do właściwości kontrolera lub silnika.
     *
     * @param string $name Nazwa właściwości.
     * @return mixed|null
     */
    public function __get(string $name)
    {
        // Najpierw kontroler
        if ($this->controller) {
            // Próba gettera w kontrolerze
            $getter = 'get' . ucfirst($name);
            if (method_exists($this->controller, $getter)) {
                return $this->controller->$getter();
            }
        }

        // Następnie engine
        if ($this->engine && property_exists($this->engine, $name)) {
            $ref = new ReflectionProperty($this->engine, $name);
            if ($ref->isPublic()) {
                return $this->engine->$name;
            }
        }

        return null;
    }

    /**
     * Metoda renderująca – implementowana w klasie wygenerowanej przez TemplateEngine.
     *
     * @param array $data Dane wejściowe do szablonu.
     * @return string Zrenderowany HTML.
     */
    abstract public function render(array $data = []): string;

    /**
     * Ustawia dane w czasie wykonania.
     *
     * @param array $data Dane przekazane do szablonu.
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * Renderuje częściowy szablon (include / partial).
     *
     * @param string $template Nazwa pliku szablonu.
     * @param array $data Dane lokalne przekazywane do części.
     * @return string Zrenderowana zawartość części.
     */
    public function renderPartial(string $template, array $data = []): string
    {
        $engine = $this->getEngineInstance();
        $vars = array_merge($this->data ?? [], $data);

        try {
            $output = $engine->renderContent(
                $template,
                $vars,
                $this->controller instanceof BaseController ? $this->controller : null
            );

            return rtrim($output) . PHP_EOL;
        } catch (Throwable $exception) {
            if ($engine->isDebuggerEnabled()) {
                return $this->debugRenderPartial($exception);
            }

            $this->logger()->critical(
                'Template error - ' . $exception->getMessage(),
                ['exception' => $exception, 'template'  => $template]
            );

            return 'Err';
        }
    }

    /**
     * Ustawia nazwę szablonu nadrzędnego.
     *
     * @param string $parent Nazwa pliku layoutu.
     */
    public function extend(string $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * Rozpoczyna definiowanie bloku (np. {% block content %}).
     *
     * @param string $name Nazwa bloku.
     */
    public function startBlock(string $name): void
    {
        $this->blockStack[] = $name;
        ob_start();
    }

    /**
     * Kończy blok i zapisuje jego zawartość w tablicy blocks.
     */
    public function endBlock(): void
    {
        $content = (string) @ob_get_clean();
        $name = array_pop($this->blockStack);

        if (!isset($this->blocks[$name])) {
            $this->blocks[$name] = $content;
            return;
        }

        if (str_contains($content, '@parent')) {
            $this->blocks[$name] = str_replace('@parent', $this->blocks[$name], $content);
        } else {
            $this->blocks[$name] = $content;
        }
    }

    /**
     * Zwraca zawartość bloku o danej nazwie.
     *
     * @param string $name Nazwa bloku.
     * @return string Zawartość bloku lub pusty string.
     */
    public function yieldBlock(string $name): string
    {
        return $this->blocks[$name] ?? '';
    }

    /**
     * Zwraca instancję silnika lub rzuca wyjątek, jeśli nie została wstrzyknięta.
     *
     * @return TemplateEngine
     * @throws TemplateException
     */
    protected function getEngineInstance(): TemplateEngine
    {
        if ($this->engine instanceof TemplateEngine) {
            return $this->engine;
        }

        throw new TemplateException('Template engine instance not available in runtime.');
    }

    /**
     * Kopiuje kontekst runtime (engine, controller, globals, translation)
     * do innej instancji TemplateRuntime (np. parent layout).
     */
    public function inheritContextTo(TemplateRuntime $target): void
    {
        // Engine
        $target->engine = $this->engine;

        // Controller
        $target->controller = $this->controller;

        // Globals (TemplateFeature)
        foreach ($this->globals() as $key => $value) {
            $target->setGlobal($key, $value);
        }
    }

    // ### Helpers

    /**
     * Renderuje partial w trybie debugowania.
     *
     * @param Throwable $exception
     * @return string
     */
    private function debugRenderPartial(Throwable $exception): string
    {
        return '<pre style="color:red">'
            . 'Partial error: '
            . "\n" . htmlspecialchars($exception::class . ': ' . $exception->getMessage())
            . "\n" . htmlspecialchars($exception->getTraceAsString())
            . '</pre>';
    }
}
