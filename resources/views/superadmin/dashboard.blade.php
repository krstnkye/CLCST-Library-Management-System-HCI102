@extends('superadmin.layout')
@section('title', 'Super Admin Dashboard')
@section('content')
    <section class="library-welcome flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
        <div><div class="mb-2 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-amber-700"><span class="material-symbols-outlined text-lg">verified_user</span>Restricted administrative access</div><h1 class="font-display text-2xl font-extrabold md:text-3xl">Super Admin Dashboard</h1><p class="mt-1 text-sm text-[#617068]">System-wide account, security, configuration, and activity overview.</p></div>
        <div class="flex flex-wrap gap-2"><a class="inline-flex items-center gap-2 rounded-lg border border-[#BCC8C0] bg-white px-4 py-2.5 text-sm font-bold text-[#17251D] hover:bg-[#EEF2EF]" href="{{ route('superadmin.section', 'reports') }}"><span class="material-symbols-outlined text-lg">monitoring</span>View Reports</a><a class="inline-flex items-center gap-2 rounded-lg bg-[#17251D] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#0F5C2E]" href="{{ route('management.data.index') }}"><span class="material-symbols-outlined text-lg">person_add</span>Import Staff Accounts</a></div>
    </section>

    <section class="library-metrics mt-2 grid sm:grid-cols-2 xl:grid-cols-6">
        @foreach ([
            ['Total Users',$stats['users'],'group','bg-emerald-50 text-[#0F5C2E]'],
            ['Staff Accounts',$stats['staff'],'badge','bg-blue-50 text-blue-700'],
            ['Students and Faculty',$stats['patrons'],'school','bg-violet-50 text-violet-700'],
            ['Book Titles',$stats['books'],'menu_book','bg-cyan-50 text-cyan-700'],
            ['Books on Loan',$stats['activeLoans'],'sync_alt','bg-slate-100 text-slate-700'],
            ['Pending Approvals',$stats['pending'],'approval','bg-amber-50 text-amber-700'],
        ] as [$label,$value,$icon,$tone])
            <article class="rounded-lg border border-[#D5DED8] bg-white p-4 shadow-sm"><div class="flex items-start justify-between gap-3"><div><p class="text-xs font-semibold text-[#68766E]">{{ $label }}</p><p class="mt-2 font-display text-2xl font-extrabold">{{ $value }}</p></div><span class="material-symbols-outlined rounded-lg p-2 {{ $tone }}">{{ $icon }}</span></div></article>
        @endforeach
    </section>

    <div class="mt-6 grid gap-6 xl:grid-cols-12">
        <section class="library-section bg-white xl:col-span-8">
            <div class="flex items-center justify-between border-b border-[#E2E8E4] px-5 py-4"><div><h2 class="font-display text-lg font-bold">Recent System Activity</h2><p class="mt-0.5 text-xs text-[#68766E]">Important actions recorded across all portals.</p></div><a class="text-sm font-bold text-[#0F5C2E] hover:underline" href="{{ route('superadmin.section', 'audit-logs') }}">View audit logs</a></div>
            <div class="overflow-x-auto"><table class="w-full min-w-[720px] text-left text-sm"><thead class="bg-[#F4F7F5] text-xs uppercase text-[#68766E]"><tr><th class="px-5 py-3">Time</th><th class="px-5 py-3">User</th><th class="px-5 py-3">Action</th><th class="px-5 py-3">Area</th></tr></thead><tbody class="divide-y divide-[#E2E8E4]">@forelse ($systemActivities as $activity)<tr class="hover:bg-[#F8FAF9]"><td class="whitespace-nowrap px-5 py-4 text-xs text-[#68766E]">{{ $activity->created_at->format('M d, g:i A') }}</td><td class="px-5 py-4 font-bold">{{ $activity->user?->name ?? 'Guest / system' }}</td><td class="px-5 py-4">{{ $activity->description }}</td><td class="px-5 py-4"><span class="rounded-full bg-slate-100 px-2.5 py-1 font-mono text-xs text-slate-700">{{ $activity->event }}</span></td></tr>@empty<tr><td class="px-5 py-8 text-center text-[#68766E]" colspan="4">No activity recorded yet.</td></tr>@endforelse</tbody></table></div>
        </section>

        <section class="library-section bg-white xl:col-span-4">
            <div class="border-b border-[#E2E8E4] px-5 py-4"><h2 class="font-display text-lg font-bold">System Alerts</h2><p class="mt-0.5 text-xs text-[#68766E]">Items requiring review or confirmation.</p></div>
            @php
                $systemAlerts = array_values(array_filter([
                    $stats['pending'] > 0 ? ['level' => 'Review', 'message' => $stats['pending'].' account request(s) need verification.', 'icon' => 'person_alert', 'tone' => 'amber'] : null,
                    $failedLogins > 0 ? ['level' => 'Security', 'message' => $failedLogins.' failed sign-in attempt(s) in the last 24 hours.', 'icon' => 'gpp_maybe', 'tone' => 'amber'] : null,
                    $stats['overdue'] > 0 ? ['level' => 'Circulation', 'message' => $stats['overdue'].' book(s) are overdue.', 'icon' => 'schedule', 'tone' => 'blue'] : null,
                    config('app.debug') ? ['level' => 'Configuration', 'message' => 'Debug mode is on. Turn it off (APP_DEBUG=false) before deployment.', 'icon' => 'bug_report', 'tone' => 'amber'] : null,
                ])) ?: [['level' => 'All clear', 'message' => 'No items need review.', 'icon' => 'verified_user', 'tone' => 'green']];
            @endphp
            <div class="space-y-3 p-4">@foreach ($systemAlerts as $alert)<article class="flex gap-3 rounded-lg border {{ $alert['tone'] === 'amber' ? 'border-amber-200 bg-amber-50' : ($alert['tone'] === 'blue' ? 'border-blue-200 bg-blue-50' : 'border-emerald-200 bg-emerald-50') }} p-3"><span class="material-symbols-outlined mt-0.5 {{ $alert['tone'] === 'amber' ? 'text-amber-700' : ($alert['tone'] === 'blue' ? 'text-blue-700' : 'text-emerald-700') }}">{{ $alert['icon'] }}</span><div><p class="text-xs font-bold uppercase tracking-wide">{{ $alert['level'] }}</p><p class="mt-1 text-xs leading-5 text-[#4F5E56]">{{ $alert['message'] }}</p></div></article>@endforeach</div>
        </section>
    </div>

    <section class="library-section mt-6 bg-white p-5">
        <div class="mb-4"><h2 class="font-display text-lg font-bold">Quick Actions</h2><p class="mt-0.5 text-xs text-[#68766E]">Common system administration tasks.</p></div>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([['users','manage_accounts','Manage Users'],['roles','admin_panel_settings','Roles and Permissions'],['borrowing-rules','rule','Borrowing Rules'],['security','security','Security Review']] as [$page,$icon,$label])<a class="flex items-center gap-3 rounded-lg border border-[#D5DED8] bg-[#F8FAF9] p-4 font-bold hover:border-[#0F5C2E] hover:bg-[#EEF2EF]" href="{{ route('superadmin.section', $page) }}"><span class="material-symbols-outlined text-[#0F5C2E]">{{ $icon }}</span>{{ $label }}<span class="material-symbols-outlined ml-auto text-lg text-[#718078]">arrow_forward</span></a>@endforeach
        </div>
    </section>
@endsection
