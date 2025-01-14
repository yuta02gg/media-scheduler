<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    // POST /api/register
    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:users', // 'username' をユニークに設定
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed', // 'password_confirmation' を期待
        ]);

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Create a new personal access token
        $token = $user->createToken('access_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'user' => $user,
        ], 201);
    }
}
