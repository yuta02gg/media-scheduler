<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserWorkController extends Controller
{
    // 登録済み作品の取得
    public function index()
    {
        $user = Auth::user();
        $media = $user->media()->get();


        return response()->json($media);
    }
}