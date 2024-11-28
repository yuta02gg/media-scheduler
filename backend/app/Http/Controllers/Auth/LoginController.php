<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        // バリデーション
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // 認証試行
        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'ログイン情報が正しくありません。'], 401);
        }

        // トークンの生成
        $user = $request->user();
        $token = $user->createToken('authToken')->plainTextToken;

        // レスポンスを返す
        return response()->json([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function logout(Request $request)
    {
        // 現在のトークンを無効化
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'ログアウトしました。']);
    }
}
