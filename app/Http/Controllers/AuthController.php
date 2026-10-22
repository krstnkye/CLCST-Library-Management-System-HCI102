<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
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
            'role' => ['nullable', Rule::in(User::ROLES)],
            'identifier' => ['required', 'string', 'max:150', function (string $attribute, mixed $value, Closure $fail): void {
                if (str_contains((string) $value, '@') && ! User::isInstitutionalEmail((string) $value)) {
                    $fail('Use your school-registered email ending in @'.config('library.allowed_email_domain').', or your school ID.');
                }
            }],
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
        // The account type picked on the form only changes the on-screen hints; the
        // database decides which portal a user may open.
        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$identifier])
            ->orWhereRaw('LOWER(patron_id) = ?', [$identifier])
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            RateLimiter::hit($key, 60);
            AuditLog::record(null, 'login.failed', 'A sign-in attempt failed.', [
                'role' => $credentials['role'] ?? null,
            ]);

            return back()->withErrors([
                'identifier' => 'The school ID or email and password do not match our records.',
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
        AuditLog::record($user, 'login.succeeded', 'User signed in successfully.');

        $welcome = 'Welcome back, '.$user->name.'.';
        if (($credentials['role'] ?? $user->role) !== $user->role) {
            $welcome .= ' Your account is registered as '.$user->roleLabel().', so we opened that portal for you.';
        }

        return redirect()->intended(route($user->dashboardRouteName()))->with('status', $welcome);
    }

    public function destroy(Request $request): RedirectResponse
    {
        AuditLog::record($request->user(), 'logout', 'User signed out.');
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

        $domain = config('library.allowed_email_domain');
        $validated = $request->validate([
            'patron_role' => ['required', Rule::in(User::SELF_REGISTER_ROLES)],
            'last_name' => ['required', 'string', 'max:100', "regex:/^[\pL\s.'-]+$/u"],
            'first_name' => ['required', 'string', 'max:100', "regex:/^[\pL\s.'-]+$/u"],
            'middle_initial' => ['nullable', 'string', 'max:5', "regex:/^[\pL.]+$/u"],
            'patron_id' => ['required', 'string', 'max:100', 'regex:/^[A-Z0-9-]+$/', 'unique:users,patron_id'],
            'program_or_department' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email:rfc', 'max:150', 'ends_with:@'.$domain, 'unique:users,email'],
            'mobile_number' => ['required', 'string', 'regex:/^(09|\+639)\d{9}$/'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'credential_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'privacy_consent' => ['accepted'],
        ], [
            'email.ends_with' => 'Use your school-registered email address ending in @'.$domain.'.',
            'patron_role.in' => 'Administrator accounts are created by the system administrator. Choose Student, Faculty, or Librarian.',
            'patron_id.regex' => 'School IDs may contain only letters, numbers, and dashes.',
            'mobile_number.regex' => 'Enter a Philippine mobile number such as 09171234567.',
            'first_name.regex' => 'Names may contain only letters, spaces, periods, apostrophes, and dashes.',
            'last_name.regex' => 'Names may contain only letters, spaces, periods, apostrophes, and dashes.',
        ]);

        $path = $request->file('credential_file')->store('credentials', 'local');
        $user = User::create([
            'patron_id' => strtoupper(trim($validated['patron_id'])),
            'name' => trim($validated['first_name'].' '.$validated['last_name']),
            'first_name' => trim($validated['first_name']),
            'last_name' => trim($validated['last_name']),
            'middle_initial' => isset($validated['middle_initial']) ? strtoupper(trim($validated['middle_initial'])) : null,
            'email' => mb_strtolower(trim($validated['email'])),
            'role' => $validated['patron_role'],
            'status' => 'pending',
            'program_or_department' => $validated['program_or_department'],
            'mobile_number' => $validated['mobile_number'],
            'credential_path' => $path,
            'password' => $validated['password'],
        ]);

        AuditLog::record($user, 'registration.submitted', 'A new account application was submitted.', [
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

}
