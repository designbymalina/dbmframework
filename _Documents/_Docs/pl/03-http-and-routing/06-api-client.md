# DBM API Client Documentation

## Overview

This package provides a flexible HTTP API client with support for:  

- Native cURL-based client (no dependencies)
- Guzzle-based client (recommended with Composer)
- Automatic driver selection

---

## Environment Configuration (`.env`)

```env
### API / JWT CONFIG ###
# Enable or disable API globally
API_ENABLED=false
# HTTP client driver
# Possible values: "auto" | "native" | "guzzle"
API_CLIENT_DRIVER=auto
# JWT secret key used for token generation
API_JWT_SECRET=your-secret-key
# Token lifetime in seconds (e.g. 3600 = 1 hour)
API_JWT_EXPIRATION=3600
```

## ApiFactory

Factory responsible for creating the appropriate API client.

Usage

```php
use Dbm\Api\ApiFactory;

$client = ApiFactory::create('https://api.example.com', $token);
```

Driver Selection Logic  

| Driver | Description |
|--------|-------------|
| auto | Uses Guzzle if available, otherwise falls back to native client |
| guzzle | Forces Guzzle HTTP client |
| native | Uses built-in cURL client |

Exception  

Throws: 'InvalidArgumentException'

If an invalid driver is provided.

## ApiClient (Native cURL Client)

Lightweight HTTP client using PHP cURL.  

Features  

- No external dependencies
- JSON request support
- Bearer token authentication
- Compatible with ApiGuzzleClient

Constructor

```php
public function __construct(
    string $baseUrl,
    ?string $token = null,
    array $defaultHeaders = [...]
)
```

Methods

request()

```php
request(string $method, string $endpoint, array $options = []): ApiResponse
```

Options:

- headers - additional headers
- json - array (auto JSON encoded)
- body - raw body
- timeout - request timeout (default: 30s)

```php
get(string $endpoint, array $query = []): ApiResponse

post(string $endpoint, array $data = []): ApiResponse

put(string $endpoint, array $data = []): ApiResponse

delete(string $endpoint, array $data = []): ApiResponse
```

Errors

Throws: `RuntimeException`

When cURL request fails.

## ApiGuzzleClient (Guzzle-based Client)

Advanced HTTP client using Guzzle.

Features

- Requires Composer
- Built-in logging
- Better error handling
- Performance metrics (request duration)

Constructor

```php
public function __construct(string $baseUrl, ?string $jwtToken = null)
```

Methods

Same interface as ApiClient:

- request()
- get()
- post()
- put()
- delete()

Logging

Each request is logged:

```bash
API Request {method} {endpoint} => {status} in {time} ms
```

Error Handling

Throws: `ApiException`

Cases:

- Guzzle request failure
- Unexpected runtime errors

## Authentication

Both clients support Bearer token authentication:

```bash
Authorization: Bearer {token}
```

Token is passed in constructor:

```php
$client = ApiFactory::create($baseUrl, $token);
```

## ApiResponse

Both clients return a unified response object: `ApiResponse`

Typical structure:

- HTTP status code
- response body
- headers

Example Usage

Basic Example

```php
$client = ApiFactory::create('https://api.example.com', 'your-jwt-token');

$response = $client->get('/users');

$data = json_decode($response->getBody(), true);
```

POST Example

```php
$response = $client->post('/orders', [
    'product_id' => 1,
    'quantity' => 2,
]);
```

Custom Headers

```php
$response = $client->request('GET', '/secure', [
    'headers' => [
        'X-Custom-Header: value'
    ]
]);
```

## Notes

- Use guzzle driver for production (better performance & logging)
- Use native driver for lightweight environments (no Composer)
- auto is recommended for most cases

## Requirements

### Native Client
PHP with cURL extension

### Guzzle Client
Composer
guzzlehttp/guzzle

## Summary

This API client system provides:

- Flexible driver selection
- Unified interface
- JWT authentication support
- Production-ready logging (Guzzle)
- Zero-dependency fallback (cURL)
