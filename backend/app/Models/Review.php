<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Media;

class Review extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'media_id', 'rating', 'comment'];

    // ユーザーとのリレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // メディアとのリレーション
    public function media()
    {
        return $this->belongsTo(Media::class);
    }
}
