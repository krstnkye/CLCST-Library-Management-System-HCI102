<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->dashboardRouteName());
        }

        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'role' => ['required', 'in:'.implode(',', User::ROLES)],
            'identifier' => ['required', 'string', 'max:150'],
            'password' => ['required', 'string', 'max:255'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $key = $this->throttleKey($request);
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            return back()->withErrors([
                'identifier' => "Too many sign-in attempts. Try again in {$seconds} seconds.",
            ])->onlyInput('identifier', 'role');
        }

        $identifier = mb_strtolower(trim($credentials['identifier']));
        $user = User::query()
            ->where('role', $credentials['role'])
            ->where(function ($query) use ($identifier): void {
                $query->whereRaw('LOWER(email) = ?', [$identifier])
                    ->orWhereRaw('LOWER(patron_id) = ?', [$identifier]);
            })
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            RateLimiter::hit($key, 60);
            $this->audit($request, null, 'login.failed', 'A sign-in attempt failed.', [
                'role' => $credentials['role'],
            ]);

            return back()->withErrors([
                'identifier' => 'The account details are incorrect. Check your account type, ID or email, and password.',
            ])->onlyInput('identifier', 'role');
        }

        if (! $user->isActive()) {
            RateLimiter::hit($key, 60);

            return back()->withErrors([
                'identifier' => 'This account is awaiting approval or has been disabled. Contact the library office.',
            ])->onlyInput('identifier', 'role');
        }

        RateLimiter::clear($key);
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        $user->forceFill(['last_login_at' => now()])->save();
        $this->audit($request, $user, 'login.succeeded', 'User signed in successfully.');

        return redirect()->intended(route($user->dashboardRouteName()))
            ->with('status', 'Welcome back, '.$user->name.'.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();
        $this->audit($request, $user, 'logout', 'User signed out.');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'You have been logged out safely.');
    }

    public function register(): View
    {
        return view('auth.register');
    }

    public function storeRegistration(Request $request): RedirectResponse
    {
        // Normalize before the unique checks so "Ana@..." cannot slip past an existing "ana@...".
        $request->merge([
            'email' => mb_strtolower(trim((string) $request->input('email'))),
            'patron_id' => strtoupper(trim((string) $request->input('patron_id'))),
        ]);

        $validated = $request->validate([
            'patron_role' => ['required', 'in:'.implode(',', User::ROLES)],
            'last_name' => ['required', 'string', 'max:100'],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_initial' => ['nullable', 'string', 'max:5'],
            'patron_id' => ['required', 'string', 'max:100', 'unique:users,patron_id'],
            'program_or_department' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email:rfc', 'max:150', 'unique:users,email'],
            'mobile_number' => ['required', 'string', 'max:30'],
            'password' => ['required', 'confirmed', Password::min(10)->mixedCase()->letters()->numbers()],
            'credential_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'privacy_consent' => ['accepted'],
        ]);

        $path = $request->file('credential_file')->store('credentials', 'local');
        $user = User::create([
            'patron_id' => strtoupper(trim($validated['patron_id'])),
            'name' => trim($validated['first_name'].' '.$validated['last_name']),
            'first_name' => trim($validated['first_name']),
            'last_name' => trim($validated['last_name']),
            'middle_initial' => $validated['middle_initial'] ?: null,
            'email' => mb_strtolower(trim($validated['email'])),
            'role' => $validated['patron_role'],
            'status' => 'pending',
            'program_or_department' => $validated['program_or_department'],
            'mobile_number' => $validated['mobile_number'],
            'credential_path' => $path,
            'password' => $validated['password'],
        ]);

        $this->audit($request, $user, 'registration.submitted', 'A new account application was submitted.', [
            'role' => $user->role,
        ]);

        return redirect()->route('login')->with(
            'status',
            'Registration submitted. A librarian or administrator must verify your account before you can sign in.'
        );
    }

    private function throttleKey(Request $request): string
    {
        return 'login:'.mb_strtolower((string) $request->input('identifier')).'|'.$request->ip();
    }

    private function audit(Request $request, ?User $user, string $event, string $description, array $metadata = []): void
    {
        AuditLog::create([
            'user_id' => $user?->id,
            'event' => $event,
            'description' => $description,
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'metadata' => $metadata,
        ]);
    }
}
