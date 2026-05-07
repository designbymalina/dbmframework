# Ekosystem DBM

DBM to zestaw narzędzi do budowy i uruchamiania aplikacji webowych.

Składa się z dwóch głównych części:

---

## DBM Framework

Lekki silnik aplikacji (core), który dostarcza:

- cykl życia request -> response
- routing i middleware
- kontener Dependency Injection
- podstawowe komponenty infrastruktury

Framework nie jest kompletną aplikacją - stanowi bazę do budowy własnych systemów.

---

## DBM Platform

Gotowa aplikacja oparta na DBM Framework.

Stanowi warstwę aplikacyjną nad frameworkiem - pozwala rozpocząć projekt bez budowania wszystkiego od zera.

### Wersja podstawowa

Podstawowa wersja platformy (CMS Lite + Admin) rozszerza framework o:

- system uwierzytelniania (logowanie, rejestracja użytkowników)
- panel administracyjny (kiedy liczy się wygoda)
- zarządzanie modułami (instalacja / odinstalowywanie)
- system budowy stron oparty na plikach (bez wymaganej bazy danych)
- moduł użytkowników i podstawowe funkcje aplikacyjne

Platforma może działać jako:

- lekki CMS
- baza pod aplikację webową
- punkt startowy dla bardziej zaawansowanych systemów

### Rozszerzalność

System oparty jest o moduły - możliwa jest instalacja i rozwój kolejnych warstw funkcjonalnych: Core, Pro, e-commerce, payments i inne moduły aplikacyjne.

Każdy projekt może być rozwijany stopniowo, bez narzucania pełnego stacku na starcie.

---

## Relacja

DBM Platform wykorzystuje DBM Framework jako warstwę wykonawczą.

Framework może być używany niezależnie, bez platformy.

## Moduły

Zarówno framework, jak i platforma wspierają architekturę modułową.

Aplikacje budowane są jako zestaw niezależnych modułów, które mogą być rozwijane i utrzymywane oddzielnie.

---

## Kiedy używać czego

- użyj DBM Framework - gdy budujesz aplikację od zera
- użyj DBM Platform - gdy chcesz zacząć od gotowej bazy systemu
