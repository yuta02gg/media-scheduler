<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    // ユーザー情報を取得
    public function show(Request $request)
    {
        return response()->json([
            'username' => $request->user()->username,
            'email' => $request->user()->email,
        ]);
    }

    // ユーザー情報を更新
    public function update(Request $request)
    {
        // バリデーション
        $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $request->user()->id,
            'new_password' => 'nullable|string|min:8|confirmed', // 新しいパスワードは任意
        ]);

        $user = $request->user();
        $user->username = $request->username; // ユーザー名を更新
        $user->email = $request->email; // メールアドレスを更新

        if ($request->filled('new_password')) {
            $user->password = bcrypt($request->new_password); // パスワードを更新
        }

        $user->save();

        return response()->json(['message' => '設定が更新されました']);
    }

    // アカウント削除
    public function destroy(Request $request)
    {
        $user = $request->user();
        $user->delete();

        return response()->json(['message' => 'アカウントが削除されました']);
    }
}
