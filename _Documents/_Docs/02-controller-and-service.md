# Creating first Controller and Service

This document describes the basic way to create and run the first controller and service in DBM Framework v5.

---

## 1. Application entry point

DBM Framework uses PHP-based routing.

The web application entry point is: `/`

The API entry point is: `/api`

Routing configuration is loaded from: `application/web.php`

API routing configuration is loaded from: `application/api.php`

These files are responsible for registering controllers and mapping HTTP requests to controller actions.

---

## 2. Controller location

Controllers must be placed in: `src/Controller`.

Example controller:  

```php
declare(strict_types=1);

namespace App\Controller;

use App\Service\IndexService;
use Dbm\Database\Contracts\DatabaseInterface;
use Dbm\Http\Controller\BaseController;
use Psr\Http\Message\ResponseInterface;

class IndexController extends BaseController
{
    public function __construct(
        ?DatabaseInterface $database = null
    ) {
        parent::__construct($database);
    }

    /**
     * Index page
     * @routing GET '/' name: index
     */
    public function index(IndexService $indexService): ResponseInterface
    {
        $this->setFlash(
            'Your application is now ready and you can start working on a new project.'
        );

        return $this->render('index/start.phtml', [
            'meta' => $indexService->getMetaIndex(),
        ]);
    }

    /**
     * Start page
     * @routing GET '/start' name: start
     */
    public function start(IndexService $indexService): ResponseInterface
    {
        return $this->render('index/start.phtml', [
            'meta' => $indexService->getMetaStart(),
        ]);
    }
}
```

## 3. Service location

Services should be placed in: `src/Service`

Example service:  

```php
declare(strict_types=1);

namespace App\Service;

class IndexService
{
    public function getMetaIndex(): array
    {
        return [
            'title' => 'DBM Framework',
            'description' => 'Welcome to DBM Framework v5',
        ];
    }

    public function getMetaStart(): array
    {
        return [
            'title' => 'Getting Started',
            'description' => 'Start building your application',
        ];
    }
}
```

## 4. Routing

Routes are defined explicitly in:

```bash
application/web.php
application/api.php
```

These files are responsible for registering controllers and mapping HTTP methods and paths to controller actions.

The @routing PHPDoc annotation is descriptive only and serves as documentation for the developer.
It does not register routes automatically.

## 5. Dependency Injection

Services are injected automatically by type-hinting method arguments.

Example:

```php
public function index(IndexService $indexService): ResponseInterface
```

No manual container configuration is required.

## 6. Views / Templates

Templates are located in: `templates/`

Rendered using:

```php
$this->render('index/start.phtml', [...]);
```

Which resolves to: `templates/index/start.phtml`

## 7. Running the application

Configure your web server to point to: `public/`.

Open in browser: `http://localhost/`

The IndexController::index() method will be executed.
