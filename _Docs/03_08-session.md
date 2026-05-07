# Zarządzanie sesją

## Opis

Klasa `SessionManager` zapewnia interfejs do obsługi sesji PHP.

---

## Podstawowe użycie

```php
use Dbm\Infrastructure\Session\SessionManager;
```

```php
$session = new SessionManager();
```

## Ustawienie wartości

```php
$session->setSession('user_id', 1);
```

## Pobranie wartości

```php
$userId = $session->getSession('user_id');
```

## Usunięcie wartości

```php
$session->unsetSession('user_id');
```

## Zniszczenie sesji

```php
$session->destroySession();
```

## Pop (pobierz i usuń)

```php
$message = $session->pop('flash_message');
```

## Dostęp przez referencję

```php
$data = &$session->getSessionByReference('cart');

$data[] = 'product_id';
```

## Uwagi

- Sesja uruchamiana jest automatycznie (jeśli potrzebna)  
- Bezpieczny fallback do null  
- Przydatne do komunikatów flash i koszyka  

## Dobre praktyki

- Przechowuj w sesji tylko małe dane  
- Unikaj dużych obiektów  
- Zabezpieczaj cookies sesyjne  
