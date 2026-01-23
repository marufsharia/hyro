<?php

namespace Marufsharia\Hyro\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiEnabled
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Config::get('hyro.api.enabled', false)) {
            return response()->json([
                'error' => [
                    'code' => 'api_disabled',
                    'message' => 'The Hyro API is currently disabled',
                ]
            ], 503);
        }

        return $next($request);
    }
}
