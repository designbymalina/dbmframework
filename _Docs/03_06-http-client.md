# HTTP Client for communicating with the API

## Overview

DBM Framework provides a lightweight and extensible HTTP client abstraction for communicating with external services and REST APIs.

The framework itself contains only a generic HTTP contract and a default implementation based on the PHP cURL extension. It does **not** depend on any third-party HTTP libraries.

Applications built on top of DBM Framework (for example DBM Platform) may replace the default implementation with another HTTP client, such as Guzzle, by registering a different service in the dependency container.

---

## Architecture

The HTTP layer consists of the following components:

```
HttpClientInterface
        │
CurlHttpClient
        │
HttpResponse
```

Applications may provide their own implementation of `HttpClientInterface`.

---

## Components

### HttpClientInterface

The common contract used by all HTTP client implementations.

```php
namespace Dbm\Http\Contracts;

interface HttpClientInterface
{
    public function request(string $method, string $url, array $options = []): HttpResponseInterface;

    public function get(string $url, array $options = []): HttpResponseInterface;

    public function post(string $url, array $options = []): HttpResponseInterface;

    public function put(string $url, array $options = []): HttpResponseInterface;

    public function delete(string $url, array $options = []): HttpResponseInterface;
}
```

---

### CurlHttpClient

The default HTTP client included with DBM Framework.

Features:

- no external dependencies
- based on the PHP cURL extension
- JSON request support
- configurable headers
- configurable timeout
- PSR-3 logging support

---

### HttpResponseInterface

All requests return a common response object implementing `HttpResponseInterface`.

Typical information available:

- HTTP status code
- response body
- response headers

---

## Request Options

The client accepts an optional `$options` array.

Example:

```php
$options = [
    'headers' => [
        'Accept' => 'application/json',
        'Authorization' => 'Bearer your-token',
    ],
    'json' => [
        'name' => 'John',
    ],
    'timeout' => 15,
];
```

Supported options:

| Option | Description |
|--------|------|
| headers | Additional HTTP headers |
| json | Automatically encoded JSON body |
| timeout | Request timeout in seconds |
| auth | Basic Auth (`[login, password]`) authentication |
| verify | Enables or disables SSL certificate verification |
| follow_redirects | Automatically follow HTTP redirects |

---

## Basic Usage

```php
use Dbm\Http\Contracts\HttpClientInterface;

final class ExampleService
{
    public function __construct(
        private readonly HttpClientInterface $http
    ) {
    }

    public function users(): array
    {
        $response = $this->http->get(
            'https://api.example.com/users'
        );

        return json_decode($response->body(), true);
    }
}
```

---

## POST Request

```php
$response = $http->post(
    'https://api.example.com/orders',
    [
        'json' => [
            'product_id' => 10,
            'quantity' => 2,
        ],
    ]
);
```

## PUT Request

```php
$response = $http->put(
    'https://api.example.com/orders/15',
    [
        'json' => [
            'status' => 'completed',
        ],
    ]
);
```

## DELETE Request

```php
$response = $http->delete(
    'https://api.example.com/orders/15'
);
```

---

## Custom Headers

```php
$response = $http->get(
    'https://api.example.com/profile',
    [
        'headers' => [
            'Authorization' => 'Bearer token',
            'Accept' => 'application/json',
        ],
    ]
);
```

---

## Error Handling

The default implementation never throws HTTP exceptions for unsuccessful status codes.

Always verify the returned status code.

```php
$response = $http->get($url);

if ($response->statusCode() !== 200) {
    // handle error
}
```

Unexpected runtime errors (for example network failures) should be handled using `try/catch`.

```php
try {
    $response = $http->get($url);
} catch (\Throwable $e) {
    // log error
}
```

---

## Logging

`CurlHttpClient` supports optional PSR-3 logging.

Logging can be enabled or disabled via application configuration.

Typical log entries include:

- HTTP method
- URL
- HTTP response code
- request execution time
- transport errors

---

# Using Guzzle (DBM Platform)

DBM Framework intentionally has **no dependency** on Guzzle.

Applications may register their own implementation of `HttpClientInterface`.

For example, DBM Platform Admin version provides:

```
App\Infrastructure\Http\GuzzleHttpClient
```

which internally uses:

```
guzzlehttp/guzzle
```

The implementation is registered through the application's dependency container and transparently replaces the default `CurlHttpClient`.

For example:

```php
HttpClientProvider::register($container);
```

Because services depend only on `HttpClientInterface`, no application code needs to change.

Example:

```php
final class CurrencyRateProvider
{
    public function __construct(
        private readonly HttpClientInterface $http
    ) {
    }
}
```

The provider works identically regardless of whether the application uses:

- CurlHttpClient
- GuzzleHttpClient
- any custom implementation

---

## Design Philosophy

DBM Framework provides only the HTTP abstraction.

It does **not** include:

- Guzzle
- Symfony HTTP Client
- any third-party networking library

This keeps the framework lightweight, dependency-free and suitable for any type of application.

Applications remain free to choose the HTTP implementation that best fits their requirements.

### Implementation Selection (DBM Platform)

The application can select the HTTP client implementation using the environment variable:

```bash
HTTP_CLIENT_DRIVER=auto
```

### HTTP Logging

HTTP communication logging can be enabled via the following environment variable:

```bash
HTTP_CLIENT_LOG=true
```

When enabled, the client logs diagnostic information in accordance with PSR-3.

By default, HTTP logging is disabled.

---

## Requirements

### Default implementation

- PHP
- cURL extension

### Custom implementations

May require additional Composer packages depending on the selected HTTP client.

---

## Summary

DBM Framework provides:

- HTTP client abstraction
- dependency-free default implementation
- unified response interface
- PSR-3 logging support
- easy replacement through Dependency Injection
- compatibility with custom HTTP clients such as Guzzle

---
