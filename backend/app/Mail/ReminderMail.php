<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $media;

    public function __construct($user, $media)
    {
        $this->user = $user;
        $this->media = $media;
    }

    public function build()
    {
        return $this->subject('リマインダー通知')
                    ->view('emails.reminder');
    }
}
