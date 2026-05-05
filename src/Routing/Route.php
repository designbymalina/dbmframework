<?php

/**
 * Application: DbM Framework
 * A lightweight PHP framework for building web applications.
 *
 * @author Artur Malinowski
 * @copyright Design by Malina (All Rights Reserved)
 * @license MIT
 * @link https://www.dbm.org.pl
 *
 * @INFO Można rozbudować uprawnienia o $permissions - nie używane.
 * Wówczas jedank lepiej przejść od razu do budowy modułu z uprawnieniami...
 *
 * Ns skróty w AuthMiddleware (bez modułu):
 * $permissions = $route?->getPermissions() ?? [];
 * if ($permissions) {
 *     $this->guard->checkPermissions($permissions);
 * }
 * return $handler->handle($request);
 *
 * Przykład z użyciem $permissions:
 * $routes->get($panel . '/', [PanelController::class, 'index'], 'panel')
 *     ->permissions([
 *         PermissionEnum::ADMIN_ACCESS->value,
 *         PermissionEnum::CONTENT_EDIT->value,
 *     ]);
 *
 * @INFO Można zrobić:
 * - RouteCacheCompiler (compile -> PHP array dump) - boost wydajności przy dużych routach.
 * - Attribute routing
 */

declare(strict_types=1);

namespace Dbm\Routing;

final class Route
{
    public string $httpMethod;
    public string $path;

    public ?string $controller = null;
    public ?string $action = null;

    public ?string $name;
    public ?string $permission;

    /**
     * callable(Request $request): Response
     *
     * @var callable|array{0: class-string, 1: string}|string|null
     */
    public mixed $handler = null;

    /** @var string[] */
    public array $middleware = [];

    private ?string $compiledPattern = null;

    /** @var string[]|null */
    private ?array $paramNames = null;

    /** @var string[] */
    private array $permissions = [];

    /** @var array<string, string> */
    private array $defaults = [];

    /**
     * @param callable|array{0: class-string, 1: string}|string|null $handler
     */
    public function __construct(
        string $httpMethod,
        string $path,
        array|string|callable|null $handler,
        ?string $name = null,
        ?string $permission = null
    ) {
        $this->httpMethod = strtoupper($httpMethod);
        $this->path = self::normalizePath($path);
        $this->name = $name;
        $this->permission = $permission;

        $this->resolveHandler($handler);
    }

    public function getCompiledPattern(): string
    {
        if ($this->compiledPattern === null) {
            $this->compile();
        }

        return $this->compiledPattern;
    }

    /**
     * @return array<string, mixed>
     */
    public function getParamNames(): array
    {
        if ($this->paramNames === null) {
            $this->compile();
        }

        return $this->paramNames;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getMethod(): string
    {
        return $this->httpMethod;
    }

    /**
     * @param array<string, string> $defaults
     */
    public function defaults(array $defaults): self
    {
        $this->defaults = array_merge($this->defaults, $defaults);

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getDefaults(): array
    {
        return $this->defaults;
    }

    public function permission(string $permission): self
    {
        $this->permission = $permission;
        return $this;
    }

    public function getPermission(): ?string
    {
        return $this->permission;
    }

    /**
     * @param string[] $permissions
     */
    public function permissions(array $permissions): self
    {
        $this->permissions = $permissions;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    ### Helpers for route cache ###

    public function isStatic(): bool
    {
        return !str_contains($this->path, '{');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        // @INFO Optional
        // if ($this->handler instanceof \Closure) {
        //     throw new \RuntimeException('Cannot cache closure routes.');
        // }

        return [
            'method' => $this->httpMethod,
            'path' => $this->path,
            'controller' => $this->controller,
            'action' => $this->action,
            'name' => $this->name,
            'permission' => $this->permission,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $handler = null;

        if (!empty($data['controller']) && !empty($data['action'])) {
            $handler = [$data['controller'], $data['action']];
        }

        return new self(
            $data['method'],
            $data['path'],
            $handler,
            $data['name'] ?? null,
            $data['permission'] ?? null
        );
    }

    /**
     * Uniwersalna fabryka
     *
     * @param array{0: string, 1: string} $handler
     */
    public static function fromMethod(
        string $method,
        string $path,
        array|string|callable|null $handler,
        ?string $name = null,
        ?string $permission = null
    ): self {
        return new self(
            strtoupper($method),
            $path,
            $handler,
            $name,
            $permission
        );
    }

    /**
     * @param string|string[] $middleware
     */
    public function middleware(string|array $middleware): self
    {
        $this->middleware = array_merge(
            $this->middleware,
            (array) $middleware
        );

        return $this;
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
     * @param callable|array{0: class-string, 1: string}|string|null $handler
     */
    private function resolveHandler(array|string|callable|null $handler): void
    {
        $this->handler = $handler;

        if (is_array($handler) && count($handler) === 2) {
            $this->controller = $handler[0];
            $this->action = $handler[1];
            return;
        }

        if (is_string($handler) && str_contains($handler, '@')) {
            [$this->controller, $this->action] = explode('@', $handler, 2);
            return;
        }

        $this->controller = null;
        $this->action = null;
    }

    private static function normalizePath(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '/' ? '/' : rtrim($path, '/');
    }
}
