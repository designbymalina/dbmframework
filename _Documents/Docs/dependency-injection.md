# Dependency Injection – plik `services.php`

DbM Framework wykorzystuje własny, lekki kontener Dependency Injection (`DependencyContainer`), który umożliwia ręczne rejestrowanie i zarządzanie zależnościami w aplikacji. Mechanizm ten zapewnia pełną kontrolę nad instancjami klas i pozwala na elastyczne definiowanie zależności.

Plik rejestracji usług znajduje się tutaj: `application/services.php`

## Rejestracja usług

Usługi są rejestrowane w kontenerze poprzez przekazanie funkcji zwracającej instancję danej klasy. Przykład:

```php
return function (DependencyContainer $container) {
    // Rejestracja interfejsu DatabaseInterface
    $container->set(DatabaseInterface::class, function () {
        return isConfigDatabase() ? new Database() : null;
    });

    // Rejestracja klasy Request
    $container->set(Request::class, function () {
        return new Request();
    });

    // Rejestracja klasy Logger
    $container->set(Logger::class, function () {
        return new Logger();
    });

    // Rejestracja klasy IndexService
    $container->set(IndexService::class, function () {
        return new IndexService();
    });

    // Rejestracja klasy IndexController
    $container->set(IndexController::class, function () use ($container) {
        return new IndexController(
            $container->get(IndexService::class),
            $container->get(DatabaseInterface::class)
        );
    });
};
```

## Użycie w kontrolerach

Zarejestrowane usługi mogą być wstrzykiwane do kontrolerów poprzez konstruktor. Przykład:

```php
class IndexController extends BaseController
{
    public function __construct(
        IndexService $indexService,
        ?DatabaseInterface $database = null
    ) {
        parent::__construct($database);
    }

    public function index(IndexService $indexService): ResponseInterface
    {
        $this->setFlash('messageInfo', 'Your application is now ready and you can start working on a new project. Optionally, proceed to installing the DbM CMS content management system.');

        return $this->render('index/start.phtml', [
            'meta' => $indexService->getMetaIndex(),
        ]);
    }
}
```

W powyższym przykładzie, IndexService oraz DatabaseInterface są wstrzykiwane do konstruktora IndexController dzięki wcześniejszej rejestracji w kontenerze DI.

## Zalety ręcznego DI

Pełna kontrola - samodzielne definiowanie, które klasy są rejestrowane i w jaki sposób.

Elastyczność - możliwość dostosowania sposobu tworzenia instancji do potrzeb aplikacji.

Brak zewnętrznych zależności - nie jest wymagane korzystanie z zewnętrznych bibliotek do zarządzania zależnościami.

## Gdzie są używane te usługi?

Kontrolery - do obsługi logiki aplikacji i interakcji z użytkownikiem.

Szablony  - poprzez klasy pomocnicze, takie jak TemplateFeature.

Inne klasy aplikacji - np. do obsługi bazy danych, logowania, czy obsługi żądań HTTP.

## Podsumowanie

System Dependency Injection w DbM Framework pozwala na efektywne zarządzanie zależnościami w aplikacji. Dzięki ręcznej rejestracji usług w pliku services.php, deweloper ma pełną kontrolę nad tym, jakie klasy są dostępne w kontenerze i w jaki sposób są tworzone ich instancje, unikasz zbędnego obciążenia, korzystasz z wydajnego mechanizmu.
