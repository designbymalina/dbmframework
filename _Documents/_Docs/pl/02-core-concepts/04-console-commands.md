# Console – Konsola poleceń DbM Framework

## Omówienie

**Konsola DbM Framework** umożliwia uruchamianie poleceń administracyjnych oraz zadań roboczych (workers) bezpośrednio z wiersza poleceń.

### Architektura konsoli:
- jest lekka i niezależna,  
- inspirowana Artisan (Laravel) oraz Symfony Console,  
- rozdziela Command (zadania użytkowe) i Worker (zadania robocze / batch / cron),  
- obsługuje moduły bez bazy danych.  

### Punktem wejścia do konsoli jest plik:

```bash
bin/dbm
```

### Podstawowe użycie

Wyświetlenie pomocy:  

```bash
php bin/dbm
```

Wyświetli:  

```bash
DbM Framework Console
----------
Usage:
  php bin/dbm list
  php bin/dbm command:list
  php bin/dbm worker:list
  php bin/dbm command <name>
  php bin/dbm worker <name>
```

### Tryby konsoli

`list`  

Wyświetla wszystkie dostępne wpisy konsoli:  

```bash
php bin/dbm list
```

`command`  

Służy do uruchamiania poleceń użytkowych.  

```bash
php bin/dbm command example
```

Uruchomi klasę:  

```bash
App\Console\Command\ExampleCommand
```

`worker`  

Służy do uruchamiania zadań roboczych / batch / cron.  

```bash
php bin/dbm worker example
```

Uruchomi klasę:

```bash
App\Console\Worker\ExampleWorker
```

Worker jest uruchamiany z:  
- pomiarem czasu,
- pomiarem zużycia pamięci,
- obsługą wyjątków,
- automatycznym zamknięciem połączeń (np. DB).


## Commands - Polecenia konsoli

### Lokalizacja  

```arduino
src/Console/Command/
```

### Konwencja nazewnictwa

Plik:

```arduino
ExampleCommand.php
```

Klasa:

```arduino
App\Console\Command\ExampleCommand
```

## Przykład klasy polecenia  

```php
namespace App\Console\Command;

use Dbm\Console\AbstractCommand;

final class ExampleCommand extends AbstractCommand
{
    public function execute(): void
    {
        $this->success('OK!', true);
    }
}
```

### Uruchomienie:  

```bash
php bin/dbm command example
```

## Workers - Zadania robocze

### Lokalizacja

```arduino
src/Console/Worker/
```

### Konwencja nazewnictwa

Plik:

```arduino
ExampleWorker.php
```

Klasa:

```arduino
App\Console\Worker\ExampleWorker
```

### Przykład workera bez bazy danych

```php
namespace App\Console\Worker;

use Dbm\Console\AbstractWorker;

final class ClearCacheWorker extends AbstractWorker
{
    public function run(): void
    {
        $this->log('Cache cleared');
        $this->success('Done', true);
    }
}
```

### Przykład workera z bazą danych

Jeśli worker wymaga bazy danych, musi zadeklarować to jawnie.

```php
namespace App\Console\Worker;

use Dbm\Console\AbstractWorker;
use Dbm\Database\Contracts\DatabaseInterface;
use Dbm\Database\Contracts\RequiresDatabaseInterface;

final class ExampleWorker extends AbstractWorker implements RequiresDatabaseInterface
{
    public function __construct(
        private readonly DatabaseInterface $database
    ) {}

    public function run(): void
    {
        $users = $this->database->fetchAll(
            'SELECT id, login, email FROM dbm_user'
        );

        foreach ($users as $user) {
            $this->log($user['login']);
        }

        $this->success('Finished successfully');
    }
}
```

RequiresDatabaseInterface

```php
namespace Dbm\Database\Contracts;

interface RequiresDatabaseInterface {}
```

To interfejs znacznikowy (marker interface).

### Co robi?

- informuje konsolę, że worker wymaga bazy danych,
- powoduje: walidację .env, utworzenie połączenia DB, przekazanie DB do konstruktora workera.

### Worker bez tego interfejsu:

- nie uruchamia bazy danych,
- może działać w trybie instalatora lub na plikach.

## Obsługa bazy danych

- DB jest tworzona tylko wtedy, gdy worker tego wymaga  
- DB_NAME jest opcjonalne:  umożliwia moduły bez bazy danych, umożliwia instalatory i migracje
- brak wymaganych zmiennych środowiskowych skutkuje czytelnym błędem

## Najlepsze praktyki

- Jeden command / worker = jeden plik
- Workers: batch, cron, integracje, migracje
- Commands: narzędzia developerskie, diagnostyka, helpery
- Jeśli worker używa DB - zawsze RequiresDatabaseInterface
- Loguj postęp (log()), kończ komunikatem (success())

## Podsumowanie  

Konsola DbM Framework:  

- jest szybka i lekka,
- nie wymaga zewnętrznych bibliotek,
- obsługuje moduły z DB i bez DB,
- jasno oddziela logikę infrastruktury od logiki biznesowej.
