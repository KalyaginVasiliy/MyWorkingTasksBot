<?php

namespace App\Http;

use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use App\Http\Middleware\SkipCsrfToken;

class Middleware
{
    public array $middleware = [
        PreventRequestsDuringMaintenance::class,
        HandleCors::class,
        SkipCsrfToken::class,  // Добавлено для глобального применения
    ];

    public array $middlewareGroups = [
        'web' => [
            SubstituteBindings::class,
        ],

        'api' => [
            SkipCsrfToken::class,
            ThrottleRequests::class.':api',
            SubstituteBindings::class,
        ],
    ];

    public array $middlewareAliases = [
        'throttle' => ThrottleRequests::class,
        // Здесь можно добавить другие алиасы для middleware
    ];
}
