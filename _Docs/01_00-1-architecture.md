# Architektura DBM Framework

## Cykl życia aplikacji

Request -> Routing -> Middleware -> Controller -> Response  

Framework przetwarza każde żądanie w sposób liniowy i przewidywalny - bez ukrytych etapów.

---

## Główne komponenty

- **Core (Kernel)** – zarządza cyklem życia aplikacji
- **Router** – mapuje żądania HTTP na kontrolery
- **Middleware Dispatcher** – obsługuje potok middleware
- **DI Container** – zarządza zależnościami

---

## Model architektoniczny

DBM Framework opiera się na podejściu:

- modularny monolit
- separacja odpowiedzialności (Separation of Concerns)
- jawna konfiguracja

Aplikacja składa się z niezależnych modułów, które są rozwijane osobno, ale wdrażane jako jeden system.

---

## Komponenty wbudowane

Framework dostarcza domyślne implementacje:

- routing HTTP
- middleware
- kontener DI
- system szablonów (DbM View Engine)
- warstwę dostępu do danych

Każdy komponent może zostać zastąpiony własnym rozwiązaniem.

---

## Czego framework nie narzuca

- struktury katalogów aplikacji
- konkretnego ORM
- konkretnego silnika szablonów
- warstwy CMS / platformy

Framework dostarcza narzędzia - nie narzuca finalnej architektury aplikacji.
