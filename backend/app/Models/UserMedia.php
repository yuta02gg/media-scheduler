<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserMedia extends Model
{
    use HasFactory;

    protected $table = 'user_media';

    protected $fillable = [
        'user_id',
        'media_id',
        'status',
        'reminder_time',
        'notification_type',
    ];

    public $timestamps = true; // 明示的に設定

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function media()
    {
        return $this->belongsTo(Media::class);
    }
}
