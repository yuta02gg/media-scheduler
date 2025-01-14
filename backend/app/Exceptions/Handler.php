<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * このアプリケーションで報告しない例外の型のリスト
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * バリデーション例外の際にセッションへフラッシュしない項目
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * アプリケーションの例外ハンドリング登録
     */
    public function register(): void
    {
        //
    }
}
