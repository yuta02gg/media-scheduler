<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class AdminUserController extends Controller
{
    /**
     * ユーザー一覧を取得します。
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // フィルタリングパラメータを取得
        $username = $request->input('username');
        $email = $request->input('email');

        // クエリビルダーを使用してユーザーを取得
        $query = User::query();

        if ($username) {
            $query->where('username', 'like', '%' . $username . '%');
        }

        if ($email) {
            $query->where('email', 'like', '%' . $email . '%');
        }

        $users = $query->get()->map(function($user) {
            return [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'is_admin' => $user->is_admin,
                'created_at' => $user->created_at,
            ];
        });

        return response()->json($users);
    }

    /**
     * 指定されたユーザーを削除します。
     *
     * @param int $id ユーザーID
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // 管理者自身を削除できないようにする（オプション）
        if ($user->id === auth()->user()->id) {
            return response()->json(['message' => '自身のアカウントは削除できません。'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'ユーザーが削除されました。'], 200);
    }
}
