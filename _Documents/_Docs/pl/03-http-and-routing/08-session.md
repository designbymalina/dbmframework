# Session Management

## Overview

The `SessionManager` provides a simple interface for handling PHP sessions.

---

## Basic Usage

```php
use Dbm\Infrastructure\Session\SessionManager;

$session = new SessionManager();
```

---

## Set Session

```php
$session->setSession('user_id', 1);
```

---

## Get Session

```php
$userId = $session->getSession('user_id');
```

---

## Remove Session

```php
$session->unsetSession('user_id');
```

---

## Destroy Session

```php
$session->destroySession();
```

---

## Pop (Get & Remove)

```php
$message = $session->pop('flash_message');
```

---

## Reference Access

```php
$data = &$session->getSessionByReference('cart');

$data[] = 'product_id';
```

---

## Notes

* Automatically starts session if needed
* Safe fallback to `null`
* Useful for flash messages and carts

---

## Best Practices

* Use sessions only for small data
* Avoid storing large objects
* Always secure session cookies

---
