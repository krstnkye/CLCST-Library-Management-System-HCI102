{{-- Account review opens inside the signed-in staff member's own portal, so the sidebar stays in place. --}}
@php
    $role = auth()->user()->role;
    $pendingView = request('status') === 'pending';
    [$portalLayout, $activePage] = match ($role) {
        'superadmin' => ['superadmin.layout', $pendingView ? 'approvals' : (['librarian' => 'librarians', 'admin' => 'library-staff'][request('role')] ?? 'users')],
        'admin' => ['admin.layout', $pendingView ? 'approvals' : 'users'],
        default => ['librarian.layout', 'users'],
    };
@endphp
@extends($portalLayout)
@section('title', 'Account Review')
@section('content')
<div class="space-y-5">
    <div>
        <h1 class="text-2xl font-bold">Review Library Accounts</h1>
        <p class="mt-2 text-sm text-slate-600">Open the uploaded ID and confirm it matches the school record before approving. You can manage only the account types your role allows.</p>
    </div>
    <nav class="flex flex-wrap gap-2" aria-label="Filter accounts">
        @php($filters = ['' => 'All', 'pending' => 'Waiting for approval', 'active' => 'Active', 'suspended' => 'Suspended'])
        @foreach ($filters as $value => $label)
            <a class="rounded-full px-3 py-1.5 text-xs font-bold {{ (string) request('status') === $value ? 'bg-emerald-900 text-white' : 'border border-slate-300 bg-white' }}" href="{{ route('management.accounts.index', array_filter(['status' => $value, 'role' => request('role')])) }}">{{ $label }}{{ $value === 'pending' ? ' ('.$pendingCount.')' : '' }}</a>
        @endforeach
        <span class="mx-1 w-px bg-slate-300"></span>
        @foreach (auth()->user()->manageableRoles() as $role)
            <a class="rounded-full px-3 py-1.5 text-xs font-bold {{ request('role') === $role ? 'bg-emerald-900 text-white' : 'border border-slate-300 bg-white' }}" href="{{ route('management.accounts.index', array_filter(['role' => request('role') === $role ? null : $role, 'status' => request('status')])) }}">{{ ucfirst($role === 'admin' ? 'administrator' : ($role === 'superadmin' ? 'super admin' : $role)) }}</a>
        @endforeach
    </nav>
    @include('partials.account-table', ['users' => $users, 'searchRoute' => route('management.accounts.index')])
</div>
@endsection
