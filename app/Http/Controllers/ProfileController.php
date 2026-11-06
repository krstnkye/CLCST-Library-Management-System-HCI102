<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
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

    public function storePhoto(Request $request): RedirectResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:min_width=96,min_height=96,max_width=6000,max_height=6000'],
        ], [
            'photo.max' => 'Choose a photo smaller than 2 MB.',
            'photo.dimensions' => 'Choose a photo at least 96 by 96 pixels.',
            'photo.mimes' => 'Use a JPG, PNG or WebP photo.',
        ]);

        $user = $request->user();
        $old = $user->profile_photo_path;
        $path = $request->file('photo')->store('profile-photos', 'local');
        $user->forceFill(['profile_photo_path' => $path])->save();

        if ($old && $old !== $path) {
            Storage::disk('local')->delete($old);
        }

        AuditLog::record($user, 'profile.photo_updated', 'Updated their profile photo.');

        return back()->with('status', 'Your profile photo was updated.');
    }

    public function destroyPhoto(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->profile_photo_path) {
            Storage::disk('local')->delete($user->profile_photo_path);
            $user->forceFill(['profile_photo_path' => null])->save();
            AuditLog::record($user, 'profile.photo_removed', 'Removed their profile photo.');
        }

        return back()->with('status', 'Your profile photo was removed.');
    }

    public function showPhoto(User $user): StreamedResponse
    {
        abort_unless($user->profile_photo_path && Storage::disk('local')->exists($user->profile_photo_path), 404);

        return Storage::disk('local')->response($user->profile_photo_path, null, [
            'Cache-Control' => 'private, max-age=604800',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
