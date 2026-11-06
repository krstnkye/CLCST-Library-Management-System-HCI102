<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class AuthController extends Controller
{
    public function create(Request $request): Response|RedirectResponse
    {
        // A signed-in user never sees the sign-in form: the browser Back button (or reopening the
        // link) keeps them in their own portal until they press Log Out. The session still ends
        // after the idle limit or when the browser is closed.
        if (Auth::check()) {
            return redirect()->route(Auth::user()->dashboardRouteName());
        }

        // Never let the browser keep a copy of this page, so Back always asks the server.
        return response()->view('auth.login', ['signedInAs' => Auth::user()])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private')
            ->header('Pragma', 'no-cache');
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        // Any use of the sign-in form ends the account already signed in on this browser, whether or
        // not the new attempt succeeds, so an earlier session can never stay open behind it.
        if (Auth::check()) {
            AuditLog::record($request->user(), 'logout', 'Signed out by a new sign-in attempt.');
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $credentials = $request->validate([
            'role' => ['required', Rule::in(User::ROLES)],
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

            return $this->failedSignIn($request, "Too many sign-in attempts. Try again in {$seconds} seconds.");
        }

        $identifier = mb_strtolower(trim($credentials['identifier']));
        // The database decides which portal a user may open. The account type picked on the
        // form must also match that saved role, so a student cannot sign in through the
        // Super Administrator (or any other) option.
        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$identifier])
            ->orWhereRaw('LOWER(patron_id) = ?', [$identifier])
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            RateLimiter::hit($key, 60);
            AuditLog::record(null, 'login.failed', 'A sign-in attempt failed.', [
                'role' => $credentials['role'] ?? null,
            ]);

            return $this->failedSignIn($request, 'The school ID or email and password do not match our records.');
        }

        if (! $user->isActive()) {
            RateLimiter::hit($key, 60);

            return $this->failedSignIn($request, 'This account is awaiting approval or has been disabled. Contact the library office.');
        }

        if ($credentials['role'] !== $user->role) {
            RateLimiter::hit($key, 60);
            AuditLog::record($user, 'login.wrong_role', 'Tried to sign in through the '.$credentials['role'].' option.', [
                'role' => $credentials['role'],
            ]);
            $chosen = ['admin' => 'Administrator', 'superadmin' => 'Super Administrator'][$credentials['role']] ?? ucfirst($credentials['role']);

            return $this->failedSignIn($request, "This account cannot sign in as {$chosen}. Choose the account type that matches your library record.");
        }

        RateLimiter::clear($key);
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        $user->forceFill(['last_login_at' => now()])->save();
        AuditLog::record($user, 'login.succeeded', 'User signed in successfully.');

        $welcome = 'Welcome back, '.$user->name.'.';

        $request->session()->flash('status', $welcome);
        $request->session()->flash('status_quiet', true);

        // The sign-in page submits in the background and then replaces itself with the portal,
        // so the browser Back button never returns to the sign-in form.
        if ($request->expectsJson()) {
            return response()->json(['redirect' => route($user->dashboardRouteName())]);
        }

        return redirect()->route($user->dashboardRouteName());
    }

    private function failedSignIn(Request $request, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message, 'errors' => ['identifier' => [$message]], 'token' => csrf_token()], 422);
        }

        return back()->withErrors(['identifier' => $message])->onlyInput('identifier', 'role');
    }

    public function status(): JsonResponse
    {
        $user = Auth::user();

        return response()->json([
            'signed_in' => (bool) $user,
            'dashboard' => $user ? route($user->dashboardRouteName()) : null,
            'token' => csrf_token(),
        ])->header('Cache-Control', 'no-store, private');
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
            'password' => ['required', 'string', 'max:128', 'confirmed', Password::defaults()],
            'credential_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'mimetypes:application/pdf,image/jpeg,image/png', 'max:10240'],
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
        if (! is_string($path) || $path === '') {
            throw ValidationException::withMessages(['credential_file' => 'The verification document could not be stored. Please try again.']);
        }

        try {
            DB::transaction(function () use ($validated, $path): void {
                $user = User::forceCreate([
                    'patron_id' => $validated['patron_id'],
                    'name' => trim($validated['first_name'].' '.$validated['last_name']),
                    'first_name' => trim($validated['first_name']),
                    'last_name' => trim($validated['last_name']),
                    'middle_initial' => isset($validated['middle_initial']) ? mb_strtoupper(trim($validated['middle_initial'])) : null,
                    'email' => $validated['email'],
                    'role' => $validated['patron_role'],
                    'status' => 'pending',
                    'program_or_department' => trim($validated['program_or_department']),
                    'mobile_number' => trim($validated['mobile_number']),
                    'credential_path' => $path,
                    'password' => $validated['password'],
                ]);

                AuditLog::record($user, 'registration.submitted', 'A new account application was submitted.', [
                    'role' => $user->role,
                ]);
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }

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
