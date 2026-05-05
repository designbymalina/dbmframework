# DBM Framework

Lightweight modular PHP framework (engine-only core)  

**Fast. Flexible. PSR-Compatible.**

DBM Framework v6 is a **pure application engine** - with no CMS, no platform layer, and no opinionated application structure.  
It is designed to be embedded into custom applications, not to be a full product by itself.  

## Features

Framework Kernel + MVC + DI + Routing  

- Modular architecture (PSR-4 compliant)  
- Lightweight middleware pipeline (PSR-style request flow)  
- Flexible routing system (framework-level only)    
- Dependency Injection container support 
- Event-driven extensibility  
- CLI-ready (via external application layer) 
- Framework core only (no CMS, no platform, no UI layer)  

## Philosophy

DBM Framework v6 follows a strict separation of concerns:  

- **Framework = execution engine only**  
- **Application layer = fully user-defined**  
- **CMS / Platform = optional, external packages**  

This keeps the core fast, predictable and reusable across different systems.  

## Requirements

- PHP 8.1 or higher  
- Composer  

## Installation

```bash
composer require designbymalina/dbmframework
```

## Basic Usage

DBM Framework is not a standalone application. It must be used inside your own application layer.  

**Example:**  

Run the framework using a minimal sandbox:  

```bash
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

**Bootstrap structure:**  

- bootstrap/app.php – application factory  
- bootstrap/services.php – DI container setup  
- bootstrap/controller.php – example controller  

```bash
php -S localhost:8000 example/index.php
```

URL: `http://localhost:8000/`

## Architecture Overview

DBM Framework consists of:  

- Core kernel (request lifecycle)  
- Router (flexible routing)  
- Middleware dispatcher  
- Container (DI)  

## Design principles

- No global state  
- No framework lock-in  
- No hidden magic  
- Explicit configuration  
- Composition over inheritance  

## Development

Clone repository and install dependencies:  

```bash
git clone https://github.com/designbymalina/dbmframework
cd dbmframework
composer install
```

## Documentation

Version: DBM Framework v6.x  

[Full documentation (external)](https://github.com/designbymalina/dbmplatform/blob/v6/README.md)

## License

MIT License  
