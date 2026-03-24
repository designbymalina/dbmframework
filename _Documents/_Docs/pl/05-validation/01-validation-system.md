# DBM Validation System

## Overview

The `Validator` class provides a flexible and extensible way to validate form data.

### Features

* Rule-based validation system
* Custom validation rules
* CSRF protection support
* Translation support (multi-language)
* Normalized error output (API-friendly)
* Extendable via inheritance

---

## Basic Usage

### Without Translation

```php
use Dbm\Validation\Validator;

$validator = new Validator();
$errors = $validator->rules([
    'email' => ['required', 'email'],
], $data);
```

---

### With Translation

```php
$validator = new Validator($translation);
$errors = $validator->rules($rules, $data);
```

---

## Validation Flow

1. Define rules
2. Pass input data
3. Validation runs through each rule
4. Errors are collected and returned

```php
$errors = $validator->rules($rules, $data);

if ($validator->isValid()) {
    // success
}
```

---

## Available Validation Rules

### Required

```php
'required'
```

Field must not be empty.

---

### String

```php
'string'
```

Value must be a string.

---

### Min Length

```php
'min:3'
```

Minimum number of characters.

---

### Max Length

```php
'max:255'
```

Maximum number of characters.

---

### Email

```php
'email'
```

Valid email format.

---

### URL

```php
'url'
```

Valid URL format.

---

### Phone

```php
'phone'
```

Supports formats like:

* `123 123 123`
* `+48 123 123 123`

---

### Letters & Spaces

```php
'letters_spaces'
```

Allows only:

* letters
* spaces
* `'` and `-`

---

### Password

```php
'password'
```

Requirements:

* 8–30 characters
* at least one lowercase letter
* one uppercase letter
* one digit
* one special character

---

### Confirmed

```php
'confirmed'
```

Checks if value matches:

* `{field}_confirmation`
* `{field}_repeat`

---

### Regex

```php
'regex:/pattern/'
```

Custom regex validation.

---

### CSRF

```php
'csrf'
```

Requires registering CSRF rule first.

---

## CSRF Protection

### Register CSRF Rule

```php
$validator->registerCsrfRule($csrfManager);
```

### Usage

```php
'form_csrf' => ['required', 'csrf']
```

---

## Custom Rules

You can define your own validation rules:

```php
$validator->addRule('alpha_dash', function ($field, $value) {
    return preg_match('/^[A-Za-z0-9_-]+$/', $value)
        ? null
        : 'Invalid format';
});
```

---

## Error Handling

### Default Error Format

```php
[
    'error_email' => 'Field email is required.'
]
```

---

### Normalized Errors (API-friendly)

```php
$validator->getNormalizedErrors();
```

Output:

```php
[
    'email' => 'Field email is required.'
]
```

---

## Translation Support

### Translation File Example

```php
return [
    'validation.required' => 'The :field field is required.',
    'validation.email' => 'The :field must be a valid email address.',
];
```

---

### Configuration

Set available languages in `.env`:

```env
APP_LANGUAGES=en,pl
```

---

### Placeholder Replacement

Supported placeholders:

* `:field`
* `:value`

Example:

```php
'validation.min' => 'Field :field must be at least :value characters.'
```

---

## Extending Validator (Recommended Approach)

Create form-specific validator:

```php
class UserForm extends Validator
{
    public function validate(array $data): array
    {
        $this->rules([
            'email' => ['required', 'email'],
        ], $data);

        return $this->getErrors();
    }
}
```

---

## Advanced Example (Real Case)

### PanelUserForm

```php
$form = new PanelUserForm($repository, $csrf, $translation);

$errors = $form->validateCreate($_POST);
```

---

### Features Demonstrated

* Base validation rules
* Conditional validation (create vs update)
* Password confirmation
* Database uniqueness checks
* CSRF validation

---

## Conditional Validation

Example from real usage:

```php
if (!$id) {
    // create logic
}

if ($id && $password) {
    // update logic
}
```

---

## Data Normalization

Optional preprocessing:

```php
$data = $this->normalize($data);
```

Removes unnecessary spaces from strings.

---

## Internal Architecture

### Key Methods

| Method            | Description          |
| ----------------- | -------------------- |
| `rules()`         | Runs validation      |
| `applyRule()`     | Applies single rule  |
| `registerError()` | Stores error         |
| `addRule()`       | Adds custom rule     |
| `trans()`         | Handles translations |

---

## Best Practices

* Always validate input before processing
* Use `normalizedErrors()` for API responses
* Keep validation logic inside Form classes
* Use translation system for user-facing errors
* Separate business logic from validation

---

## Summary

The DBM validation system is:

* Lightweight
* Extensible
* Translation-ready
* API-friendly
* Framework-agnostic

Perfect for both:

* simple forms
* complex admin panels

---
