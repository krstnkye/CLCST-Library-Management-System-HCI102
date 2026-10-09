<!DOCTYPE html>
<html class="h-full bg-background" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} - CLCST Library Portal</title>
    @include('partials.favicon')
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
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
                        'error-container': '#ffdad6',
                        tertiary: '#002e97',
                        'tertiary-fixed': '#dce1ff',
                        'on-tertiary-fixed': '#001551'
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
        .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f2f3ff; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #c0c9bd; border-radius: 9999px; }
    </style>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="h-full overflow-x-hidden bg-background font-body text-on-surface antialiased">
    @php
        $activeLoanCount = count($activeLoans);
        $reservationCount = count($reservations);
        $totalRows = count($historyRows);
        $returnedCount = collect($historyRows)->whereIn('status', ['returned', 'grace', 'settled'])->count();
        $onTimeCount = collect($historyRows)->where('status', 'returned')->count();
        $onTimeRate = $totalRows > 0 ? round(($onTimeCount / $totalRows) * 100) : 0;
    @endphp

    <header class="sticky top-0 z-40 flex h-16 w-full items-center justify-between border-b border-outline-variant bg-surface-container-lowest px-4 shadow-sm md:px-6">
        <div class="flex items-center gap-4">
            @include('partials.celtech-logo', ['class' => 'flex h-10 w-10 items-center justify-center rounded-full bg-primary text-sm font-extrabold text-white shadow-sm object-contain bg-white p-0.5'])
            <div class="flex flex-col">
                <span class="font-headline text-base font-bold leading-tight tracking-wide text-primary">CLCST SCHOOL LIBRARY</span>
                <span class="text-xs font-medium text-on-surface-variant">Integrated Library System - IM101 Patron Portal</span>
            </div>
            <div class="ml-4 hidden items-center border-l border-outline-variant pl-4 lg:flex">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-surface-container px-2.5 py-1 text-xs font-semibold text-primary">
                    <span class="h-2 w-2 rounded-full bg-primary-container"></span>
                    Student Patron Portal - IM101 Ready
                </span>
            </div>
        </div>
        <form class="mx-8 hidden max-w-md flex-1 items-center md:flex" method="GET" action="{{ route('student.section', 'history') }}">
            <div class="relative w-full">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-lg text-outline">search</span>
                <input class="w-full rounded-lg border border-outline-variant bg-surface-container-low py-1.5 pl-9 pr-10 text-sm placeholder:text-outline focus:bg-surface-container-lowest focus:ring-1 focus:ring-primary" name="q" placeholder="Search catalog, call number, or transaction..." value="{{ request('q') }}">
                <span class="absolute right-2.5 top-1/2 -translate-y-1/2 rounded border border-outline-variant bg-surface-container px-1.5 py-0.5 font-mono text-[10px] text-on-surface-variant">Ctrl K</span>
            </div>
        </form>
        <div class="flex items-center gap-3">
            <a class="relative rounded-lg p-2 text-on-surface-variant hover:bg-surface-container-low" href="{{ route('student.section', 'notifications') }}" title="Notifications">
                <span class="material-symbols-outlined text-xl">notifications</span>
                @if ($reservationCount > 0)
                    <span class="absolute right-1.5 top-1.5 h-2 w-2 rounded-full bg-error ring-2 ring-surface-container-lowest"></span>
                @endif
            </a>
            <a class="rounded-lg p-2 text-on-surface-variant hover:bg-surface-container-low" href="{{ route('student.section', 'support') }}" title="Help"><span class="material-symbols-outlined text-xl">help</span></a>
            <div class="flex items-center gap-3 border-l border-outline-variant pl-3">
                <div class="hidden text-right sm:block">
                    <p class="text-xs font-bold leading-tight">Juan Dela Cruz</p>
                    <p class="text-[10px] text-on-surface-variant">2024-10492-OL - BSCS-3A</p>
                </div>
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-primary-container text-xs font-bold text-on-primary ring-2 ring-primary-fixed">JD</div>
            </div>
        </div>
    </header>

    <div class="flex min-h-[calc(100vh-4rem)]">
        @include('partials.student-sidebar', ['activePage' => 'history'])

        <main class="md:ml-64 flex min-w-0 flex-1 flex-col bg-background pb-16">
            <div class="border-b border-outline-variant bg-surface-container-lowest px-4 pb-4 pt-6 md:px-8">
                <nav class="mb-2 flex items-center gap-2 text-[10px] font-bold text-on-surface-variant">
                    <span>Student Portal</span><span class="material-symbols-outlined text-sm">chevron_right</span><span>Circulation Records</span><span class="material-symbols-outlined text-sm">chevron_right</span><span class="text-primary">Borrowing History</span>
                </nav>
                @if (session('status'))
                    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>
                @endif
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h1 class="font-headline text-3xl font-bold tracking-tight text-primary">Borrowing History & Academic Reading Ledger</h1>
                        <p class="mt-1 max-w-3xl text-sm text-on-surface-variant">Complete audit record of physical textbooks, journals, and thesis manuscripts borrowed, returned, renewed, or cleared by <span class="font-semibold text-on-surface">Juan Dela Cruz</span>.</p>
                    </div>
                    <div class="flex shrink-0 items-center gap-3">
                        <a class="inline-flex items-center gap-2 rounded-lg border border-primary px-3.5 py-2.5 text-sm font-semibold text-primary hover:bg-surface-container-low" href="{{ route('student.section', 'history', ['export' => 'csv']) }}"><span class="material-symbols-outlined text-lg">download</span>Export History (.CSV)</a>
                        <a class="inline-flex items-center gap-2 rounded-lg bg-primary-container px-4 py-2.5 text-sm font-semibold text-on-primary shadow-sm hover:bg-primary" href="#clearance"><span class="material-symbols-outlined text-lg">picture_as_pdf</span>Official Clearance</a>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 items-start gap-8 px-4 py-6 md:px-8 xl:grid-cols-12">
                <div class="flex flex-col gap-6 xl:col-span-8">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-xl border border-outline-variant bg-surface-container-lowest p-4 shadow-sm"><div class="mb-2 flex items-center justify-between"><span class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Lifetime Borrows</span><span class="material-symbols-outlined rounded-lg bg-surface-container p-1.5 text-primary">auto_stories</span></div><p class="font-headline text-2xl font-bold">{{ $totalRows }} Books</p><div class="mt-2 flex items-center gap-1.5 text-xs text-on-surface-variant"><span class="h-2 w-2 rounded-full bg-primary-container"></span>{{ $returnedCount }} Returned - {{ $activeLoanCount }} Active Loans</div></div>
                        <div class="rounded-xl border border-outline-variant bg-surface-container-lowest p-4 shadow-sm"><div class="mb-2 flex items-center justify-between"><span class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">On-Time Return Rate</span><span class="material-symbols-outlined rounded-lg bg-surface-container p-1.5 text-primary-container">verified_user</span></div><p class="font-headline text-2xl font-bold text-primary-container">{{ $onTimeRate }}% Rate</p><div class="mt-2 flex items-center gap-1.5 text-xs text-on-surface-variant"><span class="material-symbols-outlined text-sm text-primary-container">check_circle</span>{{ $onTimeCount }} on-time records</div></div>
                        <div class="rounded-xl border border-outline-variant bg-surface-container-lowest p-4 shadow-sm"><div class="mb-2 flex items-center justify-between"><span class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Circulation Penalties</span><span class="material-symbols-outlined rounded-lg bg-surface-container p-1.5 text-secondary">payments</span></div><p class="font-headline text-2xl font-bold">PHP 0.00 Due</p><div class="mt-2 text-xs text-on-surface-variant"><span class="font-semibold text-primary-container">Clean Record</span> - PHP 15.00 settled prior</div></div>
                        <div class="rounded-xl border border-outline-variant bg-surface-container-lowest p-4 shadow-sm"><div class="mb-2 flex items-center justify-between"><span class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Clearance Status</span><span class="material-symbols-outlined rounded-lg bg-primary-fixed p-1.5 text-primary">assignment_turned_in</span></div><p class="font-headline text-2xl font-bold text-primary-container">Clear & Eligible</p><div class="mt-2 flex items-center gap-1.5 text-xs text-on-surface-variant"><span class="material-symbols-outlined text-sm text-primary">sensor_door</span>Turnstile pass unlocked</div></div>
                    </div>

                    <div class="flex flex-col gap-3 rounded-xl border border-outline-variant bg-surface-container-lowest p-4 shadow-sm">
                        <div class="flex flex-col items-stretch gap-3 md:flex-row md:items-center">
                            <form class="relative flex-1" method="GET" action="{{ route('student.section', 'history') }}">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-lg text-outline">search</span>
                                <input class="w-full rounded-lg border border-outline-variant bg-background py-2 pl-9 pr-4 text-sm placeholder:text-outline focus:bg-surface-container-lowest focus:ring-1 focus:ring-primary" name="q" placeholder="Search by title, author, call number, or transaction ID..." value="{{ request('q') }}">
                            </form>
                            <div class="flex items-center gap-2">
                                <select class="rounded-lg border border-outline-variant bg-background py-2 pl-3 pr-8 text-sm focus:ring-1 focus:ring-primary"><option>All Loan Statuses</option><option>Active Loans ({{ $activeLoanCount }})</option><option>Returned - On Time ({{ $onTimeCount }})</option><option>Returned - Fine Settled (1)</option></select>
                                <select class="rounded-lg border border-outline-variant bg-background py-2 pl-3 pr-8 text-sm focus:ring-1 focus:ring-primary"><option>All Terms</option><option>1st Sem 2024-2025</option><option>2nd Sem 2023-2024</option></select>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center justify-between gap-2 border-t border-outline-variant/50 pt-2">
                            <div class="flex flex-wrap items-center gap-2"><span class="mr-1 text-[10px] font-bold text-on-surface-variant">Classification:</span><a class="rounded-md bg-primary-container px-2.5 py-1 text-[10px] font-bold text-on-primary" href="#">All ({{ $totalRows }})</a><a class="rounded-md bg-surface-container px-2.5 py-1 text-[10px] font-bold text-on-surface-variant hover:text-on-surface" href="#">Computer Science</a><a class="rounded-md bg-surface-container px-2.5 py-1 text-[10px] font-bold text-on-surface-variant hover:text-on-surface" href="#">Mathematics</a></div>
                            <div class="flex items-center gap-2"><span class="text-[10px] font-bold text-on-surface-variant">Sort:</span><select class="rounded border-none bg-surface-container py-1 pl-2 pr-6 text-[10px] font-bold"><option>Newest Borrow Date</option><option>Oldest Borrow Date</option><option>Title A-Z</option></select></div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm">
                        <div class="flex items-center justify-between border-b border-outline-variant p-4">
                            <div><h2 class="font-headline text-base font-bold">Circulation Audit Ledger</h2><p class="text-xs text-on-surface-variant">Displaying {{ $returnedCount }} historical and {{ $activeLoanCount }} active circulation transaction logs</p></div>
                            <span class="text-[10px] font-bold text-outline">Sorted by Borrow Date</span>
                        </div>
                        <div class="custom-scrollbar overflow-x-auto">
                            <table class="w-full border-collapse text-left">
                                <thead>
                                    <tr class="border-b border-outline-variant bg-surface-container-low text-[10px] font-bold text-on-surface-variant">
                                        <th class="px-4 py-3">Book Title & Catalog Details</th>
                                        <th class="px-4 py-3">Barcode / Copy ID</th>
                                        <th class="px-4 py-3">Borrowed Date & Desk</th>
                                        <th class="px-4 py-3">Due Date</th>
                                        <th class="px-4 py-3">Return / Audit Timestamp</th>
                                        <th class="px-4 py-3">Loan Status</th>
                                        <th class="px-4 py-3 text-right">Actions / Receipt</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-outline-variant/60 text-xs">
                                    @foreach ($historyRows as $row)
                                        @php
                                            $isActive = $row['status'] === 'active';
                                            $statusClasses = match ($row['status']) {
                                                'active' => 'bg-secondary-fixed text-on-secondary-container',
                                                'returned' => 'bg-green-100 text-green-800',
                                                'grace' => 'bg-surface-container-high text-on-surface',
                                                'settled' => 'bg-surface-container text-on-surface border border-outline-variant',
                                                default => 'bg-surface-container text-on-surface',
                                            };
                                            $statusLabel = match ($row['status']) {
                                                'active' => 'Active Loan',
                                                'returned' => 'Returned - On Time',
                                                'grace' => 'Grace Period',
                                                'settled' => 'Returned - Fine Settled',
                                                default => 'Recorded',
                                            };
                                        @endphp
                                        <tr class="{{ $isActive ? 'bg-primary-fixed/5' : '' }} transition-colors hover:bg-surface-container-low/50">
                                            <td class="px-4 py-3.5">
                                                <div class="flex items-start gap-3">
                                                    <div class="flex h-12 w-9 shrink-0 items-center justify-center rounded border border-outline-variant bg-surface-container text-primary"><span class="material-symbols-outlined text-xl">book</span></div>
                                                    <div>
                                                        <p class="font-headline text-sm font-bold leading-tight">{{ $row['title'] }}</p>
                                                        <p class="text-xs text-on-surface-variant">{{ $row['author'] }}</p>
                                                        <div class="mt-1 flex flex-wrap items-center gap-2">
                                                            <span class="rounded border border-outline-variant bg-surface-container px-1.5 py-0.5 font-mono text-[10px] text-on-surface-variant">{{ $row['call'] }}</span>
                                                            <span class="rounded bg-tertiary-fixed px-1.5 py-0.5 text-[10px] font-semibold text-on-tertiary-fixed">{{ $row['classification'] }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3.5 font-mono font-medium">{{ $row['barcode'] }}</td>
                                            <td class="px-4 py-3.5"><p class="font-semibold">{{ $row['borrowed'] }}</p><p class="text-[11px] text-on-surface-variant">{{ $row['desk'] }}</p></td>
                                            <td class="px-4 py-3.5"><p class="{{ $isActive && str_contains($row['note'], '2 days') ? 'text-error' : 'text-on-surface' }} font-semibold">{{ $renewedLoans[$row['loan_id'] ?? ''] ?? $row['due'] }}</p>@if ($isActive)<span class="mt-1 inline-flex items-center gap-1 rounded bg-primary-fixed px-1.5 py-0.5 text-[10px] font-bold text-primary">{{ $row['note'] }}</span>@endif</td>
                                            <td class="px-4 py-3.5"><p class="{{ $isActive ? 'text-secondary italic' : 'font-semibold text-primary-container' }}">{{ $row['returned'] }}</p><p class="text-[11px] text-on-surface-variant">{{ $isActive ? '' : $row['note'] }}</p></td>
                                            <td class="px-4 py-3.5"><span class="inline-flex items-center gap-1 rounded-full px-2 py-1 text-[11px] font-bold {{ $statusClasses }}"><span class="material-symbols-outlined text-[13px]">{{ $isActive ? 'schedule' : 'check_circle' }}</span>{{ $statusLabel }}</span></td>
                                            <td class="px-4 py-3.5 text-right">
                                                @if ($isActive)
                                                    <div class="inline-flex items-center justify-end gap-1.5">
                                                        <form method="POST" action="{{ route('loans.renew', $row['loan_id']) }}">
                                                            @csrf
                                                            <button class="rounded bg-primary-container px-2.5 py-1 text-[10px] font-bold text-on-primary hover:bg-primary" type="submit">Renew</button>
                                                        </form>
                                                        <a class="rounded p-1 text-on-surface-variant hover:bg-surface-container hover:text-primary" href="{{ route('student.section', 'borrow-return') }}" title="View Loan Slip"><span class="material-symbols-outlined text-lg">receipt_long</span></a>
                                                    </div>
                                                @else
                                                    <a class="inline-flex items-center gap-1 rounded border border-outline-variant px-2.5 py-1 text-[10px] font-bold text-on-surface hover:bg-surface-container" href="#clearance" title="Download Return Receipt"><span class="material-symbols-outlined text-base text-primary">download</span>{{ $row['receipt'] }}</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="flex items-center justify-between border-t border-outline-variant bg-surface-container-lowest p-4 text-xs text-on-surface-variant">
                            <span>Showing 1 to {{ $totalRows }} of {{ $totalRows }} records</span>
                            <div class="flex items-center gap-2"><button class="rounded border border-outline-variant px-3 py-1 opacity-50" disabled>Previous</button><button class="rounded bg-primary-container px-3 py-1 font-bold text-on-primary">1</button><button class="rounded border border-outline-variant px-3 py-1 hover:bg-surface-container">Next</button></div>
                        </div>
                    </div>
                </div>

                <aside class="flex flex-col gap-6 xl:col-span-4">
                    <section id="clearance" class="overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm">
                        <div class="bg-primary-container p-4 text-on-primary">
                            <div class="mb-1 flex items-center justify-between"><span class="text-[10px] font-bold uppercase tracking-wider text-on-primary/80">Official Document</span><span class="rounded border border-primary-fixed/40 bg-primary px-2 py-0.5 text-[10px] font-bold">TERM 1 - AY 24-25</span></div>
                            <h3 class="font-headline text-base font-bold leading-tight text-white">Student Library Clearance Certificate</h3>
                            <p class="mt-0.5 text-xs text-on-primary/90">Central Luzon College of Science and Technology</p>
                        </div>
                        <div class="flex flex-col gap-4 p-5">
                            <div class="flex items-center justify-between gap-4 rounded-xl border border-outline-variant bg-surface-container-low p-4">
                                <div><span class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Registrar Status</span><p class="mt-0.5 flex items-center gap-1.5 text-sm font-bold text-primary-container"><span class="material-symbols-outlined text-xl">verified</span>CLEAR TO ENROLL</p><p class="mt-1 font-mono text-[11px] text-outline">ID: CLR-2024-10492-99B</p></div>
                                <div class="grid h-16 w-16 shrink-0 grid-cols-4 gap-0.5 rounded-lg border border-outline-variant bg-white p-1.5 shadow-sm">@for ($i = 0; $i < 16; $i++)<div class="{{ in_array($i, [2,5,7,8,13]) ? 'bg-surface' : 'bg-primary' }} rounded-[1px]"></div>@endfor</div>
                            </div>
                            <div class="space-y-2.5 pt-1 text-sm">
                                <div class="flex items-center justify-between border-b border-outline-variant/40 py-1"><span class="text-on-surface-variant">Patron Name</span><span class="font-bold">Juan Dela Cruz</span></div>
                                <div class="flex items-center justify-between border-b border-outline-variant/40 py-1"><span class="text-on-surface-variant">Student Number</span><span class="font-mono font-medium">2024-10492-OL</span></div>
                                <div class="flex items-center justify-between border-b border-outline-variant/40 py-1"><span class="text-on-surface-variant">Degree Program</span><span class="font-medium">BS Computer Science (3A)</span></div>
                                <div class="flex items-center justify-between border-b border-outline-variant/40 py-1"><span class="text-on-surface-variant">Overdue Copies Held</span><span class="font-bold text-primary-container">0 Books</span></div>
                                <div class="flex items-center justify-between border-b border-outline-variant/40 py-1"><span class="text-on-surface-variant">Outstanding Fines</span><span class="font-bold text-primary-container">PHP 0.00</span></div>
                            </div>
                            <div class="flex items-center gap-3 rounded-lg border border-outline-variant bg-surface-container p-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-container text-xs font-bold text-on-primary">MS</div>
                                <div><p class="text-xs font-bold">Ms. Maria Santos, RL</p><p class="text-[11px] text-on-surface-variant">Head Librarian - CLCST Circulation Section</p><p class="mt-0.5 font-mono text-[10px] text-outline">Signed: Oct 20, 2024 - 08:30 AM PST</p></div>
                            </div>
                            <button class="flex w-full items-center justify-center gap-2 rounded-lg border border-outline-variant bg-surface-container px-4 py-2.5 text-xs font-bold text-primary hover:bg-surface-container-high" type="button"><span class="material-symbols-outlined text-lg">verified</span>Generate Official Seal Certificate</button>
                        </div>
                    </section>

                    <section class="flex flex-col gap-4 rounded-xl border border-outline-variant bg-surface-container-lowest p-5 shadow-sm">
                        <div class="flex items-center justify-between"><h3 class="font-headline text-base font-bold">Reading Analytics</h3><span class="material-symbols-outlined text-xl text-primary">analytics</span></div>
                        <div><div class="mb-1.5 flex items-center justify-between text-[10px] font-bold text-on-surface-variant"><span>Field Classification Breakdown</span><span class="text-primary">{{ $totalRows }} Volumes Total</span></div><div class="flex h-3 w-full overflow-hidden rounded-full bg-surface-container"><div class="h-full bg-primary-container" style="width: 75%"></div><div class="h-full bg-secondary-container" style="width: 15%"></div><div class="h-full bg-tertiary" style="width: 10%"></div></div><div class="mt-2 flex items-center justify-between text-[11px] text-on-surface-variant"><span>CompSci 75%</span><span>Math 15%</span><span>GenEd 10%</span></div></div>
                        <div class="grid grid-cols-2 gap-3 border-t border-outline-variant/50 pt-2"><div class="rounded-lg border border-outline-variant bg-surface-container-low p-3"><p class="text-[11px] font-semibold uppercase text-on-surface-variant">Avg. Loan Time</p><p class="font-headline mt-0.5 text-base font-bold text-primary">11.4 Days</p><p class="text-[10px] text-outline">Under 14-day limit</p></div><div class="rounded-lg border border-outline-variant bg-surface-container-low p-3"><p class="text-[11px] font-semibold uppercase text-on-surface-variant">Top Authors</p><p class="mt-0.5 truncate text-xs font-bold">R. Martin, J. Zelle</p><p class="text-[10px] text-outline">Software Engineering</p></div></div>
                    </section>

                    <section class="rounded-xl border border-secondary/40 bg-surface-container-lowest p-5 shadow-sm">
                        <div class="mb-2 flex items-center gap-2 text-secondary"><span class="material-symbols-outlined text-xl">info</span><h4 class="text-sm font-bold text-on-surface">Circulation Regulations</h4></div>
                        <ul class="space-y-2 text-sm text-on-surface-variant">
                            <li class="flex items-start gap-2"><span class="material-symbols-outlined mt-0.5 shrink-0 text-base text-primary">check_circle</span><span><strong>14-Day Maximum:</strong> Regular circulation books may be kept for up to 14 calendar days.</span></li>
                            <li class="flex items-start gap-2"><span class="material-symbols-outlined mt-0.5 shrink-0 text-base text-primary">check_circle</span><span><strong>1 Renewal Allowed:</strong> Eligible titles can be renewed once online unless another patron has a hold.</span></li>
                            <li class="flex items-start gap-2"><span class="material-symbols-outlined mt-0.5 shrink-0 text-base text-secondary">warning</span><span><strong>PHP 5.00/Day Fine:</strong> Overdue penalties accumulate daily per book.</span></li>
                        </ul>
                        <div class="mt-4 flex items-center justify-between border-t border-outline-variant/60 pt-3"><a class="flex items-center gap-1 text-xs font-bold text-primary hover:underline" href="{{ route('student.section', 'circulation-policy') }}">View Full Library Manual<span class="material-symbols-outlined text-sm">arrow_forward</span></a><span class="font-mono text-[11px] text-outline">Rev. 2024</span></div>
                    </section>
                </aside>
            </div>
        </main>
    </div>
</body>
</html>
