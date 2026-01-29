# DBM Framework - Ultraszybki framework PHP dla wydajnych aplikacji internetowych

**Fast. Flexible. PSR-Compatible.**  
**Modern PHP MVC/MVP Framework + CMS Engine with built-in API**

[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D8.1-blue)](http://php.net)
[![PSR](https://img.shields.io/badge/PSR-1%2C%204%2C%2011%2C%2012-green)](https://www.php-fig.org/)
[![Build](https://img.shields.io/badge/build-passing-success)]()
[![Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen)]()
[![Composer](https://img.shields.io/badge/composer-ready-orange)](https://getcomposer.org/)
[![Speed](https://img.shields.io/badge/performance-ultra%20fast-red)]()
[![License](https://img.shields.io/badge/license-DbM-orange)](https://dbm.org.pl)

DBM Framework PHP MVC MVP + DBM CMS, Version 4  
Wszystkie prawa autorskie zastrzeżone przez Design by Malina (DbM)  
Strona WWW: [www.dbm.org.pl](http://www.dbm.org.pl)  

## O frameworku

DbM Framework to jedno z najszybszych rozwiązań PHP opartych na wzorcu MVC i MVP, łączące lekkość, elastyczność i wydajność z nowoczesnymi możliwościami rozbudowy. Pozwala na łatwe dodawanie funkcji bez ingerencji w rdzeń, a przemyślana architektura zapewnia stabilność i bezpieczeństwo. To idealny wybór dla programistów ceniących pełną kontrolę nad kodem i swobodę w tworzeniu zaawansowanych aplikacji webowych.  

DbM CMS to oparte na frameworku gotowe rozwiązanie dla tych, którzy chcą szybko uruchomić stronę lub aplikację bez konieczności kodowania. Obsługuje zarówno proste strony jak i rozbudowane projekty oparte na bazie danych. Jeśli nie masz czasu na tworzenie własnych modułów, możesz skorzystać z gotowych narzędzi do zarządzania treścią, SEO i strukturą witryny. Skuteczne rozwiązanie, które przyspiesza rozwój projektów bez rezygnacji z elastyczności frameworka.  

### DbM Framework to:  
⚡ **Ultra-fast core** - Zoptymalizowane routing i buforowanie żądań
⚙️ **Zgodność z PSR (1, 4, 11, 12)** - kod gotowy na standardy branżowe  
🔁 **REST API Routing** - lekki, czytelny, błyskawiczny  
🧠 **Smart DI Container** - ręczne lub półautomatyczne wstrzykiwanie zależności  
🧱 **Composer & Autoload** - gotowy do użycia w dowolnym projekcie  
🚀 **Ultra Fast View Engine 2.0** - prędkość zbliżona do natywnego PHP  
🧩 **DbM CMS** - system zarządzania treścią oparty na frameworku, gotowa autentykacja i panel administracyjny

DbM to framework, który nie walczy z programistą - **pozwala mu pracować tak, jak lubi**.

## Struktura Frameworka

- `application/` – rdzeń frameworka: klasy, interfejsy, biblioteki (+ Routing, DI, API)
- `config/` – pliki configuracji (opcjonalne, np. php.ini, moduły CMS)
- `frontend/` - frontend (opcjonalnie React.js lub Vue.js, Node.js, Webpack)
- `libraries/` – zewnętrzne biblioteki (PSR, PHPMailer, Guzzle)
- `public/` – pliki publiczne (root domeny)
- `src/` – logika aplikacji: kontrolery, serwisy, modele, usługi
- `templates/` – szablony widoków
- `tests/` – testy jednostkowe
- `translations/` – pliki tłumaczeń (opcjonalny)
- `var/` – cache i logi (tworzone automatycznie, wymagane prawa do zapisu)
- `vendor/` – biblioteki zainstalowane przez Composera (tworzone automatycznie)

## Dodatkowa struktura w przypadku instalacji CMS

- `data/` – dane i pliki (wymagane prawa do zapisu)
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
| Szybkość | średnia | dobra | 🚀 najwyższa |
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

Lekki i szybki CLI do zadań CRON i DEV. Zapewnia prosty sposób uruchamiania zadań w tle lub zadań konserwacyjnych bezpośrednio z wiersza poleceń z lekką i niezależną implementacją. Polecenia konsoli są wykonywane za pośrednictwem pliku: `application/console.php`.

## Informacja dodatkowa

W środowisku produkcyjnym kieruj domenę na katalog public/. W przypadku uruchamiania aplikacji w środowisku produkcyjnym (na serwerze zdalnym), **należy skierować domenę na katalog `/public/`**, ponieważ to właśnie on pełni rolę katalogu głównego (document root).

Upewnij się, że open_basedir nie blokuje dostępu do katalogów. Dodatkowo, w zależności od konfiguracji serwera, **może być konieczne wyłączenie ograniczenia `open_basedir`** w ustawieniach PHP. To zabezpieczenie, znane jako "separacja stron", może blokować dostęp do niektórych katalogów i plików spoza katalogu głównego domeny, co uniemożliwi otworzenie aplikacji w domenie.

Po uruchomieniu aplikacji włącz cache (`CACHE_ENABLED=true`) co przyspiesza działanie strony.

Jeśli używasz CMS, zadbaj o prawa zapisu w data/, modules/.

**WAŻNE!** Prosimy o zachowanie stopki: "Created with <a href="https://dbm.org.pl/" title="DbM">DbM Framework</a>". Link powinien pozostać nienaruszony. Dziękujemy za wsparcie rozwoju projektu! Zachowując link w stopce pomagasz rozwijać darmowy framework open source, wspierasz jego rozwój i społeczność niezależnych twórców PHP.

Dokumentacja:

[Application Programming Interface (api.php)](_Documents/Docs/api.md)  
[Command console (console.php)](_Documents/Docs/console.md)  
[Dependency Injection - DI (services.php)](_Documents/Docs/dependency-injection.md)  
[Environment configuration (.env)](_Documents/Docs/env.md)  
[Middleware (middleware.php)](_Documents/Docs/middleware.md)  
[Routing (routes.php)](_Documents/Docs/routing.md)  
[Request](_Documents/Docs/request.md)  
[Response](_Documents/Docs/response.md)  
[TemplateEngine](_Documents/Docs/template-engine.md)  
[TemplateFeature](_Documents/Docs/template-feature.md)  
[Templates](_Documents/Docs/templates.md)  

--- DbM CMS ---  

[Quick Start](_Documents/Docs/installer.md)  
