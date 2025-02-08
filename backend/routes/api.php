<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Minishlink\WebPush\VAPID;

// コントローラー
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

// VAPIDキーの生成（必要に応じて）
Route::get('/generate-vapid-keys', function () {
    $keys = VAPID::createVapidKeys();
    return response()->json([
        'publicKey' => $keys['publicKey'],
        'privateKey' => $keys['privateKey'],
    ]);
});

// 検索
Route::get('/media/search', [MediaController::class, 'search']);

// ----- レビュー（ログイン不要）-----
Route::get('/media/{media_type}/{tmdb_id}/reviews', [ReviewController::class, 'index'])
    ->where([
        'media_type' => 'movie|tv',
        'tmdb_id'   => '[0-9]+',
    ]);

// 全レビュー
Route::get('/reviews', [ReviewController::class, 'getAllReviews']);

// レビューランキング
Route::get('/reviews/ranking', [ReviewController::class, 'getReviewRanking']);

// メディア詳細
Route::get('/media/{media_type}/{tmdb_id}', [MediaController::class, 'show'])
    ->where([
        'media_type' => 'movie|tv',
        'tmdb_id'   => '[0-9]+',
    ]);

// ログイン・登録
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);

// 作品一覧
Route::get('/works', [WorkController::class, 'index']);

/*
|--------------------------------------------------------------------------
| 管理者向けルート
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum','is_admin'])
    ->group(function () {
        // ユーザー管理
        
        Route::get('/admin/users', [AdminUserController::class, 'index']);
        Route::delete('/admin/users/{id}', [AdminUserController::class, 'destroy']);

        // レビュー管理
        Route::get('/admin/users/{id}/reviews', [AdminReviewController::class, 'index']);
        Route::get('/admin/reviews', [AdminReviewController::class, 'allReviews']);
        Route::delete('/admin/reviews/{id}', [AdminReviewController::class, 'destroy']);
    });

/*
|--------------------------------------------------------------------------
| 認証済みユーザー向けルート
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    // パターンマッチングを 'media_id' に変更
    Route::pattern('media_type', 'movie|tv');
    Route::pattern('media_id', '[0-9]+');

    // メディア登録
    Route::post('/media/{media_type}/{tmdb_id}/register', [MediaController::class, 'register'])
    ->where([
        'media_type' => 'movie|tv',
        'tmdb_id'    => '[0-9]+',
    ]);

    Route::get('/media/{media_type}/{media_id}/is-registered', [MediaController::class, 'isRegistered']);

    // 登録作品一覧
    Route::get('/user/registered-works', [UserController::class, 'getRegisteredWorks']);
    // 登録作品削除エンドポイント
    Route::delete('/user/registered-works/{id}', [UserController::class, 'destroyRegisteredWork']);
    
    // レビュー投稿
    Route::post('/media/{media_type}/{media_id}/reviews', [ReviewController::class, 'store']);

    // ユーザー情報
    Route::get('/user', [UserController::class, 'show']);
    Route::put('/user', [UserController::class, 'update']);
    Route::delete('/user', [UserController::class, 'destroy']);

    // スケジュール
    Route::get('/schedule', [ScheduleController::class, 'index']);
    Route::post('/schedule', [ScheduleController::class, 'store']);
    Route::delete('/schedule/{id}', [ScheduleController::class, 'destroy']);

    // ログアウト
    Route::post('/logout', [LoginController::class, 'logout']);
});
