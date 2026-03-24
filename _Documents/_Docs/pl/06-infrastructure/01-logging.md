# System rejestrowania

## Przegląd

System rejestrowania zapewnia standardowy interfejs do rejestrowania aplikacji.

Ma strukturę podobną do PSR-3.

---

## Interfejs rejestratora

```php
use Dbm\Infrastructure\Log\Contracts\LoggerInterface;
```

---

## Poziomy rejestrowania

| Poziom | Opis |
|-----------|--------------------------|
| awaryjny | System nie nadaje się do użytku |
| alert | Wymagane natychmiastowe działanie |
| krytyczny | Warunki krytyczne |
| błąd | Błędy w czasie wykonywania |
| ostrzeżenie | Ostrzeżenia |
| uwaga | Normalne, ale istotne |
| informacje | Informacje |
| debugowanie | Komunikaty na poziomie debugowania |

---

## Przykładowe użycie

```php
$logger->info('Użytkownik zalogowany', [
    'user_id' => 1
]);
```

---

## Dane kontekstowe

Obsługuje symbole zastępcze:

```php
$logger->info(
    'Użytkownik {id} zalogowany',
    ['id' => 1]
);
```

---

## Ogólna metoda logowania

```php
$logger->log('info', 'Wiadomość');
```

---

## Najlepsze praktyki

* Używaj odpowiednich poziomów logowania
* Unikaj rejestrowania danych wrażliwych
* Używaj kontekstu strukturalnego

---

## Integracja

Używane w:

* Klient API (Guzzle)
* Obsługa błędów
* Systemy monitorowania

---
