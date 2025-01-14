<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\NotificationMail;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    /**
     * メール送信メソッド
     */
    public function sendNotificationEmail(Request $request)
    {
        $validated = $request->validate([
            'email'   => 'required|email',
            'title'   => 'required|string',
            'message' => 'required|string',
        ]);

        $data = [
            'title'   => $validated['title'],
            'message' => $validated['message'],
        ];

        Mail::to($validated['email'])->send(new NotificationMail($data));

        return response()->json(['message' => 'メールを送信しました。'], 200);
    }
}
