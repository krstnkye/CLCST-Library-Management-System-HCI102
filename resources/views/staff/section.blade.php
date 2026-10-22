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
    @include('partials.ui-motion')
</head>
<body class="h-full bg-[#F8FAFC] text-[#0F172A] antialiased">
    @php
        $faculty = auth()->user();
        $facultyDepartment = $faculty->program_or_department ?: 'Department not specified';
    @endphp
    <header class="sticky top-0 z-40 flex h-16 w-full items-center justify-between border-b border-[#CBD5E1] bg-white px-4 shadow-sm lg:px-6">
        <div class="flex items-center gap-3">
            @include('partials.celtech-logo', ['class' => 'flex h-10 w-10 items-center justify-center rounded-full bg-[#065F2A] text-sm font-extrabold text-white shadow-sm object-contain bg-white p-0.5'])
            <div>
                <span class="block font-display text-sm font-bold leading-none tracking-wide text-[#064E3B] sm:text-base">CLCST LIBRARY MANAGEMENT SYSTEM</span>
                <span class="mt-0.5 hidden text-[10px] font-bold uppercase tracking-wider text-[#64748B] sm:block">{{ $title }}</span>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a class="hidden rounded-lg border border-[#CBD5E1] px-3 py-2 text-xs font-bold text-[#334155] hover:bg-[#F8FAFC] sm:inline-flex" href="{{ route('faculty.dashboard') }}">Faculty Dashboard</a>
            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-[#065F2A] text-sm font-bold text-white">{{ $faculty->initials() }}</div>
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
                                <span>Borrowing</span>
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
                                Export Loan Record
                            </a>
                            <a class="inline-flex items-center gap-2 rounded-lg border border-[#CBD5E1] bg-white px-3.5 py-2 text-xs font-bold text-[#334155] shadow-sm hover:bg-[#F8FAFC]" href="{{ route('staff.section', 'support') }}">
                                <span class="material-symbols-outlined text-base">meeting_room</span>
                                Return Drop Box Info
                            </a>
                            <a class="inline-flex items-center gap-2 rounded-lg bg-[#065F2A] px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-[#064E3B]" href="{{ route('staff.section', 'reports') }}">
                                <span class="material-symbols-outlined text-base">autorenew</span>
                                Renew All Allowed
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
                        <p class="mt-4 text-xs leading-5 text-[#64748B]">Renewals are allowed when no other borrower has a reservation for the same copy.</p>
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
                                            <div><span class="block text-[10px] font-bold uppercase text-[#64748B]">Library Call Number</span><span class="font-medium text-[#0F172A]">{{ $loan['call'] }}</span></div>
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
                                            <span class="text-[11px] font-semibold text-[#64748B]">{{ $book['location'] ?? 'Main Library Shelves' }}</span>
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
                                            <p class="text-[10px] text-[#AAF4B5]">Library Entry Verified</p>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center gap-1 rounded-full border border-[#AAF4B5]/40 bg-white/10 px-2 py-0.5 text-[10px] font-bold text-[#AAF4B5]"><span class="h-1.5 w-1.5 rounded-full bg-[#8FD79B]"></span>Active</span>
                                </div>
                                <div class="mt-4">
                                    <span class="block text-[10px] font-bold uppercase tracking-wider text-[#AAF4B5]">Authorized User</span>
                                    <p class="mt-1 font-display text-lg font-bold">{{ $faculty->name }}</p>
                                    <p class="text-xs text-white/80">{{ $facultyDepartment }} - Faculty / Researcher</p>
                                    <p class="mt-1 text-[11px] text-white/70">ID: <span class="font-mono font-bold text-white">{{ $faculty->patron_id }}</span></p>
                                </div>
                                <div class="mt-5 rounded-xl bg-white p-4 text-center text-black shadow-inner">
                                    <div class="font-mono text-lg font-black tracking-[0.35em]">|||| |||| ||||| ||||</div>
                                    <div class="mt-2 border-t border-slate-100 pt-2 text-[10px] font-mono font-bold tracking-wider">{{ $faculty->patron_id }}</div>
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
                                <h3 class="font-display text-base font-bold">Borrowing Guidelines</h3>
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
                                    <p class="font-bold">Library Counter</p>
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
            @elseif ($section === 'feedback')
                @php
                    $feedbackRows = [
                        ['status' => 'In Progress', 'tone' => 'amber', 'title' => 'Shelves 2B HVAC and study carrel comfort issue', 'category' => 'Facility & Stack Environment', 'submitted' => 'Oct 08, 2026', 'body' => 'Feedback noting excess heat and inadequate airflow near QA76 Computer Science aisles, affecting faculty study carrels and preservation conditions.', 'response' => 'Facilities inspection scheduled with the Library Property Custodian.'],
                        ['status' => 'Resolved', 'tone' => 'green', 'title' => 'Open Online Library Access Access for Off-Campus Faculty', 'category' => 'Digital Online Resources Access', 'submitted' => 'Sep 29, 2026', 'body' => 'Request to integrate department faculty IDs with institutional OpenAthens federation for seamless remote access to IEEE Xplore and ScienceDirect journals.', 'response' => 'SAML 2.0 endpoint re-connectd with CLCST Faculty Directory.'],
                        ['status' => 'Resolved', 'tone' => 'blue', 'title' => 'Additional reserve copy for Clean Code', 'category' => 'Collection & Pickup Shelf', 'submitted' => 'Sep 20, 2026', 'body' => 'Requested another high-demand reserve copy for Software Engineering classes.', 'response' => 'Copy added to the library and linked to the course reserve shelf.'],
                    ];
                    $resolvedCount = collect($feedbackRows)->where('status', 'Resolved')->count();
                    $inProgressCount = collect($feedbackRows)->where('status', 'In Progress')->count();
                @endphp

                <section class="rounded-2xl border border-[#CBD5E1] bg-white p-6 shadow-sm lg:p-8">
                    <div class="flex flex-col justify-between gap-5 lg:flex-row lg:items-start">
                        <div>
                            <nav aria-label="Breadcrumb" class="mb-3 flex flex-wrap items-center gap-2 text-xs font-semibold text-[#64748B]">
                                <span>Faculty Services</span>
                                <span class="material-symbols-outlined text-sm">chevron_right</span>
                                <span class="font-bold text-[#065F2A]">Library Feedback & Evaluation Hub</span>
                            </nav>
                            <span class="inline-flex rounded-full bg-[#DCFCE7] px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-[#166534]">ISO 9001:2015 Quality Feedback</span>
                            <h1 class="mt-4 font-display text-3xl font-bold text-[#064E3B] md:text-4xl">Library Feedback & Academic Consultation</h1>
                            <p class="mt-3 max-w-3xl text-sm leading-6 text-[#475569]">Submit institutional evaluations, report facility or stack issues, suggest collection improvements, and rate instruction or research support services for continuous quality assurance.</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a class="inline-flex items-center gap-2 rounded-lg border border-[#CBD5E1] bg-white px-3 py-2 text-xs font-bold text-[#334155] hover:bg-[#F8FAFC]" href="{{ route('staff.section', 'history') }}"><span class="material-symbols-outlined text-base">download</span>Download Past Feedback</a>
                            <a class="inline-flex items-center gap-2 rounded-lg bg-[#065F2A] px-4 py-2 text-xs font-bold text-white hover:bg-[#064E3B]" href="#feedback-form"><span class="material-symbols-outlined text-base">add_circle</span>Submit New Evaluation</a>
                        </div>
                    </div>
                </section>

                <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ([
                        ['label' => 'Submitted Feedback', 'value' => count($feedbackRows) + 3, 'meta' => 'AY 2024-2025 records', 'icon' => 'rate_review'],
                        ['label' => 'In Progress', 'value' => $inProgressCount, 'meta' => 'Awaiting desk action', 'icon' => 'pending_actions'],
                        ['label' => 'Faculty Satisfaction Index', 'value' => '94%', 'meta' => 'Latest mid-semester survey', 'icon' => 'sentiment_satisfied'],
                        ['label' => 'Resolved Tickets', 'value' => $resolvedCount + 3, 'meta' => 'Closed with librarian response', 'icon' => 'verified'],
                    ] as $metric)
                        <article class="rounded-xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-[#64748B]">{{ $metric['label'] }}</p>
                                    <h2 class="mt-2 font-display text-3xl font-bold text-[#065F2A]">{{ $metric['value'] }}</h2>
                                    <p class="mt-1 text-xs text-[#64748B]">{{ $metric['meta'] }}</p>
                                </div>
                                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#DCFCE7] text-[#166534]"><span class="material-symbols-outlined">{{ $metric['icon'] }}</span></span>
                            </div>
                        </article>
                    @endforeach
                </section>

                <section class="grid grid-cols-1 gap-6 xl:grid-cols-12">
                    <div class="space-y-6 xl:col-span-8">
                        <section class="rounded-2xl border border-[#CBD5E1] bg-white p-5 shadow-sm" id="feedback-form">
                            <div class="flex items-center gap-2 border-b border-[#CBD5E1] pb-4">
                                <span class="material-symbols-outlined text-[#065F2A]">rate_review</span>
                                <h2 class="font-display text-xl font-bold text-[#064E3B]">Submit Faculty Feedback & Service Ticket</h2>
                            </div>
                            <form class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2" method="GET" action="{{ route('staff.section', 'feedback') }}">
                                <label class="space-y-1 text-xs font-bold text-[#0F172A]">Feedback Category <span class="text-[#B91C1C]">*</span>
                                    <select class="w-full rounded-lg border-[#CBD5E1] text-sm focus:border-[#065F2A] focus:ring-[#065F2A]">
                                        <option>Facility & Stack Environment</option>
                                        <option>Collection & Pickup Shelf</option>
                                        <option>Digital Online Resources Access</option>
                                        <option>Library Instruction / Research Support</option>
                                        <option>Library Desk Service</option>
                                    </select>
                                </label>
                                <label class="space-y-1 text-xs font-bold text-[#0F172A]">Priority Level
                                    <select class="w-full rounded-lg border-[#CBD5E1] text-sm focus:border-[#065F2A] focus:ring-[#065F2A]">
                                        <option>Normal faculty service request</option>
                                        <option>Urgent academic disruption</option>
                                        <option>Collection recommendation</option>
                                    </select>
                                </label>
                                <label class="space-y-1 text-xs font-bold text-[#0F172A]">Service Rating
                                    <div class="flex gap-2 rounded-lg border border-[#CBD5E1] bg-[#F8FAFC] p-2">
                                        @foreach ([1, 2, 3, 4, 5] as $star)
                                            <button class="flex h-9 w-9 items-center justify-center rounded-lg {{ $star <= 4 ? 'bg-[#FEF3C7] text-[#92400E]' : 'bg-white text-[#64748B]' }}" type="button"><span class="material-symbols-outlined">star</span></button>
                                        @endforeach
                                    </div>
                                </label>
                                <label class="space-y-1 text-xs font-bold text-[#0F172A]">Related Area
                                    <select class="w-full rounded-lg border-[#CBD5E1] text-sm focus:border-[#065F2A] focus:ring-[#065F2A]">
                                        <option>Shelves 2B - Computer Science</option>
                                        <option>Library Desk</option>
                                        <option>Faculty Pickup Shelf</option>
                                        <option>Digital Repositories</option>
                                    </select>
                                </label>
                                <label class="space-y-1 text-xs font-bold text-[#0F172A] md:col-span-2">Detailed Comments / Recommendations
                                    <textarea class="min-h-32 w-full rounded-lg border-[#CBD5E1] text-sm focus:border-[#065F2A] focus:ring-[#065F2A]" placeholder="Describe the issue, recommendation, requested improvement, or service evaluation details."></textarea>
                                </label>
                                <div class="rounded-xl border border-[#BBF7D0] bg-[#F0FDF4] p-4 md:col-span-2">
                                    <span class="block text-sm font-bold text-[#166534]">Priority Faculty Verification</span>
                                    <span class="mt-1 block text-xs text-[#166534]">Submit with authenticated credentials: {{ $faculty->name }}</span>
                                </div>
                                <div class="flex justify-end gap-3 md:col-span-2">
                                    <button class="rounded-lg border border-[#CBD5E1] px-4 py-2 text-xs font-bold text-[#334155] hover:bg-[#F8FAFC]" type="reset">Clear Draft</button>
                                    <button class="inline-flex items-center gap-2 rounded-lg bg-[#065F2A] px-5 py-2.5 text-xs font-bold text-white hover:bg-[#064E3B]" type="submit"><span class="material-symbols-outlined text-base">send</span>Submit Feedback to Chief Librarian</button>
                                </div>
                            </form>
                        </section>

                        <section class="rounded-2xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                            <div class="flex flex-col justify-between gap-3 border-b border-[#CBD5E1] pb-4 sm:flex-row sm:items-center">
                                <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[#065F2A]">fact_check</span><h2 class="font-display text-xl font-bold text-[#064E3B]">Feedback History & Resolution Status Record</h2></div>
                                <div class="flex gap-2 text-xs font-bold">
                                    <a class="rounded-lg bg-[#065F2A] px-3 py-1.5 text-white" href="{{ route('staff.section', 'feedback') }}">All (6)</a>
                                    <a class="rounded-lg border border-[#CBD5E1] px-3 py-1.5 text-[#334155]" href="{{ route('staff.section', 'feedback') }}">In Progress (1)</a>
                                    <a class="rounded-lg border border-[#CBD5E1] px-3 py-1.5 text-[#334155]" href="{{ route('staff.section', 'feedback') }}">Resolved (5)</a>
                                </div>
                            </div>
                            <div class="mt-4 space-y-4">
                                @foreach ($feedbackRows as $row)
                                    <article class="rounded-xl border border-[#CBD5E1] bg-[#F8FAFC] p-4">
                                        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
                                            <div>
                                                <div class="flex flex-wrap gap-2">
                                                    <span class="{{ $row['tone'] === 'amber' ? 'bg-[#FEF3C7] text-[#92400E]' : ($row['tone'] === 'green' ? 'bg-[#DCFCE7] text-[#166534]' : 'bg-[#DBEAFE] text-[#1D4ED8]') }} rounded-full px-2 py-0.5 text-[10px] font-bold uppercase">{{ $row['status'] }}</span>
                                                    <span class="rounded-full bg-white px-2 py-0.5 text-[10px] font-bold uppercase text-[#64748B]">{{ $row['category'] }}</span>
                                                </div>
                                                <h3 class="mt-2 font-display text-base font-bold text-[#0F172A]">{{ $row['title'] }}</h3>
                                                <p class="mt-2 text-sm leading-6 text-[#475569]">{{ $row['body'] }}</p>
                                                <p class="mt-3 rounded-lg border border-[#CBD5E1] bg-white p-3 text-xs leading-5 text-[#334155]"><strong>Librarian response:</strong> {{ $row['response'] }}</p>
                                            </div>
                                            <span class="shrink-0 text-xs font-semibold text-[#64748B]">{{ $row['submitted'] }}</span>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    </div>

                    <aside class="space-y-6 xl:col-span-4">
                        <section class="rounded-2xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                            <div class="rounded-2xl bg-[#064E3B] p-5 text-white">
                                <div class="flex items-center gap-3">
                                    @include('partials.celtech-logo', ['class' => 'flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-white p-1'])
                                    <div>
                                        <p class="text-xs font-bold uppercase text-[#BBF7D0]">Digital Faculty Pass</p>
                                        <h3 class="font-display text-lg font-bold">{{ $faculty->name }}</h3>
                                        <p class="text-xs text-[#D1FAE5]">Authenticated Faculty Evaluator</p>
                                    </div>
                                </div>
                                <div class="mt-5 rounded-xl bg-white p-4 text-center text-[#0F172A]">
                                    <div class="mx-auto h-10 w-full max-w-xs bg-[repeating-linear-gradient(90deg,#0F172A_0_2px,transparent_2px_5px,#0F172A_5px_7px,transparent_7px_10px)]"></div>
                                    <p class="mt-2 font-mono text-xs font-bold tracking-[0.25em]">{{ $faculty->patron_id }}</p>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-2xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                            <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[#92400E]">workspace_premium</span><h3 class="font-display text-lg font-bold text-[#064E3B]">AY 2024-2025 Library Service Survey</h3></div>
                            <p class="mt-3 text-sm leading-6 text-[#64748B]">Current faculty satisfaction checkpoint for collection support, digital access, facilities, and librarian assistance.</p>
                            <div class="mt-4 space-y-3">
                                @foreach ([['Collection support', '96%'], ['Digital access', '92%'], ['Instruction support', '95%'], ['Facility readiness', '88%']] as $score)
                                    <div>
                                        <div class="mb-1 flex justify-between text-xs font-bold"><span>{{ $score[0] }}</span><span>{{ $score[1] }}</span></div>
                                        <div class="h-2 overflow-hidden rounded-full bg-[#E2E8F0]"><div class="h-full rounded-full bg-[#065F2A]" style="width: {{ $score[1] }}"></div></div>
                                    </div>
                                @endforeach
                            </div>
                            <a class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg border border-[#CBD5E1] bg-[#F8FAFC] px-3 py-2 text-xs font-bold text-[#065F2A] hover:bg-[#E2E8F0]" href="#feedback-form"><span class="material-symbols-outlined text-base">edit</span>Update Mid-Semester Rating</a>
                        </section>

                        <section class="rounded-2xl border border-[#CBD5E1] bg-[#F0FDF4] p-5 shadow-sm">
                            <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[#065F2A]">support_agent</span><h3 class="font-display text-lg font-bold text-[#064E3B]">Feedback Escalation Contact</h3></div>
                            <div class="mt-4 space-y-2 text-sm text-[#334155]">
                                <p>Ms. Maria Santos - Chief Librarian</p>
                                <p>Faculty Library Committee Chairperson</p>
                                <p>Engineering Faculty Lounge</p>
                                <p>library@clcst.edu.ph</p>
                            </div>
                        </section>
                    </aside>
                </section>
            @elseif ($section === 'profile')
                @php
                    $profileStats = [
                        ['label' => 'Active Loans', 'value' => count($activeLoans), 'meta' => '15-volume faculty quota', 'icon' => 'menu_book'],
                        ['label' => 'Reserve Requests', 'value' => count($facultyRequests), 'meta' => 'Academic service queue', 'icon' => 'school'],
                        ['label' => 'Clearance Status', 'value' => '100%', 'meta' => 'No unresolved account flags', 'icon' => 'verified'],
                        ['label' => 'Access Zones', 'value' => 4, 'meta' => 'Faculty lounge, stacks, media, archive', 'icon' => 'key'],
                    ];
                @endphp

                <section class="rounded-2xl border border-[#CBD5E1] bg-white p-6 shadow-sm lg:p-8">
                    <div class="flex flex-col justify-between gap-5 lg:flex-row lg:items-start">
                        <div>
                            <nav aria-label="Breadcrumb" class="mb-3 flex flex-wrap items-center gap-2 text-xs font-semibold text-[#64748B]">
                                <span>Faculty Services</span>
                                <span class="material-symbols-outlined text-sm">chevron_right</span>
                                <span class="font-bold text-[#065F2A]">My Profile</span>
                            </nav>
                            <span class="inline-flex rounded-full bg-[#DCFCE7] px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-[#166534]">Verified Academic Library User</span>
                            <h1 class="mt-4 font-display text-3xl font-bold text-[#064E3B] md:text-4xl">Faculty Profile & Academic Library User Documents</h1>
                            <p class="mt-3 max-w-3xl text-sm leading-6 text-[#475569]">Manage faculty identity details, library privilege status, Barcode card sync, clearance records, notification settings, and secure institutional access.</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a class="inline-flex items-center gap-2 rounded-lg border border-[#CBD5E1] bg-white px-3 py-2 text-xs font-bold text-[#334155] hover:bg-[#F8FAFC]" href="{{ route('staff.section', 'notifications') }}"><span class="material-symbols-outlined text-base">notifications</span>Alerts</a>
                            <a class="inline-flex items-center gap-2 rounded-lg border border-[#CBD5E1] bg-white px-3 py-2 text-xs font-bold text-[#334155] hover:bg-[#F8FAFC]" href="{{ route('staff.section', 'support') }}"><span class="material-symbols-outlined text-base">download</span>Download Profile</a>
                            <a class="inline-flex items-center gap-2 rounded-lg bg-[#065F2A] px-4 py-2 text-xs font-bold text-white hover:bg-[#064E3B]" href="#profile-editor"><span class="material-symbols-outlined text-base">edit</span>Edit Profile Info</a>
                        </div>
                    </div>
                </section>

                <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($profileStats as $stat)
                        <article class="rounded-xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-[#64748B]">{{ $stat['label'] }}</p>
                                    <h2 class="mt-2 font-display text-3xl font-bold text-[#065F2A]">{{ $stat['value'] }}</h2>
                                    <p class="mt-1 text-xs text-[#64748B]">{{ $stat['meta'] }}</p>
                                </div>
                                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#DCFCE7] text-[#166534]"><span class="material-symbols-outlined">{{ $stat['icon'] }}</span></span>
                            </div>
                        </article>
                    @endforeach
                </section>

                <section class="flex gap-2 overflow-x-auto border-b border-[#CBD5E1]">
                    @foreach ([['person', 'Personal & Academic Info'], ['badge', 'Digital Faculty Pass'], ['verified_user', 'Clearance'], ['notifications', 'Alerts'], ['lock', 'Security']] as $tab)
                        <a class="{{ $loop->first ? 'border-[#065F2A] bg-white text-[#065F2A]' : 'border-transparent text-[#64748B] hover:bg-[#F8FAFC]' }} inline-flex shrink-0 items-center gap-1.5 rounded-t-lg border-t-2 px-4 py-2 text-xs font-bold" href="{{ $loop->first ? '#profile-editor' : route('staff.section', 'profile') }}">
                            <span class="material-symbols-outlined text-base">{{ $tab[0] }}</span>{{ $tab[1] }}
                        </a>
                    @endforeach
                </section>

                <section class="grid grid-cols-1 gap-6 xl:grid-cols-12">
                    <div class="space-y-6 xl:col-span-8">
                        <section class="overflow-hidden rounded-2xl border border-[#CBD5E1] bg-white shadow-sm">
                            <div class="bg-[#064E3B] p-6 text-white">
                                <div class="flex flex-col gap-5 md:flex-row md:items-center">
                                    <div class="flex h-24 w-24 shrink-0 items-center justify-center rounded-2xl border-2 border-white/70 bg-white p-2 shadow-md">
                                        @include('partials.celtech-logo', ['class' => 'h-full w-full object-contain'])
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs font-bold uppercase tracking-wider text-[#FACC15]">CLCST Faculty Digital Library Pass</p>
                                        <h2 class="mt-1 font-display text-2xl font-bold">{{ $faculty->name }}</h2>
                                        <p class="mt-1 text-sm text-[#D1FAE5]">{{ $facultyDepartment }}</p>
                                        <div class="mt-3 flex flex-wrap gap-2 text-[10px] font-bold uppercase">
                                            <span class="rounded-full bg-white/15 px-2.5 py-1">Faculty ID: {{ $faculty->patron_id }}</span>
                                            <span class="rounded-full bg-white/15 px-2.5 py-1">Active Account</span>
                                            <span class="rounded-full bg-[#FACC15] px-2.5 py-1 text-[#5A4300]">Full Access</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-6 rounded-xl bg-white p-4 text-center text-[#0F172A]">
                                    <div class="mx-auto h-12 w-full max-w-md bg-[repeating-linear-gradient(90deg,#0F172A_0_2px,transparent_2px_5px,#0F172A_5px_9px,transparent_9px_11px,#0F172A_11px_14px,transparent_14px_18px)]"></div>
                                    <p class="mt-2 font-mono text-xs font-bold tracking-[0.25em]">{{ $faculty->patron_id }}</p>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-2xl border border-[#CBD5E1] bg-white p-5 shadow-sm" id="profile-editor">
                            <div class="flex items-center justify-between gap-3 border-b border-[#CBD5E1] pb-4">
                                <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[#065F2A]">id_card</span><h2 class="font-display text-xl font-bold text-[#064E3B]">Academic & Institutional Record</h2></div>
                                <a class="inline-flex items-center gap-1 text-xs font-bold text-[#065F2A] hover:underline" href="{{ route('staff.section', 'support') }}"><span class="material-symbols-outlined text-base">edit</span>Request Update</a>
                            </div>
                            <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2">
                                @foreach ([
                                    ['Account Type', 'Faculty / Academic Staff'],
                                    ['School Email', $faculty->email],
                                    ['School or Employee ID', $faculty->patron_id],
                                    ['Mobile Number', $faculty->mobile_number ?: 'Not provided'],
                                    ['Academic Department', $facultyDepartment],
                                    ['Account Status', ucfirst($faculty->status)],
                                ] as $field)
                                    <div class="rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] p-4">
                                        <label class="block text-[10px] font-bold uppercase tracking-wider text-[#64748B]">{{ $field[0] }}</label>
                                        <p class="mt-1 text-sm font-semibold text-[#0F172A]">{{ $field[1] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </section>

                        <section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                            <article class="rounded-2xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                                <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[#065F2A]">fact_check</span><h3 class="font-display text-lg font-bold text-[#064E3B]">Borrowing Access & Book Limit</h3></div>
                                <p class="mt-3 text-sm leading-6 text-[#64748B]">Faculty borrowing access are active for instructional and research titles.</p>
                                <div class="mt-4 space-y-2 text-sm">
                                    <div class="flex justify-between border-b border-[#E2E8F0] py-2"><span>Loan quota</span><strong>2 / 15 volumes used</strong></div>
                                    <div class="flex justify-between border-b border-[#E2E8F0] py-2"><span>Loan period</span><strong>60 days</strong></div>
                                    <div class="flex justify-between py-2"><span>Reserve desk access</span><strong class="text-[#065F2A]">Enabled</strong></div>
                                </div>
                            </article>
                            <article class="rounded-2xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                                <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[#065F2A]">verified</span><h3 class="font-display text-lg font-bold text-[#064E3B]">Academic Library Clearance</h3></div>
                                <p class="mt-3 text-sm leading-6 text-[#64748B]">Official verification for Registrar, faculty merit promotion, and institutional quality assurance audit.</p>
                                <div class="mt-4 rounded-xl border border-[#BBF7D0] bg-[#F0FDF4] p-4">
                                    <p class="font-bold text-[#166534]">Certified Cleared</p>
                                    <p class="mt-1 text-xs text-[#166534]">No unreturned materials, fines, or unresolved circulation records.</p>
                                </div>
                            </article>
                        </section>

                        <section class="rounded-2xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                            <div class="flex flex-col justify-between gap-3 border-b border-[#CBD5E1] pb-4 sm:flex-row sm:items-center">
                                <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[#065F2A]">contactless</span><h3 class="font-display text-lg font-bold text-[#064E3B]">Physical Barcode Library Entry Card Sync</h3></div>
                                <span class="rounded-full bg-[#DCFCE7] px-2.5 py-1 text-[10px] font-bold uppercase text-[#166534]">Connected</span>
                            </div>
                            <div class="mt-4 grid gap-4 md:grid-cols-3">
                                <div class="rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] p-4"><p class="text-[10px] font-bold uppercase text-[#64748B]">Card Serial</p><p class="mt-1 font-mono text-sm font-bold">{{ $faculty->patron_id }}</p></div>
                                <div class="rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] p-4"><p class="text-[10px] font-bold uppercase text-[#64748B]">Last Sync</p><p class="mt-1 text-sm font-bold">Oct 09, 2026 - 9:44 AM</p></div>
                                <div class="rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] p-4"><p class="text-[10px] font-bold uppercase text-[#64748B]">Access Levels</p><p class="mt-1 text-sm font-bold">Faculty Lounge, Shelves 1-4, Media Room, Archival Vault</p></div>
                            </div>
                        </section>
                    </div>

                    <aside class="space-y-6 xl:col-span-4">
                        <section class="rounded-2xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                            <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[#065F2A]">notifications_active</span><h3 class="font-display text-lg font-bold text-[#064E3B]">Borrowing & Collection Alerts</h3></div>
                            <div class="mt-4 space-y-3">
                                @foreach ([['Due date watch', 'Clean Code due in 2 days'], ['Reserve queue', '3 active academic requests'], ['Collection updates', '2 recommended titles in review']] as $alert)
                                    <a class="block rounded-xl border border-[#CBD5E1] bg-[#F8FAFC] p-3 hover:bg-[#E2E8F0]" href="{{ route('staff.section', 'notifications') }}">
                                        <p class="text-sm font-bold text-[#0F172A]">{{ $alert[0] }}</p>
                                        <p class="mt-1 text-xs text-[#64748B]">{{ $alert[1] }}</p>
                                    </a>
                                @endforeach
                            </div>
                        </section>

                        <section class="rounded-2xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                            <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[#065F2A]">lock</span><h3 class="font-display text-lg font-bold text-[#064E3B]">Security, SSO & Access Documents</h3></div>
                            <div class="mt-4 space-y-3 text-sm">
                                <div class="flex justify-between border-b border-[#E2E8F0] py-2"><span>SSO domain</span><strong>clcst.edu.ph</strong></div>
                                <div class="flex justify-between border-b border-[#E2E8F0] py-2"><span>Two-factor status</span><strong class="text-[#065F2A]">Enabled</strong></div>
                                <div class="flex justify-between py-2"><span>Last login</span><strong>{{ $faculty->last_login_at?->format('M d, Y g:i A') ?? 'Not recorded' }}</strong></div>
                            </div>
                            <div class="mt-4 flex gap-2">
                                <a class="flex-1 rounded-lg border border-[#CBD5E1] px-3 py-2 text-center text-xs font-bold text-[#334155] hover:bg-[#F8FAFC]" href="{{ route('staff.section', 'support') }}">Reset PIN</a>
                                <a class="flex-1 rounded-lg bg-[#065F2A] px-3 py-2 text-center text-xs font-bold text-white hover:bg-[#064E3B]" href="{{ route('staff.section', 'settings') }}">Manage SSO</a>
                            </div>
                        </section>

                        <section class="rounded-2xl border border-[#CBD5E1] bg-[#F0FDF4] p-5 shadow-sm">
                            <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[#065F2A]">support_agent</span><h3 class="font-display text-lg font-bold text-[#064E3B]">Profile Support</h3></div>
                            <p class="mt-3 text-sm leading-6 text-[#475569]">For rank, department, contact, or clearance corrections, send a verified request to the library desk.</p>
                            <a class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-[#065F2A] px-3 py-2 text-xs font-bold text-white hover:bg-[#064E3B]" href="{{ route('staff.section', 'support') }}"><span class="material-symbols-outlined text-base">chat</span>Request Profile Update</a>
                        </section>
                    </aside>
                </section>
            @elseif ($section === 'patrons')
                <section class="rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[760px] text-left text-sm">
                            <thead class="border-b border-[#E2E8F0] text-xs uppercase tracking-wider text-[#64748B]">
                                <tr>
                                    <th class="py-3">Library User</th>
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
                @php
                    $recommendedBooks = [
                        ['status' => 'Priority Acquisition', 'tone' => 'green', 'title' => 'Designing Data-Intensive Applications', 'author' => 'Martin Kleppmann', 'publisher' => 'O\'Reilly Media', 'isbn' => '978-1449373320', 'course' => 'CS401 Advanced Database Systems', 'copies' => 4, 'amount' => 'PHP 12,800.00', 'note' => 'Primary reference for distributed data systems, replication, and stream processing modules.'],
                        ['status' => 'Under Dean Review', 'tone' => 'amber', 'title' => 'Database Internals: A Deep Dive into How Distributed Data Systems Work', 'author' => 'Alex Petrov', 'publisher' => 'O\'Reilly Media', 'isbn' => '978-1492040347', 'course' => 'CS302 Database Systems & Architecture', 'copies' => 2, 'amount' => 'PHP 8,900.00', 'note' => 'Supplemental reading for database normalization and distributed storage engines.'],
                        ['status' => 'Completed - On Shelves Shelf', 'tone' => 'blue', 'title' => 'Clean Architecture: A Craftsman\'s Guide to Software Structure and Design', 'author' => 'Robert C. Martin', 'publisher' => 'Prentice Hall', 'isbn' => 'CLCST-ACQ-2024-412', 'course' => 'Software Engineering Curriculum', 'copies' => 4, 'amount' => 'Borrowing Ready', 'note' => 'Four copies received and added to the library for faculty reserve and 14-day checkout.'],
                        ['status' => 'Peer Request - Needs Endorsement', 'tone' => 'purple', 'title' => 'Cloud Native Patterns: Designing Change-Tolerant Software', 'author' => 'Cornelia Davis', 'publisher' => 'Manning Publications', 'isbn' => '978-1617294297', 'course' => 'CS / Software Engineering Elective', 'copies' => 2, 'amount' => 'PHP 7,400.00', 'note' => 'Requires chair endorsement to qualify for CICT allocated departmental book budget.'],
                    ];
                    $priorityCount = collect($recommendedBooks)->where('tone', 'green')->count();
                    $reviewCount = collect($recommendedBooks)->whereIn('tone', ['amber', 'purple'])->count();
                    $completedCount = collect($recommendedBooks)->where('tone', 'blue')->count();
                @endphp

                <section class="rounded-2xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                    <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-start">
                        <div>
                            <nav aria-label="Breadcrumb" class="mb-2 flex items-center gap-2 text-xs font-medium text-[#64748B]">
                                <span>Academic Portal</span>
                                <span class="material-symbols-outlined text-sm">chevron_right</span>
                                <span>Faculty Services</span>
                                <span class="material-symbols-outlined text-sm">chevron_right</span>
                                <span class="font-bold text-[#065F2A]">Recommend a Book</span>
                            </nav>
                            <span class="inline-flex rounded-full bg-[#DCFCE7] px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-[#166534]">Faculty Collection Book Requests</span>
                            <h1 class="mt-3 font-display text-3xl font-bold text-[#064E3B]">Recommend a Book & Collection Book Requests</h1>
                            <p class="mt-2 max-w-4xl text-sm leading-6 text-[#475569]">Submit syllabus-aligned titles, track department endorsements, and monitor acquisition status for CLCST library collection development.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <a class="inline-flex items-center gap-2 rounded-lg border border-[#CBD5E1] bg-white px-3.5 py-2 text-xs font-bold text-[#334155] shadow-sm hover:bg-[#F8FAFC]" href="{{ route('books.catalog') }}">
                                <span class="material-symbols-outlined text-base">search</span>
                                Search Books First
                            </a>
                            <a class="inline-flex items-center gap-2 rounded-lg border border-[#065F2A] px-3.5 py-2 text-xs font-bold text-[#065F2A] hover:bg-[#DCFCE7]" href="{{ route('staff.section', 'support') }}">
                                <span class="material-symbols-outlined text-base">policy</span>
                                Acquisition Policy
                            </a>
                            <a class="inline-flex items-center gap-2 rounded-lg bg-[#065F2A] px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-[#064E3B]" href="#recommendation-form">
                                <span class="material-symbols-outlined text-base">add_circle</span>
                                Submit Acquisition
                            </a>
                        </div>
                    </div>
                </section>

                <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <article class="rounded-xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div><p class="text-[10px] font-bold uppercase tracking-wider text-[#64748B]">Active Recommendations</p><h2 class="mt-1 font-display text-2xl font-bold text-[#065F2A]">{{ count($recommendedBooks) }} <span class="text-sm font-medium text-[#475569]">titles</span></h2></div>
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#DCFCE7] text-[#065F2A]"><span class="material-symbols-outlined">recommend</span></span>
                        </div>
                        <p class="mt-4 text-xs leading-5 text-[#64748B]">Faculty-submitted titles for procurement and collection review.</p>
                    </article>
                    <article class="rounded-xl border border-[#BBF7D0] bg-[#F0FDF4] p-5 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div><p class="text-[10px] font-bold uppercase tracking-wider text-[#166534]">Priority</p><h2 class="mt-1 font-display text-2xl font-bold text-[#166534]">{{ $priorityCount }} <span class="text-sm font-medium">for purchase</span></h2></div>
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#DCFCE7] text-[#065F2A]"><span class="material-symbols-outlined">shopping_cart_checkout</span></span>
                        </div>
                        <p class="mt-4 text-xs leading-5 text-[#166534]">Syllabus and CHED-aligned titles with immediate instructional value.</p>
                    </article>
                    <article class="rounded-xl border border-[#FACC15] bg-[#FFFBEB] p-5 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div><p class="text-[10px] font-bold uppercase tracking-wider text-[#92400E]">Under Review</p><h2 class="mt-1 font-display text-2xl font-bold text-[#92400E]">{{ $reviewCount }} <span class="text-sm font-medium">pending</span></h2></div>
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#FACC15] text-[#6D5200]"><span class="material-symbols-outlined">hourglass_top</span></span>
                        </div>
                        <p class="mt-4 text-xs leading-5 text-[#92400E]">Awaiting dean, committee, or department endorsement.</p>
                    </article>
                    <article class="rounded-xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div><p class="text-[10px] font-bold uppercase tracking-wider text-[#64748B]">Completed</p><h2 class="mt-1 font-display text-2xl font-bold text-[#002E97]">{{ $completedCount }} <span class="text-sm font-medium text-[#475569]">shelved</span></h2></div>
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#E2E7FF] text-[#002E97]"><span class="material-symbols-outlined">check_circle</span></span>
                        </div>
                        <p class="mt-4 text-xs leading-5 text-[#64748B]">Titles already added to the library and discoverable from Book Catalog.</p>
                    </article>
                </section>

                <section class="grid grid-cols-1 gap-6 xl:grid-cols-12">
                    <div class="space-y-5 xl:col-span-8">
                        <section id="recommendation-form" class="rounded-xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                            <div class="flex flex-col justify-between gap-3 lg:flex-row lg:items-center">
                                <div>
                                    <h2 class="font-display text-xl font-bold text-[#0F172A]">New Acquisition Recommendation</h2>
                                    <p class="text-xs text-[#64748B]">Prepared for faculty syllabus, CHED CMO, and collection committee review.</p>
                                </div>
                                <span class="rounded-full bg-[#F0FDF4] px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-[#166534]">Q4 Acquisition Cycle Open</span>
                            </div>
                            <form class="mt-5 grid grid-cols-1 gap-4 lg:grid-cols-2" method="GET" action="{{ route('books.catalog') }}">
                                <label class="block"><span class="text-xs font-bold text-[#0F172A]">Book Title</span><input class="mt-1 w-full rounded-lg border-[#CBD5E1] text-sm focus:border-[#065F2A] focus:ring-[#065F2A]" name="q" placeholder="Example: Designing Data-Intensive Applications"></label>
                                <label class="block"><span class="text-xs font-bold text-[#0F172A]">Author / Editor</span><input class="mt-1 w-full rounded-lg border-[#CBD5E1] text-sm focus:border-[#065F2A] focus:ring-[#065F2A]" placeholder="Author name"></label>
                                <label class="block"><span class="text-xs font-bold text-[#0F172A]">ISBN / Edition</span><input class="mt-1 w-full rounded-lg border-[#CBD5E1] text-sm focus:border-[#065F2A] focus:ring-[#065F2A]" placeholder="978-0000000000 / latest edition"></label>
                                <label class="block"><span class="text-xs font-bold text-[#0F172A]">Course / Program Alignment</span><select class="mt-1 w-full rounded-lg border-[#CBD5E1] text-sm focus:border-[#065F2A] focus:ring-[#065F2A]"><option>BSCS / Software Engineering</option><option>BSIT / Database Systems</option><option>BSCpE / Embedded Systems</option><option>General Education</option></select></label>
                                <label class="block"><span class="text-xs font-bold text-[#0F172A]">Recommended Copies</span><input class="mt-1 w-full rounded-lg border-[#CBD5E1] text-sm focus:border-[#065F2A] focus:ring-[#065F2A]" min="1" placeholder="2" type="number"></label>
                                <label class="block"><span class="text-xs font-bold text-[#0F172A]">Estimated Budget</span><input class="mt-1 w-full rounded-lg border-[#CBD5E1] text-sm focus:border-[#065F2A] focus:ring-[#065F2A]" placeholder="PHP 8,500.00"></label>
                                <label class="block lg:col-span-2"><span class="text-xs font-bold text-[#0F172A]">Academic Justification</span><textarea class="mt-1 w-full rounded-lg border-[#CBD5E1] text-sm focus:border-[#065F2A] focus:ring-[#065F2A]" placeholder="Explain syllabus relevance, accreditation need, or student demand..." rows="4"></textarea></label>
                                <div class="flex flex-wrap justify-end gap-2 lg:col-span-2">
                                    <button class="rounded-lg border border-[#CBD5E1] px-4 py-2 text-xs font-bold text-[#334155] hover:bg-[#F8FAFC]" type="reset">Clear Form</button>
                                    <button class="rounded-lg bg-[#065F2A] px-4 py-2 text-xs font-bold text-white hover:bg-[#064E3B]" type="submit">Check Catalog & Submit</button>
                                </div>
                            </form>
                        </section>

                        <section class="rounded-xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                            <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                                <div><h2 class="font-display text-xl font-bold text-[#0F172A]">Recommendation Tracker</h2><p class="text-xs text-[#64748B]">Live acquisition workflow from request to stacks shelving.</p></div>
                                <div class="flex flex-wrap gap-2">
                                    <span class="rounded-full bg-[#065F2A] px-3 py-1.5 text-xs font-bold text-white">All</span>
                                    <span class="rounded-full border border-[#CBD5E1] bg-[#F8FAFC] px-3 py-1.5 text-xs font-bold text-[#334155]">In Review</span>
                                    <span class="rounded-full border border-[#CBD5E1] bg-[#F8FAFC] px-3 py-1.5 text-xs font-bold text-[#334155]">Completed</span>
                                </div>
                            </div>
                            <div class="mt-5 space-y-4">
                                @foreach ($recommendedBooks as $item)
                                    @php
                                        $toneClass = match ($item['tone']) {
                                            'green' => 'bg-[#DCFCE7] text-[#166534]',
                                            'amber' => 'bg-[#FEF3C7] text-[#92400E]',
                                            'blue' => 'bg-[#E2E7FF] text-[#002E97]',
                                            default => 'bg-[#F3E8FF] text-[#6B21A8]',
                                        };
                                        $coverClass = match ($item['tone']) {
                                            'green' => 'bg-[#065F2A] text-white',
                                            'amber' => 'bg-[#785A00] text-white',
                                            'blue' => 'bg-[#002E97] text-white',
                                            default => 'bg-[#0041CC] text-white',
                                        };
                                    @endphp
                                    <article class="rounded-xl border border-[#CBD5E1] bg-white p-5 shadow-sm transition-all hover:border-[#707A6F]">
                                        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
                                            <div class="flex gap-4">
                                                <div class="{{ $coverClass }} flex h-28 w-20 shrink-0 flex-col justify-between rounded-lg border border-[#CBD5E1] p-2 shadow-sm">
                                                    <span class="text-[9px] font-mono uppercase opacity-80">{{ Str::limit($item['publisher'], 12, '') }}</span>
                                                    <span class="line-clamp-3 text-[10px] font-bold leading-tight">{{ $item['title'] }}</span>
                                                    <span class="text-[8px] opacity-80">{{ Str::limit($item['author'], 16) }}</span>
                                                </div>
                                                <div>
                                                    <div class="mb-1 flex flex-wrap items-center gap-2">
                                                        <span class="{{ $toneClass }} rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider">{{ $item['status'] }}</span>
                                                        <span class="rounded bg-[#F8FAFC] px-2 py-0.5 text-[10px] font-bold text-[#475569]">{{ $item['course'] }}</span>
                                                    </div>
                                                    <h3 class="font-display text-lg font-bold text-[#0F172A]">{{ $item['title'] }}</h3>
                                                    <p class="mt-1 text-sm text-[#64748B]">by <span class="font-semibold text-[#334155]">{{ $item['author'] }}</span> - {{ $item['publisher'] }} - ISBN: <span class="font-mono text-[#0F172A]">{{ $item['isbn'] }}</span></p>
                                                    <p class="mt-3 rounded-lg border border-[#CBD5E1] bg-[#F8FAFC] p-3 text-sm leading-6 text-[#475569]"><strong class="text-[#065F2A]">Justification:</strong> {{ $item['note'] }}</p>
                                                </div>
                                            </div>
                                            <div class="text-left sm:text-right">
                                                <span class="text-xs text-[#64748B]">Recommended: {{ $item['copies'] }} {{ Str::plural('Copy', $item['copies']) }}</span>
                                                <span class="mt-1 block font-display text-lg font-bold text-[#0F172A]">{{ $item['amount'] }}</span>
                                            </div>
                                        </div>
                                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-[#E2E8F0] pt-3">
                                            <span class="flex items-center gap-1.5 text-xs font-medium text-[#64748B]"><span class="material-symbols-outlined text-base">{{ $item['tone'] === 'blue' ? 'verified' : 'schedule' }}</span>{{ $item['tone'] === 'blue' ? 'Verified by Head Cataloger M. Santos' : 'Academic committee workflow in progress' }}</span>
                                            <div class="flex flex-wrap gap-2">
                                                <a class="rounded-lg border border-[#CBD5E1] px-3 py-1.5 text-xs font-bold text-[#334155] hover:bg-[#F8FAFC]" href="{{ route('books.catalog', ['q' => $item['title']]) }}">View Book Match</a>
                                                <a class="rounded-lg bg-[#065F2A] px-3 py-1.5 text-xs font-bold text-white hover:bg-[#064E3B]" href="{{ route('staff.section', 'support') }}">{{ $item['tone'] === 'purple' ? 'Endorse Book' : 'Update Request' }}</a>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    </div>

                    <aside class="space-y-4 xl:col-span-4">
                        <section class="relative overflow-hidden rounded-2xl border border-[#064E3B] bg-gradient-to-br from-[#135D2E] to-[#072C15] p-5 text-white shadow-md">
                            @include('partials.celtech-logo', ['class' => 'absolute -bottom-6 -right-6 h-36 w-36 rounded-full bg-white/10 object-contain p-2 opacity-20'])
                            <div class="relative">
                                <div class="flex items-center justify-between border-b border-white/20 pb-3">
                                    <div class="flex items-center gap-2">@include('partials.celtech-logo', ['class' => 'h-7 w-7 rounded-full bg-white object-contain p-0.5'])<span class="text-xs font-bold uppercase tracking-wider text-[#FDC425]">CLCST Digital Pass</span></div>
                                    <span class="rounded bg-white/10 px-2 py-0.5 text-[10px] font-bold">Faculty / PECE</span>
                                </div>
                                <div class="my-4"><p class="text-[11px] uppercase tracking-wider text-white/70">Faculty Borrower Name</p><h3 class="font-display text-lg font-bold">{{ $faculty->name }}</h3><p class="text-xs text-[#FDC425]">{{ $facultyDepartment }}</p><p class="mt-1 font-mono text-[11px] text-white/80">ID: {{ $faculty->patron_id }} - Main Campus</p></div>
                                <div class="rounded-lg bg-white p-3 text-center text-black"><div class="h-12 bg-[repeating-linear-gradient(90deg,#000,#000_2px,transparent_2px,transparent_4px,#000_4px,#000_7px,transparent_7px,transparent_9px,#000_9px,#000_12px)]"></div><p class="mt-1.5 font-mono text-[11px] font-bold tracking-widest">*{{ $faculty->patron_id }}*</p><div class="mt-2 flex items-center justify-between border-t border-slate-100 pt-2 text-[10px] text-slate-500"><span>Library Entry Pass</span><span class="font-bold text-[#065F2A]">VALID AY 2024-2025</span></div></div>
                            </div>
                        </section>

                        <section class="rounded-xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                            <div class="flex items-center gap-2 border-b border-[#CBD5E1] pb-3"><span class="material-symbols-outlined text-[#065F2A]">policy</span><h3 class="font-display text-base font-bold">Acquisition Guidelines</h3></div>
                            <div class="mt-4 space-y-3.5 text-sm leading-6 text-[#475569]">
                                @foreach ([['1', 'CHED CMO Syllabi Priority', 'Titles listed in approved BSIT, BSCS, and BSCpE syllabi receive top priority.'], ['2', '5-Year Publication Rule', 'Technology and computer engineering references should be published within 2019-2024.'], ['3', 'E-Book Multi-User Option', 'Institutional digital licenses are preferred for high-enrollment courses.'], ['4', 'Per-Faculty Quota', 'Maximum PHP 50,000 allocation per faculty member per semester without council sign-off.']] as $rule)
                                    <div class="flex items-start gap-2.5"><span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#065F2A] text-[10px] font-bold text-white">{{ $rule[0] }}</span><p><strong class="text-[#0F172A]">{{ $rule[1] }}:</strong> {{ $rule[2] }}</p></div>
                                @endforeach
                            </div>
                            <a class="mt-4 flex items-center gap-1 border-t border-[#E2E8F0] pt-3 text-xs font-bold text-[#065F2A] hover:underline" href="{{ route('staff.section', 'support') }}">Download Collection Development Manual<span class="material-symbols-outlined text-base">arrow_forward</span></a>
                        </section>

                        <section class="rounded-xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                            <div class="flex items-center gap-2 border-b border-[#CBD5E1] pb-3"><span class="material-symbols-outlined text-[#785A00]">contact_phone</span><h3 class="font-display text-base font-bold">Committee Contacts</h3></div>
                            <div class="mt-4 space-y-3">
                                <div class="flex items-center justify-between rounded-lg bg-[#F8FAFC] p-3"><div><p class="text-sm font-bold">Ms. Maria Santos, RL</p><p class="text-xs text-[#64748B]">Chief Book Requests Officer</p></div><span class="rounded bg-white px-2 py-0.5 font-mono text-[11px] font-bold text-[#065F2A]">Ext. 108</span></div>
                                <div class="flex items-center justify-between rounded-lg bg-[#F8FAFC] p-3"><div><p class="text-sm font-bold">Dr. A. De Leon</p><p class="text-xs text-[#64748B]">Dean of Academic Affairs</p></div><span class="rounded bg-white px-2 py-0.5 font-mono text-[11px] font-bold text-[#065F2A]">Ext. 102</span></div>
                            </div>
                            <div class="mt-4 rounded-lg border border-[#FACC15]/40 bg-[#FFFBEB] p-3"><p class="flex items-center gap-1.5 text-xs font-bold text-[#92400E]"><span class="material-symbols-outlined text-base">event_busy</span>Q4 Acquisition Cut-Off: Nov 20, 2024</p><p class="mt-1 text-xs leading-5 text-[#64748B]">Late recommendations move to the Q1 2025 procurement stack.</p></div>
                        </section>
                    </aside>
                </section>
            @elseif ($section === 'reservations')
                @php
                    $reservationRows = collect($reservations)->values();
                    $holdBooks = collect($catalogBooks)->filter(fn ($book) => in_array($book['id'], ['clean-code', 'ai-modern-approach', 'computer-networks'], true))->values();
                    $activeHoldCount = max($reservationRows->count(), 2);
                    $readyCount = $reservationRows->count();
                    $waitlistCount = max($activeHoldCount - $readyCount, 1);
                @endphp

                <section class="rounded-2xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                    <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-start">
                        <div>
                            <nav aria-label="Breadcrumb" class="mb-2 flex items-center gap-2 text-xs font-medium text-[#64748B]">
                                <span>Academic Portal</span>
                                <span class="material-symbols-outlined text-sm">chevron_right</span>
                                <span>Faculty Services</span>
                                <span class="material-symbols-outlined text-sm">chevron_right</span>
                                <span class="font-bold text-[#065F2A]">My Reservations</span>
                            </nav>
                            <span class="inline-flex rounded-full bg-[#DCFCE7] px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-[#166534]">Active Shelves Access</span>
                            <h1 class="mt-3 font-display text-3xl font-bold text-[#064E3B]">My Reservations & Hold List</h1>
                            <p class="mt-2 max-w-3xl text-sm leading-6 text-[#475569]">Track ready-for-pickup holds, waitlist positions, and faculty course reserve staging at the Library Shelves Level 1 Library Desk.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <a class="inline-flex items-center gap-2 rounded-lg border border-[#CBD5E1] bg-white px-3.5 py-2 text-xs font-bold text-[#334155] shadow-sm hover:bg-[#F8FAFC]" href="{{ route('staff.section', 'reports') }}">
                                <span class="material-symbols-outlined text-base">picture_as_pdf</span>
                                Download Hold Slip
                            </a>
                            <a class="inline-flex items-center gap-2 rounded-lg border border-[#065F2A] px-3.5 py-2 text-xs font-bold text-[#065F2A] hover:bg-[#DCFCE7]" href="{{ route('staff.section', 'reservations') }}">
                                <span class="material-symbols-outlined text-base">sync</span>
                                Refresh List Status
                            </a>
                            <a class="inline-flex items-center gap-2 rounded-lg bg-[#065F2A] px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-[#064E3B]" href="{{ route('books.catalog') }}">
                                <span class="material-symbols-outlined text-base">add_box</span>
                                New Book Reservation
                            </a>
                        </div>
                    </div>
                </section>

                <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <article class="rounded-xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-[#64748B]">Active Reservations</p>
                                <h2 class="mt-1 font-display text-2xl font-bold text-[#065F2A]">{{ $activeHoldCount }} <span class="text-sm font-medium text-[#475569]">requests</span></h2>
                            </div>
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#DCFCE7] text-[#065F2A]"><span class="material-symbols-outlined">book_online</span></span>
                        </div>
                        <p class="mt-4 text-xs leading-5 text-[#64748B]">Faculty/staff hold allowance remains available for course reading and research titles.</p>
                    </article>
                    <article class="rounded-xl border border-[#FACC15] bg-[#FFFBEB] p-5 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-[#92400E]">Ready for Pickup</p>
                                <h2 class="mt-1 font-display text-2xl font-bold text-[#92400E]">{{ $readyCount }} <span class="text-sm font-medium">ready</span></h2>
                            </div>
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#FACC15] text-[#6D5200]"><span class="material-symbols-outlined">inventory_2</span></span>
                        </div>
                        <p class="mt-4 text-xs leading-5 text-[#92400E]">Pick up ready items at Counter 02 before the hold expiry date.</p>
                    </article>
                    <article class="rounded-xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-[#64748B]">Waitlisted</p>
                                <h2 class="mt-1 font-display text-2xl font-bold text-[#002E97]">{{ $waitlistCount }} <span class="text-sm font-medium text-[#475569]">queued</span></h2>
                            </div>
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#E2E7FF] text-[#002E97]"><span class="material-symbols-outlined">hourglass_top</span></span>
                        </div>
                        <p class="mt-4 text-xs leading-5 text-[#64748B]">List positions update automatically when returned copies are scanned.</p>
                    </article>
                    <article class="rounded-xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-[#64748B]">Course Reserves</p>
                                <h2 class="mt-1 font-display text-2xl font-bold text-[#065F2A]">{{ count($facultyRequests) }} <span class="text-sm font-medium text-[#475569]">staged</span></h2>
                            </div>
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#DCFCE7] text-[#065F2A]"><span class="material-symbols-outlined">school</span></span>
                        </div>
                        <p class="mt-4 text-xs leading-5 text-[#64748B]">Syllabus-related requests are prioritized for enrolled class access.</p>
                    </article>
                </section>

                <section class="grid grid-cols-1 gap-6 xl:grid-cols-12">
                    <div class="space-y-4 xl:col-span-8">
                        <div class="rounded-xl border border-[#CBD5E1] bg-white p-4 shadow-sm">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                <div>
                                    <h2 class="font-display text-xl font-bold text-[#0F172A]">Reservation List</h2>
                                    <p class="text-xs text-[#64748B]">Ready items, active holds, waitlist positions, and staged faculty reserve requests.</p>
                                </div>
                                <form class="flex w-full max-w-md items-center gap-2 rounded-lg border border-[#CBD5E1] bg-[#F8FAFC] px-3 py-2" method="GET" action="{{ route('books.catalog') }}">
                                    <span class="material-symbols-outlined text-base text-[#64748B]">search</span>
                                    <input class="w-full border-0 bg-transparent p-0 text-sm text-[#0F172A] placeholder:text-[#94A3B8] focus:ring-0" name="q" placeholder="Search title, ISBN, or call number...">
                                    <button class="rounded bg-[#065F2A] px-3 py-1.5 text-xs font-bold text-white" type="submit">Search</button>
                                </form>
                            </div>
                        </div>

                        @forelse ($reservationRows as $reservation)
                            @php
                                $book = collect($catalogBooks)->firstWhere('id', $reservation['book_id'] ?? null);
                            @endphp
                            <article class="overflow-hidden rounded-xl border border-[#FACC15] bg-white shadow-sm">
                                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-[#FDE68A] bg-[#FFFBEB] px-4 py-2.5">
                                    <span class="rounded-full bg-[#FACC15] px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-[#6D5200]">Ready for Pickup</span>
                                    <span class="text-xs font-semibold text-[#92400E]">Requested {{ $reservation['requested_at'] }}</span>
                                </div>
                                <div class="p-5">
                                    <div class="flex flex-col gap-5 sm:flex-row">
                                        <div class="h-32 w-24 shrink-0 overflow-hidden rounded-lg border border-[#CBD5E1] bg-[#E2E7FF] shadow-sm">
                                            @if ($book)
                                                <img class="h-full w-full object-cover" src="{{ $book['cover_url'] }}" alt="{{ $reservation['title'] }} cover">
                                            @else
                                                <div class="flex h-full w-full flex-col justify-between p-3 text-center text-[#065F2A]">
                                                    <span class="material-symbols-outlined text-3xl">book</span>
                                                    <span class="text-[10px] font-bold">HOLD</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <h3 class="font-display text-xl font-bold text-[#0F172A]">{{ $reservation['title'] }}</h3>
                                            <p class="mt-1 text-sm text-[#64748B]">by <span class="font-semibold text-[#334155]">{{ $book['author'] ?? 'Catalog author record' }}</span>{{ $book ? ' - '.$book['publisher'] : '' }}</p>
                                            <div class="my-4 grid grid-cols-1 gap-3 rounded-lg border border-[#CBD5E1] bg-[#F8FAFC] p-3 text-xs sm:grid-cols-3">
                                                <div><span class="block text-[10px] font-bold uppercase text-[#64748B]">Library Call Number</span><span class="font-mono font-bold text-[#0F172A]">{{ $book['call'] ?? 'Pending copy scan' }}</span></div>
                                                <div><span class="block text-[10px] font-bold uppercase text-[#64748B]">Pickup Desk</span><span class="font-semibold text-[#0F172A]">Counter 02</span></div>
                                                <div><span class="block text-[10px] font-bold uppercase text-[#64748B]">Pickup Deadline</span><span class="font-semibold text-[#92400E]">7 calendar days</span></div>
                                            </div>
                                            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-[#E2E8F0] pt-4">
                                                <div class="flex flex-wrap gap-2">
                                                    <a class="inline-flex items-center gap-1.5 rounded-lg bg-[#065F2A] px-3 py-2 text-xs font-bold text-white hover:bg-[#064E3B]" href="{{ route('books.catalog', ['selected' => $reservation['book_id'] ?? '']) }}"><span class="material-symbols-outlined text-base">qr_code</span>View Pickup Slip</a>
                                                    <a class="inline-flex items-center gap-1.5 rounded-lg border border-[#CBD5E1] px-3 py-2 text-xs font-bold text-[#334155] hover:bg-[#F8FAFC]" href="{{ route('staff.section', 'support') }}"><span class="material-symbols-outlined text-base">group_add</span>Authorize Pickup</a>
                                                </div>
                                                @if (!empty($reservation['book_id']))
                                                    <form method="POST" action="{{ route('reservations.cancel', $reservation['book_id']) }}">
                                                        @csrf
                                                        <button class="inline-flex items-center gap-1 rounded-lg px-3 py-2 text-xs font-bold text-[#B91C1C] hover:bg-[#FEE2E2]" type="submit"><span class="material-symbols-outlined text-base">close</span>Cancel Reservation</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @empty
                            @foreach ($holdBooks->take(2) as $index => $book)
                                <article class="overflow-hidden rounded-xl border border-[#CBD5E1] bg-white shadow-sm">
                                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-[#CBD5E1] bg-[#F8FAFC] px-4 py-2.5">
                                        <span class="rounded-full {{ $index === 0 ? 'bg-[#FACC15] text-[#6D5200]' : 'bg-[#E2E7FF] text-[#002E97]' }} px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider">{{ $index === 0 ? 'Waiting for Staff to Pull Book' : 'Waiting Position: #1 of 3' }}</span>
                                        <span class="text-xs font-semibold text-[#64748B]">{{ $index === 0 ? 'Expected shelf scan today' : 'Expected return: Oct 18, 2024' }}</span>
                                    </div>
                                    <div class="p-5">
                                        <div class="flex flex-col gap-5 sm:flex-row">
                                            <img class="h-32 w-24 shrink-0 rounded-lg border border-[#CBD5E1] object-cover shadow-sm" src="{{ $book['cover_url'] }}" alt="{{ $book['title'] }} cover">
                                            <div class="min-w-0 flex-1">
                                                <h3 class="font-display text-xl font-bold text-[#0F172A]">{{ $book['title'] }}</h3>
                                                <p class="mt-1 text-sm text-[#64748B]">by <span class="font-semibold text-[#334155]">{{ $book['author'] }}</span> - {{ $book['publisher'] }}</p>
                                                <div class="my-4 grid grid-cols-1 gap-3 rounded-lg border border-[#CBD5E1] bg-[#F8FAFC] p-3 text-xs sm:grid-cols-3">
                                                    <div><span class="block text-[10px] font-bold uppercase text-[#64748B]">Library Call No.</span><span class="font-mono font-bold text-[#0F172A]">{{ $book['call'] }}</span></div>
                                                    <div><span class="block text-[10px] font-bold uppercase text-[#64748B]">ISBN / Asset</span><span class="font-mono font-bold text-[#0F172A]">{{ $book['isbn'] }}</span></div>
                                                    <div><span class="block text-[10px] font-bold uppercase text-[#64748B]">Location</span><span class="font-semibold text-[#0F172A]">{{ $book['location'] }}</span></div>
                                                </div>
                                                <div class="rounded-lg border border-[#CBD5E1] bg-[#EEF2FF] p-3 text-xs text-[#334155]">
                                                    <div class="mb-2 flex items-center justify-between">
                                                        <span class="flex items-center gap-1.5 font-bold"><span class="material-symbols-outlined text-sm text-[#002E97]">person_search</span>Borrowing Status</span>
                                                        <span class="font-mono font-bold text-[#002E97]">{{ collect($book['copies'])->where('status', 'available')->count() }} available copies</span>
                                                    </div>
                                                    <div class="h-2 overflow-hidden rounded-full bg-[#DAE2FD]"><div class="h-full rounded-full bg-[#065F2A]" style="width: {{ collect($book['copies'])->where('status', 'available')->count() > 0 ? 70 : 30 }}%"></div></div>
                                                </div>
                                                <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-[#E2E8F0] pt-4">
                                                    <div class="flex flex-wrap gap-2">
                                                        <a class="inline-flex items-center gap-1.5 rounded-lg border border-[#CBD5E1] px-3 py-2 text-xs font-bold text-[#334155] hover:bg-[#F8FAFC]" href="{{ route('books.catalog', ['selected' => $book['id']]) }}"><span class="material-symbols-outlined text-base">visibility</span>View Catalog Record</a>
                                                        <a class="inline-flex items-center gap-1.5 rounded-lg bg-[#DCE1FF] px-3 py-2 text-xs font-bold text-[#001551] hover:bg-[#B7C4FF]" href="{{ route('books.catalog', ['q' => $book['title']]) }}"><span class="material-symbols-outlined text-base">cloud_download</span>Find E-Book Alternative</a>
                                                    </div>
                                                    <a class="inline-flex items-center gap-1 rounded-lg px-3 py-2 text-xs font-bold text-[#B91C1C] hover:bg-[#FEE2E2]" href="{{ route('staff.section', 'reservations') }}"><span class="material-symbols-outlined text-base">close</span>Cancel Reservation</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        @endforelse

                        <section class="rounded-xl border border-[#0041CC]/30 bg-gradient-to-br from-[#F2F3FF] to-[#EAEDFF] p-5 shadow-sm">
                            <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
                                <div class="flex items-start gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#002E97] text-white"><span class="material-symbols-outlined">menu_book</span></span>
                                    <div>
                                        <h3 class="font-display text-lg font-bold text-[#0F172A]">Instructional Syllabus Reserves</h3>
                                        <p class="text-sm text-[#475569]">Physical copies staged for enrolled student closed-shelf use.</p>
                                    </div>
                                </div>
                                <span class="rounded-full bg-[#0041CC] px-2.5 py-1 text-[10px] font-bold text-white">1st Sem AY 2024-2025</span>
                            </div>
                            <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                                @foreach ($facultyRequests as $request)
                                    <article class="rounded-lg border border-[#CBD5E1] bg-white p-4 shadow-sm">
                                        <span class="rounded bg-[#DCFCE7] px-2 py-0.5 text-[10px] font-bold text-[#166534]">{{ $request['course'] }}</span>
                                        <h4 class="mt-2 text-sm font-bold text-[#0F172A]">{{ $request['request'] }}</h4>
                                        <p class="mt-1 text-xs text-[#64748B]">Owner: {{ $request['owner'] }}</p>
                                        <p class="mt-2 border-t border-[#E2E8F0] pt-2 text-xs font-bold text-[#065F2A]">{{ $request['status'] }}</p>
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    </div>

                    <aside class="space-y-4 xl:col-span-4">
                        <section class="relative overflow-hidden rounded-2xl border border-[#064E3B] bg-gradient-to-br from-[#135D2E] to-[#00441D] p-6 text-white shadow-md">
                            @include('partials.celtech-logo', ['class' => 'absolute -bottom-8 -right-8 h-40 w-40 rounded-full bg-white/10 object-contain p-2 opacity-20'])
                            <div class="relative">
                                <div class="flex items-center justify-between border-b border-white/20 pb-4">
                                    <div class="flex items-center gap-2.5">
                                        @include('partials.celtech-logo', ['class' => 'h-8 w-8 rounded-full bg-white object-contain p-0.5'])
                                        <div><p class="text-[11px] font-extrabold uppercase tracking-wider text-[#FDC425]">CLCST Faculty Pass</p><p class="text-[9px] text-white/80">Barcode Library Entry & Borrowing Verified</p></div>
                                    </div>
                                    <span class="rounded-full bg-white/20 px-2 py-0.5 text-[10px] font-bold">ACTIVE</span>
                                </div>
                                <div class="my-4">
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-white/70">Borrower / Faculty In-Charge</span>
                                    <h4 class="mt-1 font-display text-lg font-bold">Engr. Roberto M. Mendoza</h4>
                                    <p class="text-xs text-white/90">College of Computer Studies & Engineering</p>
                                    <p class="mt-1 text-[11px] text-white/80">ID: <strong class="font-mono text-white">FAC-2018-0412</strong></p>
                                </div>
                                <div class="rounded-xl bg-white p-3 text-center text-black shadow-inner">
                                    <p class="mb-1 text-[9px] font-bold uppercase tracking-wider text-[#64748B]">Optical Scan Barcode</p>
                                    <div class="font-mono text-lg font-black tracking-[0.35em]">|||| ||| |||||| |||</div>
                                    <p class="mt-1 font-mono text-xs font-bold tracking-wider">FAC*20180412*LIB</p>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                            <div class="flex items-center justify-between border-b border-[#CBD5E1] pb-3">
                                <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[#065F2A]">storefront</span><h3 class="font-display text-base font-bold">Library Desk Staging</h3></div>
                                <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                            </div>
                            <div class="mt-4 space-y-2 text-xs">
                                <div class="flex justify-between border-b border-[#E2E8F0] py-1"><span class="text-[#64748B]">Desk Duty:</span><span class="font-bold">Ms. M. Santos</span></div>
                                <div class="flex justify-between border-b border-[#E2E8F0] py-1"><span class="text-[#64748B]">Intercom:</span><span class="font-mono font-bold text-[#065F2A]">Local Ext. 108</span></div>
                                <div class="flex justify-between py-1"><span class="text-[#64748B]">Hours:</span><span class="font-medium">7:30 AM - 6:30 PM</span></div>
                            </div>
                            <a class="mt-4 flex w-full items-center justify-center gap-1.5 rounded-lg bg-[#F8FAFC] px-3 py-2 text-xs font-bold text-[#065F2A] hover:bg-[#E2E8F0]" href="{{ route('staff.section', 'support') }}"><span class="material-symbols-outlined text-base">support_agent</span>Contact Desk 1 Support</a>
                        </section>

                        <section class="rounded-xl border border-[#CBD5E1] bg-[#F8FAFC] p-5 shadow-sm">
                            <div class="flex items-center gap-2 text-[#065F2A]"><span class="material-symbols-outlined text-base">policy</span><h3 class="font-display text-base font-bold">Faculty Reservation Rules</h3></div>
                            <ul class="mt-4 list-disc space-y-2 pl-4 text-xs leading-5 text-[#475569]">
                                <li><strong>10 maximum concurrent holds</strong> for faculty members across departments.</li>
                                <li><strong>7-day pickup window</strong> at Desk Counter 02 after notification.</li>
                                <li><strong>TA authorization</strong> is accepted with a signed faculty slip.</li>
                                <li><strong>Course syllabus priority</strong> applies to required reading materials.</li>
                            </ul>
                            <div class="mt-4 flex items-center justify-between border-t border-[#CBD5E1] pt-3 text-[11px]"><span class="text-[#64748B]">CLCST Handbook Art. IV</span><a class="font-bold text-[#065F2A] hover:underline" href="{{ route('staff.section', 'support') }}">Read Policy</a></div>
                        </section>
                    </aside>
                </section>
            @elseif ($section === 'reports')
                @php
                    $bookLookup = collect($catalogBooks)->keyBy('id');
                    $historyLookup = collect($borrowingHistory)->whereNotNull('loan_id')->keyBy('loan_id');
                    $renewedLoanMap = $renewedLoans ?? [];
                    $pendingRenewals = collect($activeLoans)->filter(fn ($loan) => !array_key_exists($loan['id'], $renewedLoanMap));
                    $approvedRenewals = collect($activeLoans)->filter(fn ($loan) => array_key_exists($loan['id'], $renewedLoanMap));
                    $restrictedBook = $bookLookup->get('clean-code');
                    $eligibleBook = $bookLookup->get('algorithms') ?? $bookLookup->get('ai-modern-approach');
                @endphp

                <section class="rounded-2xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                    <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-start">
                        <div>
                            <nav aria-label="Breadcrumb" class="mb-2 flex items-center gap-2 text-xs font-medium text-[#64748B]">
                                <span>Academic Portal</span>
                                <span class="material-symbols-outlined text-sm">chevron_right</span>
                                <span>Faculty Services</span>
                                <span class="material-symbols-outlined text-sm">chevron_right</span>
                                <span class="font-bold text-[#065F2A]">Renewal Requests</span>
                            </nav>
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-[#BBF7D0] bg-[#DCFCE7] px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-[#166534]">
                                <span class="material-symbols-outlined text-sm">verified_user</span>
                                Faculty loan extension available
                            </span>
                            <h1 class="mt-3 font-display text-3xl font-bold text-[#0F172A]">Renew or Extend Borrowed Books</h1>
                            <p class="mt-2 max-w-3xl text-sm leading-6 text-[#475569]">Renew eligible borrowed books before the due date. Books with another borrower waiting must be returned or checked by the library desk.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <a class="inline-flex items-center gap-2 rounded-lg border border-[#CBD5E1] bg-white px-3.5 py-2 text-xs font-bold text-[#334155] shadow-sm hover:bg-[#F8FAFC]" href="{{ route('staff.section', 'circulation') }}">
                                <span class="material-symbols-outlined text-base">menu_book</span>
                                My Borrowed Books
                            </a>
                            <a class="inline-flex items-center gap-2 rounded-lg border border-[#065F2A] px-3.5 py-2 text-xs font-bold text-[#065F2A] hover:bg-[#DCFCE7]" href="{{ route('staff.section', 'support') }}">
                                <span class="material-symbols-outlined text-base">desk</span>
                                Ask Library Desk
                            </a>
                            <a class="inline-flex items-center gap-2 rounded-lg bg-[#065F2A] px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-[#064E3B]" href="{{ route('staff.section', 'reports') }}">
                                <span class="material-symbols-outlined text-base">autorenew</span>
                                Renew All Allowed
                            </a>
                        </div>
                    </div>
                </section>

                <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <article class="rounded-xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-[#64748B]">Waiting for Review</p>
                                <h2 class="mt-1 font-display text-2xl font-bold text-[#065F2A]">{{ max($pendingRenewals->count(), 1) }} <span class="text-sm font-medium text-[#475569]">request</span></h2>
                            </div>
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#DCFCE7] text-[#065F2A]"><span class="material-symbols-outlined">pending_actions</span></span>
                        </div>
                        <p class="mt-4 text-xs leading-5 text-[#64748B]">These books can still be checked by the library staff before renewal.</p>
                    </article>
                    <article class="rounded-xl border border-[#FACC15] bg-[#FFFBEB] p-5 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-[#92400E]">Due Soon</p>
                                <h2 class="mt-1 font-display text-2xl font-bold text-[#92400E]">{{ collect($activeLoans)->filter(fn ($loan) => str_contains($loan['due'], 'Oct'))->count() }} <span class="text-sm font-medium">items</span></h2>
                            </div>
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#FACC15] text-[#6D5200]"><span class="material-symbols-outlined">notifications_active</span></span>
                        </div>
                        <p class="mt-4 text-xs leading-5 text-[#92400E]">Request renewal before the due date to avoid account review.</p>
                    </article>
                    <article class="rounded-xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-[#64748B]">Approved Renewals</p>
                                <h2 class="mt-1 font-display text-2xl font-bold text-[#002E97]">{{ $approvedRenewals->count() }} <span class="text-sm font-medium text-[#475569]">applied</span></h2>
                            </div>
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#E2E7FF] text-[#002E97]"><span class="material-symbols-outlined">done_all</span></span>
                        </div>
                        <p class="mt-4 text-xs leading-5 text-[#64748B]">New due dates are saved for receipts and borrowing records.</p>
                    </article>
                    <article class="rounded-xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-[#64748B]">Cannot Renew Online</p>
                                <h2 class="mt-1 font-display text-2xl font-bold text-[#B91C1C]">1 <span class="text-sm font-medium text-[#475569]">book</span></h2>
                            </div>
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#FEE2E2] text-[#B91C1C]"><span class="material-symbols-outlined">lock_clock</span></span>
                        </div>
                        <p class="mt-4 text-xs leading-5 text-[#64748B]">A book cannot be renewed online when someone else is waiting for it.</p>
                    </article>
                </section>

                <section class="grid grid-cols-1 gap-6 xl:grid-cols-12">
                    <div class="space-y-4 xl:col-span-8">
                        <div class="rounded-xl border border-[#CBD5E1] bg-white p-4 shadow-sm">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                <div>
                                    <h2 class="font-display text-xl font-bold text-[#0F172A]">Borrowed Books for Renewal</h2>
                                    <p class="text-xs text-[#64748B]">See which borrowed books can be renewed, already renewed, or need help from the desk.</p>
                                </div>
                                <form class="flex w-full max-w-md items-center gap-2 rounded-lg border border-[#CBD5E1] bg-[#F8FAFC] px-3 py-2" method="GET" action="{{ route('books.catalog') }}">
                                    <span class="material-symbols-outlined text-base text-[#64748B]">search</span>
                                    <input class="w-full border-0 bg-transparent p-0 text-sm text-[#0F172A] placeholder:text-[#94A3B8] focus:ring-0" name="q" placeholder="Search by title, author, ISBN, or call number...">
                                    <button class="rounded bg-[#065F2A] px-3 py-1.5 text-xs font-bold text-white" type="submit">Search</button>
                                </form>
                            </div>
                        </div>

                        @foreach ($activeLoans as $loan)
                            @php
                                $book = $bookLookup->get($loan['book_id']);
                                $history = $historyLookup->get($loan['id']);
                                $isRenewed = array_key_exists($loan['id'], $renewedLoanMap);
                                $isLimited = $loan['book_id'] === 'clean-code';
                                $newDueDate = $renewedLoanMap[$loan['id']] ?? 'Nov 14, 2024';
                            @endphp
                            <article class="overflow-hidden rounded-xl border {{ $isLimited ? 'border-[#FCA5A5]' : ($isRenewed ? 'border-[#CBD5E1]' : 'border-[#BBF7D0]') }} bg-white shadow-sm">
                                <div class="flex flex-wrap items-center justify-between gap-2 border-b px-5 py-3 {{ $isLimited ? 'border-[#FCA5A5] bg-[#FEE2E2]/50' : ($isRenewed ? 'border-[#CBD5E1] bg-[#F8FAFC]' : 'border-[#BBF7D0] bg-[#F0FDF4]') }}">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $isLimited ? 'bg-[#B91C1C] text-white' : ($isRenewed ? 'bg-[#E2E7FF] text-[#002E97]' : 'bg-[#065F2A] text-white') }}">
                                        <span class="material-symbols-outlined text-sm">{{ $isLimited ? 'warning' : ($isRenewed ? 'done_all' : 'autorenew') }}</span>
                                        {{ $isLimited ? 'Cannot Renew Online - Someone Is Waiting' : ($isRenewed ? 'Renewal Submitted' : 'Can Be Renewed Online') }}
                                    </span>
                                    <span class="text-xs font-semibold {{ $isLimited ? 'text-[#B91C1C]' : 'text-[#64748B]' }}">{{ $isRenewed ? 'New due: '.$newDueDate : 'Current due: '.$loan['due'] }}</span>
                                </div>
                                <div class="p-5">
                                    <div class="flex flex-col gap-5 md:flex-row">
                                        <div class="h-32 w-24 shrink-0 overflow-hidden rounded-lg border border-[#CBD5E1] bg-[#E2E7FF] shadow-sm">
                                            @if ($book)
                                                <img class="h-full w-full object-cover" src="{{ $book['cover_url'] }}" alt="{{ $loan['title'] }} cover">
                                            @else
                                                <div class="flex h-full w-full flex-col items-center justify-center p-2 text-center text-[#64748B]"><span class="material-symbols-outlined text-3xl">book</span><span class="mt-1 text-[10px] font-bold">Library Copy</span></div>
                                            @endif
                                        </div>
                                        <div class="min-w-0 flex-1 space-y-3">
                                            <div class="flex flex-wrap items-start justify-between gap-2">
                                                <div>
                                                    <h3 class="font-display text-xl font-bold leading-tight text-[#0F172A]">{{ $loan['title'] }}</h3>
                                                    <p class="mt-1 text-sm text-[#64748B]">by <span class="font-semibold text-[#334155]">{{ $book['author'] ?? ($history['author'] ?? 'Catalog author record') }}</span>{{ $book ? ' - '.$book['publisher'] : '' }}</p>
                                                </div>
                                                <span class="rounded-full {{ $isLimited ? 'bg-[#FEE2E2] text-[#991B1B]' : 'bg-[#DCFCE7] text-[#166534]' }} px-2.5 py-1 text-[10px] font-bold">{{ $isLimited ? '2 People Waiting' : 'Renewal Available' }}</span>
                                            </div>
                                            <div class="grid grid-cols-1 gap-3 rounded-lg border border-[#CBD5E1] bg-[#F8FAFC] p-3 text-xs sm:grid-cols-4">
                                                <div><span class="block text-[10px] font-bold uppercase text-[#64748B]">Library Call Number</span><span class="font-semibold text-[#0F172A]">{{ $loan['call'] }}</span></div>
                                                <div><span class="block text-[10px] font-bold uppercase text-[#64748B]">Barcode</span><span class="font-mono text-[#0F172A]">{{ $loan['barcode'] }}</span></div>
                                                <div><span class="block text-[10px] font-bold uppercase text-[#64748B]">Borrowed</span><span class="font-semibold text-[#0F172A]">{{ $history['borrowed'] ?? 'Desk Record' }}</span></div>
                                                <div><span class="block text-[10px] font-bold uppercase text-[#64748B]">Renewals Used</span><span class="font-bold {{ $isLimited ? 'text-[#B91C1C]' : 'text-[#065F2A]' }}">{{ $isRenewed ? '1 of 2 Used' : ($isLimited ? 'Not Available' : '0 of 2 Used') }}</span></div>
                                            </div>

                                            @if ($isLimited)
                                                <div class="rounded-lg border-l-4 border-[#B91C1C] bg-[#F8FAFC] p-3 text-sm">
                                                    <p class="flex items-center gap-1.5 font-semibold text-[#0F172A]"><span class="material-symbols-outlined text-base text-[#B91C1C]">info</span>This book has a waiting list</p>
                                                    <p class="mt-1 pl-5 text-[#475569]">Please return this book on time or ask the library desk if you need a short teaching-related extension.</p>
                                                </div>
                                            @else
                                                <div class="flex items-center gap-2 rounded-lg border border-[#CBD5E1] bg-[#F8FAFC] p-3 text-sm">
                                                    <span class="material-symbols-outlined text-base text-[#065F2A]">event_upcoming</span>
                                                    <span>New due date after renewal: <strong class="text-[#065F2A]">{{ $newDueDate }}</strong> (+{{ $isRenewed ? '7' : '30' }} days)</span>
                                                </div>
                                            @endif

                                            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-[#E2E8F0] pt-3">
                                                <div class="flex flex-wrap gap-2">
                                                    @if ($isLimited)
                                                        <a class="inline-flex items-center gap-1.5 rounded-lg bg-[#E2E8F0] px-3.5 py-2 text-xs font-bold text-[#334155] hover:bg-[#CBD5E1]" href="{{ route('staff.section', 'support') }}"><span class="material-symbols-outlined text-base">assignment_return</span>Ask About Return</a>
                                                        <a class="inline-flex items-center gap-1.5 rounded-lg border border-[#CBD5E1] px-3.5 py-2 text-xs font-bold text-[#065F2A] hover:bg-[#DCFCE7]" href="{{ route('staff.section', 'reservations') }}"><span class="material-symbols-outlined text-base">copy_all</span>Request Replacement Copy</a>
                                                    @else
                                                        <form method="POST" action="{{ route('loans.renew', $loan['id']) }}">
                                                            @csrf
                                                            <button class="inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-xs font-bold shadow-sm {{ $isRenewed ? 'bg-[#DCFCE7] text-[#166534]' : 'bg-[#065F2A] text-white hover:bg-[#064E3B]' }}" type="submit" @disabled($isRenewed)>
                                                                <span class="material-symbols-outlined text-base">{{ $isRenewed ? 'check_circle' : 'bolt' }}</span>
                                                                {{ $isRenewed ? 'Renewal Submitted' : 'Submit Online Renewal' }}
                                                            </button>
                                                        </form>
                                                        <a class="inline-flex items-center gap-1.5 rounded-lg border border-[#CBD5E1] px-3.5 py-2 text-xs font-bold text-[#334155] hover:bg-[#F8FAFC]" href="{{ route('staff.section', 'support') }}">Ask for a Different Date</a>
                                                    @endif
                                                </div>
                                                <a class="inline-flex items-center gap-1 text-xs font-bold text-[#64748B] hover:text-[#0F172A]" href="https://www.google.com/search?q={{ urlencode($loan['title'].' citation') }}" target="_blank" rel="noopener"><span class="material-symbols-outlined text-sm">format_quote</span>Cite Book</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach

                        @if ($approvedRenewals->isNotEmpty())
                            <section class="rounded-xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                                <h3 class="font-display text-lg font-bold text-[#0F172A]">Recently Approved Extension Record</h3>
                                <div class="mt-3 space-y-2">
                                    @foreach ($approvedRenewals as $loan)
                                        <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-[#E2E8F0] bg-[#F8FAFC] p-3 text-sm">
                                            <div>
                                                <p class="font-bold">{{ $loan['title'] }}</p>
                                                <p class="text-xs text-[#64748B]">Approved by Ms. M. Santos - {{ $loan['barcode'] }}</p>
                                            </div>
                                            <span class="rounded bg-[#DCFCE7] px-2.5 py-1 text-xs font-bold text-[#166534]">Extended to {{ $renewedLoanMap[$loan['id']] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </section>
                        @endif
                    </div>

                    <aside class="space-y-4 xl:col-span-4">
                        <section class="relative overflow-hidden rounded-2xl border border-[#064E3B] bg-gradient-to-br from-[#135D2E] to-[#042410] p-6 text-white shadow-md">
                            @include('partials.celtech-logo', ['class' => 'absolute -bottom-8 -right-8 h-40 w-40 rounded-full bg-white/10 object-contain p-2 opacity-20'])
                            <div class="relative">
                                <div class="flex items-center justify-between border-b border-white/20 pb-4">
                                    <div class="flex items-center gap-2.5">
                                        @include('partials.celtech-logo', ['class' => 'h-8 w-8 rounded-full bg-white object-contain p-0.5'])
                                        <div><p class="text-[10px] font-bold uppercase tracking-wider text-[#FDC425]">Central Luzon College of Science and Tech</p><p class="font-display text-base font-bold">Faculty Borrower Pass</p></div>
                                    </div>
                                    <span class="material-symbols-outlined text-[#FDC425]">nfc</span>
                                </div>
                                <div class="py-4">
                                    <p class="text-xs text-white/80">Name</p>
                                    <p class="font-display text-lg font-bold">Engr. Roberto M. Mendoza, PECE</p>
                                    <p class="text-xs text-white/90">Dept. Chair - CICT & CpE</p>
                                </div>
                                <div class="rounded-xl bg-white p-3 text-center text-black shadow-inner">
                                    <div class="flex items-center justify-center gap-1 border-b border-[#CBD5E1] pb-1 font-mono text-xs font-bold tracking-wider"><span class="material-symbols-outlined text-sm text-[#065F2A]">qr_code_2</span>FAC*20180412*LIB</div>
                                    <div class="mt-2 font-mono text-lg font-black tracking-[0.35em]">|||| ||| |||||| |||</div>
                                    <p class="mt-1 text-[9px] text-[#64748B]">Tap at stacks library entry or scan under barcode reader</p>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                            <div class="flex items-center gap-2 border-b border-[#CBD5E1] pb-3">
                                <span class="material-symbols-outlined text-[#065F2A]">policy</span>
                                <h3 class="font-display text-base font-bold">Renewal Rules</h3>
                            </div>
                            <ul class="mt-4 space-y-3 text-sm leading-6 text-[#475569]">
                                <li class="flex gap-2"><span class="material-symbols-outlined mt-0.5 shrink-0 text-base text-[#065F2A]">check_circle</span><span><strong>Max 2 Online Renewals:</strong> Up to two renewals per item before physical desk inspection.</span></li>
                                <li class="flex gap-2"><span class="material-symbols-outlined mt-0.5 shrink-0 text-base text-[#065F2A]">calendar_month</span><span><strong>Faculty Loan Period Grant:</strong> Standard 30 to 60 calendar days per approved extension.</span></li>
                                <li class="flex gap-2"><span class="material-symbols-outlined mt-0.5 shrink-0 text-base text-[#92400E]">lock_clock</span><span><strong>Hold Lock Policy:</strong> Active student/researcher holds can lock online renewal.</span></li>
                                <li class="flex gap-2"><span class="material-symbols-outlined mt-0.5 shrink-0 text-base text-[#002E97]">thunderstorm</span><span><strong>Suspension Clause:</strong> Campus closures automatically pause return dates.</span></li>
                            </ul>
                            <a class="mt-4 flex items-center justify-between border-t border-[#CBD5E1] pt-3 text-xs font-bold text-[#065F2A] hover:underline" href="{{ route('staff.section', 'support') }}"><span>View Full CLCST Faculty Library Code</span><span class="material-symbols-outlined text-base">arrow_forward</span></a>
                        </section>

                        <section class="rounded-xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                            <div class="flex items-center gap-2 border-b border-[#CBD5E1] pb-3">
                                <span class="material-symbols-outlined text-[#065F2A]">desk</span>
                                <h3 class="font-display text-base font-bold">Library Desk Duty</h3>
                            </div>
                            <div class="mt-4 flex items-center gap-3 rounded-lg border border-[#E2E8F0] bg-[#F8FAFC] p-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#DCFCE7] font-bold text-[#065F2A]">MS</div>
                                <div><p class="text-sm font-bold">Ms. M. Santos</p><p class="text-xs text-[#64748B]">Head Librarian - Library Counter 01</p><p class="text-xs font-bold text-[#065F2A]">Local Ext. 108</p></div>
                            </div>
                            <div class="mt-4 space-y-1.5 text-xs text-[#475569]">
                                <div class="flex justify-between"><span>Desk Hours:</span><span class="font-semibold text-[#0F172A]">7:30 AM - 6:30 PM</span></div>
                                <div class="flex justify-between"><span>Saturday Service:</span><span class="font-semibold text-[#0F172A]">8:00 AM - 12:00 PM</span></div>
                                <div class="flex justify-between"><span>Location:</span><span class="font-semibold text-[#0F172A]">Main Bldg - Level 1</span></div>
                            </div>
                            <a class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg bg-[#065F2A] px-3 py-2 text-xs font-bold text-white hover:bg-[#064E3B]" href="{{ route('staff.section', 'support') }}"><span class="material-symbols-outlined text-base">chat</span>Contact Desk 1 Support</a>
                        </section>
                    </aside>
                </section>
            @elseif ($section === 'history')
                @php
                    $historyRows = collect($borrowingHistory);
                    $activeHistoryRows = $historyRows->where('status', 'active');
                    $returnedRows = $historyRows->whereNotIn('status', ['active']);
                    $settledRows = $historyRows->whereIn('status', ['returned', 'settled', 'grace']);
                    $totalHistoricalLoans = max($historyRows->count(), 48);
                    $currentYearRows = $historyRows->filter(fn ($row) => str_contains($row['borrowed'], '2024'));
                @endphp

                <section class="rounded-2xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                    <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-start">
                        <div>
                            <nav aria-label="Breadcrumb" class="mb-2 flex items-center gap-2 text-xs font-medium text-[#64748B]">
                                <span>Academic Portal</span>
                                <span class="material-symbols-outlined text-sm">chevron_right</span>
                                <span>Faculty Services</span>
                                <span class="material-symbols-outlined text-sm">chevron_right</span>
                                <span class="font-bold text-[#065F2A]">Borrowing History</span>
                            </nav>
                            <span class="inline-flex rounded-full bg-[#DCFCE7] px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-[#166534]">Academic Reading Record</span>
                            <h1 class="mt-3 font-display text-3xl font-bold text-[#064E3B]">Academic Borrowing History & Reading Record</h1>
                            <p class="mt-2 max-w-4xl text-sm leading-6 text-[#475569]">Comprehensive archive of returned instructional books, historical research loans, syllabus references, and citation records for faculty accreditation.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <a class="inline-flex items-center gap-2 rounded-lg border border-[#CBD5E1] bg-white px-3.5 py-2 text-xs font-bold text-[#334155] shadow-sm hover:bg-[#F8FAFC]" href="{{ route('staff.section', 'reports') }}">
                                <span class="material-symbols-outlined text-base">download</span>
                                Export Record
                            </a>
                            <a class="inline-flex items-center gap-2 rounded-lg border border-[#065F2A] px-3.5 py-2 text-xs font-bold text-[#065F2A] hover:bg-[#DCFCE7]" href="{{ route('books.catalog') }}">
                                <span class="material-symbols-outlined text-base">search</span>
                                Search Books
                            </a>
                            <a class="inline-flex items-center gap-2 rounded-lg bg-[#065F2A] px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-[#064E3B]" href="{{ route('staff.section', 'support') }}">
                                <span class="material-symbols-outlined text-base">verified</span>
                                Clearance Slip
                            </a>
                        </div>
                    </div>
                </section>

                <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <article class="rounded-xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-[#64748B]">Historical Loans</p>
                                <h2 class="mt-1 font-display text-2xl font-bold text-[#065F2A]">{{ $totalHistoricalLoans }} <span class="text-sm font-medium text-[#475569]">records</span></h2>
                            </div>
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#DCFCE7] text-[#065F2A]"><span class="material-symbols-outlined">history_edu</span></span>
                        </div>
                        <p class="mt-4 text-xs leading-5 text-[#64748B]">Includes returned books, renewed loans, and archive records used in faculty documentation.</p>
                    </article>
                    <article class="rounded-xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-[#64748B]">Active Loans</p>
                                <h2 class="mt-1 font-display text-2xl font-bold text-[#92400E]">{{ $activeHistoryRows->count() }} <span class="text-sm font-medium text-[#475569]">open</span></h2>
                            </div>
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#FEF3C7] text-[#92400E]"><span class="material-symbols-outlined">local_library</span></span>
                        </div>
                        <p class="mt-4 text-xs leading-5 text-[#92400E]">Current loans are still visible for continuity with the renewal queue.</p>
                    </article>
                    <article class="rounded-xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-[#64748B]">Returned / Cleared</p>
                                <h2 class="mt-1 font-display text-2xl font-bold text-[#065F2A]">{{ $settledRows->count() }} <span class="text-sm font-medium text-[#475569]">items</span></h2>
                            </div>
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#DCFCE7] text-[#065F2A]"><span class="material-symbols-outlined">check_circle</span></span>
                        </div>
                        <p class="mt-4 text-xs leading-5 text-[#64748B]">No unresolved replacement records appear in the current faculty ledger.</p>
                    </article>
                    <article class="rounded-xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-[#64748B]">AY 2024 Reads</p>
                                <h2 class="mt-1 font-display text-2xl font-bold text-[#002E97]">{{ $currentYearRows->count() }} <span class="text-sm font-medium text-[#475569]">records</span></h2>
                            </div>
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#E2E7FF] text-[#002E97]"><span class="material-symbols-outlined">calendar_month</span></span>
                        </div>
                        <p class="mt-4 text-xs leading-5 text-[#64748B]">Current academic-year borrowing activity for reports and clearance review.</p>
                    </article>
                </section>

                <section class="grid grid-cols-1 gap-6 xl:grid-cols-12">
                    <div class="space-y-4 xl:col-span-8">
                        <div class="rounded-xl border border-[#CBD5E1] bg-white p-4 shadow-sm">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                <div>
                                    <h2 class="font-display text-xl font-bold text-[#0F172A]">Borrowing Record</h2>
                                    <p class="text-xs text-[#64748B]">Showing {{ min($historyRows->count(), 5) }} of {{ $totalHistoricalLoans }} historical loans.</p>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <span class="rounded-full bg-[#065F2A] px-3 py-1.5 text-xs font-bold text-white">All Records</span>
                                    <span class="rounded-full border border-[#CBD5E1] bg-[#F8FAFC] px-3 py-1.5 text-xs font-bold text-[#334155]">Returned</span>
                                    <span class="rounded-full border border-[#CBD5E1] bg-[#F8FAFC] px-3 py-1.5 text-xs font-bold text-[#334155]">Renewed</span>
                                    <span class="rounded-full border border-[#CBD5E1] bg-[#F8FAFC] px-3 py-1.5 text-xs font-bold text-[#334155]">Course Reserve</span>
                                </div>
                            </div>
                            <form class="mt-4 flex items-center gap-2 rounded-lg border border-[#CBD5E1] bg-[#F8FAFC] px-3 py-2" method="GET" action="{{ route('books.catalog') }}">
                                <span class="material-symbols-outlined text-base text-[#64748B]">search</span>
                                <input class="w-full border-0 bg-transparent p-0 text-sm text-[#0F172A] placeholder:text-[#94A3B8] focus:ring-0" name="q" placeholder="Search catalog again by title, author, ISBN, or call number...">
                                <button class="rounded bg-[#065F2A] px-3 py-1.5 text-xs font-bold text-white" type="submit">Search</button>
                            </form>
                        </div>

                        @foreach ($historyRows->take(5) as $row)
                            @php
                                $statusClass = match ($row['status']) {
                                    'active' => 'bg-[#FEF3C7] text-[#92400E]',
                                    'settled', 'returned', 'grace' => 'bg-[#DCFCE7] text-[#166534]',
                                    default => 'bg-[#E2E7FF] text-[#002E97]',
                                };
                                $statusLabel = match ($row['status']) {
                                    'active' => 'Currently On Loan',
                                    'grace' => 'Returned With Grace',
                                    'settled' => 'Fine Settled',
                                    default => 'Returned On Time',
                                };
                            @endphp
                            <article class="rounded-xl border border-[#CBD5E1] bg-white p-4 shadow-sm transition-all hover:border-[#707A6F]">
                                <div class="flex flex-col gap-4 sm:flex-row">
                                    <div class="flex h-28 w-20 shrink-0 flex-col items-center justify-center rounded-lg border border-[#CBD5E1] bg-[#EAEDFF] p-2 text-center">
                                        <span class="material-symbols-outlined text-3xl text-[#065F2A]">{{ str_contains($row['title'], 'Network') ? 'hub' : (str_contains($row['title'], 'Architecture') ? 'architecture' : 'menu_book') }}</span>
                                        <span class="mt-1 text-[9px] font-bold uppercase text-[#64748B]">{{ $row['classification'] }}</span>
                                    </div>
                                    <div class="min-w-0 flex-1 space-y-2">
                                        <div class="flex flex-wrap items-start justify-between gap-2">
                                            <div>
                                                <span class="{{ $statusClass }} inline-block rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider">{{ $statusLabel }}</span>
                                                <span class="ml-1.5 inline-block rounded-full bg-[#F8FAFC] px-2 py-0.5 text-[10px] font-semibold text-[#065F2A]">{{ $row['classification'] }}</span>
                                                <h3 class="mt-1 font-display text-lg font-bold text-[#0F172A]">{{ $row['title'] }}</h3>
                                                <p class="text-sm text-[#64748B]">{{ $row['author'] }}</p>
                                            </div>
                                            <span class="rounded bg-[#F8FAFC] px-2 py-1 text-[10px] font-bold text-[#64748B]">{{ $row['receipt'] }}</span>
                                        </div>
                                        <div class="grid grid-cols-1 gap-2 rounded-lg border border-[#CBD5E1] bg-[#F8FAFC] p-3 text-xs text-[#475569] md:grid-cols-3">
                                            <div><span class="font-bold text-[#0F172A]">LC Call No:</span> {{ $row['call'] }}</div>
                                            <div><span class="font-bold text-[#0F172A]">Accession No:</span> {{ $row['barcode'] }}</div>
                                            <div><span class="font-bold text-[#0F172A]">Desk:</span> {{ $row['desk'] }}</div>
                                        </div>
                                        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-[#E2E8F0] pt-2 text-xs text-[#64748B]">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span><strong class="text-[#0F172A]">Borrowed:</strong> {{ $row['borrowed'] }}</span>
                                                <span>-</span>
                                                <span><strong class="text-[#0F172A]">{{ $row['status'] === 'active' ? 'Due' : 'Returned' }}:</strong> {{ $row['status'] === 'active' ? $row['due'] : $row['returned'] }}</span>
                                                <span>-</span>
                                                <span>{{ $row['note'] }}</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <a class="inline-flex items-center gap-1 rounded bg-[#EAEDFF] px-2 py-1 text-[11px] font-bold text-[#065F2A] hover:bg-[#E2E7FF]" href="https://www.google.com/search?q={{ urlencode($row['title'].' citation') }}" target="_blank" rel="noopener"><span class="material-symbols-outlined text-sm">format_quote</span>Cite</a>
                                                <a class="inline-flex items-center gap-1 rounded bg-[#065F2A] px-2 py-1 text-[11px] font-bold text-white hover:bg-[#064E3B]" href="{{ route('books.catalog', ['q' => $row['title']]) }}"><span class="material-symbols-outlined text-sm">bookmark_add</span>Re-Borrow / Reserve</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach

                        <div class="flex flex-col items-center justify-between gap-3 rounded-xl border border-[#CBD5E1] bg-white p-4 text-sm shadow-sm sm:flex-row">
                            <span class="text-[#64748B]">Showing <strong>1-{{ min($historyRows->count(), 5) }}</strong> of <strong>{{ $totalHistoricalLoans }}</strong> historical loans</span>
                            <div class="flex items-center gap-1">
                                <span class="rounded border border-[#CBD5E1] px-2.5 py-1 text-[#94A3B8]">Previous</span>
                                <span class="rounded bg-[#065F2A] px-3 py-1 font-bold text-white">1</span>
                                <span class="rounded px-3 py-1 text-[#334155] hover:bg-[#F8FAFC]">2</span>
                                <span class="rounded px-3 py-1 text-[#334155] hover:bg-[#F8FAFC]">3</span>
                                <span class="px-1 text-[#64748B]">...</span>
                                <span class="rounded border border-[#CBD5E1] px-2.5 py-1 text-[#334155]">Next</span>
                            </div>
                        </div>
                    </div>

                    <aside class="space-y-4 xl:col-span-4">
                        <section class="relative overflow-hidden rounded-2xl border border-[#064E3B] bg-gradient-to-br from-[#135D2E] to-[#00441D] p-5 text-white shadow-md">
                            @include('partials.celtech-logo', ['class' => 'absolute -bottom-6 -right-6 h-36 w-36 rounded-full bg-white/10 object-contain p-2 opacity-20'])
                            <div class="relative">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center gap-2">
                                        @include('partials.celtech-logo', ['class' => 'h-8 w-8 rounded-full bg-white object-contain p-0.5'])
                                        <div><h4 class="text-[11px] font-bold uppercase tracking-wider text-[#BBF7D0]">CLCST Academic Library</h4><p class="text-[9px] text-emerald-100">Official Faculty Borrower Pass</p></div>
                                    </div>
                                    <span class="rounded-full bg-[#FDC425] px-2 py-0.5 text-[10px] font-bold text-[#251A00]">NFC Active</span>
                                </div>
                                <div class="mt-4">
                                    <h3 class="font-display text-lg font-bold">Engr. Roberto M. Mendoza</h3>
                                    <p class="text-xs text-emerald-100">Department Chair - CICT & CpE</p>
                                    <p class="mt-1 font-mono text-[11px] text-[#BBF7D0]">FAC-2018-0412 - Valid AY 2024-2025</p>
                                </div>
                                <div class="mt-4 rounded-xl bg-white p-3 text-center text-black">
                                    <div class="h-10 rounded bg-[repeating-linear-gradient(90deg,#000_0px,#000_2px,transparent_2px,transparent_4px,#000_4px,#000_7px,transparent_7px,transparent_9px,#000_9px,#000_13px,transparent_13px,transparent_15px)]"></div>
                                    <p class="mt-1 font-mono text-[11px] font-bold tracking-widest">FAC*20180412*LIB</p>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[#065F2A]">verified</span><h3 class="font-display text-base font-bold">Faculty Clearance Status</h3></div>
                                <span class="rounded bg-[#DCFCE7] px-2 py-0.5 text-[10px] font-bold uppercase text-[#166534]">AY 2024-2025</span>
                            </div>
                            <div class="mt-4 rounded-lg border border-[#BBF7D0] bg-[#F0FDF4] p-3">
                                <p class="flex items-start gap-2 text-sm font-bold text-[#166534]"><span class="material-symbols-outlined text-base">check_circle</span>Certified Cleared for Accreditation</p>
                                <p class="mt-1 pl-6 text-xs leading-5 text-[#166534]">No unreturned books, no pending replacement costs, and no unresolved records on file.</p>
                            </div>
                            <div class="mt-4 space-y-1.5 text-xs text-[#475569]">
                                <div class="flex justify-between border-b border-[#E2E8F0] py-1"><span>Library Standing:</span><span class="font-bold text-[#065F2A]">Unencumbered</span></div>
                                <div class="flex justify-between border-b border-[#E2E8F0] py-1"><span>CHED Readiness:</span><span class="font-bold text-[#166534]">100% Compliant</span></div>
                                <div class="flex justify-between py-1"><span>Last Verified:</span><span class="text-[#64748B]">August 28, 2024</span></div>
                            </div>
                            <a class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg border border-[#CBD5E1] bg-[#F8FAFC] px-3 py-2 text-xs font-bold text-[#065F2A] hover:bg-[#E2E8F0]" href="{{ route('staff.section', 'support') }}"><span class="material-symbols-outlined text-base">download_for_offline</span>Download Clearance Slip</a>
                        </section>

                        <section class="rounded-xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[#785A00]">format_quote</span><h3 class="font-display text-base font-bold">Citation & Bibliography</h3></div>
                                <span class="text-[10px] font-semibold text-[#64748B]">{{ $totalHistoricalLoans }} References</span>
                            </div>
                            <p class="mt-3 text-sm leading-6 text-[#64748B]">Export historical reading list records into your academic reference workflow.</p>
                            <div class="mt-4 grid grid-cols-2 gap-2">
                                @foreach (['Zotero Export', 'Mendeley', 'BibTeX File', 'EndNote RIS'] as $label)
                                    <button class="flex items-center justify-center gap-1.5 rounded-lg border border-[#CBD5E1] bg-[#F8FAFC] px-2 py-2 text-xs font-bold text-[#334155] hover:bg-[#E2E8F0]" type="button"><span class="material-symbols-outlined text-sm text-[#065F2A]">library_books</span>{{ $label }}</button>
                                @endforeach
                            </div>
                        </section>

                        <section class="rounded-xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[#065F2A]">public</span><h3 class="font-display text-base font-bold">Institutional Repositories</h3></div>
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            </div>
                            <p class="mt-3 text-sm leading-6 text-[#64748B]">Single-sign-on access to subscribed e-journals and academic databases.</p>
                            <div class="mt-4 space-y-2 text-xs">
                                @foreach ([['OA', 'OpenAthens Access', 'Connected'], ['IEEE', 'IEEE Xplore Digital Library', 'Institutional Access'], ['SD', 'ScienceDirect / Elsevier', 'Full Text Subscribed']] as $repo)
                                    <a class="flex items-center justify-between rounded-lg border border-[#CBD5E1] bg-[#F8FAFC] p-2 hover:bg-[#E2E8F0]" href="{{ route('staff.section', 'support') }}"><span class="flex items-center gap-2"><span class="flex h-6 w-8 items-center justify-center rounded bg-[#E2E7FF] text-[10px] font-bold text-[#002E97]">{{ $repo[0] }}</span><span class="font-bold text-[#0F172A]">{{ $repo[1] }}</span></span><span class="text-[#64748B]">{{ $repo[2] }}</span></a>
                                @endforeach
                            </div>
                        </section>
                    </aside>
                </section>
            @elseif ($section === 'notifications')
                @php
                    $facultyNotifications = [
                        [
                            'category' => 'Syllabus Reserve Alert',
                            'title' => 'Clean Code reserve queue reached high-demand threshold',
                            'body' => 'Four students are queued for the same syllabus title. Add another reserve copy or adjust the closed-reserve loan window before the next class meeting.',
                            'time' => '8 minutes ago',
                            'status' => 'Unread',
                            'icon' => 'menu_book',
                            'tone' => 'amber',
                            'primary' => 'Manage Reserve',
                            'href' => route('staff.section', 'course-reserves'),
                        ],
                        [
                            'category' => 'Instruction Session',
                            'title' => 'Library instruction workshop confirmed',
                            'body' => 'Your IEEE Xplore and Scopus retrieval workshop is confirmed for Oct 28, 2024 at Multimedia AVR Room 302.',
                            'time' => '34 minutes ago',
                            'status' => 'Unread',
                            'icon' => 'cast_for_education',
                            'tone' => 'green',
                            'primary' => 'View Session',
                            'href' => route('staff.section', 'instruction-sessions'),
                        ],
                        [
                            'category' => 'Renewal Approved',
                            'title' => 'Faculty privilege renewal request approved',
                            'body' => 'Head Librarian Ms. Santos approved your extension request. New return due date: November 19, 2024.',
                            'time' => 'Today, 9:10 AM',
                            'status' => 'Unread',
                            'icon' => 'autorenew',
                            'tone' => 'blue',
                            'primary' => 'Open Renewal',
                            'href' => route('staff.section', 'reports'),
                        ],
                        [
                            'category' => 'Hold Waitlist Notice',
                            'title' => 'Reserved textbook is ready for faculty pickup',
                            'body' => 'Computer Networks: A Systems Approach is ready at Desk 02. Please pick it up within the assigned hold window.',
                            'time' => 'Yesterday',
                            'status' => 'Read',
                            'icon' => 'bookmark',
                            'tone' => 'slate',
                            'primary' => 'View Reservation',
                            'href' => route('staff.section', 'reservations'),
                        ],
                        [
                            'category' => 'Clearance',
                            'title' => 'Electronic faculty clearance is ready',
                            'body' => 'Your account is currently compliant with zero unreturned materials. You may generate your authenticated library clearance slip.',
                            'time' => 'Oct 07, 2024',
                            'status' => 'Read',
                            'icon' => 'verified',
                            'tone' => 'green',
                            'primary' => 'Generate Slip',
                            'href' => route('staff.section', 'history'),
                        ],
                    ];
                    $unreadCount = collect($facultyNotifications)->where('status', 'Unread')->count();
                    $reserveAlertCount = collect($facultyNotifications)->where('category', 'Syllabus Reserve Alert')->count();
                    $sessionAlertCount = collect($facultyNotifications)->where('category', 'Instruction Session')->count();
                @endphp

                <section class="rounded-2xl border border-[#CBD5E1] bg-white p-6 shadow-sm lg:p-8">
                    <div class="flex flex-col justify-between gap-5 lg:flex-row lg:items-start">
                        <div>
                            <nav aria-label="Breadcrumb" class="mb-3 flex flex-wrap items-center gap-2 text-xs font-semibold text-[#64748B]">
                                <span>Faculty Services</span>
                                <span class="material-symbols-outlined text-sm">chevron_right</span>
                                <span class="font-bold text-[#065F2A]">Notifications & Academic Alerts Hub</span>
                            </nav>
                            <span class="inline-flex rounded-full bg-[#FEF3C7] px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-[#92400E]">3 Unread Alerts</span>
                            <h1 class="mt-4 font-display text-3xl font-bold text-[#064E3B] md:text-4xl">Faculty Notifications & Academic Alerts</h1>
                            <p class="mt-3 max-w-3xl text-sm leading-6 text-[#475569]">Review syllabus reserve notices, renewal outcomes, instruction session confirmations, hold updates, and clearance messages in one faculty notification center.</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a class="inline-flex items-center gap-2 rounded-lg border border-[#CBD5E1] bg-white px-3 py-2 text-xs font-bold text-[#334155] hover:bg-[#F8FAFC]" href="{{ route('staff.section', 'notifications') }}"><span class="material-symbols-outlined text-base">done_all</span>Mark All as Read</a>
                            <a class="inline-flex items-center gap-2 rounded-lg border border-[#CBD5E1] bg-white px-3 py-2 text-xs font-bold text-[#334155] hover:bg-[#F8FAFC]" href="#notification-preferences"><span class="material-symbols-outlined text-base">tune</span>Preferences</a>
                            <a class="inline-flex items-center gap-2 rounded-lg bg-[#065F2A] px-4 py-2 text-xs font-bold text-white hover:bg-[#064E3B]" href="{{ route('staff.section', 'support') }}"><span class="material-symbols-outlined text-base">support_agent</span>Ask Desk</a>
                        </div>
                    </div>
                </section>

                <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ([
                        ['label' => 'Unread Notifications', 'value' => $unreadCount, 'meta' => 'Needs faculty review', 'icon' => 'notifications_active'],
                        ['label' => 'Syllabus & Reserve Alerts', 'value' => $reserveAlertCount, 'meta' => 'Course material updates', 'icon' => 'menu_book'],
                        ['label' => 'Instruction Session Alerts', 'value' => $sessionAlertCount, 'meta' => 'Workshop confirmations', 'icon' => 'cast_for_education'],
                        ['label' => 'Delivery Channels', 'value' => 4, 'meta' => 'Portal, email, SMS, terminal', 'icon' => 'rss_feed'],
                    ] as $metric)
                        <article class="rounded-xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-[#64748B]">{{ $metric['label'] }}</p>
                                    <h2 class="mt-2 font-display text-3xl font-bold text-[#065F2A]">{{ $metric['value'] }}</h2>
                                    <p class="mt-1 text-xs text-[#64748B]">{{ $metric['meta'] }}</p>
                                </div>
                                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#FEF3C7] text-[#92400E]"><span class="material-symbols-outlined">{{ $metric['icon'] }}</span></span>
                            </div>
                        </article>
                    @endforeach
                </section>

                <section class="rounded-2xl border border-[#CBD5E1] bg-white p-4 shadow-sm">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex flex-wrap gap-2 text-xs font-bold">
                            <a class="rounded-lg bg-[#065F2A] px-3.5 py-2 text-white" href="{{ route('staff.section', 'notifications') }}">All Notifications</a>
                            <a class="rounded-lg border border-[#CBD5E1] bg-[#F8FAFC] px-3.5 py-2 text-[#334155] hover:bg-[#E2E8F0]" href="#unread-alerts">Unread</a>
                            <a class="rounded-lg border border-[#CBD5E1] bg-[#F8FAFC] px-3.5 py-2 text-[#334155] hover:bg-[#E2E8F0]" href="{{ route('staff.section', 'course-reserves') }}">Reserve Alerts</a>
                            <a class="rounded-lg border border-[#CBD5E1] bg-[#F8FAFC] px-3.5 py-2 text-[#334155] hover:bg-[#E2E8F0]" href="{{ route('staff.section', 'instruction-sessions') }}">Instruction Sessions</a>
                            <a class="rounded-lg border border-[#CBD5E1] bg-[#F8FAFC] px-3.5 py-2 text-[#334155] hover:bg-[#E2E8F0]" href="{{ route('staff.section', 'reports') }}">Renewals</a>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button class="inline-flex items-center gap-1 rounded-lg border border-[#CBD5E1] bg-[#F8FAFC] px-3 py-2 text-xs font-bold text-[#334155]" type="button"><span class="material-symbols-outlined text-base">select_all</span>Select All</button>
                            <button class="inline-flex items-center gap-1 rounded-lg border border-[#CBD5E1] bg-[#F8FAFC] px-3 py-2 text-xs font-bold text-[#334155]" type="button"><span class="material-symbols-outlined text-base">archive</span>Archive</button>
                        </div>
                    </div>
                </section>

                <section class="grid grid-cols-1 gap-6 xl:grid-cols-12">
                    <div class="space-y-4 xl:col-span-8" id="unread-alerts">
                        @foreach ($facultyNotifications as $notice)
                            @php
                                $toneClasses = match ($notice['tone']) {
                                    'amber' => ['icon' => 'bg-[#FEF3C7] text-[#92400E]', 'border' => 'border-[#FACC15]'],
                                    'green' => ['icon' => 'bg-[#DCFCE7] text-[#166534]', 'border' => 'border-[#BBF7D0]'],
                                    'blue' => ['icon' => 'bg-[#DBEAFE] text-[#1D4ED8]', 'border' => 'border-[#BFDBFE]'],
                                    default => ['icon' => 'bg-[#F1F5F9] text-[#475569]', 'border' => 'border-[#CBD5E1]'],
                                };
                            @endphp
                            <article class="group rounded-2xl border {{ $notice['status'] === 'Unread' ? $toneClasses['border'].' bg-white' : 'border-[#CBD5E1] bg-[#F8FAFC]' }} p-5 shadow-sm">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                                    <span class="{{ $toneClasses['icon'] }} flex h-11 w-11 shrink-0 items-center justify-center rounded-xl">
                                        <span class="material-symbols-outlined">{{ $notice['icon'] }}</span>
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="rounded-full bg-[#EEF2FF] px-2 py-0.5 text-[10px] font-bold uppercase text-[#334155]">{{ $notice['category'] }}</span>
                                            <span class="{{ $notice['status'] === 'Unread' ? 'bg-[#FEE2E2] text-[#B91C1C]' : 'bg-[#E2E8F0] text-[#475569]' }} rounded-full px-2 py-0.5 text-[10px] font-bold uppercase">{{ $notice['status'] }}</span>
                                            <span class="text-[11px] font-semibold text-[#64748B]">{{ $notice['time'] }}</span>
                                        </div>
                                        <h3 class="mt-2 font-display text-lg font-bold text-[#0F172A] group-hover:text-[#065F2A]">{{ $notice['title'] }}</h3>
                                        <p class="mt-2 text-sm leading-6 text-[#475569]">{{ $notice['body'] }}</p>
                                        <div class="mt-4 flex flex-wrap gap-2">
                                            <a class="inline-flex items-center gap-1.5 rounded-lg bg-[#065F2A] px-3 py-1.5 text-xs font-bold text-white hover:bg-[#064E3B]" href="{{ $notice['href'] }}"><span class="material-symbols-outlined text-base">open_in_new</span>{{ $notice['primary'] }}</a>
                                            <a class="inline-flex items-center gap-1.5 rounded-lg border border-[#CBD5E1] bg-white px-3 py-1.5 text-xs font-bold text-[#334155] hover:bg-[#F8FAFC]" href="{{ route('staff.section', 'notifications') }}"><span class="material-symbols-outlined text-base">done</span>Mark Read</a>
                                            <button class="px-2 py-1 text-xs font-bold text-[#B91C1C] hover:underline" type="button">Dismiss</button>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach

                        <div class="text-center">
                            <a class="inline-flex items-center gap-2 rounded-lg border border-[#CBD5E1] bg-white px-5 py-2 text-xs font-bold text-[#334155] shadow-sm hover:bg-[#F8FAFC]" href="{{ route('staff.section', 'history') }}"><span class="material-symbols-outlined text-base">history</span>View Past Notifications Archive</a>
                        </div>
                    </div>

                    <aside class="space-y-6 xl:col-span-4">
                        <section class="rounded-2xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                            <div class="rounded-2xl bg-[#064E3B] p-5 text-white">
                                <div class="flex items-center gap-3">
                                    @include('partials.celtech-logo', ['class' => 'flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-white p-1'])
                                    <div>
                                        <p class="text-xs font-bold uppercase text-[#BBF7D0]">Faculty Pass</p>
                                        <h3 class="font-display text-lg font-bold">Engr. Roberto M. Mendoza, PECE</h3>
                                        <p class="text-[11px] text-white/80">Faculty ID: CLCST-FAC-2018-0412</p>
                                    </div>
                                </div>
                                <div class="mt-5 rounded-xl bg-white p-4 text-center text-[#0F172A]">
                                    <div class="mx-auto h-10 w-full max-w-xs bg-[repeating-linear-gradient(90deg,#0F172A_0_2px,transparent_2px_5px,#0F172A_5px_7px,transparent_7px_10px)]"></div>
                                    <p class="mt-2 font-mono text-xs font-bold tracking-[0.25em]">FAC-NOTIFY-0412</p>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-2xl border border-[#CBD5E1] bg-white p-5 shadow-sm" id="notification-preferences">
                            <h2 class="font-display text-lg font-bold text-[#064E3B]">Alert Delivery Channels</h2>
                            <div class="mt-4 space-y-3">
                                @foreach ([['Portal Alerts', 'Enabled', true], ['School Email', 'Enabled', true], ['SMS Urgent Alerts', 'Enabled for due dates', true], ['Faculty Terminal & Mac', 'Connected', true]] as $channel)
                                    <div class="flex items-center justify-between rounded-xl border border-[#CBD5E1] bg-[#F8FAFC] p-3">
                                        <div>
                                            <p class="text-sm font-bold text-[#0F172A]">{{ $channel[0] }}</p>
                                            <p class="text-xs text-[#64748B]">{{ $channel[1] }}</p>
                                        </div>
                                        <span class="flex h-6 w-11 items-center rounded-full bg-[#065F2A] p-1"><span class="h-4 w-4 rounded-full bg-white shadow-sm translate-x-5"></span></span>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-4 border-t border-[#CBD5E1] pt-4">
                                <p class="text-xs font-bold text-[#334155]">Notification Digest Cadence</p>
                                <div class="mt-2 grid grid-cols-2 gap-2 text-xs font-bold">
                                    <a class="rounded-lg bg-[#065F2A] px-3 py-2 text-center text-white" href="{{ route('staff.section', 'notifications') }}">Instant</a>
                                    <a class="rounded-lg border border-[#CBD5E1] bg-[#F8FAFC] px-3 py-2 text-center text-[#475569]" href="{{ route('staff.section', 'notifications') }}">Daily 5 PM</a>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-2xl border border-[#CBD5E1] bg-[#F0FDF4] p-5 shadow-sm">
                            <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[#065F2A]">support_agent</span><h2 class="font-display text-lg font-bold text-[#064E3B]">Notification Help</h2></div>
                            <p class="mt-3 text-sm leading-6 text-[#475569]">Contact the library desk if alerts are missing from email, SMS, or faculty terminal channels.</p>
                            <a class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-[#065F2A] px-3 py-2 text-xs font-bold text-white hover:bg-[#064E3B]" href="{{ route('staff.section', 'support') }}"><span class="material-symbols-outlined text-base">chat</span>Contact Desk</a>
                        </section>
                    </aside>
                </section>
            @elseif ($section === 'instruction-sessions')
                @php
                    $instructionSessions = [
                        [
                            'title' => 'Advanced Engineering Research & IEEE Xplore Retrieval Workshop',
                            'course' => 'CPE401 - Capstone Research 1',
                            'faculty' => 'Engr. Roberto M. Mendoza, PECE',
                            'date' => 'Oct 28, 2024',
                            'time' => '10:00 AM - 11:30 AM',
                            'venue' => 'Multimedia AVR Room 302',
                            'students' => 42,
                            'status' => 'Confirmed',
                            'topics' => ['IEEE Xplore', 'Scopus search strategy', 'Citation tracing', 'Keyword mapping'],
                            'tone' => 'green',
                        ],
                        [
                            'title' => 'Information Literacy & Academic Integrity',
                            'course' => 'IT401 - Capstone Research 1',
                            'faculty' => 'Prof. Elena Reyes',
                            'date' => 'Nov 05, 2024',
                            'time' => '1:00 PM - 2:30 PM',
                            'venue' => 'Library Instruction Lab',
                            'students' => 38,
                            'status' => 'Pending Confirmation',
                            'topics' => ['Turnitin basics', 'Plagiarism prevention', 'APA 7 citations'],
                            'tone' => 'amber',
                        ],
                        [
                            'title' => 'Book Search & Pickup Shelf Orientation',
                            'course' => 'CS102 - Programming Fundamentals',
                            'faculty' => 'Ms. Carla Dizon',
                            'date' => 'Oct 14, 2024',
                            'time' => '9:00 AM - 10:00 AM',
                            'venue' => 'CLCST Learning Commons',
                            'students' => 31,
                            'status' => 'Completed',
                            'topics' => ['Book Catalog search', 'Call numbers', 'Borrowing rules'],
                            'tone' => 'blue',
                        ],
                    ];
                    $scheduledCount = collect($instructionSessions)->whereIn('status', ['Confirmed', 'Pending Confirmation'])->count();
                    $studentTotal = collect($instructionSessions)->sum('students');
                    $topicCount = collect($instructionSessions)->flatMap(fn ($session) => $session['topics'])->unique()->count();
                @endphp

                <section class="rounded-2xl border border-[#CBD5E1] bg-white p-6 shadow-sm lg:p-8">
                    <div class="flex flex-col justify-between gap-5 lg:flex-row lg:items-start">
                        <div>
                            <nav aria-label="Breadcrumb" class="mb-3 flex flex-wrap items-center gap-2 text-xs font-semibold text-[#64748B]">
                                <span>Faculty Academic Services</span>
                                <span class="material-symbols-outlined text-sm">chevron_right</span>
                                <span class="font-bold text-[#065F2A]">Library Instruction & Literacy</span>
                            </nav>
                            <span class="inline-flex rounded-full bg-[#DCFCE7] px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-[#166534]">Academic Services Schedule</span>
                            <h1 class="mt-4 font-display text-3xl font-bold text-[#064E3B] md:text-4xl">Library Instruction Sessions</h1>
                            <p class="mt-3 max-w-3xl text-sm leading-6 text-[#475569]">Book and manage librarian-led workshops for database retrieval, Book Catalog search, citation management, academic integrity, and capstone research support.</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a class="inline-flex items-center gap-2 rounded-lg bg-[#065F2A] px-4 py-2 text-xs font-bold text-white hover:bg-[#064E3B]" href="#instruction-booking-form"><span class="material-symbols-outlined text-base">add_circle</span>Book Library Instruction Session</a>
                            <a class="inline-flex items-center gap-2 rounded-lg border border-[#CBD5E1] bg-white px-3 py-2 text-xs font-bold text-[#334155] hover:bg-[#F8FAFC]" href="{{ route('staff.section', 'reading-lists') }}"><span class="material-symbols-outlined text-base">format_list_bulleted</span>Attach Reading List</a>
                            <a class="inline-flex items-center gap-2 rounded-lg border border-[#CBD5E1] bg-white px-3 py-2 text-xs font-bold text-[#334155] hover:bg-[#F8FAFC]" href="{{ route('staff.section', 'support') }}"><span class="material-symbols-outlined text-base">event</span>Sync Calendar</a>
                        </div>
                    </div>
                </section>

                <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ([
                        ['label' => 'Scheduled Sessions', 'value' => $scheduledCount, 'meta' => 'Workshops booked this term', 'icon' => 'event_available'],
                        ['label' => 'Students to be Trained', 'value' => $studentTotal, 'meta' => 'Across active class sections', 'icon' => 'groups'],
                        ['label' => 'Topics Covered', 'value' => $topicCount, 'meta' => 'Research and literacy modules', 'icon' => 'school'],
                        ['label' => 'Session Venue', 'value' => '70%', 'meta' => 'AVR / Instruction Lab capacity', 'icon' => 'meeting_room'],
                    ] as $metric)
                        <article class="rounded-xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-[#64748B]">{{ $metric['label'] }}</p>
                                    <h2 class="mt-2 font-display text-3xl font-bold text-[#065F2A]">{{ $metric['value'] }}</h2>
                                    <p class="mt-1 text-xs text-[#64748B]">{{ $metric['meta'] }}</p>
                                </div>
                                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#EEF2FF] text-[#002E97]"><span class="material-symbols-outlined">{{ $metric['icon'] }}</span></span>
                            </div>
                        </article>
                    @endforeach
                </section>

                <section class="grid grid-cols-1 gap-6 xl:grid-cols-12">
                    <div class="space-y-6 xl:col-span-8">
                        <section class="rounded-2xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                            <div class="mb-4 flex flex-col justify-between gap-3 border-b border-[#CBD5E1] pb-4 sm:flex-row sm:items-center">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[#065F2A]">cast_for_education</span>
                                    <h2 class="font-display text-xl font-bold text-[#064E3B]">Active Scheduled Sessions</h2>
                                </div>
                                <div class="flex flex-wrap gap-2 text-xs font-bold">
                                    <a class="rounded-lg bg-[#065F2A] px-3 py-1.5 text-white" href="{{ route('staff.section', 'instruction-sessions') }}">All</a>
                                    <a class="rounded-lg border border-[#CBD5E1] px-3 py-1.5 text-[#334155] hover:bg-[#F8FAFC]" href="#instruction-booking-form">Book New</a>
                                </div>
                            </div>

                            <div class="space-y-4">
                                @foreach ($instructionSessions as $session)
                                    <article class="rounded-2xl border border-[#CBD5E1] bg-[#F8FAFC] p-5">
                                        <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-start">
                                            <div class="min-w-0">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="{{ $session['tone'] === 'green' ? 'bg-[#DCFCE7] text-[#166534]' : ($session['tone'] === 'amber' ? 'bg-[#FEF3C7] text-[#92400E]' : 'bg-[#DBEAFE] text-[#1D4ED8]') }} rounded-full px-2.5 py-1 text-[10px] font-bold uppercase">{{ $session['status'] }}</span>
                                                    <span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-semibold text-[#475569]">{{ $session['course'] }}</span>
                                                </div>
                                                <h3 class="mt-3 font-display text-xl font-bold text-[#064E3B]">{{ $session['title'] }}</h3>
                                                <p class="mt-1 text-sm text-[#64748B]">{{ $session['faculty'] }}</p>
                                                <div class="mt-4 grid grid-cols-1 gap-3 text-sm text-[#475569] sm:grid-cols-3">
                                                    <span class="flex items-center gap-2"><span class="material-symbols-outlined text-base text-[#065F2A]">calendar_month</span>{{ $session['date'] }}</span>
                                                    <span class="flex items-center gap-2"><span class="material-symbols-outlined text-base text-[#065F2A]">schedule</span>{{ $session['time'] }}</span>
                                                    <span class="flex items-center gap-2"><span class="material-symbols-outlined text-base text-[#065F2A]">location_on</span>{{ $session['venue'] }}</span>
                                                </div>
                                                <div class="mt-4 flex flex-wrap gap-2">
                                                    @foreach ($session['topics'] as $topic)
                                                        <span class="rounded-full border border-[#CBD5E1] bg-white px-2 py-1 text-[10px] font-bold text-[#334155]">{{ $topic }}</span>
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div class="shrink-0 rounded-xl border border-[#CBD5E1] bg-white p-4 text-center">
                                                <p class="font-display text-3xl font-bold text-[#065F2A]">{{ $session['students'] }}</p>
                                                <p class="text-[10px] font-bold uppercase tracking-wider text-[#64748B]">Students</p>
                                            </div>
                                        </div>
                                        <div class="mt-4 flex flex-wrap gap-2 border-t border-[#CBD5E1] pt-4">
                                            <a class="inline-flex items-center gap-1.5 rounded-lg border border-[#CBD5E1] bg-white px-3 py-1.5 text-xs font-bold text-[#065F2A] hover:bg-[#F0FDF4]" href="{{ route('staff.section', 'instruction-sessions') }}"><span class="material-symbols-outlined text-base">edit</span>Edit Session Details</a>
                                            <a class="inline-flex items-center gap-1.5 rounded-lg border border-[#CBD5E1] bg-white px-3 py-1.5 text-xs font-bold text-[#334155] hover:bg-[#F8FAFC]" href="{{ route('staff.section', 'support') }}"><span class="material-symbols-outlined text-base">mail</span>Email Class Notice</a>
                                            <a class="inline-flex items-center gap-1.5 rounded-lg border border-[#CBD5E1] bg-white px-3 py-1.5 text-xs font-bold text-[#334155] hover:bg-[#F8FAFC]" href="{{ route('staff.section', 'reading-lists') }}"><span class="material-symbols-outlined text-base">attachment</span>Attach Resources</a>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </section>

                        <section class="rounded-2xl border border-[#CBD5E1] bg-white p-5 shadow-sm" id="instruction-booking-form">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-[#065F2A]">edit_calendar</span>
                                <h2 class="font-display text-xl font-bold text-[#064E3B]">Book a New Library Instruction Session</h2>
                            </div>
                            <form class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2" method="GET" action="{{ route('staff.section', 'instruction-sessions') }}">
                                <label class="space-y-1 text-xs font-bold text-[#0F172A]">Course / Section
                                    <select class="w-full rounded-lg border-[#CBD5E1] text-sm focus:border-[#065F2A] focus:ring-[#065F2A]">
                                        <option>IT401 - Capstone Research 1 (Sec A)</option>
                                        <option>CPE401 - Engineering Research</option>
                                        <option>CS102 - Programming Fundamentals</option>
                                    </select>
                                </label>
                                <label class="space-y-1 text-xs font-bold text-[#0F172A]">Instruction Topic
                                    <select class="w-full rounded-lg border-[#CBD5E1] text-sm focus:border-[#065F2A] focus:ring-[#065F2A]">
                                        <option>Research Methodology & Capstone Literature Reviews</option>
                                        <option>Book Search and Library Call Number Orientation</option>
                                        <option>Academic Integrity and Citation Management</option>
                                    </select>
                                </label>
                                <label class="space-y-1 text-xs font-bold text-[#0F172A]">Preferred Date
                                    <input class="w-full rounded-lg border-[#CBD5E1] text-sm focus:border-[#065F2A] focus:ring-[#065F2A]" type="date">
                                </label>
                                <label class="space-y-1 text-xs font-bold text-[#0F172A]">Preferred Time
                                    <input class="w-full rounded-lg border-[#CBD5E1] text-sm focus:border-[#065F2A] focus:ring-[#065F2A]" type="time">
                                </label>
                                <label class="space-y-1 text-xs font-bold text-[#0F172A]">Expected Students
                                    <input class="w-full rounded-lg border-[#CBD5E1] text-sm focus:border-[#065F2A] focus:ring-[#065F2A]" placeholder="42">
                                </label>
                                <label class="space-y-1 text-xs font-bold text-[#0F172A]">Venue Preference
                                    <select class="w-full rounded-lg border-[#CBD5E1] text-sm focus:border-[#065F2A] focus:ring-[#065F2A]">
                                        <option>Multimedia AVR Room 302</option>
                                        <option>Library Instruction Lab</option>
                                        <option>Classroom visit</option>
                                    </select>
                                </label>
                                <textarea class="min-h-24 rounded-lg border-[#CBD5E1] text-sm focus:border-[#065F2A] focus:ring-[#065F2A] md:col-span-2" placeholder="Learning outcomes, database needs, citation style, or assignment context"></textarea>
                                <div class="flex justify-end gap-3 md:col-span-2">
                                    <button class="rounded-lg border border-[#CBD5E1] px-4 py-2 text-xs font-bold text-[#334155] hover:bg-[#F8FAFC]" type="reset">Reset</button>
                                    <button class="rounded-lg bg-[#065F2A] px-5 py-2 text-xs font-bold text-white hover:bg-[#064E3B]" type="submit">Submit Booking Request</button>
                                </div>
                            </form>
                        </section>
                    </div>

                    <aside class="space-y-6 xl:col-span-4">
                        <section class="rounded-2xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                            <div class="flex items-center justify-between"><h2 class="font-display text-lg font-bold text-[#064E3B]">Faculty Digital Pass</h2><span class="rounded bg-[#DCFCE7] px-2 py-0.5 text-[10px] font-bold text-[#166534]">AV LAB READY</span></div>
                            <div class="mt-4 rounded-2xl bg-[#064E3B] p-5 text-white">
                                <div class="flex items-center gap-3">
                                    @include('partials.celtech-logo', ['class' => 'flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-white p-1'])
                                    <div><p class="text-xs font-bold uppercase text-[#BBF7D0]">Faculty Digital Library Document</p><p class="font-display text-lg font-bold">Engr. Roberto Mendoza</p><p class="text-xs text-[#D1FAE5]">Full Research & AV Lab Booking</p></div>
                                </div>
                                <div class="mt-5 rounded-xl bg-white p-4 text-center text-[#0F172A]">
                                    <div class="mx-auto h-10 w-full max-w-xs bg-[repeating-linear-gradient(90deg,#0F172A_0_2px,transparent_2px_5px,#0F172A_5px_7px,transparent_7px_10px)]"></div>
                                    <p class="mt-2 font-mono text-xs font-bold tracking-[0.25em]">FAC-2018-0412-LIB</p>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-2xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                            <h2 class="font-display text-lg font-bold text-[#064E3B]">Instruction Lab Availability</h2>
                            <div class="mt-4 space-y-3">
                                <div class="flex justify-between text-xs font-bold"><span>Reserved for workshops</span><span>70%</span></div>
                                <div class="h-3 overflow-hidden rounded-full bg-[#E2E8F0]"><div class="h-full rounded-full bg-[#065F2A]" style="width: 70%"></div></div>
                                <p class="text-xs leading-5 text-[#64748B]">AVR Room 302 and Library Instruction Lab are available for confirmed faculty-led classes.</p>
                            </div>
                        </section>

                        <section class="rounded-2xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                            <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[#92400E]">policy</span><h2 class="font-display text-lg font-bold text-[#064E3B]">Library Instruction Policies</h2></div>
                            <div class="mt-4 space-y-3 text-sm leading-6 text-[#475569]">
                                <p><strong class="text-[#0F172A]">Advance booking:</strong> submit requests at least five working days before the preferred session date.</p>
                                <p><strong class="text-[#0F172A]">Faculty presence:</strong> faculty member presence is required throughout the workshop duration.</p>
                                <p><strong class="text-[#0F172A]">Materials:</strong> attach assignments, reading lists, or research requirements for better librarian preparation.</p>
                            </div>
                        </section>

                        <section class="rounded-2xl border border-[#CBD5E1] bg-[#F0FDF4] p-5 shadow-sm">
                            <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[#065F2A]">support_agent</span><h2 class="font-display text-lg font-bold text-[#064E3B]">Instruction Librarian</h2></div>
                            <p class="mt-3 text-sm leading-6 text-[#475569]">Coordinate session outlines, database access, and faculty calendar changes with the Academic Services Desk.</p>
                            <a class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-[#065F2A] px-3 py-2 text-xs font-bold text-white hover:bg-[#064E3B]" href="{{ route('staff.section', 'support') }}"><span class="material-symbols-outlined text-base">chat</span>Contact Librarian</a>
                        </section>
                    </aside>
                </section>
            @elseif ($section === 'course-reserves')
                @php
                    $bookLookup = collect($catalogBooks)->keyBy('id');
                    $reserveCourses = [
                        [
                            'code' => 'IT301',
                            'course' => 'Systems Analysis and Design',
                            'faculty' => 'Prof. Eleanor Vance',
                            'department' => 'College of Computer Studies',
                            'status' => 'Active Closed Reserve',
                            'period' => 'Oct 21 - Dec 06, 2024',
                            'loan' => '3-Hour Closed Reserve at Desk 02',
                            'books' => [
                                ['book' => $bookLookup->get('object-oriented-se'), 'copies' => '3 of 3 copies assigned', 'rule' => 'Room use plus one overnight copy'],
                                ['book' => $bookLookup->get('clean-code'), 'copies' => '4 copies linked', 'rule' => 'High-demand queue monitored'],
                            ],
                            'digital' => 'E-reserve supplementary PDF linked in LMS, fair-use verified.',
                        ],
                        [
                            'code' => 'CS302',
                            'course' => 'Database Management Systems',
                            'faculty' => 'Dr. Liza Mercado',
                            'department' => 'Computer Science Core',
                            'status' => 'Pending Copy Check',
                            'period' => 'Nov 04 - Dec 13, 2024',
                            'loan' => 'Library Desk review in progress',
                            'books' => [
                                ['book' => $bookLookup->get('algorithms'), 'copies' => '1 copy ready, 2 requested', 'rule' => 'Supplemental data structures reference'],
                                ['book' => $bookLookup->get('python-programming'), 'copies' => '4 copies available', 'rule' => 'Lab programming support'],
                            ],
                            'digital' => 'Instructor upload is queued for bibliographic validation.',
                        ],
                        [
                            'code' => 'CPE402',
                            'course' => 'Microprocessor Systems & Embedded Architecture',
                            'faculty' => 'Engr. Mark Ramos',
                            'department' => 'Engineering',
                            'status' => 'Lab Reserve Ready',
                            'period' => 'Whole semester',
                            'loan' => 'Hardware Lab Reserve',
                            'books' => [
                                ['book' => $bookLookup->get('computer-networks'), 'copies' => '2 reference volumes on closed reserve', 'rule' => 'In-library use only'],
                                ['book' => $bookLookup->get('ai-modern-approach'), 'copies' => '2 supporting titles ready', 'rule' => 'Machine learning module reference'],
                            ],
                            'digital' => 'Lab manual access restricted to enrolled class sections.',
                        ],
                    ];
                    $activeReserveCount = collect($reserveCourses)->where('status', 'Active Closed Reserve')->count() + 5;
                    $reserveCopies = collect($reserveCourses)->sum(fn ($course) => count($course['books']) * 2);
                @endphp

                <section class="rounded-2xl border border-[#CBD5E1] bg-white p-6 shadow-sm lg:p-8">
                    <div class="flex flex-col justify-between gap-5 lg:flex-row lg:items-start">
                        <div>
                            <nav aria-label="Breadcrumb" class="mb-3 flex flex-wrap items-center gap-2 text-xs font-semibold text-[#64748B]">
                                <span>Faculty Academic Portal</span>
                                <span class="material-symbols-outlined text-sm">chevron_right</span>
                                <span>Academic Requests</span>
                                <span class="material-symbols-outlined text-sm">chevron_right</span>
                                <span class="font-bold text-[#065F2A]">Course Syllabus Reserves</span>
                            </nav>
                            <span class="inline-flex rounded-full bg-[#FEF3C7] px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-[#92400E]">Faculty Pickup Shelf Control</span>
                            <h1 class="mt-4 font-display text-3xl font-bold text-[#064E3B] md:text-4xl">Course Syllabus Reserves</h1>
                            <p class="mt-3 max-w-3xl text-sm leading-6 text-[#475569]">Track prescribed textbook copies, closed reserve rules, e-reserve access, and syllabus-linked library materials for current faculty courses.</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a class="inline-flex items-center gap-2 rounded-lg bg-[#065F2A] px-4 py-2 text-xs font-bold text-white hover:bg-[#064E3B]" href="#reserve-request-form"><span class="material-symbols-outlined text-base">add_circle</span>Submit New Syllabus Reserve</a>
                            <a class="inline-flex items-center gap-2 rounded-lg border border-[#CBD5E1] bg-white px-3 py-2 text-xs font-bold text-[#334155] hover:bg-[#F8FAFC]" href="{{ route('staff.section', 'reading-lists') }}"><span class="material-symbols-outlined text-base">format_list_bulleted</span>Reading Lists</a>
                            <a class="inline-flex items-center gap-2 rounded-lg border border-[#CBD5E1] bg-white px-3 py-2 text-xs font-bold text-[#334155] hover:bg-[#F8FAFC]" href="{{ route('staff.section', 'support') }}"><span class="material-symbols-outlined text-base">download</span>Download Reserve Slips</a>
                        </div>
                    </div>
                </section>

                <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ([
                        ['label' => 'Active Syllabus Reserves', 'value' => $activeReserveCount, 'meta' => 'Closed reserve courses', 'icon' => 'menu_book'],
                        ['label' => 'Desk Reserve Copies', 'value' => $reserveCopies, 'meta' => '3-hour closed reserve', 'icon' => 'inventory_2'],
                        ['label' => 'Pending Copy Checks', 'value' => 3, 'meta' => 'Need librarian validation', 'icon' => 'fact_check'],
                        ['label' => 'E-Reserve Digital Access', 'value' => 8, 'meta' => 'Fair-use verified files', 'icon' => 'cloud_done'],
                    ] as $metric)
                        <article class="rounded-xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-[#64748B]">{{ $metric['label'] }}</p>
                                    <h2 class="mt-2 font-display text-3xl font-bold text-[#065F2A]">{{ $metric['value'] }}</h2>
                                    <p class="mt-1 text-xs text-[#64748B]">{{ $metric['meta'] }}</p>
                                </div>
                                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#DCFCE7] text-[#166534]"><span class="material-symbols-outlined">{{ $metric['icon'] }}</span></span>
                            </div>
                        </article>
                    @endforeach
                </section>

                <section class="rounded-2xl border border-[#CBD5E1] bg-white p-4 shadow-sm">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <form class="flex flex-1 flex-col gap-3 sm:flex-row" method="GET" action="{{ route('books.catalog') }}">
                            <div class="relative flex-1">
                                <span class="material-symbols-outlined absolute left-3 top-2.5 text-[#64748B]">search</span>
                                <input class="w-full rounded-lg border-[#CBD5E1] py-2 pl-10 pr-3 text-sm focus:border-[#065F2A] focus:ring-[#065F2A]" name="q" placeholder="Search Books, syllabi, ISBN, course code...">
                            </div>
                            <button class="rounded-lg bg-[#065F2A] px-4 py-2 text-xs font-bold text-white hover:bg-[#064E3B]" type="submit">Search</button>
                        </form>
                        <div class="flex flex-wrap gap-2 text-xs font-bold">
                            <a class="rounded-lg bg-[#065F2A] px-4 py-2 text-white" href="{{ route('staff.section', 'course-reserves') }}">All Active</a>
                            <a class="rounded-lg border border-[#CBD5E1] bg-[#F8FAFC] px-4 py-2 text-[#334155] hover:bg-[#E2E8F0]" href="#pending">Pending Review</a>
                            <a class="rounded-lg border border-[#CBD5E1] bg-[#F8FAFC] px-4 py-2 text-[#334155] hover:bg-[#E2E8F0]" href="#reserve-request-form">New Request</a>
                        </div>
                    </div>
                </section>

                <section class="grid grid-cols-1 gap-6 xl:grid-cols-12">
                    <div class="space-y-6 xl:col-span-8">
                        @foreach ($reserveCourses as $course)
                            <article class="overflow-hidden rounded-2xl border border-[#CBD5E1] bg-white shadow-sm" @if ($course['status'] === 'Pending Copy Check') id="pending" @endif>
                                <div class="flex flex-col justify-between gap-4 border-b border-[#CBD5E1] bg-[#F8FAFC] p-5 lg:flex-row lg:items-start">
                                    <div class="flex gap-4">
                                        <div class="flex h-14 w-14 shrink-0 flex-col items-center justify-center rounded-lg bg-[#065F2A] font-bold text-white">
                                            <span class="text-[9px] uppercase">Course</span>
                                            <span class="text-sm leading-none">{{ $course['code'] }}</span>
                                        </div>
                                        <div>
                                            <div class="flex flex-wrap items-center gap-2">
                                                <h2 class="font-display text-xl font-bold text-[#064E3B]">{{ $course['code'] }} - {{ $course['course'] }}</h2>
                                                <span class="{{ $course['status'] === 'Pending Copy Check' ? 'bg-[#FEF3C7] text-[#92400E]' : 'bg-[#DCFCE7] text-[#166534]' }} rounded-full px-2 py-0.5 text-[10px] font-bold uppercase">{{ $course['status'] }}</span>
                                            </div>
                                            <p class="mt-1 text-xs text-[#64748B]">{{ $course['faculty'] }} - {{ $course['department'] }}</p>
                                            <p class="mt-2 text-sm text-[#475569]">{{ $course['period'] }} - {{ $course['loan'] }}</p>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <a class="rounded-lg border border-[#CBD5E1] px-3 py-1.5 text-xs font-bold text-[#065F2A] hover:bg-[#F0FDF4]" href="{{ route('staff.section', 'course-reserves') }}">Manage Copies</a>
                                        <a class="rounded-lg border border-[#CBD5E1] px-3 py-1.5 text-xs font-bold text-[#334155] hover:bg-[#F8FAFC]" href="{{ route('staff.section', 'reports') }}">Update Loan Rules</a>
                                        <a class="rounded-lg bg-[#EEF2FF] px-3 py-1.5 text-xs font-bold text-[#002E97] hover:bg-[#E2E7FF]" href="{{ route('staff.section', 'history') }}">Borrowing Log</a>
                                    </div>
                                </div>

                                <div class="p-5">
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-[#64748B]">Assigned Desk Reserve Textbooks</div>
                                    <div class="mt-3 space-y-3">
                                        @foreach ($course['books'] as $item)
                                            <div class="flex flex-col gap-4 rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] p-4 sm:flex-row">
                                                @if (!empty($item['book']['cover_url']))
                                                    <img class="h-28 w-20 shrink-0 rounded-lg border border-[#CBD5E1] object-cover shadow-sm" src="{{ $item['book']['cover_url'] }}" alt="{{ $item['book']['title'] }} cover">
                                                @else
                                                    <div class="flex h-28 w-20 shrink-0 items-center justify-center rounded-lg bg-[#065F2A] text-white"><span class="material-symbols-outlined">menu_book</span></div>
                                                @endif
                                                <div class="min-w-0 flex-1">
                                                    <h3 class="font-display text-base font-bold text-[#0F172A]">{{ $item['book']['title'] ?? 'Reserve title pending cataloging' }}</h3>
                                                    <p class="mt-1 text-xs text-[#64748B]">{{ $item['book']['author'] ?? 'Author pending' }} @if (!empty($item['book']['year'])) - {{ $item['book']['year'] }} @endif</p>
                                                    <div class="mt-3 grid gap-2 text-xs text-[#475569] md:grid-cols-2">
                                                        <span>Call No: <strong class="text-[#0F172A]">{{ $item['book']['call'] ?? 'Pending' }}</strong></span>
                                                        <span>Location: <strong class="text-[#0F172A]">{{ $item['book']['location'] ?? 'Library Desk' }}</strong></span>
                                                    </div>
                                                    <div class="mt-3 flex flex-wrap items-center gap-2">
                                                        <span class="rounded-full border border-[#BBF7D0] bg-[#DCFCE7] px-2 py-0.5 text-[10px] font-bold text-[#166534]">{{ $item['copies'] }}</span>
                                                        <span class="rounded-full bg-[#EEF2FF] px-2 py-0.5 text-[10px] font-bold text-[#334155]">{{ $item['rule'] }}</span>
                                                    </div>
                                                </div>
                                                @if (!empty($item['book']['id']))
                                                    <a class="self-start rounded-lg border border-[#CBD5E1] bg-white px-3 py-2 text-xs font-bold text-[#065F2A] hover:bg-[#E2E8F0]" href="{{ route('books.catalog', ['selected' => $item['book']['id']]) }}">Book Catalog Details</a>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="mt-4 rounded-xl border border-[#BFDBFE] bg-[#EFF6FF] p-4 text-sm text-[#1E3A8A]">
                                        <span class="material-symbols-outlined mr-1 text-base">cloud_done</span>{{ $course['digital'] }}
                                    </div>
                                </div>
                            </article>
                        @endforeach

                        <section class="rounded-2xl border border-[#CBD5E1] bg-white p-5 shadow-sm" id="reserve-request-form">
                            <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[#065F2A]">post_add</span><h2 class="font-display text-lg font-bold text-[#064E3B]">New Course Syllabus Reserve Request</h2></div>
                            <form class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2" method="GET" action="{{ route('books.catalog') }}">
                                <label class="space-y-1 text-xs font-bold text-[#0F172A]">Course Code
                                    <input class="w-full rounded-lg border-[#CBD5E1] text-sm focus:border-[#065F2A] focus:ring-[#065F2A]" placeholder="IT301" value="IT301">
                                </label>
                                <label class="space-y-1 text-xs font-bold text-[#0F172A]">Reserve Period
                                    <input class="w-full rounded-lg border-[#CBD5E1] text-sm focus:border-[#065F2A] focus:ring-[#065F2A]" placeholder="Oct 21 - Dec 06, 2024">
                                </label>
                                <label class="space-y-1 text-xs font-bold text-[#0F172A] md:col-span-2">Prescribed Textbook / ISBN
                                    <input class="w-full rounded-lg border-[#CBD5E1] text-sm focus:border-[#065F2A] focus:ring-[#065F2A]" name="q" placeholder="Search title, ISBN, author, or call number">
                                </label>
                                <label class="space-y-1 text-xs font-bold text-[#0F172A]">Loan Rule
                                    <select class="w-full rounded-lg border-[#CBD5E1] text-sm focus:border-[#065F2A] focus:ring-[#065F2A]">
                                        <option>3-hour closed reserve</option>
                                        <option>Room-use only</option>
                                        <option>Overnight faculty loan</option>
                                    </select>
                                </label>
                                <label class="space-y-1 text-xs font-bold text-[#0F172A]">Needed Copies
                                    <input class="w-full rounded-lg border-[#CBD5E1] text-sm focus:border-[#065F2A] focus:ring-[#065F2A]" placeholder="3">
                                </label>
                                <textarea class="min-h-24 rounded-lg border-[#CBD5E1] text-sm focus:border-[#065F2A] focus:ring-[#065F2A] md:col-span-2" placeholder="Syllabus notes, chapter coverage, faculty instructions, or LMS access details"></textarea>
                                <div class="flex justify-end gap-3 md:col-span-2">
                                    <a class="rounded-lg border border-[#CBD5E1] px-4 py-2 text-xs font-bold text-[#334155] hover:bg-[#F8FAFC]" href="{{ route('staff.section', 'course-reserves') }}">Cancel</a>
                                    <button class="inline-flex items-center gap-2 rounded-lg bg-[#065F2A] px-5 py-2 text-xs font-bold text-white hover:bg-[#064E3B]" type="submit"><span class="material-symbols-outlined text-base">search</span>Find Book & Submit Draft</button>
                                </div>
                            </form>
                        </section>
                    </div>

                    <aside class="space-y-6 xl:col-span-4">
                        <section class="rounded-2xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                            <div class="flex items-center justify-between"><h2 class="font-display text-lg font-bold text-[#064E3B]">Faculty Borrower Pass</h2><span class="rounded bg-[#FEF3C7] px-2 py-0.5 text-[10px] font-bold text-[#92400E]">FAC-2018-0412</span></div>
                            <div class="mt-4 rounded-2xl bg-[#064E3B] p-5 text-white">
                                <div class="flex items-center gap-3">
                                    @include('partials.celtech-logo', ['class' => 'flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-white p-1'])
                                    <div><p class="text-xs font-bold uppercase text-[#BBF7D0]">Faculty Reserve Document</p><p class="font-display text-lg font-bold">Prof. Eleanor Vance</p><p class="text-xs text-[#D1FAE5]">Department of History</p></div>
                                </div>
                                <div class="mt-5 rounded-xl bg-white p-4 text-center text-[#0F172A]">
                                    <div class="mx-auto h-10 w-full max-w-xs bg-[repeating-linear-gradient(90deg,#0F172A_0_2px,transparent_2px_5px,#0F172A_5px_7px,transparent_7px_10px)]"></div>
                                    <p class="mt-2 font-mono text-xs font-bold tracking-[0.25em]">FAC-RES-2018-0412</p>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-2xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                            <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[#92400E]">policy</span><h2 class="font-display text-lg font-bold text-[#064E3B]">Reserve Policy Guidelines</h2></div>
                            <div class="mt-4 space-y-3 text-sm leading-6 text-[#475569]">
                                <p><strong class="text-[#0F172A]">Minimum copies:</strong> major professional courses should maintain three prescribed textbook copies on closed reserve.</p>
                                <p><strong class="text-[#0F172A]">Fair-use uploads:</strong> digitized chapters must not exceed 15% or one chapter of copyrighted works.</p>
                                <p><strong class="text-[#0F172A]">Desk control:</strong> reserve borrowing is logged through the faculty/staff portal and library desk.</p>
                            </div>
                            <a class="mt-4 inline-flex items-center gap-1 text-xs font-bold text-[#065F2A] hover:underline" href="{{ route('staff.section', 'support') }}">Download Full Faculty Reserve Handbook<span class="material-symbols-outlined text-base">arrow_forward</span></a>
                        </section>

                        <section class="rounded-2xl border border-[#CBD5E1] bg-[#F0FDF4] p-5 shadow-sm">
                            <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[#065F2A]">support_agent</span><h2 class="font-display text-lg font-bold text-[#064E3B]">Library Desk Contact</h2></div>
                            <div class="mt-4 space-y-2 text-sm text-[#334155]">
                                <p>Desk 02 - Course Reserves</p>
                                <p>library@clcst.edu.ph</p>
                                <p>Mon - Fri: 7:30 AM - 6:30 PM</p>
                            </div>
                        </section>
                    </aside>
                </section>
            @elseif ($section === 'reading-lists')
                @php
                    $bookLookup = collect($catalogBooks)->keyBy('id');
                    $readingLists = [
                        [
                            'code' => 'IT401',
                            'course' => 'Advanced Artificial Intelligence & Machine Learning',
                            'curator' => 'Engr. Roberto M. Mendoza, PECE',
                            'resources' => [
                                ['type' => 'Textbook', 'tag' => 'Mandatory Core', 'weeks' => 'Week 1-6', 'title' => 'Artificial Intelligence: A Modern Approach (4th Global Edition)', 'author' => 'Stuart Russell & Peter Norvig', 'book' => $bookLookup->get('ai-modern-approach'), 'availability' => 'Available - Shelves 3A', 'clicks' => 64],
                                ['type' => 'Research Paper', 'tag' => 'Research Paper', 'weeks' => 'Week 8', 'title' => 'Attention Is All You Need', 'author' => 'Vaswani, Shazeer, Parmar, et al.', 'book' => null, 'availability' => 'PDF Online Access Available', 'clicks' => 118],
                                ['type' => 'Dataset', 'tag' => 'Lab Resource', 'weeks' => 'Week 10-14', 'title' => 'CLCST Machine Learning Lab Datasets', 'author' => 'Faculty curated repository', 'book' => null, 'availability' => 'Digital repository ready', 'clicks' => 37],
                            ],
                        ],
                        [
                            'code' => 'CS302',
                            'course' => 'Data Structures and Algorithms',
                            'curator' => 'Prof. Elena Reyes',
                            'resources' => [
                                ['type' => 'Textbook', 'tag' => 'Core Text', 'weeks' => 'Week 1-12', 'title' => 'Introduction to Algorithms (4th Edition)', 'author' => 'Cormen, Leiserson, Rivest, Stein', 'book' => $bookLookup->get('algorithms'), 'availability' => 'Shelves 2A - 1 copy ready', 'clicks' => 92],
                                ['type' => 'Programming Guide', 'tag' => 'Supplemental', 'weeks' => 'Week 3-6', 'title' => 'Introduction to Python Programming', 'author' => 'John Zelle', 'book' => $bookLookup->get('python-programming'), 'availability' => '4 copies available', 'clicks' => 76],
                            ],
                        ],
                        [
                            'code' => 'CPE402',
                            'course' => 'Digital Logic & Microcontroller Design',
                            'curator' => 'Engr. Mark Ramos',
                            'resources' => [
                                ['type' => 'Reference', 'tag' => 'Room Use', 'weeks' => 'Lab Weeks', 'title' => 'Computer Networks: A Systems Approach (6th Edition)', 'author' => 'Larry L. Peterson & Bruce S. Davie', 'book' => $bookLookup->get('computer-networks'), 'availability' => 'Engineering reserve desk', 'clicks' => 41],
                            ],
                        ],
                    ];
                    $totalResources = collect($readingLists)->sum(fn ($list) => count($list['resources']));
                    $catalogReady = collect($readingLists)->flatMap(fn ($list) => $list['resources'])->filter(fn ($resource) => !empty($resource['book']))->count();
                @endphp

                <section class="rounded-2xl border border-[#CBD5E1] bg-white p-6 shadow-sm lg:p-8">
                    <div class="flex flex-col justify-between gap-5 lg:flex-row lg:items-start">
                        <div>
                            <nav aria-label="Breadcrumb" class="mb-3 flex flex-wrap items-center gap-2 text-xs font-semibold text-[#64748B]">
                                <span>Faculty Academic Services</span>
                                <span class="material-symbols-outlined text-sm">chevron_right</span>
                                <span class="font-bold text-[#065F2A]">Course Reading Lists</span>
                            </nav>
                            <span class="inline-flex rounded-full bg-[#DCFCE7] px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-[#166534]">2nd Semester AY 2024-2025</span>
                            <h1 class="mt-4 font-display text-3xl font-bold text-[#064E3B] md:text-4xl">Curated Course Reading Lists</h1>
                            <p class="mt-3 max-w-3xl text-sm leading-6 text-[#475569]">Manage assigned textbooks, journal articles, datasets, Book Catalog-linked copies, and reserve shelf visibility for faculty courses.</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a class="inline-flex items-center gap-2 rounded-lg bg-[#065F2A] px-4 py-2 text-xs font-bold text-white hover:bg-[#064E3B]" href="#quick-resource-adder"><span class="material-symbols-outlined text-base">add</span>Create New Reading List</a>
                            <a class="inline-flex items-center gap-2 rounded-lg border border-[#CBD5E1] bg-white px-3 py-2 text-xs font-bold text-[#334155] hover:bg-[#F8FAFC]" href="{{ route('staff.section', 'academic') }}"><span class="material-symbols-outlined text-base">cloud_sync</span>Import from Moodle</a>
                            <a class="inline-flex items-center gap-2 rounded-lg border border-[#CBD5E1] bg-white px-3 py-2 text-xs font-bold text-[#334155] hover:bg-[#F8FAFC]" href="{{ route('staff.section', 'support') }}"><span class="material-symbols-outlined text-base">file_download</span>Export BibTeX / APA</a>
                        </div>
                    </div>
                </section>

                <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ([
                        ['label' => 'Published Reading Lists', 'value' => count($readingLists), 'unit' => 'Lists', 'meta' => 'Active semester courses', 'icon' => 'collections_bookmark'],
                        ['label' => 'Total Curated Resources', 'value' => $totalResources, 'unit' => 'Titles', 'meta' => 'Textbooks, papers, datasets', 'icon' => 'library_books'],
                        ['label' => 'Student Engagement', 'value' => '1,840', 'unit' => 'Views', 'meta' => '+24% vs prelim cycle', 'icon' => 'touch_app'],
                        ['label' => 'Library Stack Availability', 'value' => '94%', 'unit' => 'Ready', 'meta' => $catalogReady.' Book Catalog-linked resources', 'icon' => 'verified'],
                    ] as $metric)
                        <article class="rounded-xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-[#64748B]">{{ $metric['label'] }}</p>
                                    <h2 class="mt-2 font-display text-3xl font-bold text-[#065F2A]">{{ $metric['value'] }} <span class="text-sm font-medium text-[#64748B]">{{ $metric['unit'] }}</span></h2>
                                    <p class="mt-1 text-xs text-[#64748B]">{{ $metric['meta'] }}</p>
                                </div>
                                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#EEF2FF] text-[#002E97]"><span class="material-symbols-outlined">{{ $metric['icon'] }}</span></span>
                            </div>
                        </article>
                    @endforeach
                </section>

                <section class="rounded-2xl border border-[#CBD5E1] bg-white p-4 shadow-sm">
                    <form class="flex flex-col gap-3 lg:flex-row lg:items-center" method="GET" action="{{ route('books.catalog') }}">
                        <div class="relative flex-1">
                            <span class="material-symbols-outlined absolute left-3 top-2.5 text-[#64748B]">search</span>
                            <input class="w-full rounded-lg border-[#CBD5E1] py-2 pl-10 pr-3 text-sm focus:border-[#065F2A] focus:ring-[#065F2A]" name="q" placeholder="Filter by course title, code, author, call number, ISBN, or DOI">
                        </div>
                        <select class="rounded-lg border-[#CBD5E1] text-sm focus:border-[#065F2A] focus:ring-[#065F2A]" name="category">
                            <option value="all">All Faculty Courses</option>
                            <option value="Computer Studies & IT">Computer Engineering & IT</option>
                            <option value="Engineering">Engineering</option>
                        </select>
                        <div class="inline-flex rounded-lg border border-[#CBD5E1] bg-[#F8FAFC] p-1 text-xs font-bold">
                            <a class="rounded-md bg-white px-3 py-1.5 text-[#065F2A] shadow-sm" href="{{ route('staff.section', 'reading-lists') }}">Current</a>
                            <a class="px-3 py-1.5 text-[#64748B] hover:text-[#0F172A]" href="{{ route('staff.section', 'history') }}">Archived</a>
                        </div>
                        <button class="rounded-lg bg-[#065F2A] px-4 py-2 text-xs font-bold text-white hover:bg-[#064E3B]" type="submit">Search Books</button>
                    </form>
                </section>

                <section class="grid grid-cols-1 gap-6 xl:grid-cols-12">
                    <div class="space-y-6 xl:col-span-8">
                        @foreach ($readingLists as $list)
                            <article class="overflow-hidden rounded-2xl border border-[#CBD5E1] bg-white shadow-sm hover:border-[#065F2A]">
                                <div class="flex flex-col justify-between gap-4 border-b border-[#CBD5E1] bg-[#F8FAFC] p-5 md:flex-row md:items-center">
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-12 w-12 shrink-0 flex-col items-center justify-center rounded-lg bg-[#065F2A] font-bold text-white">
                                            <span class="text-[9px] uppercase">Course</span>
                                            <span class="text-sm leading-none">{{ $list['code'] }}</span>
                                        </div>
                                        <div>
                                            <div class="flex flex-wrap items-center gap-2">
                                                <h2 class="font-display text-xl font-bold text-[#0F172A]">{{ $list['course'] }}</h2>
                                                <span class="rounded-full bg-[#DCFCE7] px-2 py-0.5 text-[10px] font-bold uppercase text-[#166534]">Active Syllabus</span>
                                            </div>
                                            <p class="mt-1 text-xs text-[#64748B]">Curator: <strong class="text-[#0F172A]">{{ $list['curator'] }}</strong> - {{ count($list['resources']) }} curated resources</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <a class="inline-flex items-center gap-1 rounded-lg border border-[#CBD5E1] px-3 py-1.5 text-xs font-bold text-[#065F2A] hover:bg-[#F0FDF4]" href="#quick-resource-adder"><span class="material-symbols-outlined text-base">add</span>Add Resource</a>
                                        <a class="rounded-lg border border-[#CBD5E1] p-1.5 text-[#64748B] hover:bg-[#E2E8F0]" title="Reorder priority" href="{{ route('staff.section', 'reading-lists') }}"><span class="material-symbols-outlined">swap_vert</span></a>
                                        <a class="rounded-lg border border-[#CBD5E1] p-1.5 text-[#64748B] hover:bg-[#E2E8F0]" title="Sync settings" href="{{ route('staff.section', 'academic') }}"><span class="material-symbols-outlined">sync</span></a>
                                    </div>
                                </div>
                                <div class="divide-y divide-[#E2E8F0] p-5">
                                    @foreach ($list['resources'] as $resource)
                                        <div class="flex flex-col justify-between gap-4 py-4 first:pt-0 last:pb-0 sm:flex-row sm:items-center">
                                            <div class="flex items-start gap-4">
                                                @if (!empty($resource['book']['cover_url']))
                                                    <img class="h-24 w-16 shrink-0 rounded border border-[#CBD5E1] object-cover shadow-sm" src="{{ $resource['book']['cover_url'] }}" alt="{{ $resource['book']['title'] }} cover">
                                                @else
                                                    <div class="flex h-24 w-16 shrink-0 flex-col items-center justify-center rounded border border-[#CBD5E1] bg-[#EEF2FF] p-1 text-center">
                                                        <span class="material-symbols-outlined text-[#002E97]">{{ $resource['type'] === 'Dataset' ? 'dataset' : 'description' }}</span>
                                                        <span class="mt-1 text-[9px] font-bold uppercase text-[#64748B]">{{ $resource['type'] }}</span>
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <span class="rounded bg-[#FDE68A] px-2 py-0.5 text-[10px] font-bold uppercase text-[#5A4300]">{{ $resource['tag'] }}</span>
                                                        <span class="text-xs text-[#64748B]">{{ $resource['weeks'] }}</span>
                                                    </div>
                                                    <h3 class="mt-1 font-display text-base font-bold text-[#0F172A]">{{ $resource['title'] }}</h3>
                                                    <p class="mt-1 text-xs text-[#64748B]">{{ $resource['author'] }} @if (!empty($resource['book']['isbn'])) - ISBN {{ $resource['book']['isbn'] }} @endif</p>
                                                    <div class="mt-2 flex flex-wrap items-center gap-3 text-xs">
                                                        <span class="inline-flex items-center gap-1 rounded-full border border-[#BBF7D0] bg-[#DCFCE7] px-2 py-0.5 font-bold text-[#166534]"><span class="material-symbols-outlined text-sm">inventory_2</span>{{ $resource['availability'] }}</span>
                                                        @if (!empty($resource['book']['call']))
                                                            <span class="text-[#64748B]">Call: <strong class="text-[#0F172A]">{{ $resource['book']['call'] }}</strong></span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex shrink-0 items-center gap-2 sm:flex-col sm:items-end">
                                                @if (!empty($resource['book']['id']))
                                                    <a class="rounded-lg border border-[#CBD5E1] bg-[#F8FAFC] px-3 py-1.5 text-xs font-bold text-[#065F2A] hover:bg-[#E2E8F0]" href="{{ route('books.catalog', ['selected' => $resource['book']['id']]) }}">Book Catalog Details</a>
                                                @else
                                                    <a class="rounded-lg border border-[#CBD5E1] bg-[#F8FAFC] px-3 py-1.5 text-xs font-bold text-[#065F2A] hover:bg-[#E2E8F0]" href="{{ route('staff.section', 'support') }}">Access Link</a>
                                                @endif
                                                <span class="text-[10px] font-semibold text-[#64748B]">{{ $resource['clicks'] }} student clicks</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </article>
                        @endforeach

                        <section class="rounded-2xl border border-[#CBD5E1] bg-white p-5 shadow-sm" id="quick-resource-adder">
                            <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[#065F2A]">playlist_add</span><h2 class="font-display text-lg font-bold">Quick Resource Adder</h2></div>
                            <form class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2" method="GET" action="{{ route('books.catalog') }}">
                                <input class="rounded-lg border-[#CBD5E1] text-sm focus:border-[#065F2A] focus:ring-[#065F2A]" name="q" placeholder="Title, ISBN, DOI, or URL">
                                <select class="rounded-lg border-[#CBD5E1] text-sm focus:border-[#065F2A] focus:ring-[#065F2A]" name="field">
                                    <option value="all">Search all catalog fields</option>
                                    <option value="title">Title only</option>
                                    <option value="isbn">ISBN only</option>
                                    <option value="author">Author only</option>
                                </select>
                                <input class="rounded-lg border-[#CBD5E1] text-sm focus:border-[#065F2A] focus:ring-[#065F2A]" placeholder="Course code, e.g. IT401">
                                <select class="rounded-lg border-[#CBD5E1] text-sm focus:border-[#065F2A] focus:ring-[#065F2A]">
                                    <option>Mandatory core</option>
                                    <option>Supplemental reading</option>
                                    <option>Lab resource</option>
                                    <option>Research paper</option>
                                </select>
                                <div class="flex justify-end gap-3 md:col-span-2">
                                    <a class="rounded-lg border border-[#CBD5E1] px-4 py-2 text-xs font-bold text-[#334155] hover:bg-[#F8FAFC]" href="{{ route('staff.section', 'reading-lists') }}">Clear</a>
                                    <button class="inline-flex items-center gap-2 rounded-lg bg-[#065F2A] px-4 py-2 text-xs font-bold text-white hover:bg-[#064E3B]" type="submit"><span class="material-symbols-outlined text-base">add_link</span>Find and Add Resource</button>
                                </div>
                            </form>
                        </section>
                    </div>

                    <aside class="space-y-6 xl:col-span-4">
                        <section class="rounded-2xl border border-[#CBD5E1] bg-white p-5 shadow-sm" id="digital-pass">
                            <div class="flex items-center justify-between"><h2 class="font-display text-lg font-bold text-[#064E3B]">Faculty Digital Pass</h2><span class="rounded bg-[#DCFCE7] px-2 py-0.5 text-[10px] font-bold text-[#166534]">READY</span></div>
                            <div class="mt-4 rounded-2xl bg-[#064E3B] p-5 text-white">
                                <div class="flex items-center gap-3">
                                    @include('partials.celtech-logo', ['class' => 'flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-white p-1'])
                                    <div><p class="text-xs font-bold uppercase text-[#BBF7D0]">Faculty Academic Portal</p><p class="font-display text-lg font-bold">Engr. Roberto Mendoza</p><p class="text-xs text-[#D1FAE5]">Dept. of Computer Engineering</p></div>
                                </div>
                                <div class="mt-5 rounded-xl bg-white p-4 text-center text-[#0F172A]">
                                    <div class="mx-auto h-10 w-full max-w-xs bg-[repeating-linear-gradient(90deg,#0F172A_0_2px,transparent_2px_5px,#0F172A_5px_7px,transparent_7px_10px)]"></div>
                                    <p class="mt-2 font-mono text-xs font-bold tracking-[0.25em]">FAC-READ-2024</p>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-2xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                            <h2 class="font-display text-lg font-bold text-[#064E3B]">Pedagogical Guidelines</h2>
                            <div class="mt-4 space-y-3 text-sm leading-6 text-[#475569]">
                                <p><strong class="text-[#0F172A]">Core readings:</strong> mark items required for weekly class discussion and assessment coverage.</p>
                                <p><strong class="text-[#0F172A]">Reserve copies:</strong> link Book Catalog records before students access the syllabus list.</p>
                                <p><strong class="text-[#0F172A]">Digital access:</strong> include DOI, database, or institutional repository notes for papers and datasets.</p>
                            </div>
                        </section>

                        <section class="rounded-2xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                            <h2 class="font-display text-lg font-bold text-[#064E3B]">Citation & Export Tools</h2>
                            <div class="mt-4 grid grid-cols-2 gap-2">
                                @foreach (['APA 7', 'BibTeX', 'RIS', 'CSV'] as $tool)
                                    <a class="flex items-center justify-center gap-1.5 rounded-lg border border-[#CBD5E1] bg-[#F8FAFC] p-2 text-xs font-bold text-[#334155] hover:bg-[#E2E8F0]" href="{{ route('staff.section', 'support') }}"><span class="material-symbols-outlined text-sm text-[#065F2A]">download</span>{{ $tool }}</a>
                                @endforeach
                            </div>
                        </section>

                        <section class="rounded-2xl border border-[#CBD5E1] bg-[#F0FDF4] p-5 shadow-sm">
                            <div class="flex items-start gap-3">
                                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-[#DCFCE7] text-[#065F2A]"><span class="material-symbols-outlined">support_agent</span></span>
                                <div>
                                    <h2 class="font-display text-base font-bold text-[#064E3B]">Academic Librarian</h2>
                                    <p class="mt-1 text-sm text-[#475569]">Ms. Maria Santos can help validate bibliographic records and reserve shelf availability.</p>
                                    <a class="mt-3 inline-flex text-xs font-bold text-[#065F2A] hover:underline" href="{{ route('staff.section', 'support') }}">Schedule Meeting</a>
                                </div>
                            </div>
                        </section>
                    </aside>
                </section>
            @elseif ($section === 'academic')
                @php
                    $reserveBook = collect($catalogBooks)->firstWhere('id', 'clean-code') ?? ($catalogBooks[0] ?? null);
                    $supportBook = collect($catalogBooks)->firstWhere('id', 'computer-networks') ?? ($catalogBooks[1] ?? null);
                    $activeAcademicRequests = count($facultyRequests);
                    $reserveTitles = collect($catalogBooks)->whereIn('policy', ['circulating', 'reference'])->take(4)->count();
                @endphp

                <section class="overflow-hidden rounded-2xl border border-[#CBD5E1] bg-white shadow-sm">
                    <div class="border-b border-[#CBD5E1] bg-[#064E3B] p-6 text-white lg:p-8">
                        <div class="flex flex-col justify-between gap-5 lg:flex-row lg:items-start">
                            <div>
                                <nav aria-label="Breadcrumb" class="mb-4 flex flex-wrap items-center gap-2 text-xs font-semibold text-[#BBF7D0]">
                                    <span>Academic Portal</span>
                                    <span class="material-symbols-outlined text-sm">chevron_right</span>
                                    <span>Faculty Services</span>
                                    <span class="material-symbols-outlined text-sm">chevron_right</span>
                                    <span class="text-white">Academic Requests</span>
                                </nav>
                                <span class="inline-flex rounded-full border border-[#FACC15]/50 bg-[#FACC15]/15 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-[#FEF3C7]">CHED CMO No. 25 Syllabus Setup</span>
                                <h1 class="mt-4 max-w-4xl font-display text-3xl font-bold leading-tight md:text-4xl">Faculty Academic Requests & Syllabus Setup</h1>
                                <p class="mt-3 max-w-3xl text-sm leading-6 text-[#D1FAE5]">Submit course reserve requests, reading lists, library instruction sessions, and title recommendations for librarian review using the portal catalog records.</p>
                            </div>
                            <div class="rounded-xl border border-white/15 bg-white/10 p-4 text-sm shadow-sm">
                                <p class="font-bold">Academic Services Desk</p>
                                <p class="mt-1 text-[#D1FAE5]">Room 102 - Faculty Reserve Counter</p>
                                <p class="mt-3 text-xs text-[#BBF7D0]">Cutoff: Oct 25, 2024 - 5:00 PM</p>
                            </div>
                        </div>
                        <div class="mt-6 flex flex-wrap gap-3">
                            <a class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-xs font-bold text-[#065F2A] shadow-sm hover:bg-[#F8FAFC]" href="#course-reserve-form"><span class="material-symbols-outlined text-base">library_add</span>Reserve Textbook</a>
                            <a class="inline-flex items-center gap-2 rounded-lg border border-white/25 bg-white/10 px-4 py-2 text-xs font-bold text-white hover:bg-white/15" href="#instruction-request"><span class="material-symbols-outlined text-base">co_present</span>Request Instruction Session</a>
                            <a class="inline-flex items-center gap-2 rounded-lg border border-white/25 bg-white/10 px-4 py-2 text-xs font-bold text-white hover:bg-white/15" href="{{ route('books.catalog') }}"><span class="material-symbols-outlined text-base">search</span>Open Book Catalog</a>
                        </div>
                    </div>
                </section>

                <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ([
                        ['label' => 'Active Academic Requests', 'value' => $activeAcademicRequests, 'meta' => 'Faculty service queue', 'icon' => 'assignment'],
                        ['label' => 'Course Reserve Titles', 'value' => $reserveTitles, 'meta' => 'Linked to Book Catalog records', 'icon' => 'bookmarks'],
                        ['label' => 'Confirmed Sessions', 'value' => 1, 'meta' => 'Information literacy class', 'icon' => 'event_available'],
                        ['label' => 'Review List', 'value' => 1, 'meta' => 'Needs librarian approval', 'icon' => 'pending_actions'],
                    ] as $metric)
                        <article class="rounded-xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-[#64748B]">{{ $metric['label'] }}</p>
                                    <h2 class="mt-1 font-display text-3xl font-bold text-[#065F2A]">{{ $metric['value'] }}</h2>
                                    <p class="mt-1 text-xs text-[#64748B]">{{ $metric['meta'] }}</p>
                                </div>
                                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#DCFCE7] text-[#166534]"><span class="material-symbols-outlined">{{ $metric['icon'] }}</span></span>
                            </div>
                        </article>
                    @endforeach
                </section>

                <section class="grid grid-cols-1 gap-6 xl:grid-cols-12">
                    <div class="space-y-6 xl:col-span-8">
                        <section class="rounded-2xl border border-[#CBD5E1] bg-white p-5 shadow-sm" id="course-reserve-form">
                            <div class="flex flex-col justify-between gap-3 border-b border-[#CBD5E1] pb-4 lg:flex-row lg:items-center">
                                <div>
                                    <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[#065F2A]">library_add</span><h2 class="font-display text-xl font-bold text-[#064E3B]">Course Syllabus Reserve Request</h2></div>
                                    <p class="mt-1 text-sm text-[#64748B]">Prepare a reserve request from catalog records and route it to the librarian for shelf assignment.</p>
                                </div>
                                <span class="rounded-full bg-[#FEF3C7] px-3 py-1 text-[10px] font-bold uppercase text-[#92400E]">Registrar Cross-Check Active</span>
                            </div>
                            <form class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2" method="GET" action="{{ route('books.catalog') }}">
                                <label class="space-y-1 text-xs font-bold text-[#0F172A]">Course Code / Section
                                    <input class="w-full rounded-lg border-[#CBD5E1] text-sm focus:border-[#065F2A] focus:ring-[#065F2A]" name="course" placeholder="CS 301 - BSCS 3A" value="CS 301 - BSCS 3A">
                                </label>
                                <label class="space-y-1 text-xs font-bold text-[#0F172A]">Reserve Type
                                    <select class="w-full rounded-lg border-[#CBD5E1] text-sm focus:border-[#065F2A] focus:ring-[#065F2A]" name="availability">
                                        <option value="all">Regular reserve shelf</option>
                                        <option value="reference">Room-use reference only</option>
                                        <option value="available">Available copies only</option>
                                    </select>
                                </label>
                                <label class="space-y-1 text-xs font-bold text-[#0F172A] md:col-span-2">Linked Catalog Title
                                    <select class="w-full rounded-lg border-[#CBD5E1] text-sm focus:border-[#065F2A] focus:ring-[#065F2A]" name="selected">
                                        @foreach ($catalogBooks as $book)
                                            <option value="{{ $book['id'] }}" @selected(($reserveBook['id'] ?? null) === $book['id'])>{{ $book['title'] }} - {{ $book['call'] }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label class="space-y-1 text-xs font-bold text-[#0F172A]">Class Size
                                    <input class="w-full rounded-lg border-[#CBD5E1] text-sm focus:border-[#065F2A] focus:ring-[#065F2A]" name="class_size" placeholder="42 students" value="42 students">
                                </label>
                                <label class="space-y-1 text-xs font-bold text-[#0F172A]">Term Coverage
                                    <input class="w-full rounded-lg border-[#CBD5E1] text-sm focus:border-[#065F2A] focus:ring-[#065F2A]" name="term" placeholder="1st Semester AY 2024-2025" value="1st Semester AY 2024-2025">
                                </label>
                                <label class="space-y-1 text-xs font-bold text-[#0F172A] md:col-span-2">Faculty Notes
                                    <textarea class="min-h-24 w-full rounded-lg border-[#CBD5E1] text-sm focus:border-[#065F2A] focus:ring-[#065F2A]" name="q" placeholder="Search terms, syllabus chapter coverage, or reserve instructions">{{ $reserveBook['title'] ?? 'Clean Code' }}</textarea>
                                </label>
                                <div class="flex flex-wrap justify-end gap-3 md:col-span-2">
                                    <a class="inline-flex items-center justify-center gap-2 rounded-lg border border-[#CBD5E1] px-4 py-2 text-xs font-bold text-[#334155] hover:bg-[#F8FAFC]" href="{{ route('staff.section', 'academic') }}">Clear Draft</a>
                                    <button class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#065F2A] px-5 py-2 text-xs font-bold text-white hover:bg-[#064E3B]" type="submit"><span class="material-symbols-outlined text-base">search</span>Check Book Catalog & Prepare Request</button>
                                </div>
                            </form>
                        </section>

                        <section class="space-y-4" id="reading-lists">
                            @foreach ([
                                ['status' => 'Approved / Active', 'tone' => 'green', 'title' => 'Course Reserve: CS301 Software Architecture', 'book' => $reserveBook, 'details' => 'Reserve shelf for weeks 7-12. Two-hour in-library use, one overnight checkout copy, and Book Catalog visibility for enrolled students.', 'meta' => '42 students - Oct 21 to Dec 06, 2024'],
                                ['status' => 'Confirmed Session', 'tone' => 'blue', 'title' => 'Instruction: IEEE Xplore & Scopus Literature Search', 'book' => $supportBook, 'details' => 'Information literacy orientation for Capstone 1 covering citation tracing, peer-reviewed filters, and database access.', 'meta' => 'Oct 28, 2024 - Multimedia AVR Room 302'],
                                ['status' => 'Under Librarian Review', 'tone' => 'amber', 'title' => 'Course: CPE402 Embedded Systems & IoT Engineering', 'book' => collect($catalogBooks)->firstWhere('id', 'ai-modern-approach') ?? $reserveBook, 'details' => 'Pending copy availability check and possible acquisition endorsement for additional laboratory reference material.', 'meta' => 'Submitted by Engr. Mark Ramos - Priority normal'],
                            ] as $request)
                                <article class="rounded-2xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                                    <div class="flex flex-col gap-4 md:flex-row">
                                        @if (!empty($request['book']['cover_url']))
                                            <img class="h-36 w-24 shrink-0 rounded-lg border border-[#CBD5E1] object-cover shadow-sm" src="{{ $request['book']['cover_url'] }}" alt="{{ $request['book']['title'] }} cover">
                                        @else
                                            <div class="flex h-36 w-24 shrink-0 items-center justify-center rounded-lg bg-[#065F2A] text-white"><span class="material-symbols-outlined">menu_book</span></div>
                                        @endif
                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="{{ $request['tone'] === 'green' ? 'bg-[#DCFCE7] text-[#166534]' : ($request['tone'] === 'blue' ? 'bg-[#DBEAFE] text-[#1D4ED8]' : 'bg-[#FEF3C7] text-[#92400E]') }} rounded-full px-2.5 py-1 text-[10px] font-bold uppercase">{{ $request['status'] }}</span>
                                                <span class="rounded-full bg-[#F1F5F9] px-2.5 py-1 text-[10px] font-semibold text-[#475569]">{{ $request['meta'] }}</span>
                                            </div>
                                            <h3 class="mt-3 font-display text-lg font-bold text-[#0F172A]">{{ $request['title'] }}</h3>
                                            <p class="mt-1 text-sm font-semibold text-[#065F2A]">{{ $request['book']['title'] ?? 'Catalog title pending' }}</p>
                                            <p class="mt-2 text-sm leading-6 text-[#475569]">{{ $request['details'] }}</p>
                                            <div class="mt-4 grid gap-2 border-t border-[#E2E8F0] pt-3 text-xs text-[#475569] sm:grid-cols-2">
                                                <span>Call No: <strong class="text-[#0F172A]">{{ $request['book']['call'] ?? 'Pending' }}</strong></span>
                                                <span>Location: <strong class="text-[#0F172A]">{{ $request['book']['location'] ?? 'Library Desk' }}</strong></span>
                                            </div>
                                            <div class="mt-4 flex flex-wrap gap-2">
                                                <a class="inline-flex items-center gap-1.5 rounded-lg bg-[#065F2A] px-3 py-2 text-xs font-bold text-white hover:bg-[#064E3B]" href="{{ route('books.catalog', ['selected' => $request['book']['id'] ?? null]) }}"><span class="material-symbols-outlined text-base">visibility</span>View Catalog Record</a>
                                                <a class="inline-flex items-center gap-1.5 rounded-lg border border-[#CBD5E1] px-3 py-2 text-xs font-bold text-[#334155] hover:bg-[#F8FAFC]" href="{{ route('staff.section', 'support') }}"><span class="material-symbols-outlined text-base">support_agent</span>Ask Librarian</a>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </section>

                        <section class="rounded-2xl border border-[#CBD5E1] bg-white p-5 shadow-sm" id="instruction-request">
                            <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[#065F2A]">co_present</span><h2 class="font-display text-lg font-bold">Library Instruction Session Request</h2></div>
                            <p class="mt-2 text-sm leading-6 text-[#64748B]">Schedule librarian-led sessions for Book Catalog search, academic databases, citation management, thesis writing support, or plagiarism awareness.</p>
                            <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-3">
                                @foreach (['Book Catalog & Catalog Searching', 'Database Research Workshop', 'Citation & Reference Management'] as $topic)
                                    <a class="rounded-xl border border-[#CBD5E1] bg-[#F8FAFC] p-4 hover:border-[#065F2A] hover:bg-[#F0FDF4]" href="{{ route('staff.section', 'support') }}">
                                        <span class="material-symbols-outlined text-[#065F2A]">event</span>
                                        <h3 class="mt-2 text-sm font-bold text-[#0F172A]">{{ $topic }}</h3>
                                        <p class="mt-1 text-xs leading-5 text-[#64748B]">Send preferred date, class size, and room details to the academic services desk.</p>
                                    </a>
                                @endforeach
                            </div>
                        </section>
                    </div>

                    <aside class="space-y-6 xl:col-span-4">
                        <section class="rounded-2xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                            <div class="flex items-center justify-between"><h2 class="font-display text-lg font-bold text-[#064E3B]">Faculty Digital Pass</h2><span class="rounded bg-[#DCFCE7] px-2 py-0.5 text-[10px] font-bold text-[#166534]">ACTIVE</span></div>
                            <div class="mt-4 rounded-2xl bg-[#064E3B] p-5 text-white shadow-sm">
                                <div class="flex items-center gap-3">
                                    @include('partials.celtech-logo', ['class' => 'flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-white p-1'])
                                    <div><p class="text-xs font-bold uppercase text-[#BBF7D0]">CLCST Faculty Library Access</p><p class="font-display text-lg font-bold">Maria Santos, MIT</p><p class="text-xs text-[#D1FAE5]">College of Computer Studies</p></div>
                                </div>
                                <div class="mt-5 rounded-xl bg-white p-4 text-center text-[#0F172A]">
                                    <div class="mx-auto h-12 w-full max-w-xs bg-[repeating-linear-gradient(90deg,#0F172A_0_2px,transparent_2px_5px,#0F172A_5px_7px,transparent_7px_10px)]"></div>
                                    <p class="mt-2 font-mono text-xs font-bold tracking-[0.25em]">FAC-2024-CLCST</p>
                                    <p class="mt-1 text-[10px] text-[#64748B]">Scan at reserve desk for faculty academic requests.</p>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-2xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                            <h2 class="font-display text-lg font-bold text-[#064E3B]">Quick Academic Tools</h2>
                            <div class="mt-4 space-y-2">
                                @foreach ([['route' => route('books.catalog'), 'icon' => 'search', 'label' => 'Search Book Catalog'], ['route' => route('staff.section', 'catalog'), 'icon' => 'recommend', 'label' => 'Recommend a Book'], ['route' => route('staff.section', 'reports'), 'icon' => 'autorenew', 'label' => 'Review Renewal Requests'], ['route' => route('staff.section', 'support'), 'icon' => 'help', 'label' => 'Contact Library Desk']] as $tool)
                                    <a class="flex items-center justify-between rounded-lg border border-[#CBD5E1] bg-[#F8FAFC] px-3 py-2 text-sm font-bold text-[#334155] hover:bg-[#E2E8F0]" href="{{ $tool['route'] }}"><span class="flex items-center gap-2"><span class="material-symbols-outlined text-base text-[#065F2A]">{{ $tool['icon'] }}</span>{{ $tool['label'] }}</span><span class="material-symbols-outlined text-base">chevron_right</span></a>
                                @endforeach
                            </div>
                        </section>

                        <section class="rounded-2xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                            <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[#92400E]">gavel</span><h2 class="font-display text-lg font-bold text-[#064E3B]">Reserve Policies</h2></div>
                            <div class="mt-4 space-y-3 text-sm leading-6 text-[#475569]">
                                <p><strong class="text-[#0F172A]">Course reserves:</strong> submit at least five working days before class use.</p>
                                <p><strong class="text-[#0F172A]">Reference titles:</strong> room-use items stay inside the learning commons.</p>
                                <p><strong class="text-[#0F172A]">Syllabus support:</strong> librarian validates call numbers, availability, and recommended acquisition needs.</p>
                            </div>
                        </section>

                        <section class="rounded-2xl border border-[#CBD5E1] bg-[#F0FDF4] p-5 shadow-sm">
                            <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[#065F2A]">support_agent</span><h2 class="font-display text-lg font-bold text-[#064E3B]">Academic Services Contact</h2></div>
                            <div class="mt-4 space-y-2 text-sm text-[#334155]">
                                <p>Faculty Library Desk - Room 102</p>
                                <p>library@clcst.edu.ph</p>
                                <p>Mon - Fri: 7:30 AM - 6:30 PM</p>
                            </div>
                        </section>
                    </aside>
                </section>
            @elseif ($section === 'inter-library-loan')
                <section class="rounded-2xl border border-[#CBD5E1] bg-white p-6 shadow-sm">
                    <div class="flex flex-col justify-between gap-4 xl:flex-row xl:items-start">
                        <div>
                            <nav aria-label="Breadcrumb" class="mb-2 flex flex-wrap items-center gap-2 text-xs font-medium text-[#64748B]">
                                <span>Academic Portal</span>
                                <span class="material-symbols-outlined text-sm">chevron_right</span>
                                <span>Faculty Services</span>
                                <span class="material-symbols-outlined text-sm">chevron_right</span>
                                <span>Faculty Academic Requests</span>
                                <span class="material-symbols-outlined text-sm">chevron_right</span>
                                <span class="font-bold text-[#065F2A]">Inter-Library Loan</span>
                            </nav>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex rounded-full bg-[#DCFCE7] px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-[#166534]">Faculty Research Service</span>
                                <span class="inline-flex rounded-full border border-[#CBD5E1] bg-[#F8FAFC] px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-[#475569]">6 of 8 Requests Available</span>
                            </div>
                            <h1 class="mt-4 max-w-5xl font-display text-3xl font-bold leading-tight text-[#0F172A] md:text-4xl">Inter-Library Loan & Consortium Document Delivery</h1>
                            <p class="mt-3 max-w-4xl text-sm leading-6 text-[#475569]">Request academic books, peer-reviewed journal papers, and conference proceedings that are not available at the CLCST Main Campus through partner libraries and academic repositories.</p>
                        </div>
                        <div class="flex shrink-0 flex-wrap gap-3">
                            <a class="inline-flex items-center justify-center gap-2 rounded-lg border border-[#CBD5E1] bg-white px-4 py-2 text-xs font-bold text-[#334155] shadow-sm hover:bg-[#F8FAFC]" href="{{ route('books.catalog') }}">
                                <span class="material-symbols-outlined text-base">search</span>
                                Search Books
                            </a>
                            <a class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#065F2A] px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-[#064E3B]" href="#ill-request-form">
                                <span class="material-symbols-outlined text-base">add_circle</span>
                                New Loan Request
                            </a>
                        </div>
                    </div>
                </section>

                <section class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    @foreach ([
                        ['label' => 'Available Requests', 'value' => '6 / 8', 'meta' => 'Faculty monthly allocation', 'icon' => 'inventory_2', 'tone' => 'green'],
                        ['label' => 'Partner Libraries', 'value' => '4', 'meta' => 'Online consortium hubs', 'icon' => 'hub', 'tone' => 'blue'],
                        ['label' => 'Ready Items', 'value' => '2', 'meta' => '1 download, 1 pickup', 'icon' => 'task_alt', 'tone' => 'green'],
                        ['label' => 'Average Delivery', 'value' => '3-5 days', 'meta' => 'Digital or courier based', 'icon' => 'local_shipping', 'tone' => 'amber'],
                    ] as $metric)
                        <article class="rounded-xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-[#64748B]">{{ $metric['label'] }}</p>
                                    <h2 class="mt-1 font-display text-2xl font-bold text-[#0F172A]">{{ $metric['value'] }}</h2>
                                    <p class="mt-2 text-xs leading-5 text-[#64748B]">{{ $metric['meta'] }}</p>
                                </div>
                                <span class="{{ $metric['tone'] === 'blue' ? 'bg-[#DBEAFE] text-[#1D4ED8]' : ($metric['tone'] === 'amber' ? 'bg-[#FEF3C7] text-[#92400E]' : 'bg-[#DCFCE7] text-[#166534]') }} flex h-10 w-10 shrink-0 items-center justify-center rounded-lg">
                                    <span class="material-symbols-outlined">{{ $metric['icon'] }}</span>
                                </span>
                            </div>
                        </article>
                    @endforeach
                </section>

                <section class="grid grid-cols-1 gap-6 xl:grid-cols-12">
                    <div class="space-y-6 xl:col-span-8">
                        <section id="ill-request-form" class="rounded-2xl border border-[#CBD5E1] bg-white p-6 shadow-sm">
                            <div class="flex flex-col justify-between gap-3 border-b border-[#E2E8F0] pb-4 md:flex-row md:items-start">
                                <div>
                                    <h2 class="font-display text-xl font-bold text-[#0F172A]">Request a Book or Research Paper</h2>
                                    <p class="mt-1 text-sm leading-6 text-[#64748B]">Fill in the request details so the library desk can check partner libraries and send the item for approval.</p>
                                </div>
                                <span class="inline-flex w-fit rounded-full bg-[#FEF3C7] px-3 py-1 text-[10px] font-bold uppercase text-[#92400E]">No payment needed for CICT faculty</span>
                            </div>

                            <form class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2" method="GET" action="{{ route('staff.section', 'inter-library-loan') }}">
                                <label class="space-y-1 text-xs font-bold text-[#0F172A]">Material Type
                                    <select class="w-full rounded-lg border-[#CBD5E1] text-sm focus:border-[#065F2A] focus:ring-[#065F2A]" name="material_type">
                                        <option>Book / Monograph</option>
                                        <option>Journal Article PDF</option>
                                        <option>Conference Paper</option>
                                        <option>Thesis or Dissertation</option>
                                    </select>
                                </label>
                                <label class="space-y-1 text-xs font-bold text-[#0F172A]">Needed For
                                    <select class="w-full rounded-lg border-[#CBD5E1] text-sm focus:border-[#065F2A] focus:ring-[#065F2A]" name="purpose">
                                        <option>Faculty Research</option>
                                        <option>Course Reading List</option>
                                        <option>Capstone Advising</option>
                                        <option>Laboratory Reference</option>
                                    </select>
                                </label>
                                <label class="space-y-1 text-xs font-bold text-[#0F172A] md:col-span-2">Title or Citation
                                    <input class="w-full rounded-lg border-[#CBD5E1] text-sm focus:border-[#065F2A] focus:ring-[#065F2A]" name="title" placeholder="Enter book title, article title, DOI, ISBN, or complete citation" value="FPGA-Accelerated Deep Neural Networks: Architectures and Tools">
                                </label>
                                <label class="space-y-1 text-xs font-bold text-[#0F172A]">Preferred Partner Library
                                    <select class="w-full rounded-lg border-[#CBD5E1] text-sm focus:border-[#065F2A] focus:ring-[#065F2A]" name="partner">
                                        <option>Any Available Partner</option>
                                        <option>UP Diliman Main Library</option>
                                        <option>DLSU Libraries Manila</option>
                                        <option>CLSU Central Library</option>
                                        <option>CLCST San Fernando Campus</option>
                                    </select>
                                </label>
                                <label class="space-y-1 text-xs font-bold text-[#0F172A]">Delivery Format
                                    <select class="w-full rounded-lg border-[#CBD5E1] text-sm focus:border-[#065F2A] focus:ring-[#065F2A]" name="delivery">
                                        <option>Digital PDF if allowed</option>
                                        <option>Physical copy pickup</option>
                                        <option>Either digital or physical</option>
                                    </select>
                                </label>
                                <label class="space-y-1 text-xs font-bold text-[#0F172A] md:col-span-2">Research Notes
                                    <textarea class="min-h-24 w-full rounded-lg border-[#CBD5E1] text-sm focus:border-[#065F2A] focus:ring-[#065F2A]" name="notes" placeholder="Add course code, research topic, deadline, or special request">Needed for CPE research consultation and embedded systems reference review.</textarea>
                                </label>
                                <div class="flex flex-wrap justify-end gap-3 md:col-span-2">
                                    <a class="inline-flex items-center justify-center gap-2 rounded-lg border border-[#CBD5E1] px-4 py-2 text-xs font-bold text-[#334155] hover:bg-[#F8FAFC]" href="{{ route('staff.section', 'inter-library-loan') }}">Clear Draft</a>
                                    <button class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#065F2A] px-5 py-2 text-xs font-bold text-white hover:bg-[#064E3B]" type="submit">
                                        <span class="material-symbols-outlined text-base">send</span>
                                        Submit Inter-Library Request
                                    </button>
                                </div>
                            </form>
                        </section>

                        <section class="overflow-hidden rounded-2xl border border-[#CBD5E1] bg-white shadow-sm">
                            <div class="flex flex-col justify-between gap-3 border-b border-[#E2E8F0] bg-[#F8FAFC] px-6 py-4 md:flex-row md:items-center">
                                <div>
                                    <h2 class="font-display text-xl font-bold text-[#0F172A]">Active Inter-Library Requests</h2>
                                    <p class="mt-1 text-sm text-[#64748B]">Track courier, download, and pickup status for requested academic materials.</p>
                                </div>
                                <a class="inline-flex items-center gap-2 rounded-lg border border-[#CBD5E1] bg-white px-3 py-2 text-xs font-bold text-[#334155] hover:bg-[#E2E8F0]" href="{{ route('staff.section', 'history') }}">
                                    <span class="material-symbols-outlined text-base">history</span>
                                    View Archive
                                </a>
                            </div>

                            <div class="divide-y divide-[#E2E8F0]">
                                @foreach ([
                                    ['badge' => 'In Transit via Regional Courier', 'badgeClass' => 'bg-[#DBEAFE] text-[#1D4ED8]', 'icon' => 'local_shipping', 'id' => 'ILL-2024-0391', 'title' => 'FPGA-Accelerated Deep Neural Networks: Architectures and Tools', 'partner' => 'University of the Philippines Diliman Main Library', 'meta' => 'Dispatched Nov 11 - Expected Nov 14 at Olongapo', 'detail' => 'Waybill LBC-PRALC-992014 - 45 days loan', 'primary' => 'Track Waybill', 'secondary' => 'View Voucher'],
                                    ['badge' => 'Ready to Download', 'badgeClass' => 'bg-[#DCFCE7] text-[#166534]', 'icon' => 'picture_as_pdf', 'id' => 'ILL-2024-0388', 'title' => 'IEEE Transactions on Computers: Special Issue on Neuromorphic Architectures', 'partner' => 'De La Salle University Manila Henry Sy Sr. Hall', 'meta' => 'Delivered Nov 10 - PDF valid for 30 days', 'detail' => 'DOI 10.1109/TC.2024.1089201 - Watermarked copy', 'primary' => 'Download PDF', 'secondary' => 'Cite Article'],
                                    ['badge' => 'Ready for Pickup', 'badgeClass' => 'bg-[#FEF3C7] text-[#92400E]', 'icon' => 'book_2', 'id' => 'ILL-2024-0374', 'title' => 'Embedded Real-Time Operating Systems for Robotics 4th Edition', 'partner' => 'Central Luzon State University Science Library', 'meta' => 'Shelf Slip SHELF-ILL-B3 - Hold expires Nov 17', 'detail' => 'Pickup at Library Desk - 45 days loan', 'primary' => 'Claim Code', 'secondary' => 'Extend Hold'],
                                ] as $request)
                                    <article class="p-6 transition hover:bg-[#F8FAFC]">
                                        <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-start">
                                            <div class="flex min-w-0 items-start gap-4">
                                                <span class="flex h-16 w-12 shrink-0 flex-col items-center justify-center rounded-lg border border-[#CBD5E1] bg-[#F1F5F9] text-[#065F2A]">
                                                    <span class="material-symbols-outlined text-3xl">{{ $request['icon'] }}</span>
                                                </span>
                                                <div class="min-w-0">
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <span class="{{ $request['badgeClass'] }} inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold uppercase">{{ $request['badge'] }}</span>
                                                        <span class="text-xs text-[#64748B]">Request ID: <code class="font-mono font-bold text-[#0F172A]">{{ $request['id'] }}</code></span>
                                                    </div>
                                                    <h3 class="mt-2 font-display text-lg font-bold text-[#0F172A]">{{ $request['title'] }}</h3>
                                                    <p class="mt-1 text-sm text-[#64748B]">Lending Partner: <strong class="text-[#0F172A]">{{ $request['partner'] }}</strong></p>
                                                    <div class="mt-3 flex flex-wrap gap-3 text-xs text-[#64748B]">
                                                        <span class="inline-flex items-center gap-1"><span class="material-symbols-outlined text-base text-[#065F2A]">calendar_today</span>{{ $request['meta'] }}</span>
                                                        <span>{{ $request['detail'] }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex shrink-0 flex-wrap gap-2">
                                                <a class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-[#065F2A] px-3 py-2 text-xs font-bold text-white hover:bg-[#064E3B]" href="{{ route('staff.section', 'inter-library-loan') }}">
                                                    <span class="material-symbols-outlined text-base">task_alt</span>{{ $request['primary'] }}
                                                </a>
                                                <a class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-[#CBD5E1] bg-white px-3 py-2 text-xs font-bold text-[#334155] hover:bg-[#E2E8F0]" href="{{ route('staff.section', 'inter-library-loan') }}">{{ $request['secondary'] }}</a>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    </div>

                    <aside class="space-y-6 xl:col-span-4">
                        <section class="overflow-hidden rounded-2xl border border-[#065F2A]/40 bg-gradient-to-br from-[#135D2E] via-[#0E4723] to-[#082E16] p-5 text-white shadow-md">
                            <div class="flex items-start justify-between border-b border-white/20 pb-3">
                                <div class="flex items-center gap-3">
                                    @include('partials.celtech-logo', ['class' => 'flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-white p-1'])
                                    <div>
                                        <h2 class="font-display text-sm font-extrabold uppercase tracking-wide">CLCST Digital Faculty Pass</h2>
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-[#FDE68A]">Main Library Access</p>
                                    </div>
                                </div>
                                <span class="rounded bg-[#FDE68A] px-2 py-1 text-[10px] font-extrabold text-[#713F12]">ACTIVE</span>
                            </div>
                            <div class="py-4">
                                <p class="text-[10px] uppercase tracking-wider text-white/70">Faculty Borrower</p>
                                <h3 class="mt-1 font-display text-lg font-extrabold">Engr. Roberto M. Mendoza, PECE</h3>
                                <p class="mt-1 text-xs text-white/85">Department Chairperson - CICT & Computer Engineering</p>
                                <p class="mt-2 text-xs text-white/80">Faculty ID: <strong class="text-white">FAC-2018-0412</strong></p>
                            </div>
                            <div class="rounded-xl bg-white p-3 text-center text-[#0F172A]">
                                <div class="mx-auto h-10 w-full max-w-xs bg-[repeating-linear-gradient(90deg,#0F172A_0_2px,transparent_2px_5px,#0F172A_5px_7px,transparent_7px_10px)]"></div>
                                <p class="mt-2 font-mono text-xs font-bold tracking-[0.22em]">FAC-2018-0412-LIB</p>
                            </div>
                            <div class="mt-3 flex items-center justify-between text-[11px] text-white/75">
                                <span>Fast-pass authorized</span>
                                <span>Expires Oct 2025</span>
                            </div>
                        </section>

                        <section class="rounded-2xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="font-display text-lg font-bold text-[#0F172A]">Regional Partner Libraries</h2>
                                    <p class="mt-1 text-xs text-[#64748B]">Current consortium delivery status</p>
                                </div>
                                <span class="material-symbols-outlined text-[#64748B]">sync</span>
                            </div>
                            <div class="mt-4 space-y-3">
                                @foreach ([
                                    ['name' => 'UP Diliman Main Library', 'meta' => 'Next courier run: Wed 2:00 PM', 'tag' => 'Online'],
                                    ['name' => 'DLSU Libraries Manila', 'meta' => 'Digital PDF delivery active', 'tag' => 'Direct'],
                                    ['name' => 'CLSU Central Library', 'meta' => 'Northern Luzon partner hub', 'tag' => 'Regional'],
                                    ['name' => 'CLCST San Fernando Campus', 'meta' => 'Daily campus shuttle link', 'tag' => 'Intranet'],
                                ] as $hub)
                                    <div class="flex items-start justify-between gap-3 rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] p-3">
                                        <div>
                                            <h3 class="flex items-center gap-2 text-sm font-bold text-[#0F172A]"><span class="h-2 w-2 rounded-full bg-[#22C55E]"></span>{{ $hub['name'] }}</h3>
                                            <p class="mt-1 text-xs text-[#64748B]">{{ $hub['meta'] }}</p>
                                        </div>
                                        <span class="rounded bg-[#DCFCE7] px-2 py-1 text-[10px] font-bold uppercase text-[#166534]">{{ $hub['tag'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </section>

                        <section class="rounded-2xl border border-[#CBD5E1] bg-white p-5 shadow-sm">
                            <h2 class="flex items-center gap-2 font-display text-lg font-bold text-[#0F172A]"><span class="material-symbols-outlined text-[#065F2A]">policy</span>Faculty Loan Rules</h2>
                            <ul class="mt-4 space-y-3 text-sm leading-6 text-[#475569]">
                                <li><strong class="text-[#0F172A]">Loan duration:</strong> physical items may be borrowed for 45 calendar days.</li>
                                <li><strong class="text-[#0F172A]">Renewal:</strong> one renewal is allowed if no other borrower is waiting.</li>
                                <li><strong class="text-[#0F172A]">Copyright:</strong> digital copies follow RA 8293 and partner-library access rules.</li>
                            </ul>
                        </section>

                        <section class="rounded-2xl border border-[#FEF08A] bg-[#FEFCE8] p-5 shadow-sm">
                            <div class="flex items-start gap-3">
                                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#FEF08A] text-[#854D0E]"><span class="material-symbols-outlined">support_agent</span></span>
                                <div>
                                    <h2 class="font-display text-base font-bold text-[#713F12]">Inter-Library Loan Desk</h2>
                                    <p class="mt-1 text-sm leading-6 text-[#854D0E]">Room 201, 2nd Floor Shelves - Local 108</p>
                                    <p class="mt-2 text-sm text-[#854D0E]"><strong>Coordinator:</strong> Ms. Maria Santos, RL</p>
                                    <a class="text-sm font-bold text-[#713F12] underline" href="mailto:ill-desk@clcst.edu.ph">ill-desk@clcst.edu.ph</a>
                                </div>
                            </div>
                        </section>
                    </aside>
                </section>
            @elseif ($section === 'support')
                <section class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <article class="group flex min-h-44 flex-col justify-between rounded-xl border border-[#CBD5E1] bg-white p-5 shadow-sm transition hover:border-[#22C55E] hover:shadow-md">
                        <div>
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#DCFCE7] text-[#065F2A] transition group-hover:bg-[#065F2A] group-hover:text-white">
                                <span class="material-symbols-outlined">description</span>
                            </span>
                            <h2 class="mt-4 font-display text-lg font-bold text-[#0F172A]">Daily Summary</h2>
                            <p class="mt-2 text-sm leading-6 text-[#64748B]">Review borrowed books, pending reservations, open fines, and catalog activity from today.</p>
                        </div>
                        <div class="mt-4 flex items-center justify-between border-t border-[#E2E8F0] pt-3 text-xs">
                            <span class="font-medium text-[#64748B]">142 checked out today</span>
                            <a class="font-bold text-[#065F2A] hover:underline" href="{{ route('staff.section', 'history') }}">View Log</a>
                        </div>
                    </article>

                    <article class="group flex min-h-44 flex-col justify-between rounded-xl border border-[#CBD5E1] bg-white p-5 shadow-sm transition hover:border-[#22C55E] hover:shadow-md">
                        <div>
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#DCFCE7] text-[#065F2A] transition group-hover:bg-[#065F2A] group-hover:text-white">
                                <span class="material-symbols-outlined">verified_user</span>
                            </span>
                            <h2 class="mt-4 font-display text-lg font-bold text-[#0F172A]">Access Control</h2>
                            <p class="mt-2 text-sm leading-6 text-[#64748B]">Keep staff-only pages limited to authorized library personnel and verified faculty accounts.</p>
                        </div>
                        <div class="mt-4 flex items-center justify-between border-t border-[#E2E8F0] pt-3 text-xs">
                            <span class="inline-flex items-center font-medium text-[#047857]"><span class="mr-1.5 h-2 w-2 rounded-full bg-[#22C55E]"></span>ID gates synced</span>
                            <a class="font-bold text-[#065F2A] hover:underline" href="{{ route('staff.section', 'profile') }}">Staff Roles</a>
                        </div>
                    </article>

                    <article class="group flex min-h-44 flex-col justify-between rounded-xl border border-[#CBD5E1] bg-white p-5 shadow-sm transition hover:border-[#22C55E] hover:shadow-md">
                        <div>
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#DCFCE7] text-[#065F2A] transition group-hover:bg-[#065F2A] group-hover:text-white">
                                <span class="material-symbols-outlined">support_agent</span>
                            </span>
                            <h2 class="mt-4 font-display text-lg font-bold text-[#0F172A]">Desk Support</h2>
                            <p class="mt-2 text-sm leading-6 text-[#64748B]">Use this area for library desk notes, help requests, device issues, and operating procedures.</p>
                        </div>
                        <div class="mt-4 flex items-center justify-between border-t border-[#E2E8F0] pt-3 text-xs">
                            <span class="font-medium text-[#64748B]">1 open desk note</span>
                            <a class="font-bold text-[#065F2A] hover:underline" href="#staff-support-queue">Desk SOP</a>
                        </div>
                    </article>
                </section>

                <section class="grid grid-cols-1 gap-6 lg:grid-cols-3" id="staff-support-queue">
                    <article class="lg:col-span-2 rounded-2xl border border-[#CBD5E1] bg-white p-6 shadow-sm">
                        <div class="flex flex-col justify-between gap-3 border-b border-[#E2E8F0] pb-4 sm:flex-row sm:items-start">
                            <div>
                                <h2 class="font-display text-lg font-bold text-[#0F172A]">Live Support Queue & Hardware Status</h2>
                                <p class="mt-1 text-sm leading-6 text-[#64748B]">Track library desk equipment issues, faculty requests, and service inquiries in one place.</p>
                            </div>
                            <a class="inline-flex items-center justify-center gap-2 rounded-lg border border-[#CBD5E1] bg-[#F8FAFC] px-3 py-2 text-xs font-bold text-[#334155] hover:bg-[#E2E8F0]" href="{{ route('staff.section', 'feedback') }}">
                                <span class="material-symbols-outlined text-base">add</span>
                                New Incident
                            </a>
                        </div>

                        <div class="mt-4 overflow-x-auto">
                            <table class="w-full min-w-[640px] text-left text-xs">
                                <thead>
                                    <tr class="border-b border-[#E2E8F0] text-[10px] uppercase tracking-wider text-[#64748B]">
                                        <th class="px-2 py-3">Ticket / Item</th>
                                        <th class="px-2 py-3">Location</th>
                                        <th class="px-2 py-3">Priority</th>
                                        <th class="px-2 py-3">Status</th>
                                        <th class="px-2 py-3 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#E2E8F0]">
                                    @foreach ([
                                        ['item' => 'Barcode Scanner Station 3', 'note' => 'USB connection is unstable during book returns.', 'location' => 'Library Desk', 'priority' => 'Medium', 'priorityClass' => 'bg-[#FEF3C7] text-[#92400E]', 'status' => 'Checking', 'statusClass' => 'text-[#92400E]', 'dot' => 'bg-[#F59E0B]', 'route' => route('staff.section', 'feedback'), 'action' => 'Inspect'],
                                        ['item' => 'Library Entry Gate 2 ID Sync', 'note' => 'Student ID tap delay was resolved after device update.', 'location' => 'Main Entrance', 'priority' => 'Normal', 'priorityClass' => 'bg-[#DCFCE7] text-[#166534]', 'status' => 'Working', 'statusClass' => 'text-[#047857]', 'dot' => 'bg-[#22C55E]', 'route' => route('staff.section', 'history'), 'action' => 'Log'],
                                        ['item' => 'Faculty Reserve: Engineering 402 Syllabus', 'note' => 'Requested four textbook copies for the two-hour reserve shelf.', 'location' => '2nd Floor Reserves', 'priority' => 'Academic', 'priorityClass' => 'bg-[#DBEAFE] text-[#1D4ED8]', 'status' => 'Assigned', 'statusClass' => 'text-[#1D4ED8]', 'dot' => 'bg-[#3B82F6]', 'route' => route('staff.section', 'academic'), 'action' => 'Review'],
                                    ] as $ticket)
                                        <tr class="hover:bg-[#F8FAFC]">
                                            <td class="px-2 py-4">
                                                <div class="font-bold text-[#0F172A]">{{ $ticket['item'] }}</div>
                                                <div class="mt-0.5 text-xs text-[#64748B]">{{ $ticket['note'] }}</div>
                                            </td>
                                            <td class="px-2 py-4 font-medium text-[#334155]">{{ $ticket['location'] }}</td>
                                            <td class="px-2 py-4"><span class="{{ $ticket['priorityClass'] }} rounded-full px-2 py-1 text-[10px] font-bold">{{ $ticket['priority'] }}</span></td>
                                            <td class="px-2 py-4"><span class="{{ $ticket['statusClass'] }} inline-flex items-center text-xs font-bold"><span class="{{ $ticket['dot'] }} mr-1.5 h-1.5 w-1.5 rounded-full"></span>{{ $ticket['status'] }}</span></td>
                                            <td class="px-2 py-4 text-right"><a class="font-bold text-[#065F2A] hover:underline" href="{{ $ticket['route'] }}">{{ $ticket['action'] }}</a></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 flex flex-col justify-between gap-2 border-t border-[#E2E8F0] pt-3 text-xs text-[#64748B] sm:flex-row">
                            <span>Server sync runs every 60 seconds on the campus network.</span>
                            <span class="font-mono text-[#334155]">Local gateway: 127.0.0.1:8001</span>
                        </div>
                    </article>

                    <aside class="space-y-6">
                        <section class="rounded-2xl border border-[#CBD5E1] bg-white p-6 shadow-sm">
                            <h2 class="font-display text-lg font-bold text-[#0F172A]">Duty Desk & Quick Contacts</h2>
                            <p class="mt-1 text-sm leading-6 text-[#64748B]">Use these contacts for urgent desk support and system issues.</p>

                            <div class="mt-4 space-y-3">
                                <div class="flex items-start gap-3 rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] p-3">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#DCFCE7] text-xs font-bold text-[#065F2A]">CL</span>
                                    <div>
                                        <h3 class="text-sm font-bold text-[#0F172A]">Chief Librarian Office</h3>
                                        <p class="text-xs text-[#64748B]">Local 104 - Room 201</p>
                                        <a class="text-xs font-bold text-[#065F2A] hover:underline" href="mailto:library@clcst.edu.ph">library@clcst.edu.ph</a>
                                    </div>
                                </div>

                                <div class="flex items-start gap-3 rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] p-3">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#DBEAFE] text-xs font-bold text-[#1D4ED8]">IT</span>
                                    <div>
                                        <h3 class="text-sm font-bold text-[#0F172A]">MIS & IT Helpdesk</h3>
                                        <p class="text-xs text-[#64748B]">Local 112 - Main Server Room</p>
                                        <a class="text-xs font-bold text-[#1D4ED8] hover:underline" href="mailto:mis@clcst.edu.ph">mis@clcst.edu.ph</a>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-2xl border border-[#BBF7D0] bg-[#F0FDF4] p-5 shadow-sm">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-[#065F2A]">info</span>
                                <h2 class="font-display text-lg font-bold text-[#064E3B]">Fine Rules</h2>
                            </div>
                            <div class="mt-3 space-y-2 text-sm leading-6 text-[#166534]">
                                <p><strong>Regular borrowed books:</strong> PHP 5.00 per day for each overdue title.</p>
                                <p><strong>Reserve books:</strong> PHP 2.00 per hour for hourly loans returned late.</p>
                            </div>
                            <a class="mt-4 inline-flex text-xs font-bold text-[#065F2A] hover:underline" href="{{ route('staff.section', 'reports') }}">Review Renewal Requests</a>
                        </section>
                    </aside>
                </section>

                <section class="rounded-2xl border border-[#CBD5E1] bg-white p-6 shadow-sm">
                    <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center">
                        <div>
                            <h2 class="font-display text-lg font-bold text-[#0F172A]">Staff Operations References</h2>
                            <p class="mt-1 max-w-3xl text-sm leading-6 text-[#64748B]">Quick links for common help topics, support requests, and library operations pages.</p>
                        </div>
                        <a class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#065F2A] px-4 py-2 text-sm font-bold text-white hover:bg-[#064E3B]" href="{{ route('books.catalog') }}">
                            <span class="material-symbols-outlined text-base">menu_book</span>
                            Open Book Catalog
                        </a>
                    </div>
                    <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach ([
                            ['route' => route('staff.section', 'circulation'), 'icon' => 'swap_horiz', 'label' => 'Check Borrowed Books'],
                            ['route' => route('staff.section', 'reservations'), 'icon' => 'book_online', 'label' => 'View Reservations'],
                            ['route' => route('staff.section', 'academic'), 'icon' => 'school', 'label' => 'Academic Requests'],
                            ['route' => route('staff.section', 'notifications'), 'icon' => 'notifications', 'label' => 'Notification Center'],
                        ] as $tool)
                            <a class="flex items-center justify-between rounded-xl border border-[#CBD5E1] bg-[#F8FAFC] p-3 text-sm font-bold text-[#334155] hover:bg-[#E2E8F0]" href="{{ $tool['route'] }}">
                                <span class="flex items-center gap-2"><span class="material-symbols-outlined text-base text-[#065F2A]">{{ $tool['icon'] }}</span>{{ $tool['label'] }}</span>
                                <span class="material-symbols-outlined text-base text-[#64748B]">chevron_right</span>
                            </a>
                        @endforeach
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
