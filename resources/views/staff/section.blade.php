<!DOCTYPE html>
<html class="h-full bg-[#F8FAFC]" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} - CLCST Library Management System</title>
    @include('partials.favicon')

    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Plus Jakarta Sans', 'sans-serif']
                    }
                }
            }
        };
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            display: inline-block;
            line-height: 1;
            vertical-align: middle;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="h-full bg-[#F8FAFC] text-[#0F172A] antialiased">
    <header class="sticky top-0 z-40 flex h-16 w-full items-center justify-between border-b border-[#CBD5E1] bg-white px-4 shadow-sm lg:px-6">
        <div class="flex items-center gap-3">
            @include('partials.celtech-logo', ['class' => 'flex h-10 w-10 items-center justify-center rounded-full bg-[#065F2A] text-sm font-extrabold text-white shadow-sm object-contain bg-white p-0.5'])
            <div>
                <span class="block font-display text-sm font-bold leading-none tracking-wide text-[#064E3B] sm:text-base">CLCST LIBRARY MANAGEMENT SYSTEM</span>
                <span class="mt-0.5 hidden text-[10px] font-bold uppercase tracking-wider text-[#64748B] sm:block">{{ $title }}</span>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a class="hidden rounded-lg border border-[#CBD5E1] px-3 py-2 text-xs font-bold text-[#334155] hover:bg-[#F8FAFC] sm:inline-flex" href="{{ route('staff.dashboard') }}">Staff Dashboard</a>
            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-[#065F2A] text-sm font-bold text-white">MS</div>
        </div>
    </header>

    <div class="flex min-h-[calc(100vh-4rem)]">
        @include('partials.staff-sidebar', ['activePage' => $section])

        <main class="md:ml-64 min-w-0 flex-1 space-y-6 overflow-y-auto p-4 sm:p-6 lg:p-8">
            <section class="rounded-2xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
                <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center">
                    <div>
                        <span class="inline-flex rounded-full bg-[#DCFCE7] px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-[#166534]">Operations Workspace</span>
                        <h1 class="mt-3 font-display text-3xl font-bold">{{ $title }}</h1>
                        <p class="mt-2 max-w-3xl text-sm leading-6 text-[#475569]">
                            Manage this area using the live records already available in the library portal. Each action below opens the matching catalog, queue, or record view.
                        </p>
                    </div>
                    <a class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#065F2A] px-4 py-2 text-sm font-bold text-white" href="{{ route('books.catalog') }}">
                        <span class="material-symbols-outlined text-base">menu_book</span>
                        Open Catalog
                    </a>
                </div>
            </section>

            @if ($section === 'circulation')
                @php
                    $bookLookup = collect($catalogBooks)->keyBy('id');
                    $historyLookup = collect($borrowingHistory)->whereNotNull('loan_id')->keyBy('loan_id');
                    $loanQuota = $portalRole === 'faculty' ? 15 : 5;
                    $activeLoanCount = count($activeLoans);
                    $quotaPercent = $loanQuota > 0 ? min(($activeLoanCount / $loanQuota) * 100, 100) : 0;
                    $dueSoonCount = collect($activeLoans)->filter(fn ($loan) => str_contains($loan['due'], 'Oct'))->count();
                    $remainingSlots = max($loanQuota - $activeLoanCount, 0);
                @endphp

                <section class="rounded-2xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                    <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-start">
                        <div>
                            <nav aria-label="Breadcrumb" class="mb-2 flex items-center gap-2 text-xs font-medium text-[#64748B]">
                                <span>Academic Portal</span>
                                <span class="material-symbols-outlined text-sm">chevron_right</span>
                                <span>Circulation</span>
                                <span class="material-symbols-outlined text-sm">chevron_right</span>
                                <span class="font-bold text-[#065F2A]">My Borrowed Books</span>
                            </nav>
                            <span class="inline-flex rounded-full bg-[#DCFCE7] px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-[#166534]">Faculty / Staff Borrowing Desk</span>
                            <h1 class="mt-3 font-display text-3xl font-bold text-[#064E3B]">My Borrowed Books</h1>
                            <p class="mt-2 max-w-3xl text-sm leading-6 text-[#475569]">Track active book loans, due dates, renewal limits, return options, and citation actions from one library account workspace.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <a class="inline-flex items-center gap-2 rounded-lg border border-[#CBD5E1] bg-white px-3.5 py-2 text-xs font-bold text-[#334155] shadow-sm hover:bg-[#F8FAFC]" href="{{ route('staff.section', 'reports') }}">
                                <span class="material-symbols-outlined text-base">download</span>
                                Export Loan Ledger
                            </a>
                            <a class="inline-flex items-center gap-2 rounded-lg border border-[#CBD5E1] bg-white px-3.5 py-2 text-xs font-bold text-[#334155] shadow-sm hover:bg-[#F8FAFC]" href="{{ route('staff.section', 'support') }}">
                                <span class="material-symbols-outlined text-base">meeting_room</span>
                                Return Drop Box Info
                            </a>
                            <a class="inline-flex items-center gap-2 rounded-lg bg-[#065F2A] px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-[#064E3B]" href="{{ route('staff.section', 'reports') }}">
                                <span class="material-symbols-outlined text-base">autorenew</span>
                                Renew All Eligible
                            </a>
                        </div>
                    </div>
                </section>

                <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <article class="rounded-xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-[#64748B]">Active Borrowed Books</p>
                                <h2 class="mt-1 font-display text-2xl font-bold text-[#065F2A]">{{ $activeLoanCount }} <span class="text-sm font-medium text-[#475569]">titles</span></h2>
                            </div>
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#DCFCE7] text-[#065F2A]"><span class="material-symbols-outlined">library_books</span></span>
                        </div>
                        <div class="mt-4">
                            <div class="mb-1.5 flex items-center justify-between text-xs text-[#64748B]">
                                <span>Quota Used: {{ $activeLoanCount }} of {{ $loanQuota }}</span>
                                <span class="font-bold text-[#065F2A]">{{ round($quotaPercent) }}%</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-[#E2E8F0]"><div class="h-full rounded-full bg-[#065F2A]" style="width: {{ $quotaPercent }}%"></div></div>
                            <p class="mt-1.5 text-[11px] font-medium text-[#64748B]">{{ $remainingSlots }} loan {{ Str::plural('slot', $remainingSlots) }} remaining</p>
                        </div>
                    </article>

                    <article class="rounded-xl border-2 border-[#FACC15] bg-[#FFFBEB] p-5 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-[#92400E]">Due Date Watch</p>
                                <h2 class="mt-1 font-display text-2xl font-bold text-[#92400E]">{{ $dueSoonCount }} <span class="text-sm font-medium">soon</span></h2>
                            </div>
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#FACC15] text-[#6D5200]"><span class="material-symbols-outlined">notifications_active</span></span>
                        </div>
                        <p class="mt-4 text-xs leading-5 text-[#92400E]">Books near their deadline are highlighted below so they can be renewed or returned on time.</p>
                    </article>

                    <article class="rounded-xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-[#64748B]">Renewal Requests</p>
                                <h2 class="mt-1 font-display text-2xl font-bold text-[#065F2A]">1 <span class="text-sm font-medium text-[#475569]">pending</span></h2>
                            </div>
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#EEF2FF] text-[#002E97]"><span class="material-symbols-outlined">sync</span></span>
                        </div>
                        <p class="mt-4 text-xs leading-5 text-[#64748B]">Renewals are allowed when no other patron has a hold on the same copy.</p>
                    </article>

                    <article class="rounded-xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-[#64748B]">Account Standing</p>
                                <h2 class="mt-1 font-display text-2xl font-bold text-[#065F2A]">Clear</h2>
                            </div>
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#DCFCE7] text-[#065F2A]"><span class="material-symbols-outlined">verified_user</span></span>
                        </div>
                        <p class="mt-4 text-xs leading-5 text-[#64748B]">No blocking fines or account holds are attached to this faculty/staff account.</p>
                    </article>
                </section>

                <section class="grid grid-cols-1 gap-6 xl:grid-cols-12">
                    <div class="space-y-4 xl:col-span-8">
                        <div class="rounded-xl border border-[#CBD5E1] bg-white p-4 shadow-sm">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                <div>
                                    <h2 class="font-display text-xl font-bold text-[#0F172A]">Active Loan Cards</h2>
                                    <p class="text-xs text-[#64748B]">Displaying books currently checked out to this account.</p>
                                </div>
                                <form class="flex w-full max-w-md items-center gap-2 rounded-lg border border-[#CBD5E1] bg-[#F8FAFC] px-3 py-2" method="GET" action="{{ route('books.catalog') }}">
                                    <span class="material-symbols-outlined text-base text-[#64748B]">search</span>
                                    <input class="w-full border-0 bg-transparent p-0 text-sm text-[#0F172A] placeholder:text-[#94A3B8] focus:ring-0" name="q" placeholder="Search catalog for another title...">
                                    <button class="rounded bg-[#065F2A] px-3 py-1.5 text-xs font-bold text-white" type="submit">Search</button>
                                </form>
                            </div>
                        </div>

                        @forelse ($activeLoans as $loan)
                            @php
                                $book = $bookLookup->get($loan['book_id']);
                                $history = $historyLookup->get($loan['id']);
                                $isDueSoon = str_contains($loan['due'], 'Oct');
                                $renewedLoans = session('renewed_loans', []);
                                $isRenewed = in_array($loan['id'], $renewedLoans, true);
                            @endphp
                            <article class="relative overflow-hidden rounded-xl border {{ $isDueSoon ? 'border-[#FACC15] bg-[#FFFBEB]' : 'border-[#CBD5E1] bg-white' }} p-5 shadow-sm transition-shadow hover:shadow-md">
                                <div class="absolute right-0 top-0">
                                    <span class="inline-flex items-center gap-1.5 rounded-bl-lg px-3 py-1 text-[10px] font-bold uppercase {{ $isDueSoon ? 'bg-[#FACC15] text-[#6D5200]' : 'bg-[#E2E7FF] text-[#0F172A]' }}">
                                        <span class="material-symbols-outlined text-sm">{{ $isDueSoon ? 'warning' : 'calendar_today' }}</span>
                                        Due {{ $loan['due'] }}
                                    </span>
                                </div>
                                <div class="mt-3 flex flex-col gap-5 sm:flex-row">
                                    <div class="h-32 w-24 flex-shrink-0 overflow-hidden rounded-lg border border-[#CBD5E1] bg-[#E2E7FF] shadow-sm">
                                        @if ($book)
                                            <img class="h-full w-full object-cover" src="{{ $book['cover_url'] }}" alt="{{ $loan['title'] }} cover">
                                        @else
                                            <div class="flex h-full w-full flex-col justify-between p-3 text-center text-[#065F2A]">
                                                <span class="material-symbols-outlined text-3xl">menu_book</span>
                                                <span class="text-[10px] font-bold">{{ $loan['call'] }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h3 class="font-display text-lg font-bold text-[#0F172A]">{{ $loan['title'] }}</h3>
                                        <p class="mt-1 text-sm text-[#64748B]">
                                            By <span class="font-medium text-[#334155]">{{ $book['author'] ?? ($history['author'] ?? 'Catalog author record') }}</span>
                                            @if ($book)
                                                - {{ $book['publisher'] }} ({{ $book['year'] }})
                                            @endif
                                        </p>
                                        <div class="my-4 grid grid-cols-1 gap-3 rounded-lg border border-[#CBD5E1] bg-[#F8FAFC] p-3 text-xs sm:grid-cols-2 lg:grid-cols-4">
                                            <div><span class="block text-[10px] font-bold uppercase text-[#64748B]">Accession Barcode</span><span class="font-mono font-bold text-[#0F172A]">{{ $loan['barcode'] }}</span></div>
                                            <div><span class="block text-[10px] font-bold uppercase text-[#64748B]">Call Number</span><span class="font-medium text-[#0F172A]">{{ $loan['call'] }}</span></div>
                                            <div><span class="block text-[10px] font-bold uppercase text-[#64748B]">Borrowed On</span><span class="text-[#0F172A]">{{ $history['borrowed'] ?? 'Recorded by Desk 01' }}</span></div>
                                            <div><span class="block text-[10px] font-bold uppercase text-[#64748B]">Renewal Limit</span><span class="font-bold text-[#065F2A]">{{ $isRenewed ? '1 of 2 Used' : '0 of 2 Used' }}</span></div>
                                        </div>
                                        <div class="flex flex-wrap items-center justify-between gap-3">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <form method="POST" action="{{ route('loans.renew', $loan['id']) }}">
                                                    @csrf
                                                    <button class="inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-xs font-bold shadow-sm {{ $isRenewed ? 'bg-[#DCFCE7] text-[#166534]' : 'bg-[#065F2A] text-white hover:bg-[#064E3B]' }}" type="submit" @disabled($isRenewed)>
                                                        <span class="material-symbols-outlined text-base">{{ $isRenewed ? 'check_circle' : 'autorenew' }}</span>
                                                        {{ $isRenewed ? 'Renewed' : 'Renew Book' }}
                                                    </button>
                                                </form>
                                                <a class="inline-flex items-center gap-1.5 rounded-lg border border-[#CBD5E1] px-3 py-2 text-xs font-bold text-[#334155] hover:bg-[#F8FAFC]" href="https://www.google.com/search?q={{ urlencode($loan['title'].' citation') }}" target="_blank" rel="noopener">
                                                    <span class="material-symbols-outlined text-base">format_quote</span>
                                                    Cite Book
                                                </a>
                                                <a class="inline-flex items-center gap-1 rounded-lg px-3 py-2 text-xs font-bold text-[#065F2A] hover:underline" href="{{ route('books.catalog', ['selected' => $loan['book_id']]) }}">
                                                    View Book Details
                                                    <span class="material-symbols-outlined text-base">arrow_forward</span>
                                                </a>
                                            </div>
                                            <span class="text-[11px] font-semibold text-[#64748B]">{{ $book['location'] ?? 'Main Library Stacks' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <article class="rounded-xl border border-[#CBD5E1] bg-white p-8 text-center shadow-sm">
                                <span class="material-symbols-outlined text-4xl text-[#94A3B8]">library_books</span>
                                <h2 class="mt-3 font-display text-xl font-bold">No active borrowed books</h2>
                                <p class="mt-2 text-sm text-[#64748B]">Search the catalog to reserve or borrow a title.</p>
                            </article>
                        @endforelse
                    </div>

                    <aside class="space-y-4 xl:col-span-4">
                        <section class="relative overflow-hidden rounded-2xl border border-[#064E3B] bg-gradient-to-br from-[#135D2E] to-[#00441D] p-6 text-white shadow-md">
                            @include('partials.celtech-logo', ['class' => 'absolute -bottom-8 -right-8 h-40 w-40 rounded-full bg-white/10 object-contain p-2 opacity-20'])
                            <div class="relative">
                                <div class="flex items-center justify-between border-b border-white/20 pb-4">
                                    <div class="flex items-center gap-2.5">
                                        @include('partials.celtech-logo', ['class' => 'h-8 w-8 rounded-full bg-white object-contain p-0.5'])
                                        <div>
                                            <h3 class="text-xs font-bold uppercase tracking-wide">CLCST Digital Pass</h3>
                                            <p class="text-[10px] text-[#AAF4B5]">Library Turnstile Verified</p>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center gap-1 rounded-full border border-[#AAF4B5]/40 bg-white/10 px-2 py-0.5 text-[10px] font-bold text-[#AAF4B5]"><span class="h-1.5 w-1.5 rounded-full bg-[#8FD79B]"></span>Active</span>
                                </div>
                                <div class="mt-4">
                                    <span class="block text-[10px] font-bold uppercase tracking-wider text-[#AAF4B5]">Authorized Patron</span>
                                    <p class="mt-1 font-display text-lg font-bold">Engr. Roberto M. Mendoza</p>
                                    <p class="text-xs text-white/80">College of Computer Studies - Faculty / Researcher</p>
                                    <p class="mt-1 text-[11px] text-white/70">ID: <span class="font-mono font-bold text-white">2024-10492-OL</span></p>
                                </div>
                                <div class="mt-5 rounded-xl bg-white p-4 text-center text-black shadow-inner">
                                    <div class="font-mono text-lg font-black tracking-[0.35em]">|||| |||| ||||| ||||</div>
                                    <div class="mt-2 border-t border-slate-100 pt-2 text-[10px] font-mono font-bold tracking-wider">CLCST*2018042*LIB</div>
                                </div>
                                <div class="mt-4 grid grid-cols-2 gap-2">
                                    <button class="rounded-lg bg-white/10 px-3 py-2 text-xs font-bold hover:bg-white/20" type="button">Enlarge</button>
                                    <button class="rounded-lg bg-[#FDC425] px-3 py-2 text-xs font-bold text-[#6D5200] hover:bg-[#F7BE1D]" type="button">Sync</button>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                            <div class="flex items-center gap-2 border-b border-[#CBD5E1] pb-3">
                                <span class="material-symbols-outlined text-[#065F2A]">policy</span>
                                <h3 class="font-display text-base font-bold">Circulation Guidelines</h3>
                            </div>
                            <ul class="mt-4 space-y-3 text-sm leading-6 text-[#475569]">
                                <li class="flex gap-2"><span class="material-symbols-outlined mt-0.5 shrink-0 text-base text-[#065F2A]">check_circle</span><span><strong>Loan Limits:</strong> 5 concurrent items for students; up to 15 items for permanent faculty.</span></li>
                                <li class="flex gap-2"><span class="material-symbols-outlined mt-0.5 shrink-0 text-base text-[#065F2A]">sync</span><span><strong>Renewal Policy:</strong> Maximum of 2 online renewals when no hold exists.</span></li>
                                <li class="flex gap-2"><span class="material-symbols-outlined mt-0.5 shrink-0 text-base text-[#B91C1C]">warning</span><span><strong>Overdue Penalties:</strong> Standard fines apply after the grace period.</span></li>
                            </ul>
                        </section>

                        <section class="rounded-xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                            <div class="flex items-center gap-2 border-b border-[#CBD5E1] pb-3">
                                <span class="material-symbols-outlined text-[#065F2A]">pin_drop</span>
                                <h3 class="font-display text-base font-bold">Physical Return Depots</h3>
                            </div>
                            <div class="mt-4 space-y-3">
                                <div class="rounded-lg border border-[#E2E8F0] bg-[#F8FAFC] p-3 text-sm">
                                    <p class="font-bold">Circulation Counter</p>
                                    <p class="text-xs text-[#64748B]">Monday to Friday - 8:00 AM to 5:00 PM</p>
                                </div>
                                <div class="rounded-lg border border-[#E2E8F0] bg-[#F8FAFC] p-3 text-sm">
                                    <p class="font-bold">24/7 Book Return Drop Box</p>
                                    <p class="text-xs text-[#64748B]">Gate 2 Main Entrance, East Wing</p>
                                </div>
                            </div>
                        </section>
                    </aside>
                </section>
            @elseif ($section === 'patrons')
                <section class="rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[760px] text-left text-sm">
                            <thead class="border-b border-[#E2E8F0] text-xs uppercase tracking-wider text-[#64748B]">
                                <tr>
                                    <th class="py-3">Patron</th>
                                    <th class="py-3">ID</th>
                                    <th class="py-3">Program</th>
                                    <th class="py-3">Loans</th>
                                    <th class="py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E2E8F0]">
                                @foreach ($patronRecords as $patron)
                                    <tr>
                                        <td class="py-4 font-bold">{{ $patron['name'] }}</td>
                                        <td class="py-4 font-mono text-xs">{{ $patron['id'] }}</td>
                                        <td class="py-4">{{ $patron['program'] }}</td>
                                        <td class="py-4">{{ $patron['loans'] }}</td>
                                        <td class="py-4"><span class="rounded-full bg-[#F0FDF4] px-2.5 py-1 text-xs font-bold text-[#166534]">{{ $patron['status'] }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @elseif ($section === 'catalog')
                <section class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                    @foreach ($catalogBooks as $book)
                        <article class="rounded-xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
                            <div class="flex gap-4">
                                <img class="h-28 w-20 rounded-lg border border-[#E2E8F0] object-cover shadow-sm" src="{{ $book['cover_url'] }}" alt="{{ $book['title'] }} cover">
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#A16207]">{{ $book['subject'] }}</span>
                                    <h2 class="mt-1 font-display text-lg font-bold">{{ $book['title'] }}</h2>
                                    <p class="text-sm text-[#475569]">by {{ $book['author'] }} - {{ $book['year'] }}</p>
                                    <p class="mt-2 text-xs text-[#64748B]">Call No: <strong class="text-[#0F172A]">{{ $book['call'] }}</strong></p>
                                    <p class="text-xs text-[#64748B]">Available copies: <strong class="text-[#065F2A]">{{ collect($book['copies'])->where('status', 'available')->count() }}</strong></p>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </section>
            @elseif ($section === 'reservations')
                <section class="rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
                    <h2 class="font-display text-xl font-bold">Active Reservation Queue</h2>
                    <div class="mt-4 space-y-3">
                        @forelse ($reservations as $reservation)
                            <div class="rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] p-4">
                                <p class="font-bold">{{ $reservation['title'] }}</p>
                                <p class="text-xs text-[#64748B]">Requested {{ $reservation['requested_at'] }}</p>
                            </div>
                        @empty
                            @foreach ($facultyRequests as $request)
                                <div class="rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] p-4">
                                    <p class="font-bold">{{ $request['course'] }}</p>
                                    <p class="text-sm text-[#475569]">{{ $request['request'] }}</p>
                                    <p class="mt-1 text-xs font-semibold text-[#065F2A]">{{ $request['status'] }}</p>
                                </div>
                            @endforeach
                        @endforelse
                    </div>
                </section>
            @else
                <section class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                    @foreach ([
                        ['icon' => 'description', 'title' => 'Daily Summary', 'text' => 'Review circulation counts, open fines, and catalog activity.'],
                        ['icon' => 'verified_user', 'title' => 'Access Control', 'text' => 'Keep staff-only pages limited to authorized library personnel.'],
                        ['icon' => 'support_agent', 'title' => 'Desk Support', 'text' => 'Use this area for library desk notes, help requests, and operating procedures.'],
                    ] as $card)
                        <article class="rounded-xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
                            <span class="flex h-11 w-11 items-center justify-center rounded-lg bg-[#DCFCE7] text-[#166534]">
                                <span class="material-symbols-outlined">{{ $card['icon'] }}</span>
                            </span>
                            <h2 class="mt-4 font-display text-lg font-bold">{{ $card['title'] }}</h2>
                            <p class="mt-2 text-sm leading-6 text-[#475569]">{{ $card['text'] }}</p>
                        </article>
                    @endforeach
                </section>
            @endif
        </main>
    </div>
</body>
</html>
