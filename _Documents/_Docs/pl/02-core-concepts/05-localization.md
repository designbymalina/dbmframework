# Lokalizacja (Tłumaczenia)

## Przegląd

Klasa `Translation` zapewnia lekki i elastyczny sposób obsługi tłumaczeń w aplikacji.

Obsługuje:

* Placeholdery nazwane `{key}`
* Formatowanie `sprintf` (`%s`, `%1$s`)
* Fallback do klucza, jeśli tłumaczenie nie istnieje

---

## Podstawowe użycie

```php
use Dbm\Localization\Translation;

$translations = [
    'hello' => 'Witaj {name}',
];

$translator = new Translation($translations);

echo $translator->trans('hello', ['name' => 'Jan']);
// Witaj Jan
```

---

## Metoda tłumaczenia

```php
trans(string $key, ?array $data = null): string
```

### Działanie

1. Wyszukuje tłumaczenie po kluczu
2. Zamienia placeholdery
3. Zwraca klucz, jeśli tłumaczenie nie istnieje

---

## Placeholdery

### Placeholdery nazwane

```php
'hello' => 'Witaj {name}'
```

```php
$translator->trans('hello', ['name' => 'Jan']);
```

---

### Placeholdery sprintf

```php
'items' => 'Masz %d elementów'
```

```php
$translator->trans('items', [5]);
```

---

## Zachowanie fallback

Jeśli tłumaczenie nie istnieje:

```php
$translator->trans('unknown.key');
```

Zwraca:

```
unknown.key
```

---

## Integracja z Validator

Validator automatycznie obsługuje tłumaczenia:

```php
$validator = new Validator($translator);
```

---

## Dobre praktyki

* Przechowuj tłumaczenia w osobnych plikach
* Używaj spójnych kluczy (`validation.required`)
* Unikaj hardcodowania komunikatów

---

## Podsumowanie

System tłumaczeń jest:

* lekki
* bez zależności
* elastyczny
* bezpieczny (nie powoduje błędów aplikacji)

---
