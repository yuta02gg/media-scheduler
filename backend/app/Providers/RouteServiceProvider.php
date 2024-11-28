<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * この名前空間はコントローラールートで適用されます。
     *
     * @var string|null
     */
    // protected $namespace = 'App\\Http\\Controllers'; // コメントアウトまたは削除

    /**
     * アプリケーションのルートサービスを定義します。
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        // 他のルートマッピングがあればここに追加
    }

    /**
     * "web" ミドルウェアグループ内の "web" ルートを定義します。
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            // ->namespace($this->namespace) // コメントアウトまたは削除
            ->group(base_path('routes/web.php'));
    }

    /**
     * "api" ミドルウェアグループ内の "api" ルートを定義します。
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            // ->namespace($this->namespace) // コメントアウトまたは削除
            ->group(base_path('routes/api.php'));
    }
}
