<?php

namespace  Marufsharia\Hyro\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, $guard = null)
    {
        // Use the guard if provided, e.g., 'web' or 'admin'
        if (Auth::guard($guard)->check()) {
            // Redirect logged-in users to dashboard instead of home
            return redirect()->route('hyro.admin.dashboard');
        }

        return $next($request);
    }
}
