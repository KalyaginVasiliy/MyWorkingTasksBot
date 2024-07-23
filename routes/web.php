<?php

use App\Http\Controllers\TelegramController;
use Illuminate\Support\Facades\Route;

Route::post('/api/telegram/webhook', [TelegramController::class, 'handleWebhook']);
Route::get('/api/telegram/set-webhook', [TelegramController::class, 'setWebhook']);

Route::get('/', function () {
    return 'Всё работает';
});
Route::post('/telegram/webhook', [TelegramController::class, 'handleWebhook'])
    ->middleware('telegram.session');
Route::get('/debug', function () {
    return [
        'php_version' => PHP_VERSION,
        'laravel_version' => app()->version(),
        'environment' => app()->environment(),
        'debug_mode' => config('app.debug'),
    ];
});
