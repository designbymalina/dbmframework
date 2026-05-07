# DBM API – Endpointy i Autoryzacja

## Base URL `/api`

`https://example.com/api` lub `https://api.example.com/`  

---

# Autoryzacja (JWT)

API wykorzystuje autoryzację typu Bearer Token.

## Header

Authorization: Bearer {token}

---

## Konfiguracja JWT (`.env`)

```env
API_JWT_SECRET=your-secret-key
API_JWT_EXPIRATION=3600
```

## Struktura JWT (Payload)

Przykładowy payload:

```json
{
  "sub": "user_id",
  "iat": 1710000000,
  "exp": 1710003600
}
```

| Pole | Opis |
|------|------|
| sub | Identyfikator użytkownika |
| iat | Czas wygenerowania tokena |
| exp | Czas wygaśnięcia tokena |

## Generowanie JWT (PHP)

Prosta implementacja bez zewnętrznych bibliotek:

```php
function base64UrlEncode(string $data): string
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function generateJwt(string $userId): string
{
    $secret = getenv('API_JWT_SECRET');
    $expiration = (int) getenv('API_JWT_EXPIRATION') ?: 3600;

    $header = [
        'alg' => 'HS256',
        'typ' => 'JWT'
    ];

    $payload = [
        'sub' => $userId,
        'iat' => time(),
        'exp' => time() + $expiration
    ];

    $base64Header = base64UrlEncode(json_encode($header));
    $base64Payload = base64UrlEncode(json_encode($payload));

    $signature = hash_hmac(
        'sha256',
        $base64Header . "." . $base64Payload,
        $secret,
        true
    );

    $base64Signature = base64UrlEncode($signature);

    return $base64Header . '.' . $base64Payload . '.' . $base64Signature;
}
```

## Walidacja JWT (Przykład)

```php
function validateJwt(string $jwt): bool
{
    $secret = getenv('API_JWT_SECRET');

    [$header, $payload, $signature] = explode('.', $jwt);

    $validSignature = base64UrlEncode(
        hash_hmac('sha256', "$header.$payload", $secret, true)
    );

    if (!hash_equals($validSignature, $signature)) {
        return false;
    }

    $payloadData = json_decode(base64_decode($payload), true);

    if ($payloadData['exp'] < time()) {
        return false;
    }

    return true;
}
```

## Endpointy REST API

### Health Check

#### GET `/health`

Sprawdza czy API działa.

**Response:**

```json
{
  "status": "ok"
}
```

### Autoryzacja

#### POST `/auth/token`

Generuje token JWT.

**Request:**

```json
{
  "user_id": "123"
}
```

**Response:**

```json
{
  "token": "jwt-token"
}
```

### Użytkownicy

#### GET `/users`

Pobiera listę użytkowników.

```json
[
  {
    "id": 1,
    "name": "John Doe"
  }
]
```

#### GET `/users/{id}`

Pobiera pojedynczego użytkownika.

#### POST `/users`

Tworzy użytkownika.

```json
{
  "name": "John Doe",
  "email": "john@example.com"
}
```

#### PUT `/users/{id}`

Aktualizuje użytkownika.

#### DELETE `/users/{id}`

Usuwa użytkownika.

### Zamówienia

#### GET `/orders`

Lista zamówień.

#### POST `/orders`

Tworzy zamówienie.

```json
{
  "product_id": 1,
  "quantity": 2
}
```

#### GET `/orders/{id}`

Szczegóły zamówienia.

#### DELETE `/orders/{id}`

Usuwa zamówienie.

## Błędy

### Standardowy format

```json
{
  "error": true,
  "message": "Opis błędu"
}
```

### Najczęstsze kody HTTP

| Kod | Znaczenie |
|-----|-----------|
| 200 | OK |
| 201 | Utworzono |
| 400 | Błędne żądanie |
| 401 | Brak autoryzacji |
| 403 | Zabronione |
| 404 | Nie znaleziono |
| 500 | Błąd serwera |

## Przykład użycia

```php
$client = ApiFactory::create('https://example.com/api', generateJwt('123'));

$response = $client->get('/users');

$data = json_decode($response->getBody(), true);
```

## Uwagi

- Zawsze waliduj JWT na chronionych endpointach
- Przechowuj API_JWT_SECRET w bezpiecznym miejscu
- Używaj HTTPS w środowisku produkcyjnym
- Zalecany krótki czas życia tokena (np. 1 godzina)
