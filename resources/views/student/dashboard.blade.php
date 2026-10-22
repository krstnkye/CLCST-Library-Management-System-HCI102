@extends('layouts.student')

@section('title', 'Student Dashboard')

@section('content')
    @php
        $dueSoon = $activeLoans->filter(fn ($loan) => ! $loan->isOverdue() && $loan->daysRemaining() <= 3);
        $overdue = $activeLoans->filter->isOverdue();
        $usedPercent = $loanLimit > 0 ? min(100, round($activeLoans->count() / $loanLimit * 100)) : 0;
    @endphp

    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-primary-container via-[#0e4723] to-[#0a351a] p-6 text-on-primary shadow-sm lg:p-8">
        <div class="absolute bottom-0 right-0 top-0 mr-6 hidden w-80 items-center justify-center text-8xl font-extrabold text-white/10 lg:flex" aria-hidden="true">CLCST</div>
        <div class="relative z-10 max-w-3xl space-y-3">
            <h1 class="font-headline text-3xl font-bold text-white md:text-4xl">Welcome back, {{ $patron->first_name ?: $patron->name }}!</h1>
            <p class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-slate-100">
                <span><strong>Student ID:</strong> {{ $patron->patron_id }}</span>
                <span aria-hidden="true">-</span>
                <span>{{ $patron->program_or_department ?: 'Program not specified' }}</span>
                <span class="rounded-full bg-[#FDE047] px-2.5 py-0.5 text-[10px] font-bold text-[#0F172A]">Borrowing limit: {{ $activeLoans->count() }} / {{ $loanLimit }} books</span>
            </p>
            <p class="text-xs leading-5 text-slate-200">Find a book in the catalog and reserve it online. Pick it up at the circulation desk, where a librarian checks it out to you for {{ $loanDays }} days.</p>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Account summary">
        <article class="rounded-xl border border-[#E2E8F0] bg-surface-container-lowest p-5 shadow-sm">
            <div class="flex items-center justify-between"><span class="text-xs font-semibold text-[#64748B]">Borrowed Books</span><span class="material-symbols-outlined rounded-lg bg-[#DCFCE7] p-2 text-[#166534]">menu_book</span></div>
            <p class="mt-4 font-headline text-2xl font-semibold">{{ $activeLoans->count() }} active {{ \Illuminate\Support\Str::plural('loan', $activeLoans->count()) }}</p>
            <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-[#E2E8F0]" role="progressbar" aria-valuenow="{{ $activeLoans->count() }}" aria-valuemin="0" aria-valuemax="{{ $loanLimit }}"><div class="h-full rounded-full bg-primary-container" style="width: {{ $usedPercent }}%"></div></div>
        </article>
        <article class="rounded-xl border {{ $dueSoon->isNotEmpty() ? 'border-[#FDE68A]' : 'border-[#E2E8F0]' }} bg-surface-container-lowest p-5 shadow-sm">
            <div class="flex items-center justify-between"><span class="text-xs font-semibold text-[#92400E]">Due Within 3 Days</span><span class="material-symbols-outlined rounded-lg bg-[#FEF3C7] p-2 text-[#92400E]">schedule</span></div>
            <p class="mt-4 font-headline text-2xl font-semibold text-[#92400E]">{{ $dueSoon->count() }} {{ \Illuminate\Support\Str::plural('book', $dueSoon->count()) }}</p>
            <p class="mt-2 text-xs text-[#92400E]">{{ $dueSoon->first() ? 'Next: '.$dueSoon->first()->due_at->format('M d, Y') : 'Nothing due soon' }}</p>
        </article>
        <article class="rounded-xl border {{ $overdue->isNotEmpty() ? 'border-red-300' : 'border-[#E2E8F0]' }} bg-surface-container-lowest p-5 shadow-sm">
            <div class="flex items-center justify-between"><span class="text-xs font-semibold text-[#64748B]">Overdue Books</span><span class="material-symbols-outlined rounded-lg {{ $overdue->isNotEmpty() ? 'bg-red-100 text-red-700' : 'bg-[#DCFCE7] text-[#166534]' }} p-2">{{ $overdue->isNotEmpty() ? 'warning' : 'check_circle' }}</span></div>
            <p class="mt-4 font-headline text-2xl font-semibold {{ $overdue->isNotEmpty() ? 'text-red-700' : '' }}">{{ $overdue->count() }} overdue</p>
            <p class="mt-2 text-xs text-[#64748B]">{{ $outstandingFines > 0 ? 'Unpaid fines: PHP '.number_format($outstandingFines, 2) : 'No unpaid fines' }}</p>
        </article>
        <article class="rounded-xl border border-[#E2E8F0] bg-surface-container-lowest p-5 shadow-sm">
            <div class="flex items-center justify-between"><span class="text-xs font-semibold text-[#64748B]">Books Returned</span><span class="material-symbols-outlined rounded-lg bg-[#EFF6FF] p-2 text-[#1D4ED8]">auto_stories</span></div>
            <p class="mt-4 font-headline text-2xl font-semibold">{{ $returnedCount }} {{ \Illuminate\Support\Str::plural('book', $returnedCount) }}</p>
            <p class="mt-2 text-xs text-[#64748B]">{{ $openReservations->count() }} open {{ \Illuminate\Support\Str::plural('reservation', $openReservations->count()) }}</p>
        </article>
    </section>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
        <div class="space-y-6 xl:col-span-8">
            <section class="space-y-4 rounded-2xl border border-[#E2E8F0] bg-surface-container-lowest p-5 shadow-sm lg:p-6" aria-labelledby="loans-heading">
                <div class="flex flex-col justify-between gap-2 border-b border-[#E2E8F0] pb-4 sm:flex-row sm:items-center">
                    <div>
                        <h2 id="loans-heading" class="font-headline text-xl font-bold">My Active Loans</h2>
                        <p class="text-xs text-[#64748B]">Books currently checked out under {{ $patron->patron_id }}</p>
                    </div>
                    <a class="text-xs font-bold text-primary hover:underline" href="{{ route('student.section', 'borrow-return') }}">Manage loans</a>
                </div>
                @forelse ($activeLoans as $loan)
                    @include('student.partials.loan-card', ['loan' => $loan])
                @empty
                    <div class="rounded-xl border border-dashed border-outline-variant p-6 text-center text-sm text-on-surface-variant">
                        You have no books on loan. <a class="font-bold text-primary hover:underline" href="{{ route('books.catalog') }}">Browse the catalog</a> to find one.
                    </div>
                @endforelse
            </section>

            <section class="space-y-4 rounded-2xl border border-[#E2E8F0] bg-surface-container-lowest p-5 shadow-sm lg:p-6" aria-labelledby="catalog-heading">
                <div class="flex flex-col justify-between gap-2 border-b border-[#E2E8F0] pb-4 sm:flex-row sm:items-center">
                    <div>
                        <h2 id="catalog-heading" class="font-headline text-xl font-bold">Quick Catalog Search</h2>
                        <p class="text-xs text-[#64748B]">Live shelf availability from the library database</p>
                    </div>
                </div>
                <form method="GET" action="{{ route('books.catalog') }}" class="relative" role="search">
                    <label class="sr-only" for="dashboard-search">Search books</label>
                    <span class="material-symbols-outlined pointer-events-none absolute inset-y-0 left-3.5 my-auto h-6 text-[#64748B]">search</span>
                    <input id="dashboard-search" class="w-full rounded-xl border border-[#CBD5E1] bg-[#F8FAFC] py-3 pl-11 pr-28 text-sm focus:border-primary focus:ring-primary" name="q" placeholder="Search by title, author, ISBN, or category..." type="search">
                    <button class="absolute inset-y-0 right-2 my-auto h-9 rounded-lg bg-primary-container px-4 text-xs font-bold text-on-primary hover:bg-[#0e4723]" type="submit">Search</button>
                </form>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    @foreach ($featuredBooks as $book)
                        <article class="flex flex-col justify-between rounded-xl border {{ $book->available_count > 0 ? 'border-[#E2E8F0]' : 'border-[#FEE2E2] bg-[#FFF5F5]' }} p-4">
                            <div>
                                <div class="mb-2 flex items-start justify-between gap-2">
                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $book->isReference() ? 'bg-blue-100 text-blue-900' : ($book->available_count > 0 ? 'bg-[#DCFCE7] text-[#166534]' : 'bg-[#FEE2E2] text-[#991B1B]') }}">
                                        {{ $book->isReference() ? 'In-library use only' : ($book->available_count > 0 ? 'Available ('.$book->available_count.')' : 'All copies borrowed') }}
                                    </span>
                                    <span class="text-[10px] font-semibold text-[#64748B]">{{ $book->call_number }}</span>
                                </div>
                                <h3 class="font-headline text-base font-bold">{{ $book->title }}</h3>
                                <p class="mt-0.5 text-xs text-[#64748B]">{{ $book->author }}</p>
                            </div>
                            <a class="mt-4 inline-flex w-fit rounded-lg bg-primary-container px-3 py-1.5 text-[11px] font-bold text-on-primary hover:bg-[#0e4723]" href="{{ route('books.catalog', ['selected' => $book->id]) }}">View copies</a>
                        </article>
                    @endforeach
                </div>
            </section>
        </div>

        <aside class="space-y-6 xl:col-span-4">
            <section class="space-y-4 rounded-2xl bg-gradient-to-b from-[#135d2e] via-[#0e4723] to-[#092c16] p-5 text-white shadow-md" aria-label="Digital library pass">
                <div class="flex items-center justify-between">
                    <h2 class="text-[10px] font-bold uppercase tracking-wide text-[#FDE047]">CLCST Digital Library Pass</h2>
                    <span class="rounded bg-white/20 px-2 py-0.5 text-[10px] font-bold text-[#AAF4B5]">AY {{ now()->month >= 6 ? now()->format('Y').'-'.now()->addYear()->format('Y') : now()->subYear()->format('Y').'-'.now()->format('Y') }}</span>
                </div>
                <div class="rounded-xl border border-white/20 bg-white/10 p-3.5 text-center">
                    <p class="font-headline text-xl font-extrabold tracking-wide">{{ mb_strtoupper($patron->name) }}</p>
                    <p class="font-mono text-xs font-bold text-[#FDE047]">{{ $patron->patron_id }}</p>
                    <p class="text-[11px] text-slate-200">{{ $patron->program_or_department }}</p>
                </div>
                <p class="text-center text-[11px] text-slate-300">Show this pass and your school ID at the circulation desk.</p>
            </section>

            <section class="space-y-3 rounded-2xl border border-[#E2E8F0] bg-surface-container-lowest p-5 shadow-sm" aria-labelledby="alerts-heading">
                <div class="flex items-center justify-between">
                    <h2 id="alerts-heading" class="font-headline text-base font-bold">Alerts</h2>
                    <a class="text-xs font-bold text-primary hover:underline" href="{{ route('student.section', 'notifications') }}">See all</a>
                </div>
                @forelse ($notifications->take(3) as $notification)
                    @include('partials.notification-item', ['notification' => $notification, 'compact' => true])
                @empty
                    <p class="text-sm text-on-surface-variant">You are all caught up.</p>
                @endforelse
            </section>

            <section class="space-y-2 rounded-2xl border border-[#E2E8F0] bg-surface-container-lowest p-5 shadow-sm">
                <h2 class="font-headline text-base font-bold">Quick Actions</h2>
                @foreach ([
                    ['history', 'Borrowing History', 'Returned books and receipts', route('student.section', 'history')],
                    ['book_online', 'My Reservations', 'Track books on hold', route('student.section', 'reservations')],
                    ['policy', 'Borrowing Policy', 'Loan periods, renewals, fines', route('student.section', 'circulation-policy')],
                ] as [$icon, $label, $desc, $href])
                    <a class="flex items-center justify-between rounded-xl border border-[#E2E8F0] p-3 hover:bg-slate-50" href="{{ $href }}">
                        <span class="flex items-center gap-3">
                            <span class="material-symbols-outlined rounded-lg bg-[#F0FDF4] p-2 text-sm text-[#166534]">{{ $icon }}</span>
                            <span><span class="block text-xs font-bold">{{ $label }}</span><span class="block text-xs text-[#64748B]">{{ $desc }}</span></span>
                        </span>
                        <span class="material-symbols-outlined text-[#94A3B8]">chevron_right</span>
                    </a>
                @endforeach
            </section>
        </aside>
    </div>
@endsection
