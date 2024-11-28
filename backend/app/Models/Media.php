<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User; // Userモデルをインポート

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'media_type',
        'release_date',
        'overview',
        'poster_path',
        'tmdb_id',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_media')
                    ->withPivot('status', 'reminder_time', 'notification_type')
                    ->withTimestamps();
    }
}
