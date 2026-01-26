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
       if (Auth::guard($guard)->check()) {
            return redirect()->route('hyro.admin.dashboard');
        }

        return $next($request);
    }
}
