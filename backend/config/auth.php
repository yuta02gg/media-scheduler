<?php

return [

    // ...

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'api' => [
            'driver' => 'sanctum', // 'token'から'sanctum'に変更
            'provider' => 'users',
            'hash' => false,
        ],
    ],

    // ...
];
