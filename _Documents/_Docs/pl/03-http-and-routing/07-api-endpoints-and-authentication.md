# DBM API – Endpoints & Authentication

## Base URL

```
https://api.example.com/
```

---

# Authentication (JWT)

API uses Bearer Token authentication.

## Header

```
Authorization: Bearer {token}
```

---

## JWT Configuration (`.env`)

```env
API_JWT_SECRET=your-secret-key
API_JWT_EXPIRATION=3600
```

---

## JWT Payload Structure

Example payload:

```json
{
  "sub": "user_id",
  "iat": 1710000000,
  "exp": 1710003600
}
```

| Field | Description          |
| ----- | -------------------- |
| `sub` | User identifier      |
| `iat` | Issued at timestamp  |
| `exp` | Expiration timestamp |

---

## JWT Generator (PHP)

Simple implementation without external libraries:

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

---

## JWT Validation (Example)

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

---

# REST API Endpoints

## Health Check

### GET `/health`

Check if API is working.

**Response:**

```json
{
  "status": "ok"
}
```

---

## Authentication

### POST `/auth/token`

Generate JWT token.

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

---

## Users

### GET `/users`

Get all users.

**Response:**

```json
[
  {
    "id": 1,
    "name": "John Doe"
  }
]
```

---

### GET `/users/{id}`

Get single user.

---

### POST `/users`

Create user.

**Request:**

```json
{
  "name": "John Doe",
  "email": "john@example.com"
}
```

---

### PUT `/users/{id}`

Update user.

---

### DELETE `/users/{id}`

Delete user.

---

## Orders

### GET `/orders`

List orders.

---

### POST `/orders`

Create order.

**Request:**

```json
{
  "product_id": 1,
  "quantity": 2
}
```

---

### GET `/orders/{id}`

Get order details.

---

### DELETE `/orders/{id}`

Delete order.

---

# Error Responses

## Standard Format

```json
{
  "error": true,
  "message": "Error description"
}
```

---

## Common Status Codes

| Code | Meaning      |
| ---- | ------------ |
| 200  | OK           |
| 201  | Created      |
| 400  | Bad Request  |
| 401  | Unauthorized |
| 403  | Forbidden    |
| 404  | Not Found    |
| 500  | Server Error |

---

# Example Usage

```php
$client = ApiFactory::create('https://api.example.com', generateJwt('123'));

$response = $client->get('/users');

$data = json_decode($response->getBody(), true);
```

---

# Notes

* Always validate JWT on protected endpoints
* Keep `API_JWT_SECRET` secure
* Use HTTPS in production
* Token expiration should be short (recommended: 1h)

---
