<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * CSRF チェックを除外したいルートを指定
     */
    protected $except = [
        'api/*',
        'sanctum/csrf-cookie',
    ];
}
