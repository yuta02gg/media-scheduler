<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserMedia;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReminderMail;
use Carbon\Carbon;

class SendReminders extends Command
{
    protected $signature = 'reminders:send';

    protected $description = 'リマインダーを送信します';

    public function handle()
    {
        $now = Carbon::now();

        $reminders = UserMedia::where('reminder_time', '<=', $now)
                              ->whereNotNull('reminder_time')
                              ->get();

        foreach ($reminders as $reminder) {
            $user = $reminder->user;
            $media = $reminder->media;

            Mail::to($user->email)->send(new ReminderMail($user, $media));

            // リマインダー時間をnullにして、再度送信されないようにする
            $reminder->reminder_time = null;
            $reminder->save();
        }

        $this->info('リマインダーの送信が完了しました。');
    }
}
