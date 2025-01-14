<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | デフォルトで使用するファイルシステムディスクを設定します。
    | "local" のほか、クラウドベースのディスク等も可能です。
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | storage/app/private, storage/app/public, S3 などのドライバの設定
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root'   => storage_path('app/private'),
            'serve'  => true,
            'throw'  => false,
        ],

        'public' => [
            'driver'     => 'local',
            'root'       => storage_path('app/public'),
            'url'        => env('APP_URL') . '/storage',
            'visibility' => 'public',
            'throw'      => false,
        ],

        's3' => [
            'driver'                 => 's3',
            'key'                    => env('AWS_ACCESS_KEY_ID'),
            'secret'                 => env('AWS_SECRET_ACCESS_KEY'),
            'region'                 => env('AWS_DEFAULT_REGION'),
            'bucket'                 => env('AWS_BUCKET'),
            'url'                    => env('AWS_URL'),
            'endpoint'               => env('AWS_ENDPOINT'),
            'use_path_style_endpoint'=> env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw'                  => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | `storage:link` コマンド実行時に作成されるシンボリックリンク
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],
];
