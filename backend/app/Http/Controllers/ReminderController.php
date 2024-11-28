<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserMedia;

class ReminderController extends Controller
{
    public function store(Request $request)
    {
        // バリデーション
        $validatedData = $request->validate([
            'work_id' => 'required|integer|exists:media,id',
            'reminder_time' => 'required|date|after:now',
        ]);

        try {
            // リマインダーの設定を保存
            $userMedia = UserMedia::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'media_id' => $validatedData['work_id'],
                ],
                [
                    'reminder_time' => $validatedData['reminder_time'],
                ]
            );

            return response()->json(['message' => 'リマインダーを設定しました。'], 200);
        } catch (\Exception $e) {
            \Log::error('リマインダーの保存中にエラーが発生しました: ' . $e->getMessage());
            return response()->json(['error' => 'リマインダーの設定に失敗しました。'], 500);
        }
    }
}
