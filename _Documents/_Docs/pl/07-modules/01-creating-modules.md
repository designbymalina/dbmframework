# Tworzenie modułów – DbM Framework

Ten dokument opisuje jak stworzyć własny moduł, który może być instalowany, włączany i zarządzany w DbM Framework.  

# Struktura modułu

Minimalna struktura:

```bash
modules/
└── YourModule/
    ├── YourModule.php
    ├── module.json
    └── Folders
```

**1. Plik manifestu module.json** 

To kluczowy plik modułu.  

```json
{
  "name": "Your Module",
  "description": "Module description.",
  "version": "1.0.0",
  "key": "yourmodule",
  "type": "plugin",
  "class": "Mod\\Admin\\AdminModule"
}
```

Pola:

| Pole | Opis |
|------|------|
| key | unikalny identyfikator (slug) |
| name | nazwa modułu   |   
| description | opis |  
| version | wersja |
| type | core lub plugin |

**2. Klasa modułu**  

Plik: `YourModule.php`

```php
namespace Mod\YourModule;

use Dbm\Core\Module\AbstractModule;

final class YourModule extends AbstractModule
{
    public function boot(): void
    {
        // Rejestracja routingu, usług itd.
    }

    public function install(): void
    {
        // instalacja (np. migracje, dane)
    }

    public function enable(): void
    {
        // aktywacja
    }

    public function disable(): void
    {
        // dezaktywacja
    }

    public function uninstall(): void
    {
        // usunięcie modułu
    }
}
```

**3. Rejestracja modułu**  

System automatycznie:  

- skanuje katalog /modules  
- buduje cache: storage/cache/modules.php  
- ładuje tylko enabled = true  

**4. Cache modułów**  

Plik: `storage/cache/modules.php`  

Zawiera:

```php
return [
  'yourmodule' => [
    'key' => 'yourmodule',
    'class' => 'Mod\\YourModule\\YourModule',
    'enabled' => true,
    'installed' => true,
    'path' => '/modules/YourModule'
  ],
];
```

**5. Lifecycle modułu**  

Obsługiwane stany:

| Stan | Opis |
|------|------|
| installed | moduł zainstalowany |
| enabled | aktywny |
| disabled | wyłączony |
| uninstalled | usunięty |

**6. Instalacja modułu**  

Instalacja odbywa się przez:

- Installer (First Install)
- lub przyszły ModuleLifecycleManager

Typowy flow:

1. Pobranie modułu
2. Rejestracja w cache
3. install()
4. enable()

Framework:

- porównuje wersje
- wykrywa: update, packages (do instalacji)

**7 Instalator (Installer)**  

Po pierwszej instalacji tworzony jest plik: `storage/framework/installed.lock`

```bash
{
  "installed": true,
  "admin": true,
  "completed_at": "2026-03-13T16:15:57+01:00"
}
```

Efekt: Installer jest niedostępny publicznie, dostępny w admin (opcjonalnie).

Moduł installer zostaje:  

```php
'enabled' => false
```

**8. Boot systemu**  

```php
registerModules()
```

Logika:

```php
if (
    !installed.lock
    || są nowe moduły
    || trwa instalacja (session)
) {
    bootInstaller();
}
```

## Dobre praktyki

- używaj unikalnych key
- zawsze podawaj version
- waliduj dependencies
- nie zakładaj że moduł istnieje
- używaj cache (nie skanuj FS w produkcji)  

Rozszerzenia: ModuleLifecycleManager...
