# DbM Search Engine in PHP

Uniwersalna, modułowa biblioteka wyszukiwania oparta o wzorzec Provider.

Biblioteka nie zna bazy danych ani tabel. 
Wyszukiwanie realizowane jest przez implementacje `SearchProviderInterface`
rejestrowane w kontenerze DI.

---

## Architektura

Search składa się z:

- `SearchService` – silnik agregujący wyniki
- `SearchProviderInterface` – kontrakt dla providerów
- `SearchForm` – walidacja zapytań i filtrów
- `SearchResultDto` – pojedynczy wynik
- `SearchResultPageDto` – stronicowana kolekcja wyników

Biblioteka NIE zawiera:
- SQL
- repozytoriów
- zależności do konkretnej bazy
- wiedzy o tabelach

---

## Tworzenie providera

Provider musi implementować:

```php
use Lib\Search\Src\Interfaces\SearchProviderInterface;

class UserSearchProvider implements SearchProviderInterface
{
    public function getName(): string
    {
        return 'users';
    }

    public function searchQuery(
        string $query,
        array $filters = [],
        int $page = 1,
        int $limit = 20
    ): SearchResultPageDto {
        // implementacja
    }
}
```

Provider powinien:

- obsługiwać paginację  
- zwracać SearchResultPageDto  
- zwracać total count  

## Rejestracja providerów w module

```php
$container->tag(UserSearchProvider::class, 'search.provider');

$container->singleton(SearchService::class, function ($c) {
    return new SearchService(
        $c->getByTag('search.provider')
    );
});
```

## Użycie w kontrolerze

```php
public function __construct(
    private readonly SearchForm $form,
    private readonly SearchService $service
) {}

public function index(): ResponseInterface
{
    $query = $this->form->sanitizeQuery(
        $this->request->getQuery('q') ?? ''
    );

    $filters = $this->form->extractFilters(
        $this->request->getQueryParams()
    );

    $page = max(1, (int)$this->request->getQuery('page', 1));

    $resultPage = $this->service->search($query, $filters, $page);

    return $this->render('search.phtml', [
        'resultPage' => $resultPage,
        'query' => $query
    ]);
}
```

## Skalowalność

Provider może używać SQL, Elasticsearch, API

Biblioteka nie narzuca źródła danych

Obsługa dużych zbiorów poprzez paginację w providerze

## Rejestracja w różnych frameworkach

### DbM framework

```php
$container->tag(UserSearchProvider::class, 'search.provider');

$container->singleton(SearchService::class, function ($c) {
    return new SearchService(
        $c->getByTag('search.provider')
    );
});
```

### Symfony

Plik: services.yaml

```yaml
services:
    Mod\Admin\Search\Providers\:
        resource: '../src/Search/Providers/*'
        tags: ['search.provider']

    Lib\Search\Src\Classes\SearchService:
        arguments:
            $providers: !tagged_iterator search.provider
```

### Laravel

```php
$this->app->tag([
    UserSearchProvider::class,
], 'search.provider');

$this->app->bind(SearchService::class, function ($app) {
    return new SearchService(
        $app->tagged('search.provider')
    );
});
```

## Elasticsearch

Client to klient Elasticsearch PHP:

composer require elasticsearch/elasticsearch

Namespace:

use Elastic\Elasticsearch\Client;

Tworzysz go tak:

```php
use Elastic\Elasticsearch\ClientBuilder;

$client = ClientBuilder::create()
    ->setHosts(['localhost:9200'])
    ->build();
```

I wstrzykujesz do providera.

## SQL

FULLTEXT index is optional.
If available, provider will use MATCH AGAINST,
otherwise it falls back to LIKE.
