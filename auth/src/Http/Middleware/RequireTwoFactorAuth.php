<?php

namespace Marufsharia\Hyro\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RequireTwoFactorAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // If user is authenticated and has 2FA enabled but hasn't verified yet
        if ($user && $user->two_factor_enabled && $request->session()->has('2fa:auth:id')) {
            // Allow access to 2FA verification routes
            if ($request->routeIs('hyro.2fa.*') || $request->routeIs('hyro.logout')) {
                return $next($request);
            }

            // Redirect to 2FA verification for all other routes
            return redirect()->route('hyro.2fa.verify');
        }

        return $next($request);
    }
}
