<!DOCTYPE html>
<html class="h-full bg-background text-on-surface" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} - CLCST Library Portal</title>
    @include('partials.favicon')
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#00441d',
                        'primary-container': '#135d2e',
                        'primary-fixed': '#aaf4b5',
                        'on-primary': '#ffffff',
                        secondary: '#785a00',
                        'secondary-container': '#fdc425',
                        'secondary-fixed': '#ffdf9a',
                        'on-secondary-container': '#6d5200',
                        background: '#faf8ff',
                        surface: '#faf8ff',
                        'surface-container-lowest': '#ffffff',
                        'surface-container-low': '#f2f3ff',
                        'surface-container': '#eaedff',
                        'surface-container-high': '#e2e7ff',
                        'on-surface': '#131b2e',
                        'on-surface-variant': '#404940',
                        outline: '#707a6f',
                        'outline-variant': '#c0c9bd',
                        error: '#ba1a1a',
                        'error-container': '#ffdad6'
                    },
                    fontFamily: {
                        body: ['Inter', 'sans-serif'],
                        headline: ['Plus Jakarta Sans', 'sans-serif']
                    }
                }
            }
        };
    </script>
    <style>
        .material-symbols-outlined {
            display: inline-block;
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            line-height: 1;
            vertical-align: middle;
        }
        .material-symbols-outlined.fill { font-variation-settings: 'FILL' 1, 'wght' 500, 'GRAD' 0, 'opsz' 24; }
        .custom-scroll::-webkit-scrollbar { height: 6px; width: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: #f2f3ff; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #c0c9bd; border-radius: 9999px; }
    </style>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="min-h-screen overflow-x-hidden bg-background font-body text-on-surface antialiased">
    @php
        $reservationCount = count($reservations);
        $activeLoanCount = count($activeLoans);
        $catalog = collect($catalogBooks)->keyBy('id');
        $sessionHolds = collect($reservations)->map(function ($reservation, $bookId) use ($catalog) {
            $book = $catalog->get($bookId);

            return [
                'id' => $bookId,
                'title' => $reservation['title'],
                'author' => $book['author'] ?? 'CLCST catalog record',
                'call' => $book['call'] ?? 'Pending catalog lookup',
                'copy' => collect($book['copies'] ?? [])->first()['copy'] ?? 'RES-001',
                'location' => $book['location'] ?? 'Circulation Counter 1',
                'requested_at' => $reservation['requested_at'],
                'status' => 'ready',
                'shelf' => 'Reserve Shelf B-04',
                'deadline' => now()->addHours(24)->format('M d, Y - 5:00 PM'),
                'source' => 'session',
            ];
        })->values();

        $sampleHolds = collect([
            [
                'id' => 'computer-networks',
                'title' => 'Computer Networks: A Systems Approach',
                'author' => 'Larry L. Peterson & Bruce S. Davie',
                'call' => 'TK5105.5 .P479 2021',
                'copy' => 'CN-001',
                'location' => '2nd Floor Stacks - Bay 4, Shelf 02',
                'requested_at' => 'Oct 25, 2024 - 8:30 AM',
                'status' => 'pulling',
                'shelf' => 'Awaiting shelf scan',
                'deadline' => 'Pending allocation',
                'source' => 'sample',
            ],
            [
                'id' => 'clean-code',
                'title' => 'Clean Code: A Handbook of Agile Software Craftsmanship',
                'author' => 'Robert C. Martin',
                'call' => 'QA76.76.C64 M37',
                'copy' => 'CC-004',
                'location' => 'Currently on loan',
                'requested_at' => 'Oct 25, 2024 - 9:05 AM',
                'status' => 'waitlist',
                'shelf' => 'Queue position 1',
                'deadline' => 'Expected after Oct 28',
                'source' => 'sample',
            ],
        ]);

        $holds = $sessionHolds->isNotEmpty() ? $sessionHolds->concat($sampleHolds)->values() : $sampleHolds->values();
        $readyCount = $holds->where('status', 'ready')->count();
        $pullingCount = $holds->where('status', 'pulling')->count();
        $waitlistCount = $holds->where('status', 'waitlist')->count();
    @endphp

    <header class="sticky top-0 z-40 flex h-16 w-full items-center justify-between border-b border-outline-variant bg-surface-container-lowest px-4 shadow-sm md:px-6">
        <div class="flex items-center gap-5">
            <div class="flex items-center gap-3">
                @include('partials.celtech-logo', ['class' => 'flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-extrabold text-white shadow-sm object-contain bg-white p-0.5'])
                <div>
                    <div class="font-headline text-base font-bold leading-tight tracking-wide text-primary">CLCST SCHOOL LIBRARY</div>
                    <p class="flex items-center gap-1.5 text-xs text-on-surface-variant"><span class="h-2 w-2 rounded-full bg-primary"></span>Circulation & Lending Services - Reservations Desk</p>
                </div>
            </div>
            <div class="hidden items-center gap-2 border-l border-outline-variant pl-4 xl:flex">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-surface-container px-2.5 py-1 text-[10px] font-bold"><span class="h-2 w-2 rounded-full bg-emerald-600"></span>Hold Locker 01: Online</span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-surface-container px-2.5 py-1 text-[10px] font-bold"><span class="material-symbols-outlined text-sm text-primary">shelves</span>Pickup Shelf: {{ $readyCount + 6 }}/30 Full</span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-surface-container px-2.5 py-1 text-[10px] font-bold text-outline"><span class="material-symbols-outlined text-sm">schedule</span>{{ now()->format('h:i A - M d, Y') }}</span>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <nav class="hidden items-center rounded-lg border border-outline-variant/60 bg-surface-container-low p-1 2xl:flex">
                <a class="rounded px-3 py-1.5 text-xs font-semibold text-on-surface-variant hover:bg-surface-container-lowest" href="{{ route('student.section', 'borrow-return') }}">Circulation</a>
                <a class="rounded px-3 py-1.5 text-xs font-semibold text-on-surface-variant hover:bg-surface-container-lowest" href="{{ route('books.catalog') }}">OPAC Catalog</a>
                <a class="rounded border border-outline-variant/40 bg-surface-container-lowest px-3 py-1.5 text-xs font-semibold text-primary shadow-sm" href="{{ route('student.section', 'reservations') }}">Reservations</a>
                <a class="rounded px-3 py-1.5 text-xs font-semibold text-on-surface-variant hover:bg-surface-container-lowest" href="{{ route('student.section', 'fines') }}">Fines</a>
            </nav>
            <a class="relative rounded-lg p-2 text-on-surface-variant hover:bg-surface-container-low" href="{{ route('student.section', 'notifications') }}" title="Notifications">
                <span class="material-symbols-outlined">notifications</span>
                <span class="absolute right-1 top-1 flex h-4 w-4 items-center justify-center rounded-full bg-error text-[10px] font-bold text-white">{{ $holds->count() }}</span>
            </a>
            <a class="rounded-lg p-2 text-on-surface-variant hover:bg-surface-container-low" href="{{ route('student.section', 'support') }}" title="Help"><span class="material-symbols-outlined">help</span></a>
            <div class="flex items-center gap-2 border-l border-outline-variant pl-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-primary text-xs font-bold text-on-primary">JD</div>
                <div class="hidden text-left sm:block">
                    <div class="text-xs font-semibold">Juan Dela Cruz</div>
                    <span class="text-[10px] text-outline">Student Patron</span>
                </div>
            </div>
        </div>
    </header>

    <div class="flex w-full">
        @include('partials.student-sidebar', ['activePage' => 'reservations'])

        <main class="min-h-[calc(100vh-4rem)] flex-1 space-y-6 p-4 md:ml-64 md:p-8">
            @if (session('status'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>
            @endif

            <section class="space-y-4">
                <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="rounded-full bg-primary-container px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-on-primary">Student Hold Desk</span>
                            <span class="text-xs text-outline">Term 1 - A.Y. 2024-2025</span>
                        </div>
                        <h1 class="font-headline mt-1 text-3xl font-bold text-primary">Book Reservations & Pickup Hold Shelf Management</h1>
                        <p class="text-sm text-on-surface-variant">Track your active holds, pickup windows, queue position, and reservation notices.</p>
                    </div>
                    <div class="flex shrink-0 items-center gap-3">
                        <a class="inline-flex items-center gap-2 rounded-lg border border-primary px-4 py-2.5 text-sm font-semibold text-primary hover:bg-primary-container/10" href="{{ route('student.section', 'borrow-return') }}"><span class="material-symbols-outlined text-lg">shopping_cart_checkout</span>Go to Borrow/Return</a>
                        <a class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-on-primary shadow-sm hover:bg-primary-container" href="{{ route('books.catalog') }}"><span class="material-symbols-outlined text-lg">add_circle</span>Manual Hold Request</a>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="relative overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest p-4 shadow-sm"><div class="absolute -right-3 -top-3 flex h-16 w-16 items-center justify-center rounded-full bg-secondary-fixed/40"><span class="material-symbols-outlined text-2xl text-secondary">local_mall</span></div><p class="text-xs font-semibold text-outline">Ready for Pickup</p><div class="mt-1 flex items-baseline gap-2"><span class="font-headline text-3xl font-bold">{{ $readyCount }}</span><span class="text-xs text-on-surface-variant">Books shelved</span></div><div class="mt-2.5 rounded border border-amber-200 bg-amber-50 px-2 py-1 text-[10px] font-bold text-amber-800">Pickup before assigned deadline</div></div>
                    <div class="relative overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest p-4 shadow-sm"><div class="absolute -right-3 -top-3 flex h-16 w-16 items-center justify-center rounded-full bg-primary-fixed/40"><span class="material-symbols-outlined text-2xl text-primary">layers</span></div><p class="text-xs font-semibold text-outline">Pending Stacks Retrieval</p><div class="mt-1 flex items-baseline gap-2"><span class="font-headline text-3xl font-bold text-primary">{{ $pullingCount }}</span><span class="text-xs text-on-surface-variant">Titles to pull</span></div><div class="mt-2.5 rounded bg-surface-container px-2 py-1 text-[10px] font-bold text-primary">Staff pulling route generated</div></div>
                    <div class="relative overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest p-4 shadow-sm"><div class="absolute -right-3 -top-3 flex h-16 w-16 items-center justify-center rounded-full bg-blue-100/70"><span class="material-symbols-outlined text-2xl text-blue-800">hourglass_top</span></div><p class="text-xs font-semibold text-outline">Waitlist / In Circulation</p><div class="mt-1 flex items-baseline gap-2"><span class="font-headline text-3xl font-bold">{{ $waitlistCount }}</span><span class="text-xs text-on-surface-variant">Active queued</span></div><div class="mt-2.5 rounded bg-surface-container-low px-2 py-1 text-[10px] font-bold text-outline">Auto-alert when returned</div></div>
                    <div class="relative overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest p-4 shadow-sm"><div class="absolute -right-3 -top-3 flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100/70"><span class="material-symbols-outlined text-2xl text-emerald-800">verified</span></div><p class="text-xs font-semibold text-outline">Active Reservations</p><div class="mt-1 flex items-baseline gap-2"><span class="font-headline text-3xl font-bold text-emerald-800">{{ $reservationCount }}</span><span class="text-xs text-on-surface-variant">From your account</span></div><div class="mt-2.5 rounded border border-emerald-200 bg-emerald-50 px-2 py-1 text-[10px] font-bold text-emerald-800">{{ $activeLoanCount }} current loans</div></div>
                </div>

                <div class="flex flex-col items-center justify-between gap-3 rounded-xl border border-outline-variant bg-surface-container-lowest p-3 shadow-sm md:flex-row">
                    <form class="relative w-full md:w-96" method="GET" action="{{ route('student.section', 'reservations') }}">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-xl text-outline">search</span>
                        <input class="w-full rounded-lg border border-outline-variant bg-surface py-2 pl-10 pr-9 text-sm placeholder:text-outline focus:border-primary focus:ring-2 focus:ring-primary" name="q" placeholder="Search by title, call number, or barcode..." value="{{ request('q') }}">
                        <button class="absolute right-2.5 top-1/2 -translate-y-1/2 text-outline hover:text-on-surface" type="submit" title="Search"><span class="material-symbols-outlined text-lg">photo_camera</span></button>
                    </form>
                    <div class="flex w-full items-center gap-1.5 overflow-x-auto pb-1 md:w-auto md:pb-0">
                        <a class="whitespace-nowrap rounded-lg bg-primary px-3 py-1.5 text-xs font-semibold text-on-primary shadow-sm" href="#queue">All Active ({{ $holds->count() }})</a>
                        <a class="whitespace-nowrap rounded-lg border border-outline-variant/50 bg-surface-container-low px-3 py-1.5 text-xs font-semibold text-on-surface-variant hover:bg-surface-container" href="#queue">Ready ({{ $readyCount }})</a>
                        <a class="whitespace-nowrap rounded-lg border border-outline-variant/50 bg-surface-container-low px-3 py-1.5 text-xs font-semibold text-on-surface-variant hover:bg-surface-container" href="#queue">Pending Pulling ({{ $pullingCount }})</a>
                        <a class="whitespace-nowrap rounded-lg border border-outline-variant/50 bg-surface-container-low px-3 py-1.5 text-xs font-semibold text-on-surface-variant hover:bg-surface-container" href="#queue">Waitlist ({{ $waitlistCount }})</a>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12">
                <div id="queue" class="space-y-4 lg:col-span-7">
                    <div class="flex items-center justify-between">
                        <h2 class="font-headline flex items-center gap-2 text-xl font-bold">Active Holds Queue <span class="text-xs font-normal text-outline">Sorted by pickup deadline urgency</span></h2>
                        <a class="rounded p-1 text-xs text-outline hover:text-primary" href="{{ route('student.section', 'reservations') }}"><span class="material-symbols-outlined text-base">sync</span></a>
                    </div>

                    @foreach ($holds as $hold)
                        @php
                            $isReady = $hold['status'] === 'ready';
                            $statusClass = match ($hold['status']) {
                                'ready' => 'border-amber-300 bg-amber-100 text-amber-900',
                                'pulling' => 'border-blue-200 bg-blue-100 text-blue-900',
                                default => 'border-purple-200 bg-purple-100 text-purple-900',
                            };
                            $statusLabel = match ($hold['status']) {
                                'ready' => 'READY FOR PICKUP',
                                'pulling' => 'PENDING STACKS RETRIEVAL',
                                default => 'WAITLIST POSITION #1',
                            };
                        @endphp
                        <article class="rounded-xl {{ $isReady ? 'border-2 border-amber-300' : 'border border-outline-variant' }} bg-surface-container-lowest p-5 shadow-sm transition-shadow hover:shadow-md">
                            <div class="flex flex-col items-start justify-between gap-3 border-b border-outline-variant/60 pb-3 sm:flex-row">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="flex items-center gap-1 rounded-full border px-2.5 py-1 text-[10px] font-bold {{ $statusClass }}"><span class="h-2 w-2 rounded-full {{ $isReady ? 'bg-amber-500' : 'bg-primary' }}"></span>{{ $statusLabel }}</span>
                                    <span class="rounded bg-surface-container px-2 py-0.5 font-mono text-[10px] text-on-surface-variant">Hold Ref: #HLD-{{ strtoupper(substr(md5($hold['id']), 0, 8)) }}</span>
                                </div>
                                <div class="rounded border border-amber-200 bg-amber-50 px-2 py-1 text-[10px] font-bold text-amber-900"><span class="material-symbols-outlined text-sm">timer</span> {{ $hold['deadline'] }}</div>
                            </div>
                            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-12">
                                <div class="sm:col-span-3">
                                    <div class="flex h-32 w-full flex-col justify-between rounded-lg border border-primary/20 bg-primary-container/10 p-2.5 text-left">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-primary">Reserved Book</span>
                                        <div class="font-headline line-clamp-3 text-xs font-bold text-primary">{{ $hold['title'] }}</div>
                                        <div class="font-mono text-[9px] text-outline">{{ $hold['copy'] }}</div>
                                    </div>
                                </div>
                                <div class="space-y-2 sm:col-span-9">
                                    <div>
                                        <h3 class="font-headline text-base font-bold">{{ $hold['title'] }}</h3>
                                        <p class="text-xs text-on-surface-variant">by {{ $hold['author'] }}</p>
                                    </div>
                                    <div class="grid grid-cols-1 gap-2 text-xs sm:grid-cols-2">
                                        <div><span class="text-outline">Accession Barcode:</span><span class="ml-1 font-mono font-semibold">{{ $hold['copy'] }}</span></div>
                                        <div><span class="text-outline">Call Number:</span><span class="ml-1 font-mono font-semibold">{{ $hold['call'] }}</span></div>
                                    </div>
                                    <div class="flex flex-col justify-between gap-2 rounded-lg border border-outline-variant/70 bg-surface-container-low p-2.5 text-xs sm:flex-row sm:items-center">
                                        <div>
                                            <div class="flex items-center gap-1.5 font-semibold"><span class="material-symbols-outlined text-base text-primary">person</span>Juan Dela Cruz <span class="text-outline">(BSCS 3rd Year)</span></div>
                                            <div class="mt-0.5 text-outline">ID: <span class="font-mono text-on-surface">2024-10492-OL</span> - Requested: {{ $hold['requested_at'] }}</div>
                                        </div>
                                        <div class="text-left sm:border-l sm:border-outline-variant/60 sm:pl-3 sm:text-right"><span class="block text-[10px] font-bold text-outline">Shelf Assigned</span><span class="rounded bg-primary-fixed/50 px-2 py-0.5 text-xs font-bold text-primary">{{ $hold['shelf'] }}</span></div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-outline-variant pt-3">
                                <span class="text-xs text-outline">Location: {{ $hold['location'] }}</span>
                                <div class="flex items-center gap-2">
                                    <a class="flex items-center gap-1 rounded-lg border border-outline-variant px-2.5 py-1.5 text-xs font-semibold text-on-surface-variant hover:bg-surface-container" href="{{ route('student.section', 'support') }}"><span class="material-symbols-outlined text-base">sms</span>Ask Librarian</a>
                                    @if ($hold['source'] === 'session')
                                        <form method="POST" action="{{ route('reservations.cancel', $hold['id']) }}">
                                            @csrf
                                            <button class="flex items-center gap-1 rounded-lg border border-error-container px-2.5 py-1.5 text-xs font-semibold text-error hover:bg-error-container/30" type="submit"><span class="material-symbols-outlined text-base">cancel</span>Cancel Hold</button>
                                        </form>
                                    @endif
                                    <a class="flex items-center gap-1.5 rounded-lg bg-primary px-3.5 py-1.5 text-xs font-semibold text-on-primary shadow-sm hover:bg-primary-container" href="{{ route('student.section', 'borrow-return') }}"><span class="material-symbols-outlined text-base">check_circle</span>Fulfill & Check-Out</a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <aside class="space-y-6 lg:col-span-5">
                    <section class="rounded-xl border border-outline-variant bg-surface-container-lowest p-5 shadow-sm">
                        <div class="flex items-center justify-between border-b border-outline-variant pb-3">
                            <div><h3 class="font-headline flex items-center gap-2 text-base font-bold"><span class="material-symbols-outlined text-primary">shelves</span>Reserve Shelf Capacity & Layout</h3><p class="text-xs text-outline">Circulation Counter Behind Desk 02</p></div>
                            <span class="rounded bg-primary-fixed/50 px-2.5 py-1 text-xs font-bold text-primary">{{ $readyCount + 6 }} / 30 Full</span>
                        </div>
                        <div class="mt-4 space-y-4">
                            @foreach (['A' => ['full' => 10, 'tone' => 'rose'], 'B' => ['full' => 6, 'tone' => 'emerald'], 'C' => ['full' => 2, 'tone' => 'emerald']] as $sectionName => $shelf)
                                <div>
                                    <div class="mb-1.5 flex items-center justify-between text-xs"><span class="font-bold">Section {{ $sectionName }}</span><span class="font-semibold text-outline">{{ $shelf['full'] }}/10 Occupied</span></div>
                                    <div class="grid grid-cols-10 gap-1.5">
                                        @for ($i = 1; $i <= 10; $i++)
                                            @php $filled = $i <= $shelf['full']; @endphp
                                            <span class="{{ $filled ? ($sectionName === 'A' ? 'bg-rose-200 text-rose-900 border-rose-300' : 'bg-emerald-100 text-emerald-900 border-emerald-300') : 'bg-surface-container text-outline border-outline-variant' }} {{ $sectionName === 'B' && $i === 4 ? 'ring-2 ring-primary bg-primary-container text-on-primary border-primary' : '' }} flex h-6 items-center justify-center rounded border text-[9px] font-bold">{{ $sectionName }}{{ $i }}</span>
                                        @endfor
                                    </div>
                                </div>
                            @endforeach
                            <a class="flex w-full items-center justify-center gap-2 rounded-lg border border-outline-variant px-3 py-2 text-xs font-semibold text-on-surface-variant hover:bg-surface-container" href="{{ route('books.catalog', ['q' => '1049281']) }}"><span class="material-symbols-outlined text-base">qr_code</span>Scan Barcode to Deposit on Shelf</a>
                        </div>
                    </section>

                    <section class="space-y-4 rounded-xl border border-outline-variant bg-surface-container-lowest p-5 shadow-sm">
                        <div class="flex items-center justify-between border-b border-outline-variant pb-3">
                            <div><h3 class="font-headline flex items-center gap-2 text-base font-bold"><span class="material-symbols-outlined text-primary">badge</span>Patron Verification Kiosk</h3><p class="text-xs text-outline">Student ID barcode or turnstile pass</p></div>
                            <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                        </div>
                        <div class="relative">
                            <input class="w-full rounded-lg border-2 border-primary/40 bg-surface py-2.5 pl-9 pr-20 font-mono text-xs focus:ring-2 focus:ring-primary" readonly value="2024-10492-OL">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-lg text-primary">barcode</span>
                            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 rounded bg-primary/10 px-2 py-0.5 text-[10px] font-bold text-primary">VERIFIED</span>
                        </div>
                        <div class="relative overflow-hidden rounded-xl bg-gradient-to-br from-[#135d2e] to-[#0e4723] p-4 text-white shadow-md">
                            <div class="relative z-10 flex items-start justify-between">
                                <div class="flex items-center gap-2.5">@include('partials.celtech-logo', ['class' => 'flex h-8 w-8 items-center justify-center rounded-full bg-white/10 text-xs font-bold object-contain bg-white p-0.5'])<div><div class="text-[10px] font-bold uppercase tracking-widest text-emerald-200">CLCST Library Borrower Pass</div><div class="text-xs font-bold">Central Luzon College of Sci & Tech</div></div></div>
                                <span class="rounded bg-amber-400 px-2 py-0.5 text-[10px] font-extrabold text-slate-900">ACTIVE</span>
                            </div>
                            <div class="relative z-10 mt-4 flex items-center gap-4"><div class="flex h-14 w-12 items-center justify-center rounded border border-white/40 bg-white/20 text-lg font-bold">JD</div><div><div class="font-headline text-base font-bold">Juan Dela Cruz</div><div class="text-xs text-emerald-100">BS Computer Science - 3rd Year</div><div class="mt-0.5 font-mono text-xs text-emerald-200">Patron ID: 2024-10492-OL</div></div></div>
                            <div class="relative z-10 mt-3 rounded bg-white p-2 text-center text-slate-900"><div class="font-mono text-xs font-bold tracking-[0.3em]">|||| ||| ||||| || ||||</div><span class="font-mono text-[9px] tracking-widest text-slate-700">*2024-10492-OL*</span></div>
                        </div>
                        <a class="flex w-full items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-xs font-bold text-on-primary shadow-sm hover:bg-primary-container" href="{{ route('student.section', 'borrow-return') }}"><span class="material-symbols-outlined text-lg">shopping_cart_checkout</span>Convert Reservation to Loan</a>
                    </section>

                    <section class="space-y-3 rounded-xl border border-outline-variant bg-surface-container-lowest p-5 shadow-sm">
                        <div class="flex items-center justify-between border-b border-outline-variant pb-2"><h3 class="font-headline flex items-center gap-2 text-base font-bold"><span class="material-symbols-outlined text-primary">mark_chat_unread</span>Automated Communication Log</h3><span class="text-[10px] font-bold text-outline">SMS & SMTP Gateway</span></div>
                        <div class="space-y-2.5 text-xs">
                            <div class="space-y-1 rounded-lg border border-emerald-200 bg-emerald-50 p-2.5 text-emerald-900"><div class="flex items-center justify-between font-bold"><span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-emerald-600"></span>SMS prepared for J. Dela Cruz</span><span class="font-normal text-outline">{{ now()->format('h:i A') }}</span></div><p class="text-emerald-800">Your reserved book is ready for pickup at the CLCST Library circulation desk.</p></div>
                            <div class="space-y-1 rounded-lg border border-blue-200 bg-blue-50 p-2.5 text-blue-900"><div class="flex items-center justify-between font-bold"><span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-blue-600"></span>Email notice queued</span><span class="font-normal text-outline">Today</span></div><p class="text-blue-800">Reservation queue status and pickup shelf details are available in the portal.</p></div>
                        </div>
                    </section>
                </aside>
            </section>
        </main>
    </div>
</body>
</html>
