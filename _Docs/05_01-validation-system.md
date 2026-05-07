# System walidacji DBM

## Przegląd

Klasa `Validator` zapewnia elastyczny i rozszerzalny sposób walidacji danych w formularzu.

### Funkcje

* System walidacji oparty na regułach
* Niestandardowe reguły walidacji
* Obsługa ochrony CSRF
* Obsługa tłumaczeń (wielojęzycznych)
* Znormalizowane wyjście błędów (przyjazne dla API)
* Możliwość rozszerzenia poprzez dziedziczenie

---

## Podstawowe użycie

### Bez tłumaczenia

```php
use Dbm\Validation\Validator;

$validator = new Validator();
$errors = $validator->rules([
    'email' => ['required', 'email'],
], $data);
```

---

### Z tłumaczeniem

```php
$validator = new Validator($translation);
$errors = $validator->rules($rules, $data);
```

---

## Przebieg walidacji

1. Zdefiniuj reguły
2. Przekaż dane wejściowe
3. Walidacja przechodzi przez każdą regułę
4. Zbieranie i zwracanie błędów

```php
$errors = $validator->rules($rules, $data);

if ($validator->isValid()) {
    // powodzenie
}
```

---

## Dostępne reguły walidacji

### Wymagane

```php
'required'
```

Pole nie może być puste.

---

### Ciąg znaków

```php
'string'
```

Wartość musi być ciągiem znaków.

---

### Minimalna długość

```php
'min:3'
```

Minimalna liczba znaków.

---

### Maksymalna długość

```php
'max:255'
```

Maksymalna liczba znaków.

---

### Adres e-mail

```php
'email'
```

Prawidłowy format adresu e-mail.

---

### Adres URL

```php
'url'
```

Prawidłowy format adresu URL.

---

### Telefon

```php
'telefon'
```

Obsługuje formaty takie jak:

* `123 123 123`
* `+48 123 123 123`

---

### Litery i spacje

```php
'litery_spacje'
```

Dopuszcza tylko:

* litery
* spacje
* `'` i `-`

---

### Hasło

```php
'hasło'
```

Wymagania:

* 8–30 znaków
* co najmniej jedna mała litera
* jedna wielka litera
* jedna cyfra
* jeden znak specjalny

---

### Potwierdzono

```php
'potwierdzono'
```

Sprawdza, czy wartość pasuje do:

* `{field}_confirmation`
* `{field}_repeat`

---

### Wyrażenie regularne

```php
'regex:/pattern/'
```

Walidacja niestandardowego wyrażenia regularnego.

---

### CSRF

```php
'csrf'
```

Wymaga najpierw zarejestrowania reguły CSRF.

---

## Ochrona przed CSRF

### Rejestracja reguły CSRF

```php
$validator->registerCsrfRule($csrfManager);
```

### Użycie

```php
    'form_csrf' => ['required', 'csrf']
```

---

## Reguły niestandardowe

Możesz zdefiniować własne reguły walidacji:

```php
$validator->addRule('alpha_dash', function ($field, $value) {
    return preg_match('/^[A-Za-z0-9_-]+$/', $value)
    ? null
    : 'Nieprawidłowy format';
});
```

---

## Obsługa błędów

### Domyślny format błędu

```php
[
    'error_email' => 'Pole adresu e-mail jest wymagane.'
]
```

---

### Znormalizowane błędy (przyjazne dla API)

```php
$validator->getNormalizedErrors();
```

Dane wyjściowe:

```php
[
    'email' => 'Pole e-mail jest wymagane.'
]
```

---

## Obsługa tłumaczeń

### Przykład pliku z tłumaczeniem

```php
return [
    'validation.required' => 'Pole :field jest wymagane.',
    'validation.email' => 'Pole :field musi zawierać prawidłowy adres e-mail.',
];
```

---

### Konfiguracja

Ustaw dostępne języki w `.env`:

```env
APP_LANGUAGES=en,pl
```

---

### Zastępowanie symboli zastępczych

Obsługiwane symbole zastępcze:

* `:field`
* `:value`

Przykład:

```php
'validation.min' => 'Pole :field musi mieć co najmniej :value znaków.'
```

---

## Rozszerzanie walidatora (zalecane podejście)

Utwórz walidator specyficzny dla formularza:

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

## Zaawansowany przykład (przypadek rzeczywisty)

### PanelUserForm

```php
$form = new PanelUserForm($repository, $csrf, $translation);

$errors = $form->validateCreate($_POST);
```

---

### Prezentowane funkcje

* Reguły walidacji bazy
* Walidacja warunkowa (tworzenie vs. aktualizacja)
* Potwierdzenie hasła
* Sprawdzanie unikalności bazy danych
* Walidacja CSRF

---

## Walidacja warunkowa

Przykład z rzeczywistego użycia:

```php
if (!$id) {
    // logika tworzenia
}

if ($id && $password) {
    // logika aktualizacji
}
```

---

## Normalizacja danych

Opcjonalne przetwarzanie wstępne:

```php
$data = $this->normalize($data);
```

Usuwa zbędne spacje z ciągów znaków.

---

## Architektura wewnętrzna

### Kluczowe metody

| Metoda | Opis |
|-----------------|--------------------|
| `rules()` | Uruchamia walidację |
| `applyRule()` | Stosuje pojedynczą regułę |
| `registerError()` | Przechowuje błąd |
| `addRule()` | Dodaje niestandardową regułę |
| `trans()` | Obsługuje tłumaczenia |

---

## Najlepsze praktyki

* Zawsze waliduj dane wejściowe przed przetworzeniem
* Używaj `normalizedErrors()` dla odpowiedzi API
* Zachowaj logikę walidacji w klasach formularzy
* Używaj systemu tłumaczeń dla błędów widocznych dla użytkownika
* Oddziel logikę biznesową od walidacji

---

## Podsumowanie

System walidacji DBM jest:

* Lekki
* Rozszerzalny
* Gotowy na tłumaczenia
* Przyjazny dla API
* Niezależny od frameworka

Idealny dla:

* prostych formularzy
* złożonych paneli administracyjnych

---
