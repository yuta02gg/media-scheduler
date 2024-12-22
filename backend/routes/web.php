<?php

use Illuminate\Support\Facades\Route;
use Minishlink\WebPush\VAPID;



// デフォルトのホームページルート
Route::get('/', function () {
    return view('welcome');
});

// VAPIDキー生成用のルート
Route::get('/generate-vapid-keys', function() {
    $keys = VAPID::createVapidKeys();

    return response()->json([
        'publicKey' => $keys['publicKey'],
        'privateKey' => $keys['privateKey'],
    ]);
});