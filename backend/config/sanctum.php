<?php

use Laravel\Sanctum\Sanctum;

return [

    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'localhost:8080')),

    'guard' => ['web'],

    'expiration' => null,

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    'middleware' => [
        'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        'verify_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
        'ensure_fronted_requests_are_stateful' => Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    ],

];