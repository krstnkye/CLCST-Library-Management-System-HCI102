<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AccountManagementController extends Controller
{
    public function index(Request $request): View
    {
        $roles = $request->user()->manageableRoles();

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
        abort_if($request->user()->is($user), 422, 'You cannot approve your own account.');
        abort_unless($user->status === 'pending', 422, 'Only pending accounts can be approved.');

        DB::transaction(function () use ($request, $user): void {
            $user->forceFill([
                'status' => 'active',
                'approved_at' => now(),
                'approved_by' => $request->user()->id,
            ])->save();
            $this->audit($request, 'account.approved', 'Approved '.$user->name.' ('.$user->role.').', $user);
        });

        return back()->with('status', $user->name.' can now sign in.');
    }

    public function suspend(Request $request, User $user): RedirectResponse
    {
        $this->authorizeChange($request->user(), $user);
        abort_if($request->user()->is($user), 422, 'You cannot suspend your own account.');
        abort_if($user->status === 'suspended', 422, 'This account is already suspended.');

        DB::transaction(function () use ($request, $user): void {
            $user->forceFill([
                'status' => 'suspended',
                'remember_token' => Str::random(60),
            ])->save();
            DB::table('sessions')->where('user_id', $user->id)->delete();
            $this->audit($request, 'account.suspended', 'Suspended '.$user->name.' ('.$user->role.').', $user);
        });

        return back()->with('status', $user->name.' has been suspended.');
    }

    private function authorizeChange(User $actor, User $target): void
    {
        abort_unless($actor->canManageRole($target->role), 403, 'You cannot change this account type.');
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
