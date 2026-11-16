<?php

// MIDDLEWARE
use Laravel\Pulse\Http\Middleware\Authorize;
// RECORDERS
use Laravel\Pulse\Recorders\Exceptions;
use Laravel\Pulse\Recorders\SlowRequests;

return [
    'enabled' => env('PULSE_ENABLED', false),

    'middleware' => ['web', 'auth', Authorize::class],

    'recorders' => [
        Exceptions::class => [
            'sample_rate' => 1,
            'location' => true,
        ],
        SlowRequests::class => [
            'sample_rate' => 1,
            'threshold' => env('PULSE_SLOW_REQUESTS_THRESHOLD', 2000),
            'ignore' => [
                '#^/up$#',
                '#^/pulse$#',
            ],
        ],
    ],
];
