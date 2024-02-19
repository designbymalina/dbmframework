# DbM Framework

DbM Framework PHP MVC Simple CMS, Version 2.1  
Application tested on: PHP 7.4  
All code copyright Design by Malina (DbM)  
Web: www.dbm.org.pl

This project is in the development phase.  
As soon as the project reaches a stable point. Appropriate notice will be given.

# Język polski (PL - Polish language)

DbM Framework PHP MVC Simple CMS umożliwia tworzenie prostych i pięknych stron internetowych typu wizytówka, landingpage itp. gdzie zawartość jest przechowywana w plikach tekstowych (nie potrzebujesz bazy danych), ale do edycji zawartości wskazana jest znajomość HTML i CSS. Framework umożliwia też tworzenie większych aplikacji wymagających bazy danych takich jak regularnie aktualizowany, profesjonalny blog oraz bardziej rozbudowanych systemów o charakterze indywidualnego projektu, gdzie wymagana jest znajomości programowania. Przykładowa aplikacja demonstruje opcję z Landingpage na plikach tekstowych oraz System CMS na bazie danych.

## Idea

Funkcjonalna wersja prostego i szybkiego frameworka opartego o wzorzec architektury Model-View-Controller (MVC), który oddziela aplikację na trzy główne grupy: modele, widoki i kontrolery co też oddziela warstwę programistyczną od produkcyjnej. Framework skupiony jest na naturalnym, czystym PHP co daje wręcz nieograniczone możliwości - uwarunkowane w bardziej złożonych, sztucznych frameworkach zaprojektowanych do tworzenia aplikacji internetowych.

## Warunki wstępne

Aby rozpocząć prace we frameworku, musisz mieć zainstalowane następujące komponenty: [PHP](http://php.net), [MySQL](https://www.mysql.com), [Apache](https://httpd.apache.org) lub skorzystać z pakietu serwera WWW dla PHP np.: [XAMPP](https://www.apachefriends.org/).

## Struktura

- application (classes, methods and code related to the application)
  - classes
  - interfaces
  - libraries
- config
- data
  - attachment
  - content
  - mailer
  - txt (requires writing permissions)
- public (public folders)
- src (classes, methods and code related to the application)
  - Config
  - Controller
  - Model
  - Service
- templates
- tests
- tools (to create for tools)
- translations
- var / log / mailer (created automatically, requires writing permissions)
- vendor (reserved for Composer)

## Instalacja i konfiguracja

Pobierz DbM Framework i przenieś zawartość na serwer:  
1. Przejdź do katalogu `/config/` i zmień nazwę pliku `config.php.dist` na `config.php`, następnie uzupełnij wymagane dane konfiguracji. Ustaw prawidłową ścieżkę katalogu głównego (adres domeny) w APP_PATH.
2. Na serwerze zdalnym w domenie - domenę należy skierować na adres katalogu `/public/` i ustawić prawidłową wartość argumentu RewriteBase. W katalogu /public/ w plku .htaccess ustawić RewriteBase / (katalog główny zostawić bez pliku .htaccess).  
3. Jeżeli instalujesz aplikacje na serwerze lokalnym (localhost) utwórz katalog np. dbmframework (katalog główny), następnie skopiuj plik .htaccess z katalogu _Documents do katalogu głównego i ustaw prawidłową wartość argumentu RewriteBase. W katalogu głównym dla pliku .htaccess ustaw RewriteBase /dbmframework/, w katalogu /public/ w pliku .htaccess ustaw RewriteBase /dbmframework/public/.  

Aplikacja posiada utworzony mechanizm rejestracji i logowania. Rejestracja wymaga użycia biblioteki PHPMailer, aby załadować bibliotekę należy użyć narzędzia zarządzania pakietami Composer, ewentualnie można skopiować zawartość katalogu _Documents/Composer/ do katalogu głównego (dla prawidłowego działania biblioteka powinna być załadowana za pomocą Composera). Katalog `/vendor/` jest zastrzeżony dla menedżera zależności Composer.  

### Composer

Możesz użyć Composera do załadowania wybranych pakietów, ich aktualizacji itp. (patrz do composer.json).  
Aby korzystać z Composera wykonaj polecenie, które utworzy "autoloading" oraz pobierze i zainstaluje pakiety w najnowszej dostępnej wersji:

```shell
$ composer install
```

### Data FlatFile .txt (dane w plikach tekstowych)

- `data/content/` dane w plikach tekstowych, nadaj plikom prawa do zapisu
- `data/mailer/` szablon, pliki do wysyłania wiadomości e-mail

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

Używaj domyślnego lub dowolnego silnika szablonów. Domyślne funkcje dla szablonów znajdują się w pliku template.php, natomiast render() do wyświetlania widoków jest w pliku BaseController.php. Render możesz zmienić na procujący z silnikiem szablonów np. Twig Przykład zastosowania w /_Documents/Script/BaseController_for_Twig.php.

## Biblioteki

Wykorzystano następujące pakiety:

* [jQuery](https://jquery.com) - JavaScript Library.
* [Bootstrap](https://getbootstrap.com) - The most popular HTML, CSS, and JS library in the world.

Mailer for registration module (optional):

* [PHPMailer](https://github.com/PHPMailer/PHPMailer) - The classic email sending library for PHP.

UWAGA! Wolna licencja ma tylko jedno ograniczenie. Jeśli chcesz skorzystać z frameworka nie masz prawa usuwać linku do strony z nazwą autora ze stopki skryptu. Usuwając nazwę, używasz oprogramowania nielegalnie. Autor zapewnia wsparcie dla aplikacji, nie ponosi odpowiedzialności za jakiekolwiek szkody powstałe na skutek korzystania z oprogramowania. Jeśli masz pomysł na rozwój projektu napisz do mnie, porozmawiajmy...
