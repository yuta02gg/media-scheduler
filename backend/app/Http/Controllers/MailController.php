<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\NotificationMail;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    /**
     * メール送信メソッド
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function sendNotificationEmail(Request $request)
    {
        // バリデーション（必要に応じて）
        $validated = $request->validate([
            'email' => 'required|email',
            'title' => 'required|string',
            'message' => 'required|string',
        ]);

        // メールデータの準備
        $data = [
            'title' => $validated['title'],
            'message' => $validated['message'],
        ];

        // メールの送信
        Mail::to($validated['email'])->send(new NotificationMail($data));

        // 応答の返却
        return response()->json(['message' => 'メールを送信しました。'], 200);
    }
}
