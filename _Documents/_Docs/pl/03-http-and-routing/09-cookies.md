# Zarządzanie cookies

## Opis

Klasa `CookieManager` zapewnia interfejs do pracy z cookies.

---

## Podstawowe użycie

```php
use Dbm\Infrastructure\Cookie\CookieManager;
```

```php
$cookie = new CookieManager();
```

## Ustawienie cookie

```php
$cookie->setCookie('token', 'abc123');
```

### Opcje

```php
setCookie(
    string $name,
    string $value,
    int $expiry = 86400,
    bool $secure = true,
    bool $httpOnly = true
)
```

## Pobranie cookie

```php
$value = $cookie->getCookie('token');
```

## Usunięcie cookie

```php
$cookie->unsetCookie('token');
```

## Bezpieczeństwo

`secure = true` - tylko HTTPS  

`httpOnly = true` - brak dostępu z JavaScript  

## Dobre praktyki

- Zawsze używaj secure w produkcji
- Używaj httpOnly dla danych wrażliwych
- Nie przechowuj wrażliwych danych w cookies
