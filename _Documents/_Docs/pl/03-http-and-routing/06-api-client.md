# Dokumentacja klienta API DBM

## Przegląd

Pakiet udostępnia elastycznego klienta HTTP API z obsługą:

* klienta natywnego opartego o cURL (bez zależności)
* klienta opartego o Guzzle (zalecany z Composerem)
* automatycznego wyboru drivera

---

## Konfiguracja środowiska (`.env`)

```env
### API / JWT CONFIG ###
# Globalne włączenie API
API_ENABLED=false
# Driver klienta HTTP
# Możliwe wartości: "auto" | "native" | "guzzle"
API_CLIENT_DRIVER=auto
# Sekretny klucz JWT
API_JWT_SECRET=your-secret-key
# Czas życia tokenu (sekundy, np. 3600 = 1 godzina)
API_JWT_EXPIRATION=3600
```

---

## ApiFactory

Fabryka odpowiedzialna za tworzenie klienta API.

### Użycie

```php
use Dbm\Api\ApiFactory;

$client = ApiFactory::create('https://api.example.com', $token);
```

### Logika wyboru drivera

| Driver | Opis                                                 |
| ------ | ---------------------------------------------------- |
| auto   | Używa Guzzle jeśli dostępny, w przeciwnym razie cURL |
| guzzle | Wymusza użycie Guzzle                                |
| native | Używa natywnego klienta cURL                         |

### Wyjątek

Rzuca: `InvalidArgumentException`

Gdy podano nieprawidłowy driver.

---

## ApiClient (natywny klient cURL)

Lekki klient HTTP oparty o PHP cURL.

### Funkcje

* brak zależności zewnętrznych
* obsługa JSON
* autoryzacja Bearer Token
* kompatybilność z ApiGuzzleClient

### Konstruktor

```php
public function __construct(
    string $baseUrl,
    ?string $token = null,
    array $defaultHeaders = [...]
)
```

---

### Metody

```php
request(string $method, string $endpoint, array $options = []): ApiResponse
```

#### Opcje

* `headers` – dodatkowe nagłówki
* `json` – dane (automatycznie konwertowane do JSON)
* `body` – surowe dane
* `timeout` – timeout (domyślnie: 30s)

```php
get(string $endpoint, array $query = []): ApiResponse
post(string $endpoint, array $data = []): ApiResponse
put(string $endpoint, array $data = []): ApiResponse
delete(string $endpoint, array $data = []): ApiResponse
```

---

### Błędy

Rzuca: `RuntimeException`

Gdy zapytanie cURL się nie powiedzie.

---

## ApiGuzzleClient (klient Guzzle)

Zaawansowany klient HTTP oparty o Guzzle.

### Funkcje

* wymaga Composer
* wbudowane logowanie
* lepsza obsługa błędów
* pomiar czasu zapytania

---

### Konstruktor

```php
public function __construct(string $baseUrl, ?string $jwtToken = null)
```

---

### Metody

Takie same jak w ApiClient:

* request()
* get()
* post()
* put()
* delete()

---

### Logowanie

Każde zapytanie jest logowane:

```bash
API Request {method} {endpoint} => {status} in {time} ms
```

---

### Obsługa błędów

Rzuca: `ApiException`

W przypadkach:

* błędu zapytania Guzzle
* nieoczekiwanych błędów runtime

---

## Autoryzacja

Oba klienty obsługują Bearer Token:

```bash
Authorization: Bearer {token}
```

Token przekazywany jest w konstruktorze:

```php
$client = ApiFactory::create($baseUrl, $token);
```

---

## ApiResponse

Oba klienty zwracają wspólny obiekt: `ApiResponse`

Zawiera:

* kod HTTP
* body odpowiedzi
* nagłówki

---

## Przykłady

### Podstawowy przykład

```php
$client = ApiFactory::create('https://api.example.com', 'your-jwt-token');

$response = $client->get('/users');

$data = json_decode($response->getBody(), true);
```

---

### POST

```php
$response = $client->post('/orders', [
    'product_id' => 1,
    'quantity' => 2,
]);
```

---

### Własne nagłówki

```php
$response = $client->request('GET', '/secure', [
    'headers' => [
        'X-Custom-Header: value'
    ]
]);
```

---

## Uwagi

* używaj `guzzle` w produkcji (wydajność + logowanie)
* używaj `native` w lekkich środowiskach
* `auto` jest zalecanym wyborem

---

## Wymagania

### Klient natywny

* PHP + rozszerzenie cURL

### Klient Guzzle

* Composer
* guzzlehttp/guzzle

---

## Podsumowanie

System klienta API zapewnia:

* elastyczny wybór drivera
* jednolity interfejs
* obsługę JWT
* logowanie produkcyjne (Guzzle)
* fallback bez zależności (cURL)

---
