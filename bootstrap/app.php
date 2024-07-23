<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\SkipCsrfToken;
use App\Http\Middleware\TelegramSessionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            'api/telegram/*',
        ]);

        // Добавляем глобальный middleware для сессий
        $middleware->use([
            \Illuminate\Session\Middleware\StartSession::class,
        ]);

        // Регистрируем наш кастомный middleware для Telegram
        $middleware->alias([
            'telegram.session' => TelegramSessionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
