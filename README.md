# DBM Framework - Lightweight PHP Framework

**DBM Framework is a lightweight PHP application engine for developers who want full control over their application architecture.**

It provides the infrastructure needed to build PHP applications - routing, middleware, Dependency Injection, events, data access, templates, and infrastructure components - without imposing a single, closed application architecture.

**You decide how your application is built.**

Fast. Flexible. Modular. PSR-compliant.

[![PHP](https://img.shields.io/badge/PHP-%3E%3D8.1-blue)](https://www.php.net/)
[![PSR](https://img.shields.io/badge/PSR-1%2C%204%2C%2011%2C%2012-green)](https://www.php-fig.org/)
[![Composer](https://img.shields.io/badge/Composer-ready-orange)](https://getcomposer.org/)
[![License](https://img.shields.io/badge/license-MIT-blue)](LICENSE)

---

## What is DBM Framework?

DBM Framework is a **PHP application execution engine**.

Its purpose is to provide the core infrastructure on which you can build your own application:

* HTTP handling,
* routing,
* middleware,
* Dependency Injection,
* event system,
* data access,
* templates,
* sessions and cookies,
* filesystem,
* logging,
* error handling,
* translations,
* validation,
* and other infrastructure components.

DBM does not try to define the entire application.

It does not impose a specific CMS, administration panel, domain model, or way of organizing the business layer.

**The framework provides the engine. The application layer is yours.**

---

## Why DBM?

Laravel and Symfony are powerful and extensive ecosystems that work extremely well for many types of projects.

DBM takes a different approach.

Instead of maximizing the number of features and automation mechanisms, it focuses on:

* lightweight architecture,
* low runtime overhead,
* explicit configuration,
* modularity,
* predictable execution flow,
* and full control over the application layer.

DBM is not an attempt to replace Laravel or Symfony.

It is an **innovative approach to building PHP applications** - particularly for developers who prefer to consciously define their architecture instead of relying on extensive mechanisms that automate a large part of the application.

---

## Dybem Architecture

DBM Framework is the foundation of the larger **Dybem** ecosystem.

```text
                    DYBEM ECOSYSTEM

┌──────────────────────────────────────────────┐
│              Your Application                │
│       Controllers · Services · Domain        │
├──────────────────────────────────────────────┤
│                DBM Platform                  │
│        CMS · Admin · Auth · Modules          │
│                 (optional)                   │
├──────────────────────────────────────────────┤
│               DBM Framework                  │
│ HTTP · Routing · Middleware · DI · Events    │
│ Database · Templates · Sessions · Logging    │
└──────────────────────────────────────────────┘
```

### DBM Framework

A lightweight execution engine providing the core application infrastructure.

### Application Layer

The layer defined by the developer.

You can create your own application structure, modules, services, business domain, and code organization.

### DBM Platform

An optional, ready-made application layer built on DBM Framework.

It provides, among other things, a CMS, administration panel, authentication, and application modules.

---

## Performance

DBM Framework was designed with **minimal runtime overhead** in mind.

The following results were obtained during testing:

| Scenario              | Response time |
| --------------------- | ------------: |
| Server cache enabled  |       ~1.9 ms |
| Without cache         |       ~3–4 ms |
| Database + templating |         ~5 ms |

The measurements were performed on an external server in a development environment.

Results depend on hardware, PHP configuration, web server, database, cache, application code, and system load.

The values above should therefore be treated as **results from specific tests**, not as a universal benchmark for all applications.

DBM's performance primarily comes from its architectural principles:

* a small core,
* limited automation,
* explicit dependency registration,
* no heavy reflection-based autowiring,
* and a limited number of execution layers involved in handling a request.

---

## Features

DBM Framework provides a core set of components needed to build PHP applications.

### HTTP and Application

* HTTP routing,
* middleware and request/response pipeline,
* Dependency Injection,
* event and listener system,
* application lifecycle handling,
* optional CLI integration.

### Data and Presentation

* DBM View Engine,
* Query Builder,
* data access layer compatible with Doctrine DBAL,
* translation system,
* form validator.

### Infrastructure

* sessions and cookies,
* filesystem,
* file and image uploads,
* logger,
* error handler,
* mailer interface,
* helpers,
* sanitizers.

Components are modular and can be replaced with your own implementations.

For example, the built-in view engine can be replaced with Twig.

---

## Modular Architecture

DBM Framework supports a **modular monolith** approach.

An application can be divided into independent modules with clearly defined responsibilities while keeping the simplicity of deploying a single system.

Example request flow:

```text
HTTP Request
     ↓
  Routing
     ↓
 Middleware
     ↓
 Controller
     ↓
 Services / Domain
     ↓
  Response
```

The core framework elements include:

* request lifecycle engine,
* router,
* middleware pipeline,
* Dependency Injection container,
* event system,
* infrastructure components.

---

## Explicit Dependency Injection

DBM Framework deliberately avoids heavy reflection-based autowiring.

Dependencies are registered explicitly using a lightweight DI container.

For example:

```php
$container->singleton(MyService::class, function ($container) {
    return new MyService(
        $container->get(MyRepository::class)
    );
});
```

or:

```php
$container->set(MyService::class, $service);
```

This approach provides:

* predictable execution flow,
* lower runtime overhead,
* simpler diagnostics,
* and full control over dependency creation.

This does not mean automation is bad.

DBM simply follows a different principle:

**Important dependencies should be visible and consciously configured.**

---

## Template Engine

DBM Framework uses the lightweight **DBM View Engine** by default.

Key principles:

* no external dependencies,
* direct use of PHP,
* no additional DSL,
* extensibility through callbacks.

The engine can be replaced with another solution, such as Twig.

---

## Framework ≠ Ready-Made Application

DBM Framework is not a ready-made CMS or complete application.

It is the foundation on which you can build your own system.

```text
DBM Framework
      ↓
Your Application Layer
      ↓
Your Business Logic
      ↓
Your Application
```

If you do not want to build the application layer from scratch and need a ready-made solution, you can use **DBM Platform**.

---

## DBM Platform

**DBM Platform** is a ready-made application layer built on DBM Framework.

It extends the framework with, among other things:

* administration panel,
* authentication,
* CMS,
* module management,
* application layer,
* ready-made system structure.

[DBM Platform on GitHub](https://github.com/designbymalina/dbmplatform)

Framework and Platform represent two different ways of working with the Dybem ecosystem:

| DBM Framework                                 | DBM Platform                                   |
| --------------------------------------------- | ---------------------------------------------- |
| application engine                            | ready-made application layer                   |
| infrastructure                                | infrastructure + application features          |
| your own architecture                         | ready-made system structure                    |
| for developers building their own application | for projects that need a ready-made foundation |

---

## Philosophy

DBM Framework is built around several simple principles:

* **no global state,**
* **no hidden magic,**
* **explicit configuration,**
* **composition over excessive inheritance,**
* **modularity without unnecessary complexity,**
* **control over the architecture remains with the developer.**

The framework should help developers build applications, not decide how those applications must be structured.

---

## Project History

DBM Framework evolved through several stages - from a simple microframework to the current architecture that forms the foundation of the Dybem ecosystem.

* **v1 / v2** - project beginnings and architectural experiments,
* **v3 / v4** - lightweight monolithic microframework,
* **v5** - transition to a modular monolith architecture,
* **v6** - separation of the framework engine from the application layer and development of the Dybem ecosystem.

Version v6 focuses on:

**lightweight architecture · modularity · predictability · control**

---

## Installation

### Requirements

* PHP 8.1 or newer,
* Composer.

Install via Composer:

```bash
composer require designbymalina/dbmframework
```

After installation, you need to create an application layer that configures and runs the framework.

---

## Basic Usage

DBM Framework works as an application engine and requires its own application layer.

A minimal example:

```php
declare(strict_types=1);

use Dbm\Core\DotEnv;
use Dbm\Core\Paths;
use Dbm\Http\Emitter\ResponseEmitter;

$baseDirectory = realpath(dirname(__DIR__));

require_once $baseDirectory . '/bootstrap/runtime.php';

initRuntime($baseDirectory);

require_once $baseDirectory . '/../vendor/autoload.php';

require_once $baseDirectory . '/bootstrap/support.php';

Paths::setBasePath($baseDirectory);

$envPath = Paths::basePath() . '/.env';

if (file_exists($envPath)) {
    (new DotEnv($envPath))->load();
}

$appFactory = require Paths::basePath() . '/bootstrap/app.php';

$app = $appFactory();

$response = $app->run();

(new ResponseEmitter())->emit($response);
```

Application startup process:

```text
1. Set the base path
          ↓
2. Load the autoloader
          ↓
3. Create the application through a factory
          ↓
4. Run the application
          ↓
5. Request → Response
```

A more complete application example can be found in:

```text
/example
```

---

## Routing

Example route definition:

```php
$router->get(
    '/path',
    [NameController::class, 'methodName'],
    'route_name'
);
```

It maps a URL path to a specific controller method.

Documentation:

* [Web Routing](_Docs/03_01-web-routing.md)
* [API Routing](_Docs/03_02-api-routing.md)

---

## Development

Clone the repository:

```bash
git clone https://github.com/designbymalina/dbmframework.git

cd dbmframework

composer install
```

You can also use GitHub CLI.

---

## When to Use DBM Framework?

DBM Framework can be a good choice if you:

* are building your own application from scratch,
* need a lightweight execution layer,
* care about low runtime overhead,
* want to define your application architecture yourself,
* are building an API or backend,
* are creating a modular web application,
* need greater control over dependencies and application lifecycle.

### When DBM May Not Be the Best Choice

DBM is not a "plug & play" framework.

It requires you to create your own application layer and make some architectural decisions yourself.

If you need a highly extensive ecosystem with a large number of ready-made packages, integrations, and established conventions, Laravel or Symfony may be a better choice.

If, however, you want a **lightweight foundation and the freedom to decide how your application should be structured**, DBM gives you that possibility.

---

## Projects Built on DBM Framework

DBM Framework can serve as a foundation for different types of applications:

* **DBM Platform**
* API systems,
* modular web applications,
* administration systems,
* business applications,
* custom application layers.

---

## Dybem Ecosystem

DBM Framework is part of the larger **Dybem** ecosystem.

```text
Dybem

│
├── DBM Framework
│      └── application engine
│
├── DBM Platform
│      └── ready-made application layer
│
└── Modules / Extensions
       └── additional ecosystem components
```

More information:

https://www.dybem.com/

---

## Documentation

Framework documentation is available in the `/_Docs` directory.

Key resources:

* [Introduction](_Docs/01_01-introduction.md)
* [Architecture](_Docs/01_00-1-architecture.md)
* [Ecosystem](_Docs/01_00-2-ecosystem.md)
* [Web Routing](_Docs/03_01-web-routing.md)
* [API Routing](_Docs/03_02-api-routing.md)

**Documentation is continuously being developed and will be expanded along with the project.**

---

## Open Source

DBM Framework is an open source project developed as part of the Dybem ecosystem.

If you are interested in a PHP approach based on lightweight architecture, explicit configuration, and control over the application layer:

⭐ **Leave a Star on GitHub.**

* Check the application example.
* Try the framework.
* Share your feedback or open an Issue.

[DBM Framework on GitHub](https://github.com/designbymalina/dbmframework)

---

## License

This project is licensed under the MIT License.

Copyright (c) Design by Malina
