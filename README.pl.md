# DBM Framework - Lekki framework PHP

**DBM Framework to lekki silnik aplikacji PHP dla programistów, którzy chcą mieć kontrolę nad architekturą swojej aplikacji.**

Dostarcza infrastrukturę potrzebną do budowy aplikacji PHP - routing, middleware, Dependency Injection, zdarzenia, dostęp do danych, szablony i komponenty infrastrukturalne - bez narzucania jednej, zamkniętej architektury aplikacyjnej.

**Ty decydujesz, jak zbudowana jest Twoja aplikacja.**

Szybki. Elastyczny. Modularny. Zgodny z PSR.

[![PHP](https://img.shields.io/badge/PHP-%3E%3D8.1-blue)](https://www.php.net/)
[![PSR](https://img.shields.io/badge/PSR-1%2C%204%2C%2011%2C%2012-green)](https://www.php-fig.org/)
[![Composer](https://img.shields.io/badge/Composer-ready-orange)](https://getcomposer.org/)
[![License](https://img.shields.io/badge/license-MIT-blue)](LICENSE)

---

## Czym jest DBM Framework?

DBM Framework jest **silnikiem wykonawczym aplikacji PHP**.

Jego zadaniem jest dostarczenie podstawowej infrastruktury, na której można zbudować własną aplikację:

* obsługę HTTP,
* routing,
* middleware,
* Dependency Injection,
* system zdarzeń,
* dostęp do danych,
* szablony,
* sesje i cookies,
* filesystem,
* logowanie,
* obsługę błędów,
* tłumaczenia,
* walidację,
* oraz inne komponenty infrastrukturalne.

DBM nie próbuje definiować całej aplikacji.

Nie narzuca konkretnego CMS-a, panelu administracyjnego, modelu domenowego ani sposobu organizacji warstwy biznesowej.

**Framework dostarcza silnik. Warstwa aplikacji należy do Ciebie.**

---

## Dlaczego DBM?

Laravel i Symfony są potężnymi i rozbudowanymi ekosystemami, które świetnie sprawdzają się w wielu zastosowaniach.

DBM podchodzi do problemu inaczej.

Zamiast maksymalizować liczbę funkcji i automatyzacji, skupia się na:

* lekkiej architekturze,
* małym narzucie runtime,
* jawnej konfiguracji,
* modularności,
* przewidywalnym przepływie wykonania,
* oraz pełnej kontroli nad warstwą aplikacji.

DBM nie jest próbą zastąpienia Laravel czy Symfony.

To **innowacyjne podejście do budowy aplikacji PHP** - szczególnie dla programistów, którzy wolą świadomie definiować architekturę zamiast korzystać z rozbudowanych mechanizmów automatyzujących dużą część aplikacji.

---

## Architektura Dybem

DBM Framework jest podstawą większego ekosystemu **Dybem**.

```text
                    DYBEM ECOSYSTEM

┌──────────────────────────────────────────────┐
│              Twoja aplikacja                 │
│       Controllers · Services · Domain        │
├──────────────────────────────────────────────┤
│                DBM Platform                  │
│        CMS · Admin · Auth · Modules          │
│                 (opcjonalnie)                │
├──────────────────────────────────────────────┤
│               DBM Framework                  │
│ HTTP · Routing · Middleware · DI · Events    │
│ Database · Templates · Sessions · Logging    │
└──────────────────────────────────────────────┘
```

### DBM Framework

Lekki silnik wykonawczy zapewniający podstawową infrastrukturę aplikacji.

### Warstwa aplikacji

Warstwa definiowana przez programistę.

Możesz stworzyć własną strukturę aplikacji, moduły, usługi, domenę biznesową oraz sposób organizacji kodu.

### DBM Platform

Opcjonalna, gotowa warstwa aplikacyjna zbudowana na DBM Framework.

Dostarcza między innymi CMS, panel administracyjny, uwierzytelnianie i moduły aplikacyjne.

---

## Wydajność

DBM Framework został zaprojektowany z myślą o **minimalnym narzucie runtime**.

W przeprowadzonych pomiarach uzyskano:

| Scenariusz                | Czas odpowiedzi |
| ------------------------- | --------------: |
| Z włączonym cache serwera |         ~1.9 ms |
| Bez cache                 |         ~3–4 ms |
| Baza danych + templating  |           ~5 ms |

Pomiary wykonano na zewnętrznym serwerze w środowisku developerskim.

Wyniki zależą między innymi od sprzętu, konfiguracji PHP, serwera, bazy danych, cache oraz obciążenia.

Dlatego powyższe wartości należy traktować jako **wyniki konkretnych testów**, a nie uniwersalny benchmark wszystkich aplikacji.

Wydajność DBM wynika przede wszystkim z założeń architektonicznych:

* niewielkiego rdzenia,
* ograniczenia automatyzacji,
* jawnej rejestracji zależności,
* braku ciężkiego autowiringu opartego na reflection,
* oraz ograniczenia liczby warstw wykonywanych podczas obsługi żądania.

---

## Funkcje

DBM Framework dostarcza podstawowy zestaw komponentów potrzebnych do budowy aplikacji PHP.

### HTTP i aplikacja

* routing HTTP,
* middleware i pipeline request/response,
* Dependency Injection,
* system zdarzeń i listenerów,
* obsługa cyklu życia aplikacji,
* możliwość rozszerzenia o warstwę CLI.

### Dane i prezentacja

* DBM View Engine,
* Query Builder,
* warstwa dostępu do danych kompatybilna z Doctrine DBAL,
* system tłumaczeń,
* walidator formularzy.

### Infrastruktura

* sesje i cookies,
* filesystem,
* upload plików i obrazów,
* logger,
* error handler,
* mailer interface,
* helpery,
* sanitizery.

Komponenty są modularne i mogą zostać zastąpione własną implementacją.

Przykładowo, wbudowany silnik widoków może zostać zastąpiony przez Twig.

---

## Architektura modułowa

DBM Framework wspiera podejście **modularnego monolitu**.

Aplikacja może być podzielona na niezależne moduły z wyraźnym podziałem odpowiedzialności, zachowując jednocześnie prostotę wdrożenia pojedynczego systemu.

Przykładowy przepływ obsługi żądania:

```text
HTTP Request
     ↓
  Routing
     ↓
 Middleware
     ↓
 Controller
     ↓
 Services / Domain
     ↓
  Response
```

Podstawowe elementy frameworka obejmują:

* jądro cyklu życia żądania,
* router,
* pipeline middleware,
* kontener Dependency Injection,
* system zdarzeń,
* komponenty infrastrukturalne.

---

## Jawne Dependency Injection

DBM Framework świadomie unika ciężkiego autowiringu opartego na reflection.

Zależności są rejestrowane jawnie przy pomocy lekkiego kontenera DI.

Przykładowo:

```php
$container->singleton(MyService::class, function ($container) {
    return new MyService(
        $container->get(MyRepository::class)
    );
});
```

lub:

```php
$container->set(MyService::class, $service);
```

Takie podejście zapewnia:

* przewidywalny przepływ wykonania,
* mniejszy narzut runtime,
* prostszą diagnostykę,
* oraz pełną kontrolę nad sposobem tworzenia zależności.

Nie oznacza to, że automatyzacja jest zła.

DBM po prostu przyjmuje inne założenie:

**ważne zależności powinny być widoczne i świadomie konfigurowane.**

---

## Silnik szablonów

DBM Framework domyślnie korzysta z lekkiego **DBM View Engine**.

Najważniejsze założenia:

* brak zewnętrznych zależności,
* bezpośrednie wykorzystanie PHP,
* brak dodatkowego DSL,
* możliwość rozszerzania przez callbacki.

Silnik może zostać zastąpiony innym rozwiązaniem, na przykład Twig.

---

## Framework ≠ gotowa aplikacja

DBM Framework nie jest gotowym CMS-em ani kompletną aplikacją.

Jest fundamentem, na którym możesz zbudować własny system.

```text
DBM Framework
      ↓
Twoja warstwa aplikacji
      ↓
Twoja logika biznesowa
      ↓
Twoja aplikacja
```

Jeżeli zamiast budować warstwę aplikacji od zera potrzebujesz gotowego rozwiązania, możesz wykorzystać **DBM Platform**.

---

## DBM Platform

**DBM Platform** jest gotową warstwą aplikacyjną zbudowaną na DBM Framework.

Rozszerza framework między innymi o:

* panel administracyjny,
* uwierzytelnianie,
* CMS,
* zarządzanie modułami,
* warstwę aplikacyjną,
* gotową strukturę systemu.

[DBM Platform na GitHubie](https://github.com/designbymalina/dbmplatform)

Framework i Platforma odpowiadają na dwa różne sposoby pracy z ekosystemem Dybem:

| DBM Framework                                | DBM Platform                              |
| -------------------------------------------- | ----------------------------------------- |
| silnik aplikacji                             | gotowa warstwa aplikacyjna                |
| infrastruktura                               | infrastruktura + funkcje aplikacyjne      |
| własna architektura                          | gotowa struktura systemu                  |
| dla programistów budujących własną aplikację | dla projektów potrzebujących gotowej bazy |

---

## Filozofia

DBM Framework opiera się na kilku prostych zasadach:

* **brak globalnego stanu,**
* **brak ukrytej magii,**
* **jawna konfiguracja,**
* **kompozycja zamiast nadmiernego dziedziczenia,**
* **modularność bez niepotrzebnej złożoności,**
* **kontrola nad architekturą pozostaje po stronie programisty.**

Framework ma pomagać w budowaniu aplikacji, a nie decydować za programistę, jak ta aplikacja musi wyglądać.

---

## Historia projektu

DBM Framework rozwijał się etapami - od prostego mikroframeworka do obecnej architektury będącej podstawą ekosystemu Dybem.

* **v1 / v2** - początki projektu i eksperymenty architektoniczne,
* **v3 / v4** - lekki monolityczny mikroframework,
* **v5** - przejście na architekturę modularnego monolitu,
* **v6** - oddzielenie silnika frameworka od warstwy aplikacyjnej i rozwój ekosystemu Dybem.

Wersja v6 koncentruje się na:

**lekkości · modularności · przewidywalności · kontroli**

---

## Instalacja

### Wymagania

* PHP 8.1 lub nowszy,
* Composer.

Instalacja przez Composer:

```bash
composer require designbymalina/dbmframework
```

Po instalacji należy utworzyć warstwę aplikacji, która skonfiguruje i uruchomi framework.

---

## Podstawowe użycie

DBM Framework działa jako silnik aplikacji i wymaga własnej warstwy aplikacyjnej.

Minimalny przykład:

```php
declare(strict_types=1);

use Dbm\Core\DotEnv;
use Dbm\Core\Paths;
use Dbm\Http\Emitter\ResponseEmitter;

$baseDirectory = realpath(dirname(__DIR__));

require_once $baseDirectory . '/bootstrap/runtime.php';

initRuntime($baseDirectory);

require_once $baseDirectory . '/../vendor/autoload.php';

require_once $baseDirectory . '/bootstrap/support.php';

Paths::setBasePath($baseDirectory);

$envPath = Paths::basePath() . '/.env';

if (file_exists($envPath)) {
    (new DotEnv($envPath))->load();
}

$appFactory = require Paths::basePath() . '/bootstrap/app.php';

$app = $appFactory();

$response = $app->run();

(new ResponseEmitter())->emit($response);
```

Proces uruchomienia aplikacji:

```text
1. Ustawienie ścieżki bazowej
          ↓
2. Załadowanie autoloadera
          ↓
3. Utworzenie aplikacji przez factory
          ↓
4. Uruchomienie aplikacji
          ↓
5. Request → Response
```

Pełniejszy przykład aplikacji znajduje się w katalogu:

```text
/example
```

---

## Routing

Przykładowa definicja routingu:

```php
$router->get(
    '/path',
    [NameController::class, 'methodName'],
    'route_name'
);
```

Mapuje ona ścieżkę URL na konkretną metodę kontrolera.

Dokumentacja:

* [Web Routing](_Docs/03_01-web-routing.md)
* [API Routing](_Docs/03_02-api-routing.md)

---

## Programowanie

Klonowanie repozytorium:

```bash
git clone https://github.com/designbymalina/dbmframework.git

cd dbmframework

composer install
```

Możesz również korzystać z GitHub CLI.

---

## Kiedy używać DBM Framework?

DBM Framework może być dobrym wyborem, jeżeli:

* budujesz własną aplikację od podstaw,
* potrzebujesz lekkiej warstwy wykonawczej,
* zależy Ci na niskim narzucie runtime,
* chcesz samodzielnie definiować architekturę aplikacji,
* tworzysz API lub backend,
* budujesz modułową aplikację webową,
* potrzebujesz większej kontroli nad zależnościami i cyklem życia aplikacji.

### Kiedy DBM może nie być najlepszym wyborem?

DBM nie jest frameworkiem typu „plug & play”.

Wymaga stworzenia własnej warstwy aplikacyjnej i podjęcia części decyzji architektonicznych samodzielnie.

Jeżeli potrzebujesz bardzo rozbudowanego ekosystemu z dużą liczbą gotowych pakietów, integracji i ustalonych konwencji, Laravel lub Symfony mogą być lepszym wyborem.

Jeżeli natomiast chcesz mieć **lekki fundament i samodzielnie zdecydować jak ma wyglądać aplikacja**, wówczas DBM daje Ci taką możliwość.

---

## Projekty oparte na DBM Framework

DBM Framework jest wykorzystywany jako fundament dla różnych typów aplikacji:

* **DBM Platform**
* systemy API,
* modułowe aplikacje webowe,
* aplikacje administracyjne,
* systemy biznesowe,
* własne warstwy aplikacyjne.

---

## Ekosystem Dybem

DBM Framework jest częścią większego ekosystemu **Dybem**.

```text
Dybem
 │
 ├── DBM Framework
 │      └── silnik aplikacji
 │
 ├── DBM Platform
 │      └── gotowa warstwa aplikacyjna
 │
 └── Modules / Extensions
        └── dodatkowe komponenty ekosystemu
```

Więcej informacji:

https://www.dybem.com/

---

## Dokumentacja

Dokumentacja frameworka znajduje się w katalogu `/_Docs`.

Najważniejsze materiały:

* [Wprowadzenie](_Docs/01_01-introduction.md)
* [Architektura](_Docs/01_00-1-architecture.md)
* [Ekosystem](_Docs/01_00-2-ecosystem.md)
* [Web Routing](_Docs/03_01-web-routing.md)
* [API Routing](_Docs/03_02-api-routing.md)

Uwaga: Dokumentacja jest w trakcie przygotowania. Sukcesywnie uzupełniamy opisy kolejnych modułów.

---

## Open Source

DBM Framework jest projektem open source rozwijanym jako część ekosystemu Dybem.

Jeżeli interesuje Cię podejście do PHP oparte na lekkiej architekturze, jawnej konfiguracji i kontroli nad warstwą aplikacji:

⭐ **Zostaw Star na GitHubie.**

* Sprawdź przykład aplikacji.

* Przetestuj framework.

* Podziel się opinią lub otwórz Issue.

[DBM Framework na GitHubie](https://github.com/designbymalina/dbmframework)

---

## Licencja

Projekt jest udostępniony na licencji MIT.

Copyright (c) Design by Malina
