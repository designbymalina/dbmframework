# TemplateFeature

Klasa `TemplateFeature` odpowiada za zestaw pomocniczych metod dostępnych bezpośrednio w szablonach HTML/PHP w katalogu `templates/`.

Dzięki niej w szablonach można używać wywołań takich jak:

```php
{{ $this->path('start') }}
{{ $this->htmlCreateSelect('name', $options, 'value') }}
```

## Dostęp do metod

Metody TemplateFeature są dostępne w szablonach jako $this.

### Metody

#### Metoda path()

```php
path(string $name, array $params = []): string
```

Zwraca ścieżkę URL przypisaną do trasy o nazwie $name, opcjonalnie z dynamicznymi parametrami.
{{ $this->path('start') }}

#### Metoda asset()

```php
asset(string $path): string
```

Zwraca ścieżkę do zasobu (np. CSS, JS) względem katalogu publicznego.

```html
<link rel="stylesheet" href="{{ $this->asset('css/style.css') }}">
```

#### Metoda isActive()

```php
isActive(string $name): bool
```

Sprawdza, czy aktualnie wyświetlana trasa ma nazwę $name.

```html
<li class="{{ $this->isActive('kontakt') ? 'active' : '' }}">
```

#### Metoda htmlCreateSelect()
```php
htmlCreateSelect(string $name, array $options, string $selected = ''): string
```

Generuje tag `select` z opcjami.

{{ $this->htmlCreateSelect('category', ['1' => 'News', '2' => 'Blog'], '2') }}

Dokumentacja jest w trakcie opracowywania więcej metod, któe możesz użyć w szablonach znjaduje się w klasie: `application/Classes/TemplateFeature.php`.
