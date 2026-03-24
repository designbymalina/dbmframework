# Dependency Injection - Wstrzykiwanie zależności

## Przegląd

DbM Framework wykorzystuje lekki kontener wstrzykiwania zależności (`DependencyContainer`) do zarządzania usługami aplikacji.

Kontener umożliwia:

- Ręczną rejestrację usług
- Udostępnianie instancji za pomocą `singleton`
- Automatyczne rozwiązywanie zależności (autowiring)
- Organizowanie modułów za pośrednictwem dostawców usług

Plik konfiguracyjny usługi:

```bash
application/services.php
```

---

## Rejestracja usługi

Usługi są rejestrowane wewnątrz:

```php
return function (DependencyContainer $container): void {
    // tutaj usługi
};
```

---

## `singleton()` vs `set()`

### singleton (zalecane)

Tworzy współdzieloną instancję (jedną na cykl życia żądania):

```php
$container->singleton(Logger::class, fn() => new Logger());
```

lub krócej:

```php
$container->singleton(RouteCollection::class);
```

---

### set

Tworzy nową instancję za każdym razem (lub niestandardową logikę):

```php
$container->set(DatabaseInterface::class, fn() =>
    isConfigDatabase() ? DatabaseFactory::createDatabase() : null
);
```

---

## Przykładowa konfiguracja

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

## Rozwiązywanie zależności

### Wstrzykiwanie konstruktorów

Zależności są automatycznie wstrzykiwane do konstruktorów:

```php
class IndexController
{
    public function __construct(
        private IndexService $service,
        private FlashBag $flash
    ) {}
}
```

Nie jest wymagane ręczne `$container->get()`.

---

### Przykład kontrolera

```php
class IndexController extends BaseController
{
    public function __construct(
        private readonly IndexService $service,
        private readonly FlashBag $flash
    ) {}

    public function index(): ResponseInterface
    {
        $this->flash->set('Aplikacja jest gotowa');

        return $this->render('index/start.phtml', [
            'meta' => $this->service->getMetaIndex(),
        ]);
    }
}
```

---

## Automatyczne łączenie

Kontener automatycznie rozwiązuje:

* Zależności konstruktora
* Argumenty akcji kontrolera

### Przykład

```php
public function show(Request $request): ResponseInterface
```

Jeśli `Request` jest zarejestrowany → zostanie wstrzyknięty automatycznie.

---

## Dynamiczne Rozwiązywanie

Nadal możesz ręcznie rozwiązywać usługi:

```php
$container->get(SomeService::class);
```

Jeśli klasa:

* istnieje
* ma rozwiązywalne zależności

zostanie utworzona automatycznie.

---

## Integracja z routingiem

Kontener DI jest głęboko zintegrowany z routingiem:

* `ControllerResolver` tworzy kontrolery
* `ActionArgumentResolver` wstrzykuje argumenty metody
* `Router` koordynuje pełny cykl życia żądania

---

## Moduły (Dostawcy Usług)

Moduły mogą rejestrować własne usługi:

```php
ModuleServiceProvider::register($container);
```

Umożliwia to modułową architekturę i izolację funkcji.

---

## Integracja szablonów

Usługi można udostępniać szablonom:

```php
$view->setGlobal(
    'headerNavigation',
    fn() => $this->container
        ->get(NavigationUtility::class)
        ->getHeader($request->getUri()->getPath())
);
```

---

### Użycie w szablonie

```html
{% $headerNavigation = $this->headerNavigation(); %}

{% if (is_array($headerNavigation)): %}
```

---

## Grupy usług podstawowych

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

### Widok

* `TemplateEngine`
* `FlashBag`

---

### Bezpieczeństwo

* `AccessGuard`
* `CsrfTokenManager`

---

### Lokalizacja

* `LanguageService`
* `Tłumaczenie`
* `TranslationLoader`

---

### Infrastruktura

* `Logger`
* `FileSystem`
* `PHPMailerSender`

---

## Najlepsze praktyki

* Używaj `singleton()` dla większości usług
* Używaj `set()` dla usług dynamicznych lub opcjonalnych
* Preferuj wstrzykiwanie przez konstruktor zamiast ręcznego `get()`
* Utrzymuj usługi bezstanowe, gdy jest to możliwe
* Jawnie rejestruj ważne usługi (unikaj nadużywania automatycznego rozstrzygania)

---

## Kiedy używać `set()` zamiast `singleton()`

Używaj `set()`, gdy:

* Usługa może zwrócić `null`
* Usługa zależy od warunków środowiska wykonawczego
* Za każdym razem potrzebujesz nowej instancji

---

## Podsumowanie

Kontener DI DbM zapewnia:

* Wysoką wydajność (brak intensywnego skanowania odbiciowego)
* Pełną kontrolę nad tworzeniem usług
* Automatyczne rozstrzyganie zależności
* Przejrzystą architekturę z modułami

**DI DbM** to: nie Laravel, nie Symfony tylko **lekki, kontrolowany container**.

Łączy prostotę z elastycznością, dzięki czemu nadaje się zarówno do małych aplikacji, jak i złożonych systemów modułowych.

---
