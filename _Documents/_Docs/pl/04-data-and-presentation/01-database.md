# Database

Moduł Database dla DbM Framework  
Obsługa baz danych przy pomocy PDO lub Doctrine DBAL (wymienne, konfigurowalne, transparentne dla aplikacji).

## Moduł został zaprojektowany, aby:

- działał w trybie PDO lub Doctrine,
- był wymienny - kod aplikacji działa tak samo z obydwoma driverami,
- posiadał własny Query Builder dla PDO,
- wspierał w pełni Doctrine QueryBuilder gdy driver = DOCTRINE,
- był łatwo rozbudowywalny i izolowany od reszty systemu.

## Instalacja i konfiguracja

Plik .env - parametry.

### Database driver: 'PDO|pdo_mysql' (default) / pdo_pgsql, opcjonalnie DOCTRINE - requires Composer installation

```shell
DB_DRIVER=PDO|pdo_mysql
```

### Podstawowa konfiguracja bazy

```shell
DB_HOST=127.0.0.1
DB_NAME=test
DB_USER=root
DB_PASSWORD=
```

### Zmiana drivera:

```shell
DB_DRIVER=PDO       # klasyczny szybki adapter, brak zależności  
DB_DRIVER=DOCTRINE  # wymaga doctrine/dbal - composer install
```

## Adaptery PDO i Doctrine

**PdoDatabaseAdapter**

- używa natywnego PDO
- posiada własny Query Builder (PdoQueryBuilder)
- minimalne zależności
- szybki, lekki

## DoctrineDatabaseAdapter

- wykorzystuje Doctrine\DBAL\Connection
- daje dostęp do: QueryBuilder, ExpressionBuilder, platform (MySQL, PostgreSQL itd.), typy danych Doctrine
- pełna zgodność z wersją 4.3

## Przełączenie drivera: DB_DRIVER=PDO / DOCTRINE

Po zmianie .env:

```shell
DB_DRIVER=DOCTRINE
```

Twoje repositories i services działają identycznie - bez zmian w kodzie.

## INFO

Doctrine DBAL wymaga kilku dodatkowych bibliotek.  
Minimalny zestaw: doctrine/dbal, doctrine/deprecations, doctrine/event-manager

W autoloadzie musiałbyś dodać każdą z nich,  
dlatego dla Doctrine DBAL zalecamy:

- korzystać z vendor/autoload.php
- nie próbować ręcznie ładować DBAL, bo zależności są rozproszone

Uruchamiane za pomocą polecenia "composer install".  

## Query Builder

W zależności od drivera używany jest:

1) PdoQueryBuilder - własna implementacja:

Metody:

```php
buildInsertQuery(array $data, string $table): array  
buildUpdateQuery(array $data, string $table, string $where, array $params = []): array  
buildDeleteQuery(string $table, string $where, array $params = []): array  
```

2) Doctrine QueryBuilder

Pełny QueryBuilder:

```php
$qb = $database->createQueryBuilder();
$qb->select('u.*')->from('users', 'u')->where('id = :id');
```

### AbstractRepository - API ujednolicające

```php
namespace Dbm\Database\Repository;

use Dbm\Database\Interfaces\MainDatabaseInterface;

abstract class AbstractRepository
{
    protected MainDatabaseInterface $database;
    protected string $table;

    public function __construct(MainDatabaseInterface $database)
    {
        $this->database = $database;
    }

    public function find(int $id): ?object
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";

        $this->database->queryExecute($sql, ['id' => $id]);
        return $this->database->fetchObject() ?: null;
    }

    public function insert(array $data): bool
    {
        [$sql, $params] = $this->database
            ->builder()
            ->buildInsertQuery($data, $this->table);

        return $this->database->queryExecute($sql, $params);
    }

    public function update(array $data, string $where, array $extra = []): bool
    {
        [$sql, $params] = $this->database
            ->builder()
            ->buildUpdateQuery($data, $this->table, $where, $extra);

        return $this->database->queryExecute($sql, $params);
    }

    public function delete(string $where, array $params = []): bool
    {
        [$sql, $bind] = $this->database
            ->builder()
            ->buildDeleteQuery($this->table, $where, $params);

        return $this->database->queryExecute($sql, $bind);
    }
}
```

## Przykłady repozytoriów  

1) Repozytorium używające wyłącznie adaptera PDO (zalecany styl uniwersalny):

```php
namespace Mod\Account\Repository;

use Dbm\Database\Repository\AbstractRepository;

class AccountRepository extends AbstractRepository
{
    protected string $table = 'accounts';

    public function getByEmail(string $email): ?object
    {
        $sql = "SELECT * FROM accounts WHERE email = :email";

        $this->database->queryExecute($sql, ['email' => $email]);
        return $this->database->fetchObject();
    }

    public function createAccount(array $data): bool
    {
        return $this->insert($data);
    }

    public function updateAccount(int $id, array $data): bool
    {
        return $this->update($data, "id = :id", ['id' => $id]);
    }
}
```

- działa zarówno z PDO, jak i z Doctrine
- korzysta z ujednoliconego API

2) Przykład z użyciem specjalnych funkcji Doctrine (opcjonalnie)

Ten kod zadziała tylko gdy `DB_DRIVER=DOCTRINE`:

```php
public function searchAdvanced(string $emailPart): array
{
    $qb = $this->database->createQueryBuilder();

    $qb->select('a.*')
       ->from('accounts', 'a')
       ->where('a.email LIKE :email')
       ->setParameter('email', '%' . $emailPart . '%')
       ->orderBy('a.id', 'DESC');

    return $this->database->fetchAll(
        $qb->getSQL(),
        $qb->getParameters()
    );
}
```

3) Przykład update poprzez PdoQueryBuilder

```php
public function updateLastLogin(int $id): bool
{
    $data = [
        'last_login' => date('Y-m-d H:i:s'),
    ];

    return $this->update(
        $data,
        'id = :id',
        ['id' => $id]
    );
}
```

Generuje SQL:

```sql
UPDATE accounts SET last_login = :last_login WHERE id = :id
```

## Kiedy używać którego stylu?

Uniwersalny styl - działa ZAWSZE:

- queryExecute
- fetch / fetchObject
- builder()->buildInsertQuery()
- builder()->buildUpdateQuery()
- builder()->buildDeleteQuery()

Tylko gdy `DB_DRIVER = DOCTRINE`:

- createQueryBuilder()
- ExpressionBuilder
- zaawansowane platformy / typy Doctrine

## Podsumowanie

Moduł Database oferuje:

- pełną wymienność PDO <=> Doctrine
- pełną kompatybilność repozytoriów
- szybki własny QueryBuilder dla PDO
- możliwość korzystania z potężnego QueryBuildera Doctrine
- bezpieczną abstrakcję poprzez MainDatabaseInterface
- wspólne metody (queryExecute, fetch, fetchObject, fetchAll)
