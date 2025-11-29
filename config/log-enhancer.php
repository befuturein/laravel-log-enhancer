<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Global Enable Flag
    |--------------------------------------------------------------------------
    |
    | This flag controls whether the log enhancer should resolve and attach
    | contextual metadata. You can disable this quickly in production if
    | you suspect any performance or privacy impact.
    |
    */

    'enabled' => env('LOG_ENHANCER_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Correlation ID
    |--------------------------------------------------------------------------
    |
    | A correlation ID is a unique identifier that helps you track a single
    | request across multiple log entries and services. The middleware will
    | look for this header and, if missing, generate a UUID.
    |
    */

    'correlation' => [
        'header' => env('LOG_ENHANCER_HEADER', 'X-Correlation-Id'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Context Sources
    |--------------------------------------------------------------------------
    |
    | Configure which sources should be included in the resolved context.
    | Disabling a section will completely remove it from the payload.
    |
    */

    'context' => [
        'include_request' => true,
        'include_user' => true,
        'include_app' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Redaction
    |--------------------------------------------------------------------------
    |
    | Keys listed here will be redacted from request input and headers.
    | Redaction is strict, and uses case-sensitive matching on keys.
    |
    */

    'redact' => [
        'keys' => [
            'password',
            'password_confirmation',
            'current_password',
            'token',
            'auth_token',
            'access_token',
            'refresh_token',
            'secret',
        ],

        'mask' => '***',
    ],
];
