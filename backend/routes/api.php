<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Minishlink\WebPush\VAPID;

// ===== コントローラーをインポート =====
use App\Http\Controllers\MediaController;
use App\Http\Controllers\UserWorkController;
use App\Http\Controllers\WorkController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminReviewController;
// use App\Http\Controllers\ReminderController;

// ===== VAPIDキーの生成（必要に応じて） =====
Route::get('/generate-vapid-keys', function () {
    $keys = VAPID::createVapidKeys();
    return response()->json([
        'publicKey' => $keys['publicKey'],
        'privateKey' => $keys['privateKey'],
    ]);
});

// ===== ホームページ（例: welcomeビュー） =====
Route::get('/', function () {
    return view('welcome');
});

// ===== 検索のルート =====
Route::get('/media/search', [MediaController::class, 'search']);

// ===== レビュー（ログイン不要） =====
// 単体のメディア向け
Route::get('/media/{media_type}/{media_id}/reviews', [ReviewController::class, 'index'])
    ->where([
        'media_type' => 'movie|tv',
        'media_id'   => '[0-9]+',
    ]);

// ===== 全レビュー取得 =====
Route::get('/reviews', [ReviewController::class, 'getAllReviews']);

// ===== レビューランキング取得 =====
Route::get('/reviews/ranking', [ReviewController::class, 'getReviewRanking']);

// ===== メディア詳細表示 =====
Route::get('/media/{media_type}/{id}', [MediaController::class, 'show'])
    ->where([
        'media_type' => 'movie|tv',
        'id'         => '[0-9]+',
    ]);

/*
 |-----------------------------------------------------------------------------
 | 管理者向けルート
 |  - auth:sanctum でログイン中かチェック
 |  - is_admin ミドルウェアで管理者かチェック
 |  - 実行時URL例: http://localhost:8000/api/admin/users など
 |-----------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum','is_admin'])
    ->group(function () {
        // ===== ユーザー管理 =====
        Route::get('/admin/users', [AdminUserController::class, 'index']);
        Route::delete('/admin/users/{id}', [AdminUserController::class, 'destroy']);

        // ===== レビュー管理 =====
        Route::get('/admin/reviews', [AdminReviewController::class, 'index']);
        Route::delete('/admin/reviews/{id}', [AdminReviewController::class, 'destroy']);
    });

/*
 |-----------------------------------------------------------------------------
 | 認証済みユーザー向けルート
 |  - auth:sanctum でログインしているかチェック
 |  - 既存機能を保持
 |-----------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // パラメータの共通設定（あらかじめ絞り込み）
    Route::pattern('media_type', 'movie|tv');
    Route::pattern('id', '[0-9]+');

    // ===== メディアの登録関連 =====
    Route::post('/media/{media_type}/{id}/register', [MediaController::class, 'register']);
    Route::get('/media/{media_type}/{id}/is-registered', [MediaController::class, 'isRegistered']);

    // ===== ユーザーが登録した作品一覧 =====
    Route::get('/user/registered-works', [UserWorkController::class, 'index']);

    // ===== レビュー投稿 =====
    Route::post('/media/{media_type}/{id}/reviews', [ReviewController::class, 'store']);

    // ===== ユーザー情報関連 =====
    Route::get('/user', [UserController::class, 'show']);       // ユーザー情報を取得
    Route::put('/user', [UserController::class, 'update']);     // ユーザー情報を更新
    Route::delete('/user', [UserController::class, 'destroy']); // ユーザーを削除

    // ===== スケジュール管理 =====
    Route::get('/schedule', [ScheduleController::class, 'index']);
    Route::post('/schedule', [ScheduleController::class, 'store']);
    Route::delete('/schedule/{id}', [ScheduleController::class, 'destroy']);

    // ===== (オプション) リマインダー機能 =====
    // Route::post('/reminders', [ReminderController::class, 'store']);

    // ===== ログアウト =====
    Route::post('/logout', [LoginController::class, 'logout']);
});

/*
 |-----------------------------------------------------------------------------
 | ログイン・登録のルート (未認証OK)
 |-----------------------------------------------------------------------------
*/
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login',    [LoginController::class, 'login']);

/*
 |-----------------------------------------------------------------------------
 | 作品一覧
 |-----------------------------------------------------------------------------
*/
Route::get('/works', [WorkController::class, 'index']);

// ===== (任意) メール送信のルート =====
// Route::post('/send-notification-email', [MailController::class, 'sendNotificationEmail']);
