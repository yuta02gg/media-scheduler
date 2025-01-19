<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{

    public function getRegisteredWorks()
    {
        // 1. ログインユーザーのメディア一覧を取得
        $user = Auth::user();
        // attach/pivot情報を含む
        $registeredWorks = $user->media()->get();

        // 2. pivot.media_id をトップレベルの media_id にコピーして返す
        $mapped = $registeredWorks->map(function($work) {
            return [
                'id'         => $work->id, // DBのメディアID
                'title'      => $work->title,
                'media_type' => $work->media_type,
                'media_id'   => $work->pivot->media_id, // pivotからコピー
                'tmdb_id'    => $work->tmdb_id,     
                'poster_path'=> $work->poster_path,
            ];
        });

        return response()->json($mapped);
    }

    // ユーザー情報を取得
    public function show(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'id'       => $user->id,
            'username' => $request->user()->username,
            'email' => $request->user()->email,
            'is_admin' => $user->is_admin,
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