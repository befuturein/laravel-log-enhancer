# Laravel Log Enhancer

[![Latest Version on Packagist](https://img.shields.io/packagist/v/befuturein/laravel-log-enhancer.svg?style=flat-square)](https://packagist.org/packages/befuturein/laravel-log-enhancer)
[![Total Downloads](https://img.shields.io/packagist/dt/befuturein/laravel-log-enhancer.svg?style=flat-square)](https://packagist.org/packages/befuturein/laravel-log-enhancer)
[![GitHub Tests](https://github.com/befuturein/laravel-log-enhancer/actions/workflows/tests.yml/badge.svg)](https://github.com/befuturein/laravel-log-enhancer/actions/workflows/tests.yml)
[![License](https://img.shields.io/github/license/befuturein/laravel-log-enhancer.svg?style=flat-square)](LICENSE)

A lightweight, framework-friendly Laravel package that enriches your logs with structured context such as request data, authenticated user information, correlation IDs, and application metadata.

This package is designed to be:
- **Non-intrusive** – opt-in via configuration and logging taps.
- **Framework-friendly** – built for Laravel 10+ and 11.
- **Production-ready** – focuses on predictable, structured context and safe redaction.

## Features

- Adds a correlation ID to every HTTP request (header or generated).
- Provides a `ContextResolver` that builds a consistent context array.
- Optional Laravel logging tap (`ContextTap`) to push context into Monolog records.
- Simple `LogEnhancer` facade helper for manually merging context into log calls.
- Configurable redaction for sensitive keys (e.g. `password`, `token`).
- Minimal dependencies, focused only on request and authentication context.

## Installation

Install the package via Composer:

```bash
composer require befuturein/laravel-log-enhancer
```

> The package uses Laravel's automatic package discovery. No manual registration is required.

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --provider="BeFuture\LogEnhancer\LogEnhancerServiceProvider" --tag="log-enhancer-config"
```

This will create a `config/log-enhancer.php` file. The default options look like this (simplified):

```php
return [
    'enabled' => true,
    'correlation' => [
        'header' => 'X-Correlation-Id',
    ],
    'context' => [
        'include_request' => true,
        'include_user' => true,
        'include_app' => true,
    ],
    'redact' => [
        'keys' => ['password', 'password_confirmation', 'token'],
        'mask' => '***',
    ],
];
```

Adjust these options according to your security and observability requirements.

## HTTP Middleware

The package ships with a middleware that ensures each request has a correlation ID.

Register the middleware in your HTTP kernel (or route group):

```php
use BeFuture\LogEnhancer\Middleware\InjectLogContext;

protected $middleware = [
    // ...
    InjectLogContext::class,
];
```

Or in a specific group:

```php
protected $middlewareGroups = [
    'web' => [
        // ...
        \BeFuture\LogEnhancer\Middleware\InjectLogContext::class,
    ],
];
```

## Logging Tap (Automatic Context Injection)

To automatically inject context into a specific log channel, register the tap class in your `config/logging.php`:

```php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single'],
        'tap' => [
            \BeFuture\LogEnhancer\Logging\ContextTap::class,
        ],
    ],
],
```

The `ContextTap` will push a Monolog processor that merges the resolved context into the `extra` payload of each log record.

## Manual Usage via Facade

You can also manually merge context into your logs using the `LogEnhancer` facade:

```php
use Illuminate\Support\Facades\Log;
use BeFuture\LogEnhancer\Facades\LogEnhancer;

Log::info('User updated profile', LogEnhancer::context([
    'custom_note' => 'profile_update',
]));
```

The `context()` method will merge:

- Request metadata (method, path, IP, user agent, correlation ID).
- Authenticated user data (ID, type, and optionally email/name if available).
- Application metadata (environment, app name).

Any keys configured in the `redact.keys` setting will be masked.

## Testing

The package includes a basic test suite powered by [orchestra/testbench](https://github.com/orchestral/testbench).

Run tests with:

```bash
composer test
```

or directly via PHPUnit:

```bash
./vendor/bin/phpunit
```

## Coding Style

This repository ships with a [Laravel Pint](https://github.com/laravel/pint) configuration:

```bash
./vendor/bin/pint
```

## CI / GitHub Actions

A sample GitHub Actions workflow is provided under `.github/workflows/tests.yml`. It runs:

- Installation with Composer.
- Static analysis via `php -l` (syntax check).
- PHPUnit test suite.
- Laravel Pint for code style.

You can adjust or extend this workflow based on your project needs.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).
