# Localization (Translations)

## Overview

The `Translation` class provides a lightweight and flexible way to handle application translations.

It supports:

* Named placeholders `{key}`
* `sprintf` formatting (`%s`, `%1$s`)
* Fallback to key if translation is missing

---

## Basic Usage

```php
use Dbm\Localization\Translation;

$translations = [
    'hello' => 'Hello {name}',
];

$translator = new Translation($translations);

echo $translator->trans('hello', ['name' => 'John']);
// Hello John
```

---

## Translation Method

```php
trans(string $key, ?array $data = null): string
```

### Behavior

1. Finds translation by key
2. Replaces placeholders
3. Returns key if not found

---

## Placeholders

### Named placeholders

```php
'hello' => 'Hello {name}'
```

```php
$translator->trans('hello', ['name' => 'John']);
```

---

### sprintf placeholders

```php
'items' => 'You have %d items'
```

```php
$translator->trans('items', [5]);
```

---

## Fallback Behavior

If translation is missing:

```php
$translator->trans('unknown.key');
```

Returns:

```
unknown.key
```

---

## Integration with Validator

Validator automatically uses translation:

```php
$validator = new Validator($translator);
```

---

## Best Practices

* Keep translations in separate files
* Use consistent keys (`validation.required`)
* Avoid hardcoding messages

---

## Summary

The translation system is:

* Lightweight
* Dependency-free
* Flexible
* Safe (no crashes on errors)

---
