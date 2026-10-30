@extends('superadmin.layout')
@section('title', $page['title'])
@section('content')
    <section class="flex flex-col justify-between gap-4 border-b border-[#D5DED8] pb-5 lg:flex-row lg:items-end">
        <div><a class="mb-3 inline-flex items-center gap-1 text-xs font-bold text-[#0F5C2E] hover:underline" href="{{ route('superadmin.dashboard') }}"><span class="material-symbols-outlined text-base">arrow_back</span>Dashboard</a><div class="flex items-center gap-3"><span class="material-symbols-outlined flex h-11 w-11 items-center justify-center rounded-lg bg-[#E8EEE9] text-[#17251D]">{{ $page['icon'] }}</span><div><h1 class="font-display text-2xl font-extrabold md:text-3xl">{{ $page['title'] }}</h1><p class="mt-1 text-sm text-[#5E6D65]">{{ $page['description'] }}</p></div></div></div>
    </section>

    <div class="mt-6">
        @switch($section)
            @case('roles')
                @php
                    $matrix = [
                        'Search the catalog, reserve and renew books' => ['student', 'faculty'],
                        'Submit course reserves, class sessions and inter-library loans' => ['faculty'],
                        'Lend and receive books, record fines' => ['librarian', 'admin', 'superadmin'],
                        'Add and edit books and copies, publish announcements' => ['librarian', 'admin', 'superadmin'],
                        'Approve or suspend student and faculty accounts' => ['librarian', 'admin', 'superadmin'],
                        'Approve or suspend librarian accounts' => ['admin', 'superadmin'],
                        'Import and export user accounts' => ['admin', 'superadmin'],
                        'Approve administrators and super administrators' => ['superadmin'],
                        'Change borrowing rules and fine settings' => ['superadmin'],
                        'View security monitoring and system status' => ['superadmin'],
                    ];
                @endphp
                <section class="overflow-hidden rounded-lg border border-[#D5DED8] bg-white shadow-sm">
                    <div class="overflow-x-auto"><table class="w-full min-w-[760px] text-left text-sm">
                        <thead class="bg-[#F4F7F5] text-xs uppercase text-[#68766E]"><tr><th class="px-5 py-3">Permission</th>@foreach (\App\Models\User::ROLES as $role)<th class="px-3 py-3 text-center">{{ ['student' => 'Student', 'faculty' => 'Faculty', 'librarian' => 'Librarian', 'admin' => 'Admin', 'superadmin' => 'Super Admin'][$role] }}<span class="block text-[10px] font-normal normal-case">{{ $roleCounts[$role] ?? 0 }} accounts</span></th>@endforeach</tr></thead>
                        <tbody class="divide-y divide-[#E2E8E4]">
                            @foreach ($matrix as $permission => $roles)
                                <tr><td class="px-5 py-3 font-medium">{{ $permission }}</td>@foreach (\App\Models\User::ROLES as $role)<td class="px-3 py-3 text-center">@if (in_array($role, $roles, true))<span class="material-symbols-outlined text-emerald-700" aria-label="Allowed">check_circle</span>@else<span class="text-slate-300" aria-label="Not allowed">-</span>@endif</td>@endforeach</tr>
                            @endforeach
                        </tbody>
                    </table></div>
                    <p class="border-t border-[#E2E8E4] p-4 text-xs text-[#5E6D65]">Permissions are enforced on the server by the <code>portal</code> route middleware and by role checks in each controller, not only by hiding menu items. Roles are read from the database on every request.</p>
                </section>
                @break

            @case('borrowing-rules')
            @case('fine-settings')
                @php
                    $fields = $section === 'borrowing-rules'
                        ? ['max_loans_student' => ['Student loan limit', 'books'], 'loan_days_student' => ['Student loan period', 'days'], 'max_loans_faculty' => ['Faculty loan limit', 'books'], 'loan_days_faculty' => ['Faculty loan period', 'days'], 'max_renewals' => ['Renewals allowed per loan', 'times'], 'renewal_days' => ['Days added per renewal', 'days'], 'max_reservations' => ['Open reservations per patron', 'titles'], 'pickup_days' => ['Pickup hold period', 'days']]
                        : ['fine_per_day' => ['Overdue fine per day', 'PHP'], 'max_fine_per_item' => ['Maximum fine per book', 'PHP']];
                @endphp
                <form class="max-w-3xl rounded-lg border border-[#D5DED8] bg-white p-5 shadow-sm" method="POST" action="{{ route('superadmin.settings.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach ($fields as $key => [$label, $unit])
                            <div><label class="mb-1.5 block text-sm font-bold" for="{{ $key }}">{{ $label }}</label><div class="flex items-center gap-2"><input id="{{ $key }}" name="{{ $key }}" type="number" step="{{ $unit === 'PHP' ? '0.01' : '1' }}" min="0" value="{{ old($key, $policy[$key]) }}" required class="w-full rounded-lg border-[#CBD6CF] text-sm focus:border-[#0F5C2E] focus:ring-[#0F5C2E]"><span class="w-12 text-xs font-semibold text-[#68766E]">{{ $unit }}</span></div></div>
                        @endforeach
                    </div>
                    <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-[#E2E8E4] pt-4">
                        <p class="text-xs text-[#68766E]">Changes apply to new checkouts and renewals immediately and are recorded in the audit log. Existing due dates are not changed.</p>
                        <button class="rounded-lg bg-[#17251D] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#0F5C2E]" type="submit">Save settings</button>
                    </div>
                </form>
                @break

            @case('system-policies')
                <section class="grid gap-4 md:grid-cols-2">
                    @foreach ([
                        ['mail', 'School email only', 'Registration and sign-in accept only @'.config('library.allowed_email_domain').' addresses.'],
                        ['how_to_reg', 'Approval before access', 'New accounts stay pending until a librarian or administrator with authority over that role reviews the uploaded ID.'],
                        ['password', 'Password rules', 'At least 10 characters with upper and lower case letters and a number. Passwords are stored as one-way bcrypt hashes.'],
                        ['timer', 'Sign-in throttling', 'Five failed attempts lock the account and device pair for 60 seconds, and each route allows at most 10 sign-in requests per minute.'],
                        ['cookie', 'Sessions', 'Sessions are encrypted, HTTP-only, SameSite='.ucfirst((string) config('session.same_site')).', expire after '.config('session.lifetime').' minutes, and are regenerated at sign-in.'],
                        ['shield_lock', 'Request protection', 'Every form carries a CSRF token, output is HTML-escaped, database queries are parameterized, and security headers are sent on every response.'],
                    ] as [$icon, $title, $body])
                        <article class="rounded-lg border border-[#D5DED8] bg-white p-5 shadow-sm"><h2 class="flex items-center gap-2 font-display text-base font-bold"><span class="material-symbols-outlined text-[#0F5C2E]">{{ $icon }}</span>{{ $title }}</h2><p class="mt-2 text-sm leading-6 text-[#44534B]">{{ $body }}</p></article>
                    @endforeach
                </section>
                <div class="mt-6">@include('partials.policy-table')</div>
                @break

            @case('reports')
                @include('partials.reports')
                @break

            @case('security')
                <section class="grid gap-4 sm:grid-cols-3">
                    <article class="rounded-lg border border-[#D5DED8] bg-white p-4 shadow-sm"><p class="text-xs font-semibold text-[#68766E]">Failed sign-ins (7 days)</p><p class="mt-2 font-display text-2xl font-extrabold">{{ $failed->count() }}</p></article>
                    <article class="rounded-lg border border-[#D5DED8] bg-white p-4 shadow-sm"><p class="text-xs font-semibold text-[#68766E]">Suspended accounts</p><p class="mt-2 font-display text-2xl font-extrabold">{{ $suspended }}</p></article>
                    <article class="rounded-lg border border-[#D5DED8] bg-white p-4 shadow-sm"><p class="text-xs font-semibold text-[#68766E]">Pending accounts</p><p class="mt-2 font-display text-2xl font-extrabold">{{ $stats['pending'] }}</p></article>
                </section>
                <div class="mt-6 grid gap-6 xl:grid-cols-2">
                    <section class="rounded-lg border border-[#D5DED8] bg-white shadow-sm"><h2 class="border-b border-[#E2E8E4] px-5 py-4 font-display text-lg font-bold">Failed sign-in attempts</h2><ul class="divide-y divide-[#E2E8E4] text-sm">@forelse ($failed as $log)<li class="flex justify-between gap-3 px-5 py-3"><span>Account type: {{ $log->metadata['role'] ?? 'unknown' }}</span><span class="font-mono text-xs text-[#68766E]">{{ $log->ip_address }} - {{ $log->created_at->diffForHumans() }}</span></li>@empty<li class="px-5 py-6 text-center text-[#68766E]">No failed attempts in the last 7 days.</li>@endforelse</ul></section>
                    <section class="rounded-lg border border-[#D5DED8] bg-white shadow-sm"><h2 class="border-b border-[#E2E8E4] px-5 py-4 font-display text-lg font-bold">Recent sign-ins</h2><ul class="divide-y divide-[#E2E8E4] text-sm">@forelse ($signIns as $log)<li class="flex justify-between gap-3 px-5 py-3"><span class="font-semibold">{{ $log->user?->name ?? 'Former user' }}</span><span class="font-mono text-xs text-[#68766E]">{{ $log->ip_address }} - {{ $log->created_at->diffForHumans() }}</span></li>@empty<li class="px-5 py-6 text-center text-[#68766E]">No sign-ins yet.</li>@endforelse</ul></section>
                </div>
                @break

            @case('audit-logs')
                @include('partials.audit-log', ['searchRoute' => route('superadmin.section', 'audit-logs')])
                @break

            @case('maintenance')
                <div class="grid gap-6 xl:grid-cols-2">
                    <section class="rounded-lg border border-[#D5DED8] bg-white shadow-sm"><h2 class="border-b border-[#E2E8E4] px-5 py-4 font-display text-lg font-bold">Application</h2><dl class="divide-y divide-[#E2E8E4] text-sm">@foreach ($system['environment'] as $label => $value)<div class="flex justify-between gap-3 px-5 py-3"><dt class="text-[#68766E]">{{ $label }}</dt><dd class="font-semibold">{{ $value }}</dd></div>@endforeach</dl></section>
                    <section class="rounded-lg border border-[#D5DED8] bg-white shadow-sm"><h2 class="border-b border-[#E2E8E4] px-5 py-4 font-display text-lg font-bold">Database records</h2><dl class="divide-y divide-[#E2E8E4] text-sm">@foreach ($system['tables'] as $table => $count)<div class="flex justify-between gap-3 px-5 py-3"><dt class="font-mono text-xs">{{ $table }}</dt><dd class="font-semibold">{{ number_format($count) }}</dd></div>@endforeach</dl>
                        <p class="border-t border-[#E2E8E4] p-4 text-xs text-[#68766E]">Back up the database with your hosting provider's tools (for example <code>mysqldump</code>, or a copy of <code>database/database.sqlite</code> locally). Restores are not done from inside the application on purpose.</p></section>
                </div>
                @break

            @case('profile')
                @include('partials.profile-forms')
                @break
        @endswitch
    </div>
@endsection
