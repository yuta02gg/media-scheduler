<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo($request)
    {
        // JSONリクエスト以外ならリダイレクトでも可
        if (!$request->expectsJson()) {
            // return route('login');
            abort(401, 'Unauthorized'); 
        }

        abort(401, 'Unauthorized');
    }
}
