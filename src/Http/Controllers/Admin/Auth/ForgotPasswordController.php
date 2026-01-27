<?php

namespace Marufsharia\Hyro\Http\Controllers\Admin\Auth;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;

class ForgotPasswordController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }
    /**
     * Get the redirect path after registration.
     *
     * @return string
     */
    protected function redirectTo()
    {
        if (Route::has('hyro.admin.dashboard')) {
            return route('hyro.admin.dashboard');
        }

        return $this->redirectTo;
    }
    /**
     * Display the form to request a password reset link.
     *
     * @return \Illuminate\View\View
     */
    public function showLinkRequestForm()
    {
        return view('hyro::admin.auth.passwords.email');
    }

    /**
     * Send a reset link to the given users.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        // We will send the password reset link to this users. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the users. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return $request->wantsJson()
                ? response()->json(['message' => __($status)], 200)
                : back()->with('status', __($status));
        }

        return $request->wantsJson()
            ? response()->json(['email' => __($status)], 400)
            : back()->withErrors(['email' => __($status)]);
    }
}
