<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{Hash, Password};
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class ResetPasswordController extends Controller
{
    public function showResetForm(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password'       => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
                ActivityLog::record('password_reset', $user);
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', 'Your password has been reset successfully.')
            : back()->withInput($request->only('email'))->withErrors(['email' => __($status)]);
    }
}
