<?php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'], // 全てのHTTPメソッドを許可

    'allowed_origins' => ['http://localhost:8080'], // フロントエンドのURL

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'], // 全てのヘッダーを許可

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true, // 認証付きリクエストを許可
];