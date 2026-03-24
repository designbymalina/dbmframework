# Dependency Injection

## Overview

DbM Framework uses a lightweight Dependency Injection container (`DependencyContainer`) for managing application services.

The container allows you to:

* Register services manually
* Share instances using `singleton`
* Resolve dependencies automatically (autowiring)
* Organize modules via service providers

Service configuration file:

```bash
application/services.php
```

---

## Service Registration

Services are registered inside a closure:

```php
return function (DependencyContainer $container): void {
    // services here
};
```

---

## `singleton()` vs `set()`

### singleton (recommended)

Creates a shared instance (one per request lifecycle):

```php
$container->singleton(Logger::class, fn() => new Logger());
```

or shorter:

```php
$container->singleton(RouteCollection::class);
```

---

### set

Creates a new instance each time (or custom logic):

```php
$container->set(DatabaseInterface::class, fn() =>
    isConfigDatabase() ? DatabaseFactory::createDatabase() : null
);
```

---

## Example Configuration

```php
$container->singleton(Request::class);
$container->singleton(SessionManager::class);
$container->singleton(CookieManager::class);

$container->singleton(
    FlashBag::class,
    fn($c) => new FlashBag($c->get(SessionManager::class))
);
```

---

## Dependency Resolution

### Constructor Injection

Dependencies are automatically injected into constructors:

```php
class IndexController
{
    public function __construct(
        private IndexService $service,
        private FlashBag $flash
    ) {}
}
```

No manual `$container->get()` required.

---

### Controller Example

```php
class IndexController extends BaseController
{
    public function __construct(
        private readonly IndexService $service,
        private readonly FlashBag $flash
    ) {}

    public function index(): ResponseInterface
    {
        $this->flash->set('Application is ready');

        return $this->render('index/start.phtml', [
            'meta' => $this->service->getMetaIndex(),
        ]);
    }
}
```

---

## Autowiring

The container automatically resolves:

* Constructor dependencies
* Controller action arguments

### Example

```php
public function show(Request $request): ResponseInterface
```

If `Request` is registered → it will be injected automatically.

---

## Dynamic Resolution

You can still resolve services manually:

```php
$container->get(SomeService::class);
```

If the class:

* exists
* has resolvable dependencies

it will be created automatically.

---

## Routing Integration

The DI container is deeply integrated with routing:

* `ControllerResolver` creates controllers
* `ActionArgumentResolver` injects method arguments
* `Router` orchestrates full request lifecycle

---

## Modules (Service Providers)

Modules can register their own services:

```php
ModuleServiceProvider::register($container);
```

This allows modular architecture and feature isolation.

---

## Template Integration

Services can be exposed to templates:

```php
$view->setGlobal(
    'headerNavigation',
    fn() => $this->container
        ->get(NavigationUtility::class)
        ->getHeader($request->getUri()->getPath())
);
```

---

### Usage in Template

```html
{% $headerNavigation = $this->headerNavigation(); %}

{% if (is_array($headerNavigation)): %}
```

---

## Core Service Groups

### HTTP

* `Request`
* `SessionManager`
* `CookieManager`

---

### Routing

* `Router`
* `RouteCollection`
* `RouteMatcher`
* `ControllerResolver`
* `ActionArgumentResolver`

---

### View

* `TemplateEngine`
* `FlashBag`

---

### Security

* `AccessGuard`
* `CsrfTokenManager`

---

### Localization

* `LanguageService`
* `Translation`
* `TranslationLoader`

---

### Infrastructure

* `Logger`
* `FileSystem`
* `PHPMailerSender`

---

## Best Practices

* Use `singleton()` for most services
* Use `set()` for dynamic or optional services
* Prefer constructor injection over manual `get()`
* Keep services stateless when possible
* Explicitly register important services (avoid overusing auto-resolution)

---

## When to Use `set()` Instead of `singleton()`

Use `set()` when:

* Service may return `null`
* Service depends on runtime conditions
* You need a new instance each time

---

## Summary

DbM DI container provides:

* High performance (no heavy reflection scanning)
* Full control over service creation
* Automatic dependency resolution
* Clean architecture with modules

**DI DbM** is: not Laravel, not Symfony, just a **lightweight, controlled container**.

It combines simplicity with flexibility, making it suitable for both small apps and complex modular systems.

---
