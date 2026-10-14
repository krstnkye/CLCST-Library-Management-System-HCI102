<!DOCTYPE html>
<html class="h-full bg-[#F4F6F5]" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Super Admin Dashboard - CLCST Library Management System</title>
    @include('partials.favicon')
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['Inter', 'sans-serif'], display: ['Plus Jakarta Sans', 'sans-serif'] } } } };</script>
    <style>.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 450, 'GRAD' 0, 'opsz' 24; line-height: 1; }</style>
</head>
<body class="min-h-full bg-[#F4F6F5] font-sans text-[#18251D] antialiased">
    <header class="flex h-16 items-center justify-between border-b border-[#D5DED8] bg-white px-4 shadow-sm md:px-8">
        <a class="flex items-center gap-3" href="{{ route('superadmin.dashboard') }}">
            @include('partials.celtech-logo', ['class' => 'h-10 w-10 rounded-full object-contain'])
            <div><p class="font-display text-sm font-extrabold text-[#0F5C2E] sm:text-base">CLCST Library Management System</p><p class="text-[11px] font-medium text-[#68766E]">Super Admin Control Center</p></div>
        </a>
        <div class="flex items-center gap-3"><div class="hidden text-right sm:block"><p class="text-xs font-bold">System Administrator</p><p class="text-[11px] text-[#68766E]">Full System Access</p></div><div class="flex h-9 w-9 items-center justify-center rounded-full bg-[#17251D] text-xs font-bold text-white">SA</div><form method="POST" action="{{ route('logout') }}">@csrf<button class="rounded-lg p-2 text-red-700 hover:bg-red-50" title="Log out" type="submit"><span class="material-symbols-outlined">logout</span></button></form></div>
    </header>

    <main class="mx-auto max-w-7xl p-4 md:p-8">
        @if (session('status'))<div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-900">{{ session('status') }}</div>@endif
        <section class="flex flex-col justify-between gap-4 border-b border-[#D5DED8] pb-6 md:flex-row md:items-end">
            <div><div class="mb-2 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-amber-700"><span class="material-symbols-outlined text-lg">verified_user</span>Restricted administrative access</div><h1 class="font-display text-2xl font-extrabold md:text-3xl">Super Admin Dashboard</h1><p class="mt-1 text-sm text-[#617068]">Manage system accounts, roles, security, and library-wide settings.</p></div>
            <a class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#0F5C2E] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#0A4421]" href="{{ route('librarian.section', 'activity-logs') }}"><span class="material-symbols-outlined text-lg">history</span>View Activity Logs</a>
        </section>

        <section class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([['Registered Users', $systemStats['users'], 'group'], ['Librarian Accounts', $systemStats['librarians'], 'badge'], ['Book Titles', $systemStats['bookTitles'], 'menu_book'], ['Open Requests', $systemStats['openRequests'], 'pending_actions']] as [$label, $value, $icon])
                <article class="rounded-lg border border-[#D5DED8] bg-white p-5 shadow-sm"><div class="flex items-center justify-between"><div><p class="text-xs font-semibold text-[#68766E]">{{ $label }}</p><p class="mt-2 font-display text-3xl font-extrabold">{{ $value }}</p></div><span class="material-symbols-outlined rounded-lg bg-[#E9F2EC] p-2.5 text-[#0F5C2E]">{{ $icon }}</span></div></article>
            @endforeach
        </section>

        <section class="mt-6 rounded-lg border border-[#D5DED8] bg-white p-5 shadow-sm">
            <h2 class="font-display text-lg font-bold">System Administration</h2><p class="mt-1 text-sm text-[#68766E]">Select an area to review or configure.</p>
            <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([['staff-management', 'manage_accounts', 'Accounts and Roles'], ['settings', 'settings', 'System Settings'], ['activity-logs', 'policy', 'Audit and Activity Logs'], ['reports', 'monitoring', 'System Reports'], ['announcements', 'campaign', 'Announcements'], ['support', 'support_agent', 'Technical Support']] as [$page, $icon, $label])
                    <a class="flex items-center gap-3 rounded-lg border border-[#D5DED8] bg-[#F8FAF9] p-4 font-bold hover:border-[#0F5C2E] hover:bg-[#EDF5F0]" href="{{ route('librarian.section', $page) }}"><span class="material-symbols-outlined text-[#0F5C2E]">{{ $icon }}</span>{{ $label }}<span class="material-symbols-outlined ml-auto text-lg text-[#718078]">arrow_forward</span></a>
                @endforeach
            </div>
        </section>
    </main>
</body>
</html>
