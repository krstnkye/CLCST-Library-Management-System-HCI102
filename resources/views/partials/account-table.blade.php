{{-- Account list with search, ID document viewer, and approve/suspend actions limited to the viewer's role. --}}
<section class="overflow-hidden rounded-md border border-slate-200 bg-white">
    <form class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between" method="GET" action="{{ $searchRoute }}" role="search">
        <p class="text-xs text-slate-500">{{ $users->total() }} account(s). You can manage: {{ collect(auth()->user()->manageableRoles())->map(fn ($role) => ucfirst($role))->join(', ') }}.</p>
        <div class="flex flex-wrap gap-2">
            @foreach (request()->only(['role', 'status']) as $key => $value)<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endforeach
            <label class="sr-only" for="account-search">Search accounts</label>
            <input id="account-search" class="rounded-md border-slate-300 py-1.5 text-sm" name="q" value="{{ request('q') }}" placeholder="Name, email, or ID" type="search">
            <button class="rounded-md border border-slate-300 px-3 text-xs font-bold" type="submit">Search</button>
        </div>
    </form>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[880px] text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">Account</th><th class="px-5 py-3">Role</th><th class="px-5 py-3">Program or department</th><th class="px-5 py-3">Status</th><th class="px-5 py-3 text-right">Action</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($users as $user)
                    <tr>
                        <td class="px-5 py-4"><p class="font-semibold">{{ $user->name }}</p><p class="text-xs text-slate-500">{{ $user->patron_id }} · {{ $user->email }}</p><p class="text-xs text-slate-400">Registered {{ $user->created_at?->format('M d, Y') }}</p></td>
                        <td class="px-5 py-4">{{ $user->roleLabel() }}</td>
                        <td class="px-5 py-4 text-slate-600">{{ $user->program_or_department ?: 'Not provided' }}</td>
                        <td class="px-5 py-4"><span class="rounded-full px-2 py-1 text-xs font-semibold {{ $user->status === 'active' ? 'bg-emerald-100 text-emerald-800' : ($user->status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800') }}">{{ ucfirst($user->status) }}</span></td>
                        <td class="px-5 py-4">
                            <div class="flex justify-end gap-2">
                                @if ($user->credential_path)
                                    <a class="rounded-md border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50" href="{{ route('management.accounts.credential', $user) }}" target="_blank" rel="noopener" data-preview data-preview-title="Uploaded ID: {{ $user->name }}">View ID</a>
                                @endif
                                @if ($user->status === 'pending')
                                    <form method="POST" action="{{ route('management.accounts.approve', $user) }}" data-confirm="Make sure the uploaded ID matches the school record. The person will be able to sign in right away." data-confirm-title="Approve this account?" data-confirm-detail="{{ $user->name }} · {{ $user->roleLabel() }} · {{ $user->patron_id }}" data-confirm-label="Approve" data-confirm-icon="how_to_reg">@csrf<button class="rounded-md bg-emerald-900 px-3 py-2 text-xs font-bold text-white hover:bg-emerald-800" type="submit">Approve</button></form>
                                @endif
                                @if ($user->status !== 'suspended' && ! auth()->user()->is($user))
                                    <form method="POST" action="{{ route('management.accounts.suspend', $user) }}" data-confirm="{{ $user->status === 'pending' ? 'The application will be rejected and the person cannot sign in.' : 'The user will be signed out and cannot sign in until the account is approved again.' }}" data-confirm-title="{{ $user->status === 'pending' ? 'Reject this application?' : 'Suspend this account?' }}" data-confirm-detail="{{ $user->name }} · {{ $user->email }}" data-confirm-label="{{ $user->status === 'pending' ? 'Reject' : 'Suspend' }}">@csrf<button class="rounded-md border border-red-200 px-3 py-2 text-xs font-bold text-red-700 hover:bg-red-50" type="submit">{{ $user->status === 'pending' ? 'Reject' : 'Suspend' }}</button></form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center text-slate-500">No matching accounts.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($users->hasPages())<div class="border-t border-slate-200 p-4">{{ $users->links() }}</div>@endif
</section>
