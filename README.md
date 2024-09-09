# DbM Framework

DbM Framework PHP MVC Simple CMS, Version 2  
Aplikacja stworzona w PHP 7.4, wersja v2.3.7 przetestowana w PHP 8.3  
Wszystkie prawa autorskie zastrzeżone przez Design by Malina (DbM)  
Strona WWW: [www.dbm.org.pl](http://www.dbm.org.pl)  

## DbM Framework Simple CMS

DbM Framework PHP MVC Simple CMS umożliwia tworzenie prostych i estetycznych stron internetowych typu wizytówka, landing page itp., w których zawartość jest przechowywana w plikach tekstowych, co eliminuje potrzebę korzystania z bazy danych. Mimo że framework jest prosty w obsłudze, znajomość HTML i CSS jest zalecana do edytowania zawartości stron.

Framework oferuje również bardziej zaawansowane opcje, takie jak tworzenie aplikacji wymagających bazy danych, co umożliwia zbudowanie profesjonalnych blogów lub bardziej złożonych systemów, dostosowanych do indywidualnych potrzeb. Przykładowa aplikacja demonstruje zarówno prostą stronę na plikach tekstowych, jak i bardziej zaawansowany system CMS oparty na bazie danych. Dzięki temu DbM Framework może być używany do różnych projektów, od prostych stron po bardziej rozbudowane aplikacje.

## Idea

DbM Framework to lekki, szybki i funkcjonalny framework oparty na wzorcu architektury Model-View-Controller (MVC). MVC oddziela logikę aplikacji (modele) od prezentacji (widoki) oraz obsługi żądań (kontrolery), co znacząco ułatwia rozwój i utrzymanie kodu. Framework koncentruje się na czystym PHP, co daje użytkownikom elastyczność i niemal nieograniczone możliwości dostosowywania aplikacji. W przeciwieństwie do bardziej złożonych frameworków, DbM pozostaje prosty i intuicyjny, co pozwala na szybkie wdrażanie rozwiązań bez zbędnych komplikacji.

## Warunki wstępne

Aby rozpocząć prace we frameworku, musisz mieć zainstalowane następujące komponenty: [PHP](http://php.net), [MySQL](https://www.mysql.com), [Apache](https://httpd.apache.org) lub skorzystać z kontenerów [Docker](https://www.docker.com/). W przypadku Docker, możesz użyć gotowego obrazu dla PHP, MySQL oraz Apache, aby skonfigurować środowisko.

## Struktura

- application (rdzeń aplikacji i serce frameworka)
  - classes
  - interfaces
  - libraries
- data (pliki i dane, katalogi wymagają uprawnień do zapisu)
  - attachment
  - content
  - mailer
  - txt
- public (publiczne foldery i pliki)
- src (logika aplikacji: kontrolery, modele, serwisy i inne)
  - Config
  - Controller
  - Model
  - Service
- templates (widok: szablony)
- tests (testy)
- tools (narzędzia)
- translations (tłumaczenia)
- var / log / mailer and var / cache (tworzone automatycznie, wymagają uprawnień do zapisu)
- vendor (zarezerwowany dla Composera)

## Instalacja manualna i konfiguracja

Pobierz aplikacje DbM Framework, rozpakuj plik i przenieś zawartość na serwer:  
1. Na serwerze zdalnym w domenie - domenę należy skierować na adres katalogu `/public/` i ustawić prawidłową wartość argumentu RewriteBase. W katalogu /public/ w pliku .htaccess ustawić RewriteBase / (katalog główny zostawić bez pliku .htaccess). W zależności od serwera do uruchomienia aplikacji może być wymagane wyłączenie ograniczenia listy plików zawartych w strukturze katalogu open_basedir w konfiguracji PHP.
2. Jeżeli instalujesz aplikacje na serwerze lokalnym (localhost) lub w katalogu domeny utwórz katalog np. `dbmframework` (katalog główny), następnie skopiuj plik .htaccess z katalogu _Documents do katalogu głównego i ustaw prawidłową wartość argumentu RewriteBase. W katalogu głównym dla pliku .htaccess ustaw RewriteBase /dbmframework/, w katalogu /public/ w pliku .htaccess ustaw RewriteBase /dbmframework/public/.
3. Przejdź do katalogu głównego i skonfiguruj plik konfiguracyjny `.env.dist`. Ustaw prawidłową ścieżkę katalogu głównego (adres domeny, katalog) w APP_URL. Ustaw działanie aplikacji na środowisko produkcyjne (production) i uzupełnij pozostałe parametry konfiguracji. Konfiguracje 'Mailer settings' można pominąć podając tylko parametr MAIL_SMTP=false. Ważne, aby podać poprawny adres e-mail w APP_EMAIL. Po zakończeniu konfiguracji aplikacji zmień nazwę (rozszerzenie) pliku `.env.dist` na `.env`.
4. Zaimportuj bazę danych na serwerze i skonfiguruj ją zgodnie z wymaganiami.

Aplikacja posiada mechanizm wysyłania wiadomości e-mail. Mechanizm wymaga użycia biblioteki PHPMailer, aby załadować bibliotekę należy użyć narzędzia zarządzania pakietami Composer, ewentualnie można skopiować zawartość katalogu _Documents/Composer/ oraz plik composer.lock do katalogu głównego (biblioteka powinna być załadowana za pomocą Composera, kopiowanie katalogu vendor nie jest wskazane). Katalog `/vendor/` jest zastrzeżony dla menedżera zależności Composer.  

Zaawansowane opcje konfiguracji znajdują się w pliku ConstantConfig.php. Jeśli nie ma potrzeby ich zmiany pozostaw domyślne ustawienia.

### Instalacja z pomocą Composera

Możesz użyć narzędzia Composer do załadowania wybranych pakietów, ich aktualizacji itp. (sprawdź plik `composer.json`).  
Aby skorzystać z Composera przejdź do katalogu, w którym chcesz zainstalować projekt i wykonaj poniższe kroki:

1. **Sklonuj repozytorium:**

```bash
git clone https://github.com/artimman/dbmframework.git
```

2. **Przejdź do katalogu z projektem (możesz zmienić nazwę katalogu):**

```bash
cd dbmframework
```

3. **Uruchom instalację zależności:**

```bash
composer install
```

Te kroki utworzą autoloading oraz pobiorą i zainstalują wszystkie wymagane pakiety w najnowszej dostępnej wersji. 

### Data FlatFile .txt (dane w plikach tekstowych)

- `data/content/` dane w plikach tekstowych, nadaj plikom prawa do zapisu
- `data/mailer/` szablony do wysyłania wiadomości e-mail

### Database (baza danych nie jest wymagana do uruchomienia aplikacji)

- _Documents/Database/dbm_cms.sql

Jeśli chcesz użyć bazy danych importuj ją na serwer i skonfiguruj połączenie w pliku konfiguracyjnym.

## Mechanizm routingu

Klasa znajdująca się w pliku /application/classes/Router.php jest używana przez framework do stworzenia obiektu rutera, obiekt pozwala zdefiniować routing oraz inicjuje wykonanie akcji za pomocą metody. Dla podstawowego wzorca routingu użytkownik nie wykorzystuje bezpośrednio klasy Router, używa metody klasy addRoute() w celu dodania kolejnych podstron projektu.  

Aby dodać adres routingu przejdź do pliku `/aplicattion/routes.php` i użyj metody router'a:

```shell
$router->addRoute('address', [Controller, 'method']);
```

Metoda pozwala dodać ścieżkę routingu w uproszczony sposób, przy użyciu argumentów: nazwa-strony (adres), kontroler i metoda. W adresie nazwa może zawierać parametry: {#} - stały oraz {id} zmienny, przykładowo dla linku page-title.key.5.html adres to `/{#}.key.{id}.html`. Jeżeli zachodzi potrzeba rozbudowania routingu o kolejne wzorce można dodać takie w klasie Router -> buildRouteUri() -> create pattern.

## Silnik szablonów

DbM Framework domyślnie korzysta z prostego silnika szablonów, który można zamienić na bardziej rozbudowany, np. Twig. Przykład użycia silnika Twig znajduje się w /Documents/Script/BaseController_for_Twig.php. Patrz do plików w folderze `templates`, na przykładzie dowiesz się jak używać zalecanego domyślnego systemu szablonów.

## Biblioteki

Wykorzystano następujące pakiety:

* [jQuery](https://jquery.com) - Biblioteka JavaScript.
* [Bootstrap](https://getbootstrap.com) - Popularna biblioteka HTML, CSS, i JS.

Mailer for registration module (optional):

* [PHPMailer](https://github.com/PHPMailer/PHPMailer) - Biblioteka do wysyłania e-maili.

UWAGA! Wolna licencja wymaga umieszczenia linku do strony autora w widocznym miejscu, np. w stopce strony. Usunięcie linku jest naruszeniem licencji.
