<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Minishlink\WebPush\VAPID;

// コントローラーをインポート
use App\Http\Controllers\MediaController;
use App\Http\Controllers\UserWorkController;
use App\Http\Controllers\WorkController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\UserController;
// use App\Http\Controllers\ReminderController;

// VAPIDキーの生成（必要に応じて）
Route::get('/generate-vapid-keys', function () {
    $keys = VAPID::createVapidKeys();

    return response()->json([
        'publicKey' => $keys['publicKey'],
        'privateKey' => $keys['privateKey'],
    ]);
});

// ホームページのルート（必要に応じて）
Route::get('/', function () {
    return view('welcome');
});

// 検索のルート
Route::get('/media/search', [MediaController::class, 'search']);

// レビューの取得（ログイン不要） - より具体的なルートを先に定義
Route::get('/media/{media_type}/{media_id}/reviews', [ReviewController::class, 'index'])
    ->where([
        'media_type' => 'movie|tv',
        'media_id' => '[0-9]+',
    ]);

// 全体のレビュー取得エンドポイント
Route::get('/reviews', [ReviewController::class, 'getAllReviews']);

// レビューランキングの取得
Route::get('/reviews/ranking', [ReviewController::class, 'getReviewRanking']);

// 詳細のルート
Route::get('/media/{media_type}/{id}', [MediaController::class, 'show'])
    ->where([
        'media_type' => 'movie|tv',
        'id' => '[0-9]+',
    ]);

// 認証が必要なルート
Route::middleware('auth:sanctum')->group(function () {
    // パラメータの共通設定
    Route::pattern('media_type', 'movie|tv');
    Route::pattern('id', '[0-9]+');

    // メディアの登録
    Route::post('/media/{media_type}/{id}/register', [MediaController::class, 'register']);

    // メディアの登録状態を確認
    Route::get('/media/{media_type}/{id}/is-registered', [MediaController::class, 'isRegistered']);

    // ユーザーが登録した作品を取得
    Route::get('/user/registered-works', [UserWorkController::class, 'index']);

    // メディアのレビューを投稿
    Route::post('/media/{media_type}/{id}/reviews', [ReviewController::class, 'store']);

    // ユーザー情報関連
    Route::get('/user', [UserController::class, 'show']);       // ユーザー情報を取得
    Route::put('/user', [UserController::class, 'update']);     // ユーザー情報を更新
    Route::delete('/user', [UserController::class, 'destroy']); // ユーザーを削除

    // スケジュールの取得
    Route::get('/schedule', [ScheduleController::class, 'index']);
    // スケジュールの追加
    Route::post('/schedule', [ScheduleController::class, 'store']);
    // スケジュールの削除
    Route::delete('/schedule/{id}', [ScheduleController::class, 'destroy']);

    // リマインダー機能
    // Route::post('/reminders', [ReminderController::class, 'store']);

    // ユーザーのログアウト
    Route::post('/logout', [LoginController::class, 'logout']);
});

// ログインと登録のルート
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);

// 作品一覧のルート
Route::get('/works', [WorkController::class, 'index']);

// メール送信のルート
// Route::post('/send-notification-email', [MailController::class, 'sendNotificationEmail']);
