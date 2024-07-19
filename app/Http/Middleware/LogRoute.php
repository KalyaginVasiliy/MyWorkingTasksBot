<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogRoute
{
    public function handle(Request $request, Closure $next)
    {
        Log::debug('Route accessed', [
            'uri' => $request->getRequestUri(),
            'method' => $request->getMethod(),
            'ip' => $request->ip(),
        ]);

        return $next($request);
    }
}
