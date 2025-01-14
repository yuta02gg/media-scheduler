<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    // POST /api/login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['認証に失敗しました。'],
            ]);
        }

        $user = Auth::user();

        // Create a new personal access token
        $token = $user->createToken('access_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'user' => $user,
        ]);
    }

    // POST /api/logout
    public function logout(Request $request)
    {
        // Revoke the token that was used to authenticate the current request
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'ログアウトしました。'], 200);
    }
}
