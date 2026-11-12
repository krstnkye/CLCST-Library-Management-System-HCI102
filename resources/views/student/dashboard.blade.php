@extends('layouts.student')

@section('title', 'Student Dashboard')

@section('content')
    @php
        $dueSoon = $activeLoans->filter(fn ($loan) => ! $loan->isOverdue() && $loan->daysRemaining() <= 3);
        $overdue = $activeLoans->filter->isOverdue();
        $usedPercent = $loanLimit > 0 ? min(100, round($activeLoans->count() / $loanLimit * 100)) : 0;
    @endphp

    <section class="library-welcome">
        <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center">
            <div class="max-w-3xl">
                <p class="clcst-record-label"><span class="material-symbols-outlined text-base">local_library</span>Student circulation account · {{ now()->format('Y') }}</p>
                <h1 class="mt-2 font-headline text-2xl font-bold text-[#201F1C] md:text-3xl">Welcome, {{ $patron->first_name ?: $patron->name }}</h1>
                <p class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-[#615C52]">
                    <span><strong class="text-[#302E29]">Student ID:</strong> {{ $patron->patron_id }}</span>
                    <span aria-hidden="true">·</span>
                    <span>{{ $patron->program_or_department ?: 'Program not specified' }}</span>
                </p>
                <p class="mt-2 text-xs leading-5 text-[#736D62]">Search the catalog, reserve available books, and monitor each due date from this account.</p>
            </div>
            <div class="min-w-48 border-t border-[#E0DCD3] pt-3 lg:border-l lg:border-t-0 lg:pl-5 lg:pt-0">
                <p class="text-[10px] font-bold uppercase tracking-wider text-[#736D62]">Current borrowing</p>
                <p class="mt-1 text-xl font-bold text-primary">{{ $activeLoans->count() }} of {{ $loanLimit }} books</p>
                <p class="mt-1 text-xs text-[#736D62]">Standard loan: {{ $loanDays }} days</p>
            </div>
        </div>
    </section>

    <section class="library-metrics grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4" aria-label="Account summary">
        <article class="rounded-xl border border-[#EDEAE5] bg-surface-container-lowest p-5 shadow-sm">
            <div class="flex items-center justify-between"><span class="text-xs font-semibold text-[#817B6E]">Borrowed Books</span><span class="material-symbols-outlined rounded-lg bg-[#F1F4F0] p-2 text-[#284A30]">menu_book</span></div>
            <p class="mt-4 font-headline text-2xl font-semibold">{{ $activeLoans->count() }} active {{ \Illuminate\Support\Str::plural('loan', $activeLoans->count()) }}</p>
            <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-[#EDEAE5]" role="progressbar" aria-valuenow="{{ $activeLoans->count() }}" aria-valuemin="0" aria-valuemax="{{ $loanLimit }}"><div class="h-full rounded-full bg-primary-container" style="width: {{ $usedPercent }}%"></div></div>
        </article>
        <article class="rounded-xl border {{ $dueSoon->isNotEmpty() ? 'border-[#E8D5A8]' : 'border-[#EDEAE5]' }} bg-surface-container-lowest p-5 shadow-sm">
            <div class="flex items-center justify-between"><span class="text-xs font-semibold text-[#574222]">Due Within 3 Days</span><span class="material-symbols-outlined rounded-lg bg-[#F3E8CF] p-2 text-[#574222]">schedule</span></div>
            <p class="mt-4 font-headline text-2xl font-semibold text-[#574222]">{{ $dueSoon->count() }} {{ \Illuminate\Support\Str::plural('book', $dueSoon->count()) }}</p>
            <p class="mt-2 text-xs text-[#574222]">{{ $dueSoon->first() ? 'Next: '.$dueSoon->first()->due_at->format('M d, Y') : 'Nothing due soon' }}</p>
        </article>
        <article class="rounded-xl border {{ $overdue->isNotEmpty() ? 'border-red-300' : 'border-[#EDEAE5]' }} bg-surface-container-lowest p-5 shadow-sm">
            <div class="flex items-center justify-between"><span class="text-xs font-semibold text-[#817B6E]">Overdue Books</span><span class="material-symbols-outlined rounded-lg {{ $overdue->isNotEmpty() ? 'bg-red-100 text-red-700' : 'bg-[#F1F4F0] text-[#284A30]' }} p-2">{{ $overdue->isNotEmpty() ? 'warning' : 'check_circle' }}</span></div>
            <p class="mt-4 font-headline text-2xl font-semibold {{ $overdue->isNotEmpty() ? 'text-red-700' : '' }}">{{ $overdue->count() }} overdue</p>
            <p class="mt-2 text-xs text-[#817B6E]">{{ $outstandingFines > 0 ? 'Unpaid fines: PHP '.number_format($outstandingFines, 2) : 'No unpaid fines' }}</p>
        </article>
        <article class="rounded-xl border border-[#EDEAE5] bg-surface-container-lowest p-5 shadow-sm">
            <div class="flex items-center justify-between"><span class="text-xs font-semibold text-[#817B6E]">Books Returned</span><span class="material-symbols-outlined rounded-lg bg-[#F0F3F7] p-2 text-[#3B4E6C]">auto_stories</span></div>
            <p class="mt-4 font-headline text-2xl font-semibold">{{ $returnedCount }} {{ \Illuminate\Support\Str::plural('book', $returnedCount) }}</p>
            <p class="mt-2 text-xs text-[#817B6E]">{{ $openReservations->count() }} open {{ \Illuminate\Support\Str::plural('reservation', $openReservations->count()) }}</p>
        </article>
    </section>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
        <div class="space-y-6 xl:col-span-8">
            <section class="library-section space-y-4 bg-surface-container-lowest p-5 lg:p-6" aria-labelledby="loans-heading">
                <div class="flex flex-col justify-between gap-2 border-b border-[#EDEAE5] pb-4 sm:flex-row sm:items-center">
                    <div>
                        <h2 id="loans-heading" class="font-headline text-xl font-bold">My Active Loans</h2>
                        <p class="text-xs text-[#817B6E]">Books currently checked out under {{ $patron->patron_id }}</p>
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

            <section class="library-section space-y-4 bg-surface-container-lowest p-5 lg:p-6" aria-labelledby="catalog-heading">
                <div class="flex flex-col justify-between gap-2 border-b border-[#EDEAE5] pb-4 sm:flex-row sm:items-center">
                    <div>
                        <h2 id="catalog-heading" class="font-headline text-xl font-bold">Quick Catalog Search</h2>
                        <p class="text-xs text-[#817B6E]">Live shelf availability from the library database</p>
                    </div>
                </div>
                <form method="GET" action="{{ route('books.catalog') }}" class="relative" role="search">
                    <label class="sr-only" for="dashboard-search">Search books</label>
                    <span class="material-symbols-outlined pointer-events-none absolute inset-y-0 left-3.5 my-auto h-6 text-[#817B6E]">search</span>
                    <input id="dashboard-search" class="w-full rounded-xl border border-[#DBD8D1] bg-[#F6F3EC] py-3 pl-11 pr-28 text-sm focus:border-primary focus:ring-primary" name="q" placeholder="Search by title, author, ISBN, or category..." type="search">
                    <button class="absolute inset-y-0 right-2 my-auto h-9 rounded-lg bg-primary-container px-4 text-xs font-bold text-on-primary hover:bg-[#1F3C27]" type="submit">Search</button>
                </form>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    @foreach ($featuredBooks as $book)
                        <article class="library-book">
                            <img src="{{ $book->cover_url }}" alt="" loading="lazy" onerror="this.onerror=null; this.src='{{ asset('images/book-cover.svg') }}'">
                            <div>
                                <div class="mb-2 flex items-start justify-between gap-2">
                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $book->isReference() ? 'bg-blue-100 text-blue-900' : ($book->available_count > 0 ? 'bg-[#F1F4F0] text-[#284A30]' : 'bg-[#FAF1EF] text-[#85362A]') }}">
                                        {{ $book->isReference() ? 'In-library use only' : ($book->available_count > 0 ? 'Available ('.$book->available_count.')' : 'All copies borrowed') }}
                                    </span>
                                    <span class="text-[10px] font-semibold text-[#817B6E]">{{ $book->call_number }}</span>
                                </div>
                                <h3 class="font-headline text-base font-bold">{{ $book->title }}</h3>
                                <p class="mt-0.5 text-xs text-[#817B6E]">{{ $book->author }}</p>
                                <a class="mt-3 inline-flex w-fit items-center gap-1" href="{{ route('books.catalog', ['selected' => $book->id]) }}">View copies <span class="material-symbols-outlined text-sm">arrow_forward</span></a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        </div>

        <aside class="space-y-6 xl:col-span-4">
            <section class="library-pass space-y-4 rounded-2xl p-5 text-white shadow-md" aria-label="Digital library pass">
                <div class="flex items-center justify-between">
                    <h2 class="text-[10px] font-bold uppercase tracking-wide text-[#D8BC7C]">CLCST Digital Library Pass</h2>
                    <span class="rounded bg-white/20 px-2 py-0.5 text-[10px] font-bold text-[#C5D3C3]">AY {{ now()->month >= 6 ? now()->format('Y').'-'.now()->addYear()->format('Y') : now()->subYear()->format('Y').'-'.now()->format('Y') }}</span>
                </div>
                <div class="rounded-xl border border-white/20 bg-white/10 p-3.5 text-center">
                    <p class="font-headline text-xl font-extrabold tracking-wide">{{ mb_strtoupper($patron->name) }}</p>
                    <p class="font-mono text-xs font-bold text-[#D8BC7C]">{{ $patron->patron_id }}</p>
                    <p class="text-[11px] text-slate-200">{{ $patron->program_or_department }}</p>
                </div>
                <div class="library-pass-qr" data-qr="{{ $patron->patron_id }}" data-qr-label="QR code for {{ $patron->patron_id }}"></div>
                <p class="text-center text-[11px] text-slate-300">Show this QR code and your school ID at the circulation desk.</p>
                <a class="flex items-center justify-center gap-1.5 rounded-lg bg-white/15 px-3 py-2 text-xs font-bold text-white hover:bg-white/25" href="{{ route('student.section', 'account') }}"
                    data-epass data-epass-name="{{ $patron->name }}" data-epass-id="{{ $patron->patron_id }}" data-epass-program="{{ $patron->program_or_department }}" data-epass-role="Student">
                    <span class="material-symbols-outlined text-base">open_in_full</span>Show full-screen pass
                </a>
            </section>

            <section class="library-section space-y-3 bg-surface-container-lowest p-5" aria-labelledby="alerts-heading">
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

            <section class="library-section space-y-2 bg-surface-container-lowest p-5">
                <h2 class="font-headline text-base font-bold">Quick Actions</h2>
                @foreach ([
                    ['history', 'Borrowing History', 'Returned books and receipts', route('student.section', 'history')],
                    ['book_online', 'My Reservations', 'Track books on hold', route('student.section', 'reservations')],
                    ['policy', 'Borrowing Policy', 'Loan periods, renewals, fines', route('student.section', 'circulation-policy')],
                ] as [$icon, $label, $desc, $href])
                    <a class="flex items-center justify-between rounded-xl border border-[#EDEAE5] p-3 hover:bg-slate-50" href="{{ $href }}">
                        <span class="flex items-center gap-3">
                            <span class="material-symbols-outlined rounded-lg bg-[#F1F4F0] p-2 text-sm text-[#284A30]">{{ $icon }}</span>
                            <span><span class="block text-xs font-bold">{{ $label }}</span><span class="block text-xs text-[#817B6E]">{{ $desc }}</span></span>
                        </span>
                        <span class="material-symbols-outlined text-[#B1AA9B]">chevron_right</span>
                    </a>
                @endforeach
            </section>
        </aside>
    </div>
@endsection
