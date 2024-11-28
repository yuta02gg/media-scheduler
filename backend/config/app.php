<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | この値はアプリケーションの名前です。通知や他の場所で
    | アプリケーション名を表示する必要がある場合に使用されます。
    |
    */

    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | この値は、アプリケーションが現在実行されている「環境」を示します。
    | これにより、さまざまなサービスの設定を行う際に使用できます。
    | この値は ".env" ファイルで設定してください。
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | アプリケーションがデバッグモードの場合、エラー発生時にスタックトレース付きの
    | 詳細なエラーメッセージが表示されます。無効にすると、シンプルなエラーページが
    | 表示されます。
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | このURLは、Artisanコマンドラインツールを使用する際に正しいURLを生成するために
    | コンソールで使用されます。アプリケーションのルートURLを設定してください。
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | ここでは、アプリケーションのデフォルトのタイムゾーンを指定できます。
    | これは、PHP の日付および日付時刻関数によって使用されます。
    |
    */

    'timezone' => env('APP_TIMEZONE', 'UTC'),

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | アプリケーションのロケールは、翻訳サービスで使用されるデフォルトのロケールを
    | 決定します。この値は、翻訳文字列が用意されている任意のロケールに設定できます。
    |
    */

    'locale' => env('APP_LOCALE', 'en'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | このキーは、Laravel の暗号化サービスによって使用されます。安全性を確保するために、
    | ランダムな32文字の文字列に設定してください。アプリケーションをデプロイする前に
    | 必ず設定してください。
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    'previous_keys' => [
        // 以前の暗号化キーがある場合、ここに設定します。
        // ...array_filter(explode(',', env('APP_PREVIOUS_KEYS', ''))),
    ],

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    |
    | これらの設定オプションは、Laravel の「メンテナンスモード」のステータスを
    | 管理するために使用されるドライバを決定します。"cache" ドライバを使用すると、
    | 複数のマシン間でメンテナンスモードを制御できます。
    |
    | サポートされているドライバ: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'redis'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | ここでは、アプリケーションのサービスプロバイダを指定します。これらの
    | プロバイダはアプリケーションのリクエストに対して自動的にロードされ
    | ます。自由に追加・削除してアプリケーションに必要な機能を構築できます。
    |
    */

    'providers' => [

        /*
         * Laravel フレームワークのサービスプロバイダ
         */
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,

        /*
         * パッケージのサービスプロバイダ
         */

        /*
         * アプリケーションのサービスプロバイダ
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class, // 必要に応じてコメントを外します
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,

    ],

];
