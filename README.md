# DBM Framework – Lightweight PHP Framework Focused on Performance

DBM Framework is a lightweight PHP application engine designed for developers who want full architectural control without the complexity of heavyweight frameworks.

A framework focused on performance, simplicity, and complete control over the application architecture.

Designed for high-performance modular PHP applications.

**Fast. Flexible. PSR-compliant.**

[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D8.1-blue)](http://php.net)
[![PSR](https://img.shields.io/badge/PSR-1%2C%204%2C%2011%2C%2012-green)](https://www.php-fig.org/)
[![Build](https://img.shields.io/badge/build-passing-success)]()
[![Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen)]()
[![Composer](https://img.shields.io/badge/composer-ready-orange)](https://getcomposer.org/)
[![Speed](https://img.shields.io/badge/performance-ultra%20fast-red)]()
[![License](https://img.shields.io/badge/license-DbM-orange)](https://dbm.org.pl)

The DBM v6 framework was created as a response to the excessive complexity in modern PHP frameworks.

It does not impose a complete application structure - it provides ready-made components that can be used or replaced.

## Performance

The framework was designed with minimal runtime overhead in mind:

- ~1.9 ms response time (with server caching enabled)
- ~3–4 ms without caching
- ~5 ms with database and templating

Measured on an external server in a development environment.

Results depend on configuration and load.

> "Laravel and Symfony are powerful. DBM is fast."

## Why DBM Framework?

Unlike large frameworks:

- it doesn't dictate the application's structure
- it doesn't hide logic behind "magic"
- it doesn't introduce unnecessary layers

It gives you full control over your code and performance.

DBM is a framework that doesn't fight the developer - it lets them work the way they want.

## Features

The framework provides the minimal set of tools needed to build applications—without unnecessary layers and overhead.

- Modular architecture (PSR-4 compliant)
- Lightweight middleware pipeline (PSR-style request flow)
- Flexible routing system
- Lightweight Dependency Injection container (no reflective magic)
- Event-driven extensibility
- CLI support via the application layer
- Framework core only (no CMS, no platform, no UI layer)
- Minimal runtime overhead (focused on high performance)

No hidden mechanisms or automatic configuration – everything works transparently and predictably.

## Performance First Architecture

DbM Framework was designed with one primary goal: maximum performance with minimal overhead.

From routing and dependency loading to templating and API responses - every layer is optimized for speed and flexibility.

⭐ If you like the project, leave a star on GitHub.

![DBM Framework](https://dbm.org.pl/images/page/packages/dbmframework-benchmarkach.png)

### Projects based on DBM Framework

- DBM CMS
- DBM Platform
- API systems
- Modular web applications

## Built-in components

The framework includes a set of lightweight infrastructure components needed to build web applications.

### HTTP and Application

- HTTP routing
- Middleware (request/response pipeline)
- Dependency Injection container
- Event and listener system
- CLI console mechanism (implemented in the application layer)

### Data and presentation

- Template system (DbM View Engine)
- Data access layer (Doctrine DBAL compatible Query Builder)
- Translation system
- Form validator

### Infrastructure

- Session system and cookies
- File system + file and image upload
- Logger
- Error handler
- Mailer interface
- Helpers and sanitizers

The components are lightweight, modular, and can be replaced with a custom implementation (e.g., Twig instead of the built-in view engine).

The framework was designed as a modular monolith – components can be developed independently, maintaining the simplicity of implementing a single application.

## Template Engine

The framework uses the lightweight DbM View Engine by default.

- Fast and dependency-free
- Based directly on PHP (no DSL)
- Extensible via callbacks

Can be replaced with another engine (e.g. Twig).

## Philosophy

The DBM framework separates areas:

- **Framework = execution engine**
- **Application Layer = user-defined**
- **CMS / Platform = optional extensions**

The core is fast, predictable, and reusable.

## Project History

The DBM Framework evolved in stages – from a simple micro-framework to a full application ecosystem.

- **v1 / v2** - project beginnings and architectural experiments
- **v3 / v4** - lightweight monolithic microframework
- **v5** - transition to a modular monolith architecture
- **v6** - separation of the framework engine from the application layer and development of the DBM ecosystem

The current version focuses on performance, modularity, and full control over the application architecture.

## Installation

Requirements:

- PHP 8.1 or later
- Composer

```bash
composer require designbymalina/dbmframework
```

After installation, create the application layer (bootstrap) responsible for running the framework.

## Basic Usage

DBM Framework is not a standalone application. It must be used within its own application layer.

**Example:**

Below is a minimal example of running an application based on DBM Framework.

```php
// example/index.php

declare(strict_types=1);

use Dbm\Core\Paths;

$baseDirectory = realpath(dirname(__DIR__));

require_once $baseDirectory . '/vendor/autoload.php';

Paths::setBasePath($baseDirectory);

$appFactory = require __DIR__ . '/bootstrap/app.php';

$app = $appFactory();

$response = $app->run();

$response->send();

```

**Process:**

1. Set the base path
2. Load the autoloader
3. Create the application via the factory
4. Start the request -> response lifecycle

### Minimal application structure

- bootstrap/app.php - application factory
- bootstrap/services.php - DI container configuration
- bootstrap/controller.php - example controller

```bash
php -S localhost:8000 example/index.php
```

URL: `http://localhost:8000/`

### Routing example

```php
$router->get('/path', [NameController::class, 'methodName'], 'route_name');
```

A simple example of mapping a URL path to a controller.

Details:

- [Web Routing](_Docs/03_01-web-routing.md)
- [API Routing](_Docs/03_02-api-routing.md)

## Modular Architecture

The DBM Framework supports a modular monolith approach.

An application can be developed as a set of independent modules with a clear separation of concerns while maintaining the simplicity of a single system implementation.

**Architecture Overview**

The framework operates based on the following cycle:

Request -> Routing -> Middleware -> Controller -> Response

More: [Architecture](_Docs/01_00-1-architecture.md)

**The DBM framework consists of:**

- kernel (request lifecycle)
- router (flexible routing)
- middleware dispatcher
- container (DI)

## Design Principles

- no global state
- no hidden magic
- explicit configuration
- composition instead of inheritance

## Development

Cloning the repository and installing dependencies:

```bash
git clone https://github.com/designbymalina/dbmframework
cd dbmframework
composer install
```

or via GitHub CLI.

## When to use DBM Framework

The framework is suitable when:

- you're building your own system from scratch
- you need high performance
- you don't want an opinionated framework (like Laravel/Symfony)
- you're creating an API or backend for your application

It's not a "plug & play" framework; it requires building your own application layer.

If you need a ready-made solution, see DBM Platform.

[DBM Platform - framework-based application (GitHub)](https://github.com/designbymalina/dbmplatform)

## DBM Ecosystem

DBM Framework is part of a larger ecosystem:

- DBM Framework - application engine
- DBM Platform - ready-made application layer

The platform extends the framework with an administration panel, authentication, and application modules.

More: [Ecosystem](_Docs/01_00-2-ecosystem.md)

## Documentation

Full framework documentation is available in the `/_Docs` directory.

Start:

- [Introduction](_Docs/01_01-introduction.md)
- [Architecture](_Docs/01_00-1-architecture.md)
- [Ecosystem](_Docs/01_00-2-ecosystem.md)

## License

Project licensed under the MIT License.

Copyright (c) Design by Malina
