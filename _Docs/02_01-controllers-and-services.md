# Tworzenie pierwszego kontrolera i usługi

Ten dokument pokazuje, jak utworzyć i uruchomić pierwszy kontroler i usługę w DBM Framework.

---

## Punkty wejścia aplikacji

DBM Framework korzysta z routingu opartego na PHP.

* Punkt wejścia Web: `/`
* Punkt wejścia API: `/api`

Konfiguracja routingu jest ładowana z:

```bash
application/web.php
application/api.php
```

---

## Lokalizacja kontrolera

Kontrolery powinny być umieszczone w:

```bash
src/Controller
```

---

## Przykładowy kontroler

```php
declare(strict_types=1);

namespace App\Controller;

use App\Service\IndexService;
use Dbm\Http\Controller\BaseController;
use Dbm\Views\Flash\FlashBag;
use Psr\Http\Message\ResponseInterface;

class IndexController extends BaseController
{
    public function __construct(
        private readonly IndexService $service,
        private readonly FlashBag $flash
    ) {}

    /**
    * Strona indeksu
    * @routing GET '/' name: home
    */
    public function index(): ResponseInterface
    {
        $this->flash->set('Twoja aplikacja jest już gotowa i możesz rozpocząć pracę nad nowym projektem.');

        return $this->render('index/start.phtml', [
            'meta' => $this->service->getMetaIndex(),
        ]);
    }

    /**
    * Strona startowa
    * @routing GET '/start' name: start
    */
    public function start(): ResponseInterface
    {
        return $this->render('index/start.phtml', [
            'meta' => $this->service->getMetaStart(),
        ]);
    }
}
```

---

## Lokalizacja usługi

Usługi powinny być umieszczone w:

```bash
src/Service
```

---

## Przykładowa usługa

```php
declare(strict_types=1);

namespace App\Service;

use Dbm\Localization\Translation;

class IndexService
{
    public function __construct(
        private readonly Translation $translation,
    ) {}

    public function getMetaIndex(): array
    {
        return [
            'meta.title' => "Nazwa Twojej aplikacji internetowej",
            'meta.description' => "Opis aplikacji internetowej...",
            'meta.keywords' => "słowa kluczowe aplikacji",
        ];
    }

    public function getMetaStart(): array
    {
        return [
            'meta.title' => $this->translation->trans('index.start_meta_title'),
            'meta.description' => $this->translation->trans('index.start_meta_description'),
            'meta.keywords' => $this->translation->trans('index.start_meta_keywords'),
            'meta.robots' => "noindex,nofollow",
        ];
    }
}
```

---

## Trasowanie

Trasy są definiowane ręcznie w:

```bash
application/web.php
application/api.php
```

Adnotacja `@routing` w PHPDoc ma **tylko charakter informacyjny** i nie rejestruje tras automatycznie.

---

## Wstrzykiwanie zależności

DBM korzysta z automatycznego wstrzykiwania zależności.

### Wstrzykiwanie konstruktora (zalecane)

```php
public function __construct(
    private IndexService $service,
    private FlashBag $flash
) {}
```

Zależności są automatycznie rozwiązywane z kontenera DI.

---

### Wstrzykiwanie metod (opcjonalne)

```php
public function example(Request $request): ResponseInterface
```

---

## Wiadomości Flash

Wiadomości Flash są obsługiwane przez `FlashBag`:

```php
$this->flash->set('Message text');
```

Zwykle są wyświetlane raz (w następnym żądaniu).

---

## Tłumaczenia

Tłumaczenia są obsługiwane za pośrednictwem usługi `Translation`.

### Przykład

```php
$this->translation->trans('key');
```

### Z parametrami

```php
$this->translation->trans('hello', ['name' => 'John']);
```

---

### Pliki tłumaczeń

Przechowywane w:

```bash
translations/
```

Przykład:

```php
return [
    'index.start_meta_title' => 'Strona startowa',
];
```

---

## Widoki / Szablony

Szablony znajdują się w:

```bash
templates/
```

Renderuj za pomocą:

```php
$this->render('index/start.phtml', [...]);
```

---

## Rejestrator (opcjonalnie)

Możesz wstrzyknąć rejestrator do usług lub kontrolerów:

```php
use Dbm\Infrastructure\Log\Logger;

public function __construct(
    private Logger $logger
) {}
```

---

## Przykład użycia w usłudze

```php
$this->logger->info('Meta generated');
```

---

## Pełny przepływ

1. Żądanie trafia do `/`
2. Router rozwiązuje trasę
3. Kontroler jest tworzony przez DI
4. Wstrzykiwane są zależności
5. Wykonywana jest akcja
6. Widok jest renderowany
7. Zwracana jest odpowiedź

---

## Uruchamianie aplikacji

1. Skieruj serwer WWW na:

```bash
public/
```

2. Otwórz:

```bash
http://localhost/
```

---

## Podsumowanie

Ten przykład demonstruje:  

* Czystą strukturę kontrolera  
* Automatyczne wstrzykiwanie zależności  
* Architekturę opartą na usługach  
* Integrację tłumaczeń  
* Wiadomości Flash  
* Renderowanie szablonów  

DBM zachęca do:  

* cienkich kontrolerów  
* usług wielokrotnego użytku  
* jawnej architektury  

---
