<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ReadOnlyDemo
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! env('APP_DEMO', false)) {
            return $next($request);
        }

        if ($request->isMethod('GET') || $request->isMethod('HEAD') || $request->isMethod('OPTIONS')) {
            return $next($request);
        }

        if ($request->is('api/login')) {
            return $next($request);
        }

        return response()->json(['message' => 'Demo mode — read only.'], 403);
    }
}
