<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class IsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        Log::debug('IsAdmin middleware invoked', [
            'user_id'   => Auth::id(),
            'is_logged' => Auth::check(),
            'is_admin'  => Auth::check() ? Auth::user()->is_admin : null,
            'route'     => $request->path(),
        ]);

        if (Auth::check() && Auth::user()->is_admin) {
            return $next($request);
        }

        Log::warning('IsAdmin middleware blocked a request', [
            'user_id' => Auth::id(),
            'message' => 'Forbidden. Admins only.'
        ]);

        return response()->json(['message' => 'Forbidden. Admins only.'], 403);
    }
}
