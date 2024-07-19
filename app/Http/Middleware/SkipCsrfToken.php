<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SkipCsrfToken
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('api/telegram/*')) {
            $request->attributes->add(['skip_csrf' => true]);
        }

        return $next($request);
    }
}
