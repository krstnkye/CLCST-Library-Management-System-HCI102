<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AccountManagementController extends Controller
{
    public function index(Request $request): View
    {
        $roles = $request->user()->manageableRoles();
        $role = $request->query('role');
        $status = $request->query('status');
        $search = trim((string) $request->query('q', ''));

        return view('management.accounts', [
            'pendingCount' => User::whereIn('role', $roles)->where('status', 'pending')->count(),
            'users' => User::query()
                ->whereIn('role', in_array($role, $roles, true) ? [$role] : $roles)
                ->when(in_array($status, ['pending', 'active', 'suspended'], true), fn ($query) => $query->where('status', $status))
                ->when($search !== '', fn ($query) => $query->where(fn ($where) => $where
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('patron_id', 'like', "%{$search}%")))
                ->orderByRaw("CASE status WHEN 'pending' THEN 0 WHEN 'active' THEN 1 ELSE 2 END")
                ->orderBy('name')
                ->paginate(25)
                ->withQueryString(),
        ]);
    }

    public function credential(Request $request, User $user): StreamedResponse
    {
        $this->authorizeChange($request->user(), $user);
        abort_unless($user->credential_path && Storage::disk('local')->exists($user->credential_path), 404, 'No document was uploaded for this account.');
        $this->audit($request, 'account.credential_viewed', 'Viewed the uploaded document of '.$user->name.'.', $user);

        return Storage::disk('local')->response($user->credential_path, null, [
            'Cache-Control' => 'no-store, private',
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
        AuditLog::record($request->user(), $event, $description, ['target_user_id' => $target->id, 'target_role' => $target->role]);
    }
}
