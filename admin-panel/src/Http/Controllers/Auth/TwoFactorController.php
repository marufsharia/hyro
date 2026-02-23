<?php

namespace Marufsharia\Hyro\AdminPanel\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TwoFactorController extends Controller
{
    /**
     * Show the 2FA verification form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(Request $request)
    {
        // Check if user is authenticated and has 2FA pending
        if (!Auth::check() || !$request->session()->has('2fa:auth:id')) {
            return redirect()->route('hyro.login');
        }

        // Verify the authenticated user matches the 2FA session
        if (Auth::id() !== $request->session()->get('2fa:auth:id')) {
            Auth::logout();
            $request->session()->flush();
            return redirect()->route('hyro.login');
        }

        return view('hyro::admin.auth.two-factor');
    }

    /**
     * Verify the 2FA code.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function verify(Request $request)
    {
        // Check if user is authenticated and has 2FA pending
        if (!Auth::check() || !$request->session()->has('2fa:auth:id')) {
            return redirect()->route('hyro.login');
        }

        $user = Auth::user();

        // Verify the authenticated user matches the 2FA session
        if ($user->id !== $request->session()->get('2fa:auth:id')) {
            Auth::logout();
            $request->session()->flush();
            return redirect()->route('hyro.login');
        }

        $request->validate([
            'code' => 'required|string',
            'recovery' => 'nullable|boolean',
        ]);

        $code = $request->input('code');
        $isRecovery = $request->boolean('recovery');

        // Verify the code
        $valid = false;
        
        if ($isRecovery) {
            // Verify recovery code
            $valid = $user->verifyRecoveryCode($code);
        } else {
            // Verify 2FA code
            $valid = $user->verifyTwoFactorCode($code);
        }

        if (!$valid) {
            throw ValidationException::withMessages([
                'code' => ['The provided code is invalid.'],
            ]);
        }

        // Clear 2FA session data
        $request->session()->forget(['2fa:auth:id', '2fa:auth:remember']);

        // Log activity
        $user->logActivity('two_factor_verified', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->intended(route('hyro.admin.dashboard'));
    }

    /**
     * Show the recovery code form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showRecovery(Request $request)
    {
        // Check if user is authenticated and has 2FA pending
        if (!Auth::check() || !$request->session()->has('2fa:auth:id')) {
            return redirect()->route('hyro.login');
        }

        // Verify the authenticated user matches the 2FA session
        if (Auth::id() !== $request->session()->get('2fa:auth:id')) {
            Auth::logout();
            $request->session()->flush();
            return redirect()->route('hyro.login');
        }

        return view('hyro::admin.auth.two-factor-recovery');
    }
}
