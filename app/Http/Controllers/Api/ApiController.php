<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\LibraryBook;
use App\Models\User;
use App\Support\Jwt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;

/**
 * JSON API secured with JSON Web Tokens. Uses the same accounts, strict role check and
 * role permissions as the website.
 */
class ApiController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(User::ROLES)],
            'identifier' => ['required', 'string', 'max:150'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $key = 'api-login:'.mb_strtolower($data['identifier']).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json(['message' => 'Too many sign-in attempts. Try again in '.RateLimiter::availableIn($key).' seconds.'], 429);
        }

        $identifier = mb_strtolower(trim($data['identifier']));
        $user = User::query()->whereRaw('LOWER(email) = ?', [$identifier])->orWhereRaw('LOWER(patron_id) = ?', [$identifier])->first();

        if (! $user || ! Hash::check($data['password'], $user->password) || $user->role !== $data['role'] || ! $user->isActive()) {
            RateLimiter::hit($key, 60);
            AuditLog::record($user, 'api.login_failed', 'An API sign-in attempt failed.', ['role' => $data['role']]);

            return response()->json(['message' => 'The account type, school ID or email, or password is incorrect, or the account is not active.'], 401);
        }

        RateLimiter::clear($key);
        $token = Jwt::issue(['sub' => $user->id, 'role' => $user->role]);
        AuditLog::record($user, 'api.login', 'Signed in to the API and received a token.');

        return response()->json([
            'token_type' => 'Bearer',
            'access_token' => $token,
            'expires_in' => Jwt::ttl() * 60,
            'user' => $this->userData($user),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $this->userData($request->user())]);
    }

    public function logout(Request $request): JsonResponse
    {
        Jwt::revoke($request->attributes->get('jwt'));
        AuditLog::record($request->user(), 'api.logout', 'Revoked an API token.');

        return response()->json(['message' => 'Token revoked.']);
    }

    public function books(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $digits = preg_replace('/[^0-9Xx]/', '', $q);
        $books = LibraryBook::query()
            // Patrons see the same titles as the website catalog; staff also see archived records.
            ->when(! $request->user()->isStaff(), fn ($query) => $query->where('status', 'active'))
            ->when($q !== '', fn ($query) => $query->where(function ($w) use ($q, $digits): void {
                $w->where('title', 'like', "%{$q}%")
                    ->orWhere('author', 'like', "%{$q}%")
                    ->orWhere('isbn', 'like', "%{$q}%")
                    ->orWhere('call_number', 'like', "%{$q}%");
                // ISBNs are stored with hyphens, scanners read them without.
                if (strlen($digits) >= 4) {
                    $w->orWhereRaw("REPLACE(isbn, '-', '') like ?", ['%'.$digits.'%']);
                }
            }))
            ->orderBy('title')->paginate(20)->withQueryString();

        return response()->json($books->through(fn (LibraryBook $book) => $book->only([
            'id', 'title', 'author', 'isbn', 'call_number', 'category', 'shelf_location', 'total_copies', 'available_copies', 'loan_policy', 'status',
        ])));
    }

    public function myLoans(Request $request): JsonResponse
    {
        $loans = $request->user()->loans()->with('copy.book')->latest('borrowed_at')->get();

        return response()->json(['data' => $loans->map(fn ($loan) => [
            'receipt_no' => $loan->receipt_no,
            'title' => $loan->copy?->book?->title,
            'barcode' => $loan->copy?->barcode,
            'borrowed_at' => $loan->borrowed_at?->toIso8601String(),
            'due_at' => $loan->due_at?->toIso8601String(),
            'returned_at' => $loan->returned_at?->toIso8601String(),
            'overdue' => $loan->isOverdue(),
            'renewals' => $loan->renewals,
        ])]);
    }

    public function auditLogs(Request $request): JsonResponse
    {
        $logs = AuditLog::with('user')->latest()->paginate(50);

        return response()->json($logs->through(fn (AuditLog $log) => [
            'at' => $log->created_at->toIso8601String(),
            'user' => $log->user?->name,
            'event' => $log->event,
            'description' => $log->description,
            'ip_address' => $log->ip_address,
        ]));
    }

    private function userData(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'school_id' => $user->patron_id,
            'role' => $user->role,
            'role_label' => $user->roleLabel(),
        ];
    }
}
