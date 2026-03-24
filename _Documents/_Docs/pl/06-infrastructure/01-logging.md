# Logging System

## Overview

The logging system provides a standard interface for application logging.

It follows a structure similar to PSR-3.

---

## Logger Interface

```php
use Dbm\Infrastructure\Log\Contracts\LoggerInterface;
```

---

## Log Levels

| Level     | Description               |
| --------- | ------------------------- |
| emergency | System unusable           |
| alert     | Immediate action required |
| critical  | Critical conditions       |
| error     | Runtime errors            |
| warning   | Warnings                  |
| notice    | Normal but significant    |
| info      | Informational             |
| debug     | Debug-level messages      |

---

## Example Usage

```php
$logger->info('User logged in', [
    'user_id' => 1
]);
```

---

## Context Data

Supports placeholders:

```php
$logger->info(
    'User {id} logged in',
    ['id' => 1]
);
```

---

## Generic Log Method

```php
$logger->log('info', 'Message');
```

---

## Best Practices

* Use appropriate log levels
* Avoid logging sensitive data
* Use structured context

---

## Integration

Used in:

* API Client (Guzzle)
* Error handling
* Monitoring systems

---
