<!DOCTYPE html>
<html class="h-full bg-surface" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} - CLCST Library Portal</title>
    @include('partials.favicon')
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
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
        .custom-scroll::-webkit-scrollbar { height: 6px; width: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: #f2f3ff; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #c0c9bd; border-radius: 9999px; }
    </style>
    @include('partials.ui-motion')
</head>
<body class="flex h-full flex-col bg-background font-body text-on-surface antialiased">
    @php
        $activeLoanCount = count($activeLoans);
        $reservationCount = count($reservations);
        $slotsLeft = max(0, 5 - $activeLoanCount);
        $stagedBooks = collect($catalogBooks)->whereIn('id', ['python-programming', 'object-oriented-se'])->values();
        $recentReturns = [
            ['title' => 'Database Systems: Design, Implementation & Management', 'copy' => 'DB-002', 'patron' => 'Maria Santos', 'status' => 'Returned on time', 'fine' => 'PHP 0.00', 'tone' => 'ok'],
            ['title' => 'Computer Networks: A Systems Approach', 'copy' => 'CN-003', 'patron' => 'Robert Cortez', 'status' => '4 days overdue', 'fine' => 'PHP 80.00', 'tone' => 'warn'],
        ];
    @endphp

    <header class="sticky top-0 z-40 flex h-16 w-full items-center justify-between bg-surface-container-lowest px-4 shadow-sm md:px-6">
        <div class="flex items-center gap-4">
            @include('partials.celtech-logo', ['class' => 'flex h-10 w-10 items-center justify-center rounded-full bg-primary text-sm font-extrabold text-white shadow-sm object-contain bg-white p-0.5'])
            <div>
                <div class="font-headline text-base font-bold leading-none tracking-wide text-primary">CLCST SCHOOL LIBRARY</div>
                <div class="mt-1 flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-outline">
                    <span class="h-2 w-2 rounded-full bg-primary-container"></span>
                    Student Borrowing Desk
                </div>
            </div>
            <nav class="ml-4 hidden items-center gap-1 border-l border-outline-variant pl-5 xl:flex">
                <a class="rounded-lg px-3 py-1.5 text-xs font-semibold text-on-surface-variant hover:bg-surface-container-low" href="{{ route('student.dashboard') }}">Dashboard</a>
                <a class="rounded-lg px-3 py-1.5 text-xs font-semibold text-on-surface-variant hover:bg-surface-container-low" href="{{ route('books.catalog') }}">Book Catalog</a>
                <a class="rounded-lg bg-surface-container-low px-3 py-1.5 text-xs font-semibold text-primary ring-1 ring-outline-variant" href="{{ route('student.section', 'borrow-return') }}">Borrow/Return</a>
            </nav>
        </div>
        <div class="flex items-center gap-3">
            <div class="hidden items-center gap-2 rounded-lg border border-outline-variant bg-surface-container-low px-3 py-1.5 text-xs lg:flex">
                <span class="flex items-center gap-1 font-semibold text-primary"><span class="material-symbols-outlined text-sm">barcode_scanner</span>Scanner Ready</span>
                <span class="text-outline-variant">|</span>
                <span class="text-on-surface-variant">Slip Printer Online</span>
            </div>
            <a class="rounded-lg p-2 text-on-surface-variant hover:bg-surface-container-low" href="{{ route('student.section', 'support') }}" title="Help">
                <span class="material-symbols-outlined">help</span>
            </a>
            <div class="flex items-center gap-2 border-l border-outline-variant pl-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-primary-container text-xs font-bold text-on-primary">JD</div>
                <div class="hidden text-left sm:block">
                    <div class="text-xs font-semibold text-primary">Juan Dela Cruz</div>
                    <div class="text-[10px] text-on-surface-variant">2024-10492-OL</div>
                </div>
            </div>
        </div>
    </header>

    <div class="flex flex-1 overflow-hidden">
        @include('partials.student-sidebar', ['activePage' => 'borrow-return'])

        <main class="md:ml-64 custom-scroll flex-1 overflow-y-auto bg-surface-container-low/40">
            <section class="border-b border-outline-variant bg-surface-container-lowest px-4 py-4 md:px-6">
                <div class="mx-auto flex max-w-7xl flex-col gap-4">
                    @if (session('status'))
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>
                    @endif
                    <div class="flex flex-col justify-between gap-3 lg:flex-row lg:items-center">
                        <div class="flex items-center rounded-xl border border-outline-variant/60 bg-surface-container p-1 shadow-sm">
                            <a class="flex items-center gap-2 rounded-lg bg-primary-container px-4 py-2 text-sm font-semibold text-on-primary" href="{{ route('books.catalog') }}"><span class="material-symbols-outlined">file_upload</span>Borrow Books</a>
                            <a class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-on-surface-variant hover:bg-surface-container-lowest" href="#quick-return"><span class="material-symbols-outlined">file_download</span>Return Books</a>
                            <a class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-on-surface-variant hover:bg-surface-container-lowest" href="#active-loans"><span class="material-symbols-outlined">update</span>Renewals</a>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-1.5 text-xs">
                                <span class="text-on-surface-variant">Lending Session:</span>
                                <span class="font-mono font-semibold text-primary">#STU-2024-10492</span>
                            </div>
                            <a class="rounded-lg border border-outline-variant px-3 py-1.5 text-xs font-semibold text-on-surface-variant hover:bg-surface-container-low" href="{{ route('student.section', 'history') }}">Audit Log</a>
                        </div>
                    </div>

                    <form class="grid grid-cols-1 items-end gap-3 md:grid-cols-12" method="GET" action="{{ route('books.catalog') }}">
                        <div class="md:col-span-5">
                            <label class="mb-1 flex items-center justify-between text-xs font-semibold text-on-surface-variant"><span class="flex items-center gap-1"><span class="inline-flex h-4 w-4 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-on-primary">1</span>Student Identification</span><span class="text-primary-container">Verified</span></label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-3 text-primary-container">badge</span>
                                <input class="w-full rounded-lg border-2 border-primary-container bg-surface-container-lowest py-2.5 pl-10 pr-3 text-sm font-medium" readonly value="2024-10492-OL - Juan Dela Cruz">
                            </div>
                        </div>
                        <div class="hidden justify-center pb-2 md:col-span-1 md:flex"><span class="material-symbols-outlined text-outline">arrow_forward</span></div>
                        <div class="md:col-span-6">
                            <label class="mb-1 flex items-center justify-between text-xs font-semibold text-on-surface-variant"><span class="flex items-center gap-1"><span class="inline-flex h-4 w-4 items-center justify-center rounded-full bg-primary-container text-[10px] font-bold text-on-primary">2</span>Scan Book Barcode / Accession Number</span><span class="text-outline">Press Enter after scan</span></label>
                            <div class="flex gap-2">
                                <div class="relative flex-1">
                                    <span class="material-symbols-outlined absolute left-3 top-3 text-outline">qr_code_scanner</span>
                                    <input class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest py-2.5 pl-10 pr-16 text-sm" name="q" placeholder="Ready to scan book barcode..." value="1049281">
                                    <span class="absolute right-3 top-2.5 rounded bg-surface-container px-1.5 py-0.5 font-mono text-[10px] text-outline">F2</span>
                                </div>
                                <button class="flex shrink-0 items-center gap-1.5 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-on-primary hover:bg-primary-container" type="submit"><span class="material-symbols-outlined text-base">search</span>Find Book</button>
                            </div>
                        </div>
                    </form>
                </div>
            </section>

            <div class="mx-auto w-full max-w-7xl space-y-6 p-4 md:p-6">
                <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12">
                    <div class="space-y-6 lg:col-span-8">
                        <section class="relative overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest p-5 shadow-sm">
                            <div class="pointer-events-none absolute -bottom-8 -right-8 h-36 w-36 rounded-full bg-primary/5"></div>
                            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                                <div class="flex items-start gap-4 sm:items-center">
                                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-primary-container to-primary text-lg font-bold text-on-primary shadow-sm">JD</div>
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h1 class="font-headline text-xl font-bold text-on-surface">Juan Dela Cruz</h1>
                                            <span class="inline-flex items-center gap-1 rounded-full border border-primary-container/30 bg-primary-fixed px-2.5 py-0.5 text-[11px] font-bold text-primary"><span class="h-1.5 w-1.5 rounded-full bg-primary-container"></span>Clear Standing</span>
                                            <span class="rounded bg-surface-container px-2 py-0.5 font-mono text-[11px] text-on-surface-variant">ID: 2024-10492-OL</span>
                                        </div>
                                        <p class="mt-0.5 text-sm text-on-surface-variant">College of Information and Communications Technology - BS Computer Science (3rd Year)</p>
                                    </div>
                                </div>
                                <div class="shrink-0 rounded-lg border border-outline-variant bg-surface-container-low p-2.5 text-center">
                                    <div class="select-all font-mono text-[11px] font-bold tracking-widest text-on-surface">||||| | |||| || ||| ||||</div>
                                    <div class="mt-0.5 font-mono text-[10px] text-outline">202410492OL</div>
                                </div>
                            </div>
                            <div class="mt-4 grid grid-cols-2 gap-3 border-t border-outline-variant/60 pt-4 sm:grid-cols-4">
                                <div class="rounded-lg border border-outline-variant/50 bg-surface-container-low/70 p-2.5"><span class="block text-[10px] font-bold uppercase text-outline">Currently on Loan</span><span class="font-headline text-base font-bold">{{ $activeLoanCount }} Volumes</span></div>
                                <div class="rounded-lg border border-outline-variant/50 bg-surface-container-low/70 p-2.5"><span class="block text-[10px] font-bold uppercase text-outline">Borrowing Slots Left</span><span class="font-headline text-base font-bold text-primary-container">{{ $slotsLeft }} of 5 Available</span></div>
                                <div class="rounded-lg border border-outline-variant/50 bg-surface-container-low/70 p-2.5"><span class="block text-[10px] font-bold uppercase text-outline">Outstanding Fines</span><span class="font-headline text-base font-bold text-primary">PHP 0.00</span></div>
                                <div class="rounded-lg border border-outline-variant/50 bg-surface-container-low/70 p-2.5"><span class="block text-[10px] font-bold uppercase text-outline">Account Expiration</span><span class="font-headline text-base font-bold">May 31, 2025</span></div>
                            </div>
                        </section>

                        <section class="overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm">
                            <div class="flex items-center justify-between border-b border-outline-variant bg-surface-bright px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-primary-container">shopping_basket</span>
                                    <h2 class="font-headline text-base font-bold">Ready to Borrow / Staged Items ({{ $stagedBooks->count() }} Books)</h2>
                                </div>
                                <a class="flex items-center gap-1 text-xs font-bold text-error hover:underline" href="{{ route('student.section', 'borrow-return') }}"><span class="material-symbols-outlined text-sm">delete_sweep</span>Clear Staging</a>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full border-collapse text-left">
                                    <thead>
                                        <tr class="border-b border-outline-variant/60 bg-surface-container-low text-[10px] uppercase text-outline">
                                            <th class="px-4 py-3">Item & Catalog Details</th>
                                            <th class="px-4 py-3">Barcode / Call No.</th>
                                            <th class="px-4 py-3">Loan Duration</th>
                                            <th class="px-4 py-3">Scheduled Return</th>
                                            <th class="px-4 py-3 text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-outline-variant/50 text-sm">
                                        @foreach ($stagedBooks as $book)
                                            @php $copy = collect($book['copies'])->firstWhere('status', 'available') ?? $book['copies'][0]; @endphp
                                            <tr class="hover:bg-surface-container-low/40">
                                                <td class="px-4 py-3.5">
                                                    <div class="flex items-center gap-3">
                                                        <img class="h-16 w-11 shrink-0 rounded border border-outline-variant object-cover shadow-sm" src="{{ $book['cover_url'] }}" alt="{{ $book['title'] }} cover">
                                                        <div>
                                                            <div class="font-headline text-sm font-semibold">{{ $book['title'] }}</div>
                                                            <div class="text-xs text-on-surface-variant">Author: {{ $book['author'] }} - {{ $book['publisher'] }}</div>
                                                            <span class="mt-1 inline-flex rounded bg-primary-fixed/60 px-2 py-0.5 text-[11px] font-bold text-primary">{{ $book['policy'] === 'reference' ? 'Use in Library Only' : 'General Borrowing' }} - {{ $book['location'] }}</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3.5 align-top"><div class="font-mono text-xs font-semibold">{{ $copy['copy'] }}</div><div class="font-mono text-xs text-outline">{{ $book['call'] }}</div></td>
                                                <td class="px-4 py-3.5 align-top"><span class="rounded bg-surface-container px-2.5 py-1 text-xs font-semibold text-on-surface-variant">14 Days Regular</span></td>
                                                <td class="px-4 py-3.5 align-top"><div class="text-xs font-bold text-primary-container">{{ now()->addDays(14)->format('M d, Y') }}</div><div class="text-xs text-outline">Until 5:00 PM</div></td>
                                                <td class="px-4 py-3.5 text-right align-top"><a class="rounded-lg p-1.5 text-outline hover:bg-error-container/30 hover:text-error" href="{{ route('books.catalog', ['selected' => $book['id']]) }}" title="Review in catalog"><span class="material-symbols-outlined text-base">visibility</span></a></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="flex flex-col items-center justify-between gap-4 border-t border-outline-variant bg-surface-container-low/60 p-5 md:flex-row">
                                <div class="flex items-center gap-4">
                                    <div><span class="block text-[10px] font-bold uppercase text-outline">Issuing Terms</span><span class="text-sm font-semibold">Standard Undergraduate 14-Day Loan</span></div>
                                    <div class="h-8 w-px bg-outline-variant"></div>
                                    <div><span class="block text-[10px] font-bold uppercase text-outline">Due Calculation</span><span class="text-xs font-bold text-primary-container">Due: {{ now()->addDays(14)->format('M d, Y') }} (14 Days)</span></div>
                                </div>
                                <div class="flex w-full items-center justify-end gap-3 md:w-auto">
                                    <button class="flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-outline-variant px-4 py-2.5 text-sm font-semibold hover:bg-surface-container md:flex-none" type="button"><span class="material-symbols-outlined text-base">receipt</span>Print Slip</button>
                                    <a class="flex flex-1 items-center justify-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-bold text-on-primary shadow-sm hover:bg-primary-container md:flex-none" href="{{ route('books.catalog') }}"><span class="material-symbols-outlined text-base">check_circle</span>Choose Books in Catalog</a>
                                </div>
                            </div>
                        </section>

                        <section id="quick-return" class="overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm">
                            <div class="flex items-center justify-between border-b border-outline-variant bg-surface-container-low/40 px-5 py-3.5">
                                <div class="flex items-center gap-2"><span class="material-symbols-outlined text-primary">assignment_returned</span><h2 class="font-headline text-base font-bold">Quick Return Desk & Recent Check-Ins</h2></div>
                                <span class="rounded bg-surface-container px-2 py-0.5 text-[10px] font-bold text-outline">Scanner Dropzone</span>
                            </div>
                            <div class="divide-y divide-outline-variant/60 p-4">
                                @foreach ($recentReturns as $return)
                                    <div class="{{ $return['tone'] === 'warn' ? 'my-1 -mx-4 rounded-lg border-l-4 border-error bg-error-container/20 px-4' : '' }} flex flex-col justify-between gap-3 py-3 first:pt-1 sm:flex-row sm:items-center">
                                        <div class="flex items-start gap-3">
                                            <div class="shrink-0 rounded-lg {{ $return['tone'] === 'warn' ? 'bg-error-container text-error' : 'bg-primary-fixed text-primary' }} p-2"><span class="material-symbols-outlined">{{ $return['tone'] === 'warn' ? 'warning' : 'check' }}</span></div>
                                            <div>
                                                <div class="font-headline text-sm font-semibold">{{ $return['title'] }} ({{ $return['copy'] }})</div>
                                                <div class="text-xs text-on-surface-variant">Returned by: <span class="font-semibold text-on-surface">{{ $return['patron'] }}</span> - {{ $return['status'] }}</div>
                                                <div class="{{ $return['tone'] === 'warn' ? 'text-error' : 'text-primary' }} mt-1 text-xs font-bold">Fine assessment: {{ $return['fine'] }}</div>
                                            </div>
                                        </div>
                                        <a class="self-end rounded-lg border border-outline-variant px-3 py-1.5 text-xs font-semibold text-on-surface-variant hover:bg-surface-container sm:self-center" href="{{ route('student.section', 'history') }}">Receipt</a>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    </div>

                    <aside class="space-y-6 lg:col-span-4">
                        <section id="active-loans" class="rounded-xl border border-outline-variant bg-surface-container-lowest p-5 shadow-sm">
                            <div class="flex items-center justify-between border-b border-outline-variant pb-3">
                                <div class="flex items-center gap-2"><span class="material-symbols-outlined text-primary-container">book</span><h2 class="font-headline text-base font-bold">Juan's Active Loans</h2></div>
                                <span class="rounded bg-surface-container px-2 py-0.5 text-[10px] font-bold text-on-surface-variant">{{ $activeLoanCount }} Items Held</span>
                            </div>
                            <div class="mt-3 space-y-3">
                                @foreach ($activeLoans as $loan)
                                    <div class="rounded-lg border border-outline-variant/70 bg-surface-container-low p-3">
                                        <div class="flex items-start justify-between gap-2">
                                            <div>
                                                <div class="font-headline text-sm font-semibold leading-tight">{{ $loan['title'] }}</div>
                                                <div class="mt-0.5 font-mono text-xs text-outline">BC: {{ $loan['barcode'] }} - {{ $loan['call'] }}</div>
                                            </div>
                                            <span class="whitespace-nowrap rounded bg-secondary-container px-2 py-0.5 text-[11px] font-bold text-on-secondary-container">Due {{ $renewedLoans[$loan['id']] ?? $loan['due'] }}</span>
                                        </div>
                                        <div class="mt-3 flex items-center justify-between border-t border-outline-variant/40 pt-2">
                                            <span class="flex items-center gap-1 text-xs text-outline"><span class="material-symbols-outlined text-sm">hourglass_top</span>Renewable</span>
                                            <form method="POST" action="{{ route('loans.renew', $loan['id']) }}">
                                                @csrf
                                                <button class="flex items-center gap-1 rounded border border-outline-variant bg-surface-container-lowest px-2.5 py-1 text-xs font-semibold text-primary-container hover:bg-surface-container" type="submit"><span class="material-symbols-outlined text-sm">restart_alt</span>Renew Loan</button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </section>

                        <section class="rounded-xl border border-outline-variant bg-surface-container-lowest p-5 shadow-sm">
                            <div class="flex items-center justify-between border-b border-outline-variant pb-3">
                                <div class="flex items-center gap-2"><span class="material-symbols-outlined text-primary-container">receipt_long</span><h2 class="font-headline text-base font-bold">Recent Activity</h2></div>
                                <span class="h-2 w-2 rounded-full bg-primary-container"></span>
                            </div>
                            <div class="mt-3 space-y-3 text-sm">
                                <div class="flex items-start gap-3"><span class="material-symbols-outlined rounded-full bg-primary-fixed p-1.5 text-primary">download</span><div><div class="font-semibold">Check-in: DB-002</div><div class="text-xs text-outline">10:42 AM - On-time return</div></div></div>
                                <div class="flex items-start gap-3"><span class="material-symbols-outlined rounded-full bg-primary/10 p-1.5 text-primary">upload</span><div><div class="font-semibold">Catalog hold requested</div><div class="text-xs text-outline">{{ $reservationCount }} active reservation(s)</div></div></div>
                                <div class="flex items-start gap-3"><span class="material-symbols-outlined rounded-full bg-secondary-container p-1.5 text-on-secondary-container">autorenew</span><div><div class="font-semibold">Renewal desk ready</div><div class="text-xs text-outline">Online self-service enabled</div></div></div>
                            </div>
                        </section>

                        <section class="rounded-xl border border-outline-variant bg-surface-container-lowest p-5 shadow-sm">
                            <h2 class="flex items-center gap-2 border-b border-outline-variant pb-3 font-headline text-base font-bold"><span class="material-symbols-outlined text-primary-container">devices</span>Desk Peripherals</h2>
                            <div class="mt-3 space-y-3 text-sm">
                                <div class="flex items-center justify-between rounded-lg border border-outline-variant/60 bg-surface-container-low p-2.5"><div class="flex items-center gap-2"><span class="material-symbols-outlined text-primary-container">barcode_scanner</span><div><div class="text-xs font-semibold">Barcode Scanner</div><div class="text-[10px] text-outline">Keyboard wedge mode</div></div></div><span class="rounded bg-primary-fixed px-2 py-0.5 text-[11px] font-bold text-primary">Ready</span></div>
                                <div class="flex items-center justify-between rounded-lg border border-outline-variant/60 bg-surface-container-low p-2.5"><div class="flex items-center gap-2"><span class="material-symbols-outlined text-primary-container">receipt</span><div><div class="text-xs font-semibold">Receipt Printer</div><div class="text-[10px] text-outline">80mm paper loaded</div></div></div><span class="rounded bg-primary-fixed px-2 py-0.5 text-[11px] font-bold text-primary">Online</span></div>
                            </div>
                        </section>
                    </aside>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
