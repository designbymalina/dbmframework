# Tworzenie pierwszego kontrolera i usługi

Ten dokument opisuje podstawowy sposób tworzenia i uruchamiania pierwszego kontrolera i usługi w DBM Framework.

---

## Punkt wejścia aplikacji

DBM Framework korzysta z routingu opartego na PHP.

Punkt wejścia aplikacji internetowej to: `/`

Punkt wejścia API to: `/api`

Konfiguracja routingu jest ładowana z: `application/web.php`

Konfiguracja routingu API jest ładowana z: `application/api.php`

Te pliki są odpowiedzialne za rejestrowanie kontrolerów i mapowanie żądań HTTP na akcje kontrolera.

---

## Lokalizacja kontrolera

Kontrolery muszą być umieszczone w: `src/Controller`.

Przykładowy kontroler:

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
* Strona indeksu
* @routing GET '/' name: index
*/
public function index(IndexService $indexService): ResponseInterface
{
$this->setFlash(
'Twoja aplikacja jest już gotowa i możesz rozpocząć pracę nad nowym projektem.'
);

return $this->render('index/start.phtml', [
'meta' => $indexService->getMetaIndex(),
]);
}

/**
* Strona startowa
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

## Lokalizacja usługi

Usługi powinny być umieszczone w: `src/Service`

Przykładowa usługa:

```php
declare(strict_types=1);

namespace App\Service;

class IndexService
{
public function getMetaIndex(): array
{
return [
'title' => 'Framework DBM',
'description' => 'Witamy w DBM Framework v5',
];
}

public function getMetaStart(): array
{
return [
'title' => 'Rozpoczęcie pracy',
'description' => 'Rozpocznij tworzenie aplikacji',
];
}
}
```

## Routing

Trasy są definiowane jawnie w:

```bash
application/web.php
application/api.php
```

Te pliki odpowiadają za rejestrowanie kontrolerów oraz mapowanie metod HTTP i ścieżek na akcje kontrolera.

Adnotacja @routing PHPDoc ma charakter opisowy i służy jako dokumentacja dla programisty.

Nie rejestruje tras automatycznie.

## Wstrzykiwanie zależności

Usługi są wstrzykiwane automatycznie za pomocą argumentów metod ze wskazówkami typu.

Przykład:

```php
public function index(IndexService $indexService): ResponseInterface
```

Nie jest wymagana ręczna konfiguracja kontenera.

## Widoki / Szablony

Szablony znajdują się w: `templates/`

Wyrenderowano za pomocą:

```php
$this->render('index/start.phtml', [...]);
```

Co odpowiada: `templates/index/start.phtml`

## Uruchamianie aplikacji

Skonfiguruj serwer WWW tak, aby wskazywał na: `public/`.

Otwórz w przeglądarce: `http://localhost/`

Metoda IndexController::index() zostanie wykonana.
