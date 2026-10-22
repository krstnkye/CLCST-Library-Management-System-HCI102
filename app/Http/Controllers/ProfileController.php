<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

/**
 * Self-service account changes available in every portal.
 */
class ProfileController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'mobile_number' => ['required', 'string', 'regex:/^(09|\+639)\d{9}$/'],
        ], ['mobile_number.regex' => 'Enter a Philippine mobile number such as 09171234567.']);

        $request->user()->update($data);
        AuditLog::record($request->user(), 'profile.updated', 'Updated their mobile number.');

        return back()->with('status', 'Your contact details were saved.');
    }

    public function password(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'different:current_password', Password::defaults()],
        ]);

        $user = $request->user();
        $user->forceFill(['password' => $request->input('password')])->save();

        // Sign out every other device that was using the old password.
        Auth::logoutOtherDevices($request->input('password'));
        $request->session()->regenerate();
        AuditLog::record($user, 'password.changed', 'Changed their password.');

        return back()->with('status', 'Password updated. Other devices have been signed out.');
    }
}
