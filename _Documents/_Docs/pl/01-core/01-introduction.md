# Wprowadzenie

**DBM Framework** to modułowy monolit zaprojektowany z myślą o budowie wydajnych i łatwych w utrzymaniu aplikacji PHP. Zapewnia pełną kontrolę nad architekturą, pozwalając na tworzenie systemów o długim cyklu życia.  

W przeciwieństwie do poprzednich wersji, opartych na klasycznym monolicie, wersja 5 wprowadza **architekturę modułową**. Pozwala ona na strukturę aplikacji złożoną z niezależnych, odizolowanych modułów, które wciąż są wdrażane jako spójny system.  

Rozwiązanie to łączy prostotę i wydajność **monolitu** z elastycznością, skalowalnością i wyraźnym podziałem odpowiedzialności (Separation of Concerns) charakterystycznym dla **systemów modułowych**.

## Modułowy monolit w praktyce

W DBM Framework architektura nie jest tylko teorią, ale fundamentem struktury plików:

- **Logiczny podział**: Aplikacja jest podzielona na odizolowane moduły (katalog `modules/`), które współdzielą jedno środowisko wykonawcze.  
- **Granice odpowiedzialności**: Podziały są definiowane przez funkcjonalność biznesową, a nie przez infrastrukturę, co eliminuje zbędną złożoność mikrousług.
- **Fundament i wtyczki**: Silnik (katalog `application/`) zarządza logiką, podczas gdy moduły takie jak CMS Lite czy Admin są opcjonalnymi komponentami instalowanymi przez dedykowany Installer.
- **Pełna swoboda**: Framework pozwala na budowę aplikacji w folderze `src/` od zera lub korzystanie z gotowych, niezależnych modułów – bez gromadzenia długu architektonicznego.

## Kluczowa idea  

**DbM Framework to lekki silnik aplikacyjny,  
a CMS Lite jest opcjonalnym modułem zarządzania treścią.**  

To podejście można streścić jako:  

**Micro framework + opcjonalny CMS**  

Dla developera: pełna kontrola i wydajność  

Dla klienta: prosty panel do zarządzania treścią  

## Dlaczego warto wybrać DBM Framework?

- **Brak "magii"**: Jawna konfiguracja i przewidywalny przepływ danych sprawiają, że debugowanie i rozwój są błyskawiczne.  
- **Zero overheadu**: Ładujesz tylko te moduły, których aktualnie potrzebujesz. System pozostaje lekki niezależnie od skali.  
- **Architektura odporna na czas**: Izolacja modułów minimalizuje ryzyko powstawania splątanego kodu (spaghetti code).  
- **Elastyczność wdrożeń**: Możesz zacząć od prostej wizytówki i rozbudować ją do zaawansowanego systemu SaaS bez zmiany fundamentów.  

## Filozofia architektury

W przeciwieństwie do frameworków takich jak Symfony czy Laravel, DBM Framework:

- nie wymusza rozbudowanych abstrakcji ani przesadnie rozbudowanych warstw
- unika zbędnej magii i ukrytych zachowań
- preferuje jawną konfigurację i przewidywalny przebieg wykonywania
- utrzymuje strukturę aplikacji blisko bazowego środowiska wykonawczego HTTP i PHP

DBM Framework został zaprojektowany dla programistów, którzy chcą **zrozumieć i kontrolować cały cykl życia aplikacji**, od obsługi żądań po renderowanie odpowiedzi.

## Ekosystem CMS

Framework stanowi fundament dla **Platformy DBM**, w tym modułu **CMS Lite**. To rozwiązanie dla projektów wymagających zarządzania treścią bez bezpośredniej edycji plików.  

CMS Lite to szybkie, lekkie i bezpieczne rozwiązanie do tworzenia stron internetowych, gdzie
pliki, szablony i routing zapewniają pełną kontrolę nad systemem.

W przypadku projektów wymagających zarządzania treścią bez bezpośredniej edycji plików,
CMS Lite można rozszerzyć o CMS Lite + Admin, który dodaje:

- panel administracyjny oparty na przeglądarce
- bezpieczne uwierzytelnianie
- edycję treści bez ingerencji w kod
- zachowanie lekkiej architektury

CMS jest dostarczany jako moduł rozszerzający, co pozwala na jego instalację
w istniejących projektach bez konieczności przebudowy aplikacji.

## Kiedy wybrać DBM Framework?

To rozwiązanie idealnie sprawdza się w projektach, gdzie standardowy CMS (jak WordPress) jest zbyt ciężki i mało elastyczny, a pełny framework (jak Symfony/Laravel) wymaga zbyt dużej konfiguracji na starcie.  

1. **Aplikacje dedykowane (SaaS, systemy wewnętrzne)** - gdy potrzebujesz czystej architektury i pełnej kontroli nad logiką biznesową bez narzuconej struktury CMS-a.  
2. **Lekkie strony z panelem zarządzania** - gdy budujesz szybką wizytówkę lub portal, ale klient potrzebuje edytować tylko wybrane sekcje (dzięki CMS Lite).  
3. **Projekty o wysokiej wydajności** - modułowa konstrukcja pozwala ładować tylko to, co niezbędne, co minimalizuje narzut systemowy.  
4. **Elastyczna ścieżka rozwoju (Microservices-ready)** - modułowa konstrukcja sprawia, że aplikacja jest gotowa na przyszłość. Izolacja logiki pozwala na łatwą refaktoryzację lub wydzielenie konkretnych funkcjonalności do osobnych usług w miarę wzrostu projektu.  

## Podsumowanie

**DBM Framework** to most między prostymi skryptami a potężnymi, lecz skomplikowanymi frameworkami klasy enterprise. Wybierając to rozwiązanie, zyskujesz fundament, który nie ogranicza Twojej kreatywności, nie narzuca zbędnych zależności i pozwala aplikacji rosnąć w sposób uporządkowany.
To narzędzie stworzone przez programistów dla programistów - tam, gdzie liczy się **przewidywalność, szybkość i elegancja kodu**.
