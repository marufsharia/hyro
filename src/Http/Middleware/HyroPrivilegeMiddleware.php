<?php


namespace Marufsharia\Hyro\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class HyroPrivilegeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string|null $privilege
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ?string $privilege = null): Response
    {
        $user = Auth::user();

        // Not logged in → redirect to login
        if (!$user) {
            return redirect()->route('hyro.login');
        }

        // Check privilege
        if ($privilege && (!method_exists($user, 'hasPrivilege') || !$user->hasPrivilege($privilege))) {
            // Redirect instead of 403
            return redirect('/')->with('error', 'You do not have access to this page.');
        }

        return $next($request);
    }
}
