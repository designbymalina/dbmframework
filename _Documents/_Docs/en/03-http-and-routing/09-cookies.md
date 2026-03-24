# Cookie Management

## Overview

The `CookieManager` provides a simple interface for working with cookies.

---

## Basic Usage

```php
use Dbm\Infrastructure\Cookie\CookieManager;

$cookie = new CookieManager();
```

---

## Set Cookie

```php
$cookie->setCookie('token', 'abc123');
```

---

## Options

```php
setCookie(
    string $name,
    string $value,
    int $expiry = 86400,
    bool $secure = true,
    bool $httpOnly = true
)
```

---

## Get Cookie

```php
$value = $cookie->getCookie('token');
```

---

## Delete Cookie

```php
$cookie->unsetCookie('token');
```

---

## Security

* `secure = true` → HTTPS only
* `httpOnly = true` → not accessible via JS

---

## Best Practices

* Always use `secure` in production
* Use `httpOnly` for sensitive data
* Avoid storing sensitive info in cookies

---
