<?php

return [

    'defaults' => [
        'guard' => 'api',
        'passwords' => 'users',
    ],


    'guards' => [
        'web' => [
            'driver' => 'passport',
            'provider' => 'users',
        ],

        'api' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ]
];
