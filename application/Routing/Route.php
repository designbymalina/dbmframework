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

namespace Dbm\Routing;

final class Route
{
    public string $httpMethod;
    public string $path;
    public string $controller;
    public string $action;
    public ?string $name;
    public ?string $permission;

    private ?string $compiledPattern = null;
    private ?array $paramNames = null;

    public function __construct(
        string $httpMethod,
        string $path,
        string $controller,
        string $action,
        ?string $name = null,
        ?string $permission = null
    ) {
        $this->httpMethod = strtoupper($httpMethod);
        $this->path = self::normalizePath($path);
        $this->controller = $controller;
        $this->action = $action;
        $this->name = $name;
        $this->permission = $permission;
    }

    public function getCompiledPattern(): string
    {
        if ($this->compiledPattern === null) {
            $this->compile();
        }

        return $this->compiledPattern;
    }

    public function getParamNames(): array
    {
        if ($this->paramNames === null) {
            $this->compile();
        }

        return $this->paramNames;
    }

    ### Helpers for route cache ###

    public function isStatic(): bool
    {
        return !str_contains($this->path, '{');
    }

    public function toArray(): array
    {
        return [
            'method' => $this->httpMethod,
            'path' => $this->path,
            'controller' => $this->controller,
            'action' => $this->action,
            'name' => $this->name,
            'permission' => $this->permission,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['method'],
            $data['path'],
            $data['controller'],
            $data['action'],
            $data['name'] ?? null,
            $data['permission'] ?? null
        );
    }

    /**
     * Uniwersalna fabryka
     */
    public static function fromMethod(
        string $method,
        string $path,
        array $handler,
        ?string $name = null,
        ?string $permission = null
    ): self {
        return new self(
            strtoupper($method),
            $path,
            $handler[0],
            $handler[1],
            $name,
            $permission
        );
    }

    private function compile(): void
    {
        preg_match_all('/\{(.*?)\}/', $this->path, $matches);

        $this->paramNames = $matches[1];

        $pattern = preg_replace(
            ['/\\{#\\}/', '/\\{(.*?)\\}/'],
            ['([a-zA-Z0-9-]+)', '([a-zA-Z0-9-]+(?:\\.[a-zA-Z0-9-]+)*)'],
            $this->path
        );

        $this->compiledPattern = "#^{$pattern}$#";
    }

    /**
     * Normalizacja ścieżki (wspólna dla wszystkich tras)
     */
    private static function normalizePath(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '/' ? '/' : rtrim($path, '/');
    }
}
