<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        // バリデーション
        $validatedData = $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // ユーザーの作成
        $user = User::create([
            'username' => $validatedData['username'],
            'email' => $validatedData['email'],
            // パスワードをハッシュ化して保存
            'password' => Hash::make($validatedData['password']),
        ]);

        // トークンの生成（API 認証の場合）
        $token = $user->createToken('authToken')->plainTextToken;

        // レスポンスを返す
        return response()->json([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }
}
