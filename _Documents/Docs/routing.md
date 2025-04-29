# Routing – `application/routes.php`

W DbM Framework routing sprowadza się do jednej rzeczy: **dodania trasy za pomocą `addRoute()`**.  
Reszta (obsługa URI, dispatch itp.) działa automatycznie.

---

## Dodanie nowej trasy

```php
$router->addRoute('/sciezka', [NazwaKontrolera::class, 'metoda'], 'nazwa_trasy');
