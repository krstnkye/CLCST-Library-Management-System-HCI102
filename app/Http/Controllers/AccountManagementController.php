<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountManagementController extends Controller
{
    public function index(Request $request): View
    {
        $roles = match ($request->user()->role) {
            'librarian' => ['student', 'faculty'],
            'admin' => ['student', 'faculty', 'librarian'],
            'superadmin' => User::ROLES,
            default => [],
        };

        return view('management.accounts', [
            'users' => User::query()
                ->whereIn('role', $roles)
                ->orderByRaw("CASE status WHEN 'pending' THEN 0 WHEN 'active' THEN 1 ELSE 2 END")
                ->orderBy('name')
                ->limit(200)
                ->get(),
        ]);
    }

    public function approve(Request $request, User $user): RedirectResponse
    {
        $this->authorizeChange($request->user(), $user);
        $user->update([
            'status' => 'active',
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
        ]);
        $this->audit($request, 'account.approved', 'Approved '.$user->name.' ('.$user->role.').', $user);

        return back()->with('status', $user->name.' can now sign in.');
    }

    public function suspend(Request $request, User $user): RedirectResponse
    {
        $this->authorizeChange($request->user(), $user);
        abort_if($request->user()->is($user), 422, 'You cannot suspend your own account.');
        $user->update(['status' => 'suspended']);
        $this->audit($request, 'account.suspended', 'Suspended '.$user->name.' ('.$user->role.').', $user);

        return back()->with('status', $user->name.' has been suspended.');
    }

    private function authorizeChange(User $actor, User $target): void
    {
        $allowedTargets = match ($actor->role) {
            'librarian' => ['student', 'faculty'],
            'admin' => ['student', 'faculty', 'librarian'],
            'superadmin' => User::ROLES,
            default => [],
        };

        abort_unless(in_array($target->role, $allowedTargets, true), 403, 'You cannot change this account type.');
    }

    private function audit(Request $request, string $event, string $description, User $target): void
    {
        AuditLog::create([
            'user_id' => $request->user()->id,
            'event' => $event,
            'description' => $description,
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'metadata' => ['target_user_id' => $target->id, 'target_role' => $target->role],
        ]);
    }
}
