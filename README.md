# DbM Framework - Ultra Fast PHP Framework for High-Performance Web Apps

**Fast. Flexible. PSR-Compatible.**
**Modern PHP MVC/MVP Framework + CMS Engine with built-in API**

[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D8.1-blue)](http://php.net)
[![PSR](https://img.shields.io/badge/PSR-1%2C%204%2C%2011%2C%2012-green)](https://www.php-fig.org/)
[![Build](https://img.shields.io/badge/build-passing-success)]()
[![Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen)]()
[![Composer](https://img.shields.io/badge/composer-ready-orange)](https://getcomposer.org/)
[![Speed](https://img.shields.io/badge/performance-ultra%20fast-red)]()
[![License](https://img.shields.io/badge/license-DbM-orange)](https://dbm.org.pl)

DBM Framework PHP MVC MVP + DBM CMS, Version 5  
All copyrights reserved by Design by Malina (DbM)  
Website: [www.dbm.org.pl](http://www.dbm.org.pl)  

## About the Framework

DBM Framework v5 is a **modular monolith** PHP designed for building long-lasting, maintainable applications with full control over architecture and performance. The framework combines the lightweight simplicity of classic MVC/MVP with a modern approach to application expansion through clearly separated modules, without imposing redundant abstractions or hidden logic.

Earlier versions of the DBM Framework were based on a classic monolithic architecture. Version 5 introduces modularity, enabling the logical separation of responsibilities within a single system implemented as a whole. This approach preserves the simplicity and efficiency of the monolith while ensuring scalability, readability, and long-term architectural stability.

The framework also forms the foundation of the **DBM platform**, including the **DBM CMS** turnkey solution enabling rapid website and application launches without the need to build your own infrastructure from scratch. The CMS can operate as a lightweight file- and template-based system or be extended with administrative and database modules, maintaining full control over the application's code and structure.

The DBM Framework is designed for teams and projects that require predictable operation, high performance, and an architecture that is resilient to growth and long-term development.

### DbM Framework is:  
**Ultra-fast core** - Optimized request Routing and Caching  
**PSR (1, 4, 11, 12) Compliant** - Industry Standard Ready Code  
**REST API Routing** - Lightweight, Ready, Lightweight  
**Smart DI Container** - Manual or Semi-Automatic Dependency Injection  
**Composer & Autoload** - Ready to Use in Any Project  
**Ultra Fast View Engine 2.0** - Speed ​​Similar to Native PHP  
**DbM CMS** - Framework-Based Content Management System, Ready-Made Authentication and Administration Panel  

DbM is a framework that doesn't fight the developer - **it lets them work the way they want**.

## Framework Structure

- `application/` - framework core: classes, interfaces, libraries (+ Routing, DI, API)  
- `bin` - executables: console command interface and worker (entrypoint: bin/dbm)  
- `config/` - configuration files (optional, e.g., php.ini, CMS modules)  
- `frontend/` - frontend (optional React.js or Vue.js, Node.js, Webpack)  
- `libraries/` - external libraries (PSR, PHPMailer, Guzzle)  
- `public/` - public files (domain root)  
- `src/` - application logic: controllers, services, models, services  
- `templates/` - view templates  
- `tests/` - unit tests  
- `translations/` - translation files (optional)  
- `var/` - cache and logs (automatically created, write permissions required)  
- `vendor/` - libraries installed by Composer (automatically created)  
- `.env.example` - sample environment configuration  

## Additional Structure for CMS installations

- `_Documents` - documentation, module installation archive  
- `data/` - data and files (write permissions required)  
- `modules/` - content management system modules  

## Installation and Configuration (manual installation)

1. **Domain Configuration:** Point your domain to the `public/` directory. If you are using a local environment (localhost), copy the `.htaccess` file from `_Documents/_Server/` to the project root. Then, in both files – the root directory and public/.htaccess – adjust the RewriteBase directive to match your application's execution path.
2. **Environment File:** Configure the `.env.example` file, then rename it to `.env`.
3. **Optimization:** After completing the configuration and launching the system, set `CACHE_ENABLED`.

In the basic `.env` configuration, complete the General settings section:

```env
APP_URL="http://localhost/"
APP_NAME="Application Name"
APP_EMAIL="email@domain.com"
```

Next, configure: Cache settings, Database settings, Mailer settings, API settings.

**Note:** After launching the application, set CACHE_ENABLED=true to enable caching and speed up the page.

## Installing via Composer

If you prefer to install via Composer or your project requires additional packages:

```bash
git clone https://github.com/designbymalina/dbmframework.git
```

If you want to use external libraries, you can use Composer:

```bash
composer install
```

Installing via Composer will create autoloading and download all dependencies.

## Installing Modules (optional for DbM Platform)

Some modules (e.g., Admin) may register additional packages during installation.

In Composer mode, you must re-sync after installing the module.

**Note**

In Composer mode, the `libraries` directory can be deleted, as long as it does not contain packages dynamically installed by modules.

## Autoloading

The framework can operate in two modes:

### 1. Standalone Mode (without Composer)

By default, the framework has its own autoloading mechanism and does not require Composer.

In this mode:

- Core classes are loaded by the internal autoloader (PSR-4),
- External libraries are located in the `libraries` directory,

- Dynamically installed packages (e.g., by modules) are registered in the file: `storage/framework/bundles.php`.

The autoloader reads this file automatically.

### 2. Composer Mode

Executing the command:

```bash
composer install
```

causes:

- generating the Composer autoloader,
- installing dependencies (e.g., Doctrine DBAL, PHPMailer, Guzzle),
- switching the framework to Composer autoloading.

From this point on, the framework uses only the Composer autoloader.

### Synchronizing Bundles with Composer

In Composer mode, bundles registered in `storage/framework/bundles.php` should be synchronized with the composer.json file.

Execute:

```bash
php bin/dbm command sync-bundles-to-composer
composer dump-autoload
```

After this operation, all dynamic bundles will be available to the Composer autoloader.

## Routing

Classic routing is defined in the file: `application/routes.php`.

Example:

```shell
$router->get('/path', [NameController::class, 'methodName'], 'route_name');
```

You define REST API Routing in the `application/api.php` file.

Example:

```shell
$router->get('/api/path', [NameApiController::class, 'methodName'], 'api_route_name');
```

## Dependency Injection

DbM Framework uses a lightweight DI container, compliant with **PSR-11**, which offers two modes of operation:

- **Manual configuration (recommended)**

You register all dependencies explicitly in the `application/services.php` file:

```php
$container->set(Database::class, fn() => new Database($config));
$container->singleton(Request::class, fn() => new Request());
```

This mode guarantees full control over dependencies and performance.

- **Semi-automatic configuration (available)**

In many cases, the framework can recognize and inject a dependency based on the parameter type in the controller or service constructor:

```php
public function __construct(Mailer $mailer) { ... }
```

If the class is known and PSR-4 autoload-compliant, it will be injected correctly. However, explicit service registration is recommended for full predictability and stability.

This compromise combines the **simplicity** of manual DI with the **flexibility** of automatic detection—without the cost of full reflection found in heavy frameworks.

## Template Engine

The framework uses a built-in template engine by default. You can freely replace it with something like Twig.

Why use DbM View Engine over the most popular engines:

| Cechy | Twig | Blade | DbM View Engine |
|-------|------|-------|---------------------|
| Szybkość | średnia | dobra | najwyższa |
| PHP-friendly | ❌ | ⚠️ | ✅ programista wie co robi |
| Filtry | tak | tak | ✅ proste i rozszerzalne|
| Pluginy | trudne | brak | ✅ runtime callbacks |
| Dziedziczenie bloków | tak | tak | ✅ + append/prepend |
| Cache | tak | tak | ✅ klasy OPC |
| Sandbox | tak | brak | ✅ opcjonalny |
| Zależności | duże | średnie | ✅ niezależny |
| Waga | >400KB | ~200KB | ~50KB |

In tests with CACHE=TRUE, the results achieved were similar to those of Native PHP.

=== TEMPLATE ENGINE BENCHMARK - benchmark.phtml ===

| MODE | AVG(ms) | MEDIAN | MIN | MAX | STD |
|------|---------|--------|-----|-----|-----|
| CACHE=FALSE | 1.31 | 1.29 | 1.17 | 1.67 | 0.09 |
| CACHE=TRUE | 0.17 | 0.16 | 0.16 | 0.31 | 0.02 |
| Native PHP | 0.15 | 0.14 | 0.14 | 0.18 | 0.01 |

**Conclusion**: DbM View Engine (cache=true) is almost as fast as pure PHP, confirming its performance.

Templates are located in the `templates/` directory.

## Command Console

A lightweight and fast CLI for CRON and DEV tasks. It provides a simple way to run background or maintenance tasks directly from the command line with a lightweight and self-contained implementation. Console commands are executed via the file: `bin/dbm`.

Available commands:  

```bash
php bin/dbm list
php bin/dbm command example (for ExampleCommand)
php bin/dbm worker example (for ExampleWorker)
```

## Additional Information

In a production environment (on a remote server), **the domain must point to the `public/` directory**, as it serves as the document root. If you are using a local environment (localhost), configure the .htaccess files in both the project root and the public/ folder. For remote servers where the domain already points directly to the public/ directory, the application typically requires no further .htaccess configuration.

Ensure that `open_basedir` does not block access to directories. Depending on your server configuration, it may be necessary to disable this restriction in the PHP settings. This security feature, known as "site separation," can block access to files outside the domain's document root, preventing the application from functioning correctly.

After launching the application, enable the cache (CACHE_ENABLED=true) to speed up the website.

When using **DBM CMS**, also ensure that the data/ directories have the appropriate write permissions.

**IMPORTANT!** Please retain the footer: "Created with <a href="https://dbm.org.pl/" title="DbM">DbM Framework</a>". The link should remain intact. Thank you for supporting the project! By maintaining the link in the footer, you help develop the free, open-source framework and support its development and the community of independent PHP developers.

Documentation:

[Introduction](_Documents/_Docs/01-introduction.md)  
[Creating first Controller and Service](_Documents/_Docs/02-controller-and-service.md)  
[Application Programming Interface (api.php)](_Documents/_Docs/api.md)  
[Command console (console.php)](_Documents/_Docs/console.md)  
[Dependency Injection - DI (services.php)](_Documents/_Docs/dependency-injection.md)  
[Environment configuration (.env)](_Documents/_Docs/env.md)  
[Middleware (middleware.php)](_Documents/_Docs/middleware.md)  
[Routing (routes.php)](_Documents/_Docs/routing.md)  
[Request](_Documents/_Docs/request.md)  
[Response](_Documents/_Docs/response.md)  
[TemplateEngine](_Documents/_Docs/template-engine.md)  
[TemplateFeature](_Documents/_Docs/template-feature.md)  
[Templates](_Documents/_Docs/templates.md)  

--- DbM CMS ---  

[Quick Start](_Documents/_Docs/installer.md)  
