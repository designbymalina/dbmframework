# DbM Framework - Ultraszybki framework PHP dla wydajnych aplikacji internetowych

**Fast. Flexible. PSR-Compatible.**  
**Modern PHP MVC/MVP Framework + CMS Engine with built-in API**

[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D8.1-blue)](http://php.net)
[![PSR](https://img.shields.io/badge/PSR-1%2C%204%2C%2011%2C%2012-green)](https://www.php-fig.org/)
[![Build](https://img.shields.io/badge/build-passing-success)]()
[![Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen)]()
[![Composer](https://img.shields.io/badge/composer-ready-orange)](https://getcomposer.org/)
[![Speed](https://img.shields.io/badge/performance-ultra%20fast-red)]()
[![License](https://img.shields.io/badge/license-DbM-orange)](https://dbm.org.pl)

DBM Framework PHP MVC MVP + DBM CMS, Version 5  
Wszystkie prawa autorskie zastrzeżone przez Design by Malina (DbM)  
Strona WWW: [www.dbm.org.pl](http://www.dbm.org.pl)  

## O frameworku

DBM Framework v5 to **modułowy monolit** PHP przeznaczony do tworzenia długotrwałych, łatwych w utrzymaniu aplikacji z pełną kontrolą nad architekturą i wydajnością. Framework łączy lekkość i prostotę klasycznego MVC/MVP z nowoczesnym podejściem do rozbudowy aplikacji poprzez wyraźnie wydzielone moduły, bez narzucania nadmiarowych abstrakcji czy ukrytej logiki.

Wcześniejsze wersje DBM Framework opierały się na klasycznej architekturze monolitycznej. Wersja 5 wprowadza modularność, umożliwiając logiczne rozdzielenie odpowiedzialności w ramach jednego systemu wdrażanego jako całość. Takie podejście pozwala zachować prostotę i wydajność monolitu, jednocześnie zapewniając skalowalność, czytelność i stabilność architektury w długim okresie.

Framework stanowi również podstawę **platformy DBM**, w tym **DBM CMS** - gotowego rozwiązania umożliwiającego szybkie uruchamianie stron i aplikacji bez konieczności tworzenia własnej infrastruktury od podstaw. CMS może działać jako lekki system oparty na plikach i szablonach lub zostać rozszerzony o moduły administracyjne i bazodanowe, zachowując pełną kontrolę nad kodem i strukturą aplikacji.

DBM Framework jest przeznaczony dla zespołów i projektów, które wymagają przewidywalnego działania, wysokiej wydajności oraz architektury odpornej na rozrost i długoterminowy rozwój.  

### DbM Framework to:  
**Ultra-fast core** - Zoptymalizowane routing i buforowanie żądań  
**Zgodność z PSR (1, 4, 11, 12)** - kod gotowy na standardy branżowe  
**REST API Routing** - lekki, czytelny, błyskawiczny  
**Smart DI Container** - ręczne lub półautomatyczne wstrzykiwanie zależności  
**Composer & Autoload** - gotowy do użycia w dowolnym projekcie  
**Ultra Fast View Engine 2.0** - prędkość zbliżona do natywnego PHP  
**DbM CMS** - system zarządzania treścią oparty na frameworku, gotowa autentykacja i panel administracyjny  

DbM to framework, który nie walczy z programistą - **pozwala mu pracować tak, jak lubi**.

## Struktura Frameworka

- `application/` - rdzeń frameworka: klasy, interfejsy, biblioteki (+ Routing, DI, API)  
- `bin` - pliki wykonywalne: interfejs konsolowy (CLI) oraz worker (punkt wejścia: bin/dbm)  
- `config/` - pliki configuracji (opcjonalne, np. php.ini, moduły CMS)  
- `frontend/` - frontend (opcjonalnie React.js lub Vue.js, Node.js, Webpack)  
- `libraries/` - zewnętrzne biblioteki (PSR, PHPMailer, Guzzle)  
- `public/` - pliki publiczne (root domeny)  
- `src/` - logika aplikacji: kontrolery, serwisy, modele, usługi  
- `templates/` - szablony widoków  
- `tests/` - testy jednostkowe  
- `translations/` - pliki tłumaczeń (opcjonalny)  
- `var/` - cache i logi (tworzone automatycznie, wymagane prawa do zapisu)  
- `vendor/` - biblioteki zainstalowane przez Composera (tworzone automatycznie)  
- `.env.example` - przykładowa konfiguracja środowiskowa  

## Dodatkowa struktura w przypadku instalacji CMS

- `_Documents` - dokumentacja, archiwum instalacji modułów  
- `data/` - dane i pliki (wymagane prawa do zapisu)  
- `modules/` - moduły systemu zarządzania treścią  

## Instalacja manualna

1. Skieruj domenę na katalog `public/`. W pliku `public/.htaccess` ustaw odpowiedni `RewriteBase`.
2. Jeśli korzystasz z localhosta, skopiuj plik `.htaccess` z katalogu `_Documents` do katalogu głównego i dostosuj `RewriteBase`.
3. Skonfiguruj plik `.env.example`, następnie zmień jego nazwę na `.env`.

W konfiguracji podstawowej uzupełnij sekcję **General settings**:

```env
APP_URL="http://localhost/"
APP_NAME="Application Name"
APP_EMAIL="email@domain.com"
```

Następnie skonfiguruj: Cache settings, Database settings, Mailer settings, API settings.

**Uwaga:** Po uruchomieniu aplikacji należy ustawić CACHE_ENABLED=true, aby włączyć buforowanie i przyspieszyć działanie strony.

## Autoloading

Instalacja manualna czyni framework niezależnym od innych narzędzi, wyposażonym w własny autoloading. Wykonanie polecenia `composer install` zautomatyzuje framework, utworzy autoloading Composera i zainstaluje wybrane pakiety, np. do wysyłania wiadomości e-mail oraz pakiety deweloperskie. Po wykonaniu komendy framework będzie współpracował z Composerem.  

## Instalacja przez Composera

Jeśli preferujesz instalację za pomocą Composera lub projekt wymaga dodatkowych pakietów:

```bash
git clone https://github.com/designbymalina/dbmframework.git
```

Jeśli chcesz korzystać z zewnętrznych bibliotek, możesz użyć Composera:

```bash
composer install
```

Instalacja przez Composera utworzy autoloading oraz pobierze wszystkie zależności.

**Uwaga:** Po zainstalowaniu aplikacji przez Composer niezbędne zależoności będą dostępne, wówczas katalog `libraries` można usunąć.

## Routing

Klasyczny routing definiujesz w pliku: `application/routes.php`.

Przykład:

```shell
$router->get('/path', [NameController::class, 'methodName'], 'route_name');
```

REST API Routing definiujesz w pliku: `application/api.php`.

Przykład:  

```shell
$router->get('/api/path', [NameApiController::class, 'methodName'], 'api_route_name');
```

## Dependency Injection

DbM Framework wykorzystuje **lekki kontener DI**, zgodny z **PSR-11**, który oferuje dwa tryby działania:

- **Ręczna konfiguracja (zalecana)**  

Wszystkie zależności rejestrujesz jawnie w pliku `application/services.php`:

```php
$container->set(Database::class, fn() => new Database($config));
$container->singleton(Request::class, fn() => new Request());
```

Ten tryb gwarantuje pełną kontrolę nad zależnościami i wydajnością.

- **Półautomatyczna konfiguracja (dostępna)**

W wielu przypadkach framework potrafi sam rozpoznać i wstrzyknąć zależność na podstawie typu parametru w konstruktorze kontrolera lub usługi:

```php
public function __construct(Mailer $mailer) { ... }
```

Jeśli klasa jest znana i zgodna z PSR-4 autoload, zostanie poprawnie wstrzyknięta. Mimo to **zaleca się jawne rejestrowanie usług** dla pełnej przewidywalności i stabilności.

Ten kompromis łączy **prostotę** ręcznego DI z **elastycznością** automatycznego wykrywania - bez kosztów pełnej refleksji, jak w ciężkich frameworkach.

## Silnik szablonów

Framework domyślnie korzysta z wbudowanego silnika szablonów. Można go dowolnie zastąpić przez np. Twig.  

Dlaczego warto używać DbM View Engine w porównaniu do najbardziej popularnych silników:

| Cechy | Twig | Blade | DbM View Engine |
|-------|------|-------|---------------------|
| Szybkość | średnia | dobra | najwyższa |
| PHP-friendly | ❌ | ⚠️ | ✅ programista wie co robi |
| Filtry | tak | tak | ✅ proste i rozszerzalne|
| Pluginy | trudne | brak | ✅ runtime callbacks |
| Dziedziczenie bloków | tak | tak | ✅ + append/prepend |
| Cache | tak | tak | ✅ klasy OPC |
| Sandbox | tak | brak | ✅ opcjonalny |
| Zależności | duże | średnie | ✅ niezależny |
| Waga | >400KB | ~200KB | ~50KB |

Na testach przy CACHE=TRUE osiągnięty został wynik zblizony do Native PHP.

=== TEMPLATE ENGINE BENCHMARK - benchmark.phtml ===

| MODE | AVG(ms) | MEDIAN | MIN | MAX | STD |
|------|---------|--------|-----|-----|-----|
| CACHE=FALSE | 1.31 | 1.29 | 1.17 | 1.67 | 0.09 |
| CACHE=TRUE | 0.17 | 0.16 | 0.16 | 0.31 | 0.02 |
| Native PHP | 0.15 | 0.14 | 0.14 | 0.18 | 0.01 |

**Wniosek**: DbM View Engine (cache=true) jest niemal tak szybki jak czyste PHP, co potwierdza jego wydajność.

Szablony znajdują się w katalogu `templates/`.

## Konsola poleceń

Lekki i szybki CLI do zadań CRON i DEV. Zapewnia prosty sposób uruchamiania zadań w tle lub zadań konserwacyjnych bezpośrednio z wiersza poleceń z lekką i niezależną implementacją. Polecenia konsoli są wykonywane za pośrednictwem pliku: `bin/dbm`.

Dostępne polecenia:  

```bash
php bin/dbm list
php bin/dbm command example (for ExampleCommand)
php bin/dbm worker example (for ExampleWorker)
```

## Informacja dodatkowa

W środowisku produkcyjnym kieruj domenę na katalog public/. W przypadku uruchamiania aplikacji w środowisku produkcyjnym (na serwerze zdalnym), **należy skierować domenę na katalog `/public/`**, ponieważ to właśnie on pełni rolę katalogu głównego (document root).

Upewnij się, że open_basedir nie blokuje dostępu do katalogów. Dodatkowo, w zależności od konfiguracji serwera, **może być konieczne wyłączenie ograniczenia `open_basedir`** w ustawieniach PHP. To zabezpieczenie, znane jako "separacja stron", może blokować dostęp do niektórych katalogów i plików spoza katalogu głównego domeny, co uniemożliwi otworzenie aplikacji w domenie.

Po uruchomieniu aplikacji włącz cache (`CACHE_ENABLED=true`) co przyspiesza działanie strony.

Korzystając z **DBM CMS**, zadbaj o prawa zapisu w katalogach `data/`, `modules/`.

Jeśli korzystasz z lokalnego środowiska (localhost), skopiuj plik `.htaccess` z katalogu `_Documents/_Server/` do głównego folderu projektu. Następnie w obu plikach - w katalogu głównym oraz public/.htaccess - dostosuj dyrektywę RewriteBase do ścieżki uruchomieniowej aplikacji.

W przypadku serwera zdalnego, gdzie domena wskazuje bezpośrednio na katalog public/ i znajdujący się tam plik .htaccess, aplikacja nie wymaga dodatkowej konfiguracji.

**WAŻNE!** Prosimy o zachowanie stopki: "Created with <a href="https://dbm.org.pl/" title="DbM">DbM Framework</a>". Link powinien pozostać nienaruszony. Dziękujemy za wsparcie rozwoju projektu! Zachowując link w stopce pomagasz rozwijać darmowy framework open source, wspierasz jego rozwój i społeczność niezależnych twórców PHP.

Dokumentacja:

[Introduction](_Documents/_Docs/01-introduction.md)  
[Creating first Controller and Service](_Documents/_Docs/02-controller-and-service.md)  
[Application Programming Interface (api.php)](_Documents/_Docs/api.md)  
[Command console (console.php)](_Documents/_Docs/console.md)  
[Dependency Injection - DI (services.php)](_Documents/_Docs/dependency-injection.md)  
[Environment configuration (.env)](_Documents/_Docs/env.md)  
[Middleware (middleware.php)](_Documents/_Docs/middleware.md)  
[Routing (routes.php)](_Documents/_Docs/routing.md)  
[Request](_Documents/_Docs/request.md)  
[Response](_Documents/_Docs/response.md)  
[TemplateEngine](_Documents/_Docs/template-engine.md)  
[TemplateFeature](_Documents/_Docs/template-feature.md)  
[Templates](_Documents/_Docs/templates.md)  

--- DbM CMS ---  

[Quick Start](_Documents/_Docs/installer.md)  
