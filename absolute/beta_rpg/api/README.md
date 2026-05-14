# Beta RPG API Conventions

This folder contains JSON endpoints for beta_rpg.

## Endpoint Pattern

- Use separate endpoint files per resource/action.
- Keep endpoint files thin; delegate business logic to services.
- Always include:
  - `/backend/http/api_bootstrap.php`
  - one or more domain services

## Response Envelope

Success:

```json
{
  "ok": true,
  "data": {},
  "meta": {}
}
```

Error:

```json
{
  "ok": false,
  "error": {
    "code": "error_code",
    "message": "Human readable message",
    "details": {}
  }
}
```

## Shared Helpers

Use `/backend/http/api.php` helpers:

- `api_require_method('GET'|'POST'|...)`
- `api_require_auth_user_id()`
- `api_query_int(name, default, min, max)`
- `api_success(data, meta, status)`
- `api_error(code, message, status, details)`
- `api_pagination_meta(page, perPage, total)`

## Auth and CSRF

- Read endpoints: require authenticated session when data is private.
- Write endpoints: require authenticated session and CSRF validation.

## Endpoint Template

```php
<?php
    declare(strict_types=1);

    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/http/api_bootstrap.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/api/services/example_service.php';

    api_require_method('GET');
    $userId = api_require_auth_user_id();

    try {
        $payload = ExampleService::fetchForUser($userId);
        api_success(['items' => $payload]);
    } catch (Throwable $e) {
        if (function_exists('HandleError')) {
            HandleError($e);
        }

        api_error('example_failed', 'Failed to fetch data.', 500);
    }
```
