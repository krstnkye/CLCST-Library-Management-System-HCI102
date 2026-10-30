<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Account Review - CLCST Library</title>
    @include('partials.favicon')
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Material+Symbols+Outlined&display=swap" rel="stylesheet">
    <style>body{font-family:Inter,sans-serif}.material-symbols-outlined{font-variation-settings:'FILL' 0,'wght' 450,'GRAD' 0,'opsz' 24}</style>
    @include('partials.ui-motion')
</head>
<body class="clcst-portal clcst-role-{{ auth()->user()->role }} min-h-full text-slate-900">
<header class="border-b border-slate-200 bg-white"><div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4"><div class="flex items-center gap-3">@include('partials.celtech-logo', ['class' => 'h-10 w-10 rounded-full object-contain'])<div><p class="font-bold text-emerald-900">CLCST Library</p><p class="text-xs text-slate-500">Account review</p></div></div><a class="rounded-md border border-slate-300 px-3 py-2 text-sm font-semibold hover:bg-slate-50" href="{{ route(auth()->user()->dashboardRouteName()) }}">Back to Dashboard</a></div></header>
<main class="clcst-main mx-auto max-w-7xl space-y-5 px-4 py-8">
    <div>
        <h1 class="text-2xl font-bold">Review Library Accounts</h1>
        <p class="mt-2 text-sm text-slate-600">Open the uploaded ID and confirm it matches the school record before approving. You can manage only the account types your role allows.</p>
    </div>
    @include('partials.flash')
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
</main>
</body>
</html>
