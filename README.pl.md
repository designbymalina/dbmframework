# DBM Framework - Lekki framework PHP nastawiony na wydajność

Framework DBM to lekki silnik aplikacji PHP zaprojektowany dla programistów, którzy chcą pełnej kontroli nad architekturą bez narzuconych schematów.

Framework skupiony na wydajności, prostocie i pełnej kontroli nad architekturą aplikacji.

Zaprojektowany do wydajnych, modułowych aplikacji PHP.

**Szybki. Elastyczny. Zgodny z PSR.**

[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D8.1-blue)](http://php.net)
[![PSR](https://img.shields.io/badge/PSR-1%2C%204%2C%2011%2C%2012-green)](https://www.php-fig.org/)
[![Build](https://img.shields.io/badge/build-passing-success)]()
[![Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen)]()
[![Composer](https://img.shields.io/badge/composer-ready-orange)](https://getcomposer.org/)
[![Speed](https://img.shields.io/badge/performance-ultra%20fast-red)]()
[![License](https://img.shields.io/badge/license-DbM-orange)](https://dbm.org.pl)

Framework DBM v6 powstał jako odpowiedź na nadmiar złożoności w nowoczesnych frameworkach PHP.  
Nie narzuca pełnej struktury aplikacji - dostarcza gotowe komponenty, które można wykorzystać lub zastąpić.

## Wydajność

Framework został zaprojektowany z myślą o minimalnym narzucie runtime:

- ~1.9 ms czas odpowiedzi (z włączonym cache serwera)
- ~3–4 ms bez cache
- ~5 ms z bazą danych i templatingiem

Pomiar wykonany na zewnętrznym serwerze w środowisku developerskim.  
Wyniki zależne od konfiguracji i obciążenia.

> "Laravel and Symfony are powerful. DBM is fast."

## Dlaczego DBM Framework?

W przeciwieństwie do dużych frameworków:

- nie narzuca struktury aplikacji
- nie ukrywa logiki za „magią”
- nie wprowadza zbędnych warstw

Daje pełną kontrolę nad kodem i wydajnością.  

DBM to framework, który nie walczy z programistą - **pozwala mu pracować tak, jak lubi**.

## Funkcje

Framework dostarcza minimalny zestaw narzędzi potrzebnych do budowy aplikacji — bez zbędnych warstw i narzutów.  

- Modułowa architektura (zgodna z PSR-4)
- Lekki potok middleware (przepływ żądań w stylu PSR)
- Elastyczny system routingu (tylko na poziomie frameworka)
- Lekki kontener Dependency Injection (bez refleksyjnej magii)
- Rozszerzalność sterowana zdarzeniami
- Gotowość do obsługi CLI (poprzez zewnętrzną warstwę aplikacji)
- Tylko rdzeń Frameworka (bez CMS, bez platformy, bez warstwy interfejsu użytkownika)
- Minimalny narzut runtime (nastawiony na wysoką wydajność)

Brak ukrytych mechanizmów i automatycznej konfiguracji - wszystko działa jawnie i przewidywalnie.

## Architektura nastawiona na wydajność

DbM Framework został zaprojektowany z jednym głównym celem: maksymalną wydajnością przy minimalnym narzucie systemowym.

Od routingu i ładowania zależności po szablony oraz odpowiedzi API - każda warstwa frameworka została zoptymalizowana pod kątem szybkości i elastyczności.

⭐ Jeśli projekt Ci się podoba, zostaw gwiazdkę na GitHubie.

![DBM Framework](https://dbm.org.pl/images/page/packages/dbmframework-benchmarkach.png)

### Projekty oparte na DBM Framework

- DBM CMS
- DBM Platform
- Systemy API
- Modułowe aplikacje webowe

## Wbudowane komponenty

Framework zawiera zestaw lekkich komponentów infrastrukturalnych potrzebnych do budowy aplikacji webowych.

### HTTP i aplikacja

- routing HTTP
- middleware (pipeline request/response)
- kontener Dependency Injection
- system zdarzeń i listenerów
- mechanizm konsoli CLI (implementowany w warstwie aplikacji)

### Dane i prezentacja

- system szablonów (DbM View Engine)
- warstwa dostępu do danych (Query Builder kompatybilny z Doctrine DBAL)
- system tłumaczeń
- walidator formularzy

### Infrastruktura

- system sesji i cookies
- filesystem + upload plików i obrazów
- logger
- error handler
- mailer interface
- helpery i sanitizery

Komponenty są lekkie, modularne i mogą zostać zastąpione własną implementacją (np. Twig zamiast wbudowanego silnika widoków).  

Framework został zaprojektowany jako modularny monolit - komponenty mogą być rozwijane niezależnie, zachowując prostotę wdrożenia pojedynczej aplikacji.  

## Silnik szablonów

Framework domyślnie korzysta z lekkiego silnika DbM View Engine.

- szybki i bez zależności
- oparty bezpośrednio o PHP (bez DSL)
- rozszerzalny przez callbacki

Może zostać zastąpiony dowolnym silnikiem (np. Twig).

## Filozofia

Framework DBM rozdziela obszary:

- **Framework = silnik wykonawczy**
- **Warstwa aplikacji = definiowana przez użytkownika**
- **CMS / Platforma = opcjonalne rozszerzenia**

Rdzeń jest szybki, przewidywalny i możliwy do ponownego wykorzystania.

## Historia projektu

DBM Framework rozwijał się etapami - od prostego mikroframeworka do pełnego ekosystemu aplikacyjnego.

- **v1 / v2** - początki projektu i eksperymenty architektoniczne
- **v3 / v4** - lekki monolityczny mikroframework
- **v5** - przejście na architekturę modularnego monolitu
- **v6** - oddzielenie silnika frameworka od warstwy aplikacyjnej i rozwój ekosystemu DBM

Obecna wersja koncentruje się na wydajności, modularności i pełnej kontroli nad architekturą aplikacji.  

## Instalacja

Wymagania:  

- PHP 8.1 lub nowszy
- Composer

```bash
composer require designbymalina/dbmframework
```

Po instalacji należy stworzyć warstwę aplikacji (bootstrap), która uruchomi framework.  

## Podstawowe użycie

DBM Framework nie jest samodzielną aplikacją. Musi być używany w obrębie własnej warstwy aplikacji.

**Przykład:**

Poniżej minimalny przykład uruchomienia aplikacji opartej na DBM Framework.  

```php
// example/index.php

declare(strict_types=1);

use Dbm\Core\Paths;

$baseDirectory = realpath(dirname(__DIR__));

require_once $baseDirectory . '/vendor/autoload.php';

Paths::setBasePath($baseDirectory);

$appFactory = require __DIR__ . '/bootstrap/app.php';

$app = $appFactory();

$response = $app->run();

$response->send();
```

**Proces:**  

1. Ustawienie ścieżki bazowej  
2. Załadowanie autoloadera  
3. Utworzenie aplikacji przez factory  
4. Uruchomienie cyklu request -> response  

### Minimalna struktura aplikacji

- bootstrap/app.php - fabryka aplikacji
- bootstrap/services.php - konfiguracja kontenera DI
- bootstrap/controller.php - przykładowy kontroler

```bash
php -S localhost:8000 example/index.php
```

URL: `http://localhost:8000/`

### Przykład routingu

```php
$router->get('/path', [NameController::class, 'methodName'], 'route_name');
```

Prosty przykład mapowania ścieżki URL na kontroler.  

Szczegóły:  

- [Web Routing](_Docs/03_01-web-routing.md)  
- [API Routing](_Docs/03_02-api-routing.md)  

## Architektura modułowa

DBM Framework wspiera podejście modularnego monolitu.

Aplikacja może być rozwijana jako zestaw niezależnych modułów z wyraźnym podziałem odpowiedzialności (Separation of Concerns) przy zachowaniu prostoty wdrożenia pojedynczego systemu.

**Przegląd architektury**

Framework działa w oparciu o cykl:  

Request -> Routing -> Middleware -> Controller -> Response  

Więcej: [Architecture](_Docs/01_00-1-architecture.md)  

**Struktura DBM składa się z:**

- jądra (cykl życia żądania)
- routera (elastyczny routing)
- dyspozytora middleware
- kontenera (DI)

## Zasady projektowania

- brak globalnego stanu
- brak ukrytej magii
- jawna konfiguracja
- kompozycja zamiast dziedziczenia

## Programowanie

Klonowanie repozytorium i instalowanie zależności:

```bash
git clone https://github.com/designbymalina/dbmframework
cd dbmframework
composer install
```

lub przez GitHub CLI.

## Kiedy używać DBM Framework

Framework sprawdzi się gdy:

- budujesz własny system od zera
- potrzebujesz wysokiej wydajności
- nie chcesz narzuconej struktury (jak w Laravel/Symfony)
- tworzysz API lub backend pod aplikację

Nie jest to framework typu "plug & play" - wymaga zbudowania własnej warstwy aplikacji.  

Jeśli potrzebujesz gotowego rozwiązania, zobacz DBM Platform.  

[DBM Platform - aplikacja oparta na frameworku (GitHub)](https://github.com/designbymalina/dbmplatform)  

## Ekosystem DBM

DBM Framework jest częścią większego ekosystemu:

- DBM Framework - silnik aplikacji
- DBM Platform - gotowa warstwa aplikacyjna

Platforma rozszerza framework o panel administracyjny, uwierzytelnianie i moduły aplikacyjne.

Więcej: [Ecosystem](_Docs/01_00-2-ecosystem.md)  

## Dokumentacja

Pełna dokumentacja frameworka znajduje się w katalogu `/_Docs`.

Start:

- [Wprowadzenie](_Docs/01_01-introduction.md)
- [Architektura](_Docs/01_00-1-architecture.md)
- [Ekosystem](_Docs/01_00-2-ecosystem.md)

## Licencja

Projekt udostępniony na licencji MIT.

Copyright (c) Design by Malina
