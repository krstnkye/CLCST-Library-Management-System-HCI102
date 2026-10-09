<!DOCTYPE html>
<html class="h-full bg-[#F8FAFC]" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>CLCST Library Management System - {{ $portalRole === 'faculty' ? 'Faculty Dashboard' : 'Staff Dashboard' }}</title>

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
    @php
        $isFaculty = $portalRole === 'faculty';
        $currentUser = $isFaculty
            ? ['initials' => 'ER', 'name' => 'Prof. Elena Reyes', 'subtitle' => 'Computer Studies Faculty']
            : ['initials' => 'MS', 'name' => 'Maria Santos', 'subtitle' => 'Head Librarian'];
    @endphp

    <header class="sticky top-0 z-40 flex h-16 w-full items-center justify-between border-b border-[#CBD5E1] bg-white px-4 shadow-sm lg:px-6">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#065F2A] text-sm font-extrabold text-white shadow-sm">CL</div>
            <div>
                <span class="block font-display text-sm font-bold leading-none tracking-wide text-[#064E3B] sm:text-base">CLCST LIBRARY MANAGEMENT SYSTEM</span>
                <span class="mt-0.5 hidden text-[10px] font-bold uppercase tracking-wider text-[#64748B] sm:block">{{ $isFaculty ? 'Faculty Services Portal' : 'Librarian Operations Console' }}</span>
            </div>
        </div>

        <div class="flex items-center gap-3 lg:gap-4">
            <form class="hidden min-w-[340px] items-center gap-2 rounded-full border border-[#CBD5E1] bg-[#F8FAFC] px-3 py-1.5 lg:flex" method="GET" action="{{ route('books.catalog') }}">
                <span class="material-symbols-outlined text-base text-[#64748B]">search</span>
                <input class="w-full border-0 bg-transparent p-0 text-xs text-[#0F172A] placeholder:text-[#94A3B8] focus:ring-0" name="q" placeholder="Search catalog, barcode, patron, or call number">
            </form>
            <a class="rounded-lg p-2 text-[#475569] transition-colors hover:bg-[#F1F5F9]" href="{{ route('staff.section', 'reports') }}" aria-label="Reports">
                <span class="material-symbols-outlined">monitoring</span>
            </a>
            <a class="rounded-lg p-2 text-[#475569] transition-colors hover:bg-[#F1F5F9]" href="{{ route('staff.section', 'support') }}" aria-label="Help">
                <span class="material-symbols-outlined">help</span>
            </a>
            <div class="h-6 w-px bg-[#E2E8F0]"></div>
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-[#065F2A] text-sm font-bold text-white">{{ $currentUser['initials'] }}</div>
                <div class="hidden text-left lg:block">
                    <p class="text-xs font-semibold leading-tight">{{ $currentUser['name'] }}</p>
                    <p class="text-xs text-[#64748B]">{{ $currentUser['subtitle'] }}</p>
                </div>
            </div>
        </div>
    </header>

    <div class="flex min-h-[calc(100vh-4rem)]">
        @include('partials.staff-sidebar', ['activePage' => 'dashboard'])

        <main class="md:ml-64 min-w-0 flex-1 space-y-6 overflow-y-auto p-4 sm:p-6 lg:p-8">
            @if (session('status'))
                <div class="rounded-lg border border-[#BBF7D0] bg-[#F0FDF4] px-4 py-3 text-sm font-semibold text-[#166534]">
                    {{ session('status') }}
                </div>
            @endif

            <section class="rounded-2xl bg-gradient-to-r from-[#065F2A] via-[#0F5132] to-[#12372A] p-6 text-white shadow-sm lg:p-8">
                <div class="max-w-4xl space-y-3">
                    <div class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/15 px-3 py-1 text-[10px] font-bold uppercase tracking-wider">
                        <span class="material-symbols-outlined text-sm text-[#FDE047]">verified</span>
                        {{ $isFaculty ? 'Faculty Services Active' : 'Circulation Desk Active' }}
                    </div>
                    <h1 class="font-display text-3xl font-bold md:text-4xl">
                        {{ $isFaculty ? 'Faculty Library Access & Course Reserve Dashboard' : 'Librarian Operations Dashboard' }}
                    </h1>
                    <p class="max-w-3xl text-sm leading-6 text-slate-100">
                        {{ $isFaculty
                            ? 'Manage course reserve requests, review classroom reading lists, and check library support tickets for your department.'
                            : 'Monitor live borrowing activity, patron verification, hold shelf queues, catalog availability, and clearance tasks from one workspace.' }}
                    </p>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ([
                    ['label' => 'Active Loans', 'value' => $stats['activeLoans'], 'icon' => 'sync_alt', 'tone' => 'bg-[#DCFCE7] text-[#166534]'],
                    ['label' => 'Available Copies', 'value' => $stats['availableCopies'], 'icon' => 'inventory_2', 'tone' => 'bg-[#EFF6FF] text-[#1D4ED8]'],
                    ['label' => 'Pending Holds', 'value' => $stats['pendingReservations'], 'icon' => 'book_online', 'tone' => 'bg-[#FEF3C7] text-[#92400E]'],
                    ['label' => 'Open Fines', 'value' => 'PHP '.number_format($stats['outstandingFines'], 2), 'icon' => 'payments', 'tone' => 'bg-[#FEE2E2] text-[#B91C1C]'],
                ] as $card)
                    <article class="rounded-xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-[#64748B]">{{ $card['label'] }}</span>
                            <span class="{{ $card['tone'] }} flex h-10 w-10 items-center justify-center rounded-lg">
                                <span class="material-symbols-outlined">{{ $card['icon'] }}</span>
                            </span>
                        </div>
                        <p class="mt-4 font-display text-2xl font-bold">{{ $card['value'] }}</p>
                        <p class="mt-1 text-xs text-[#64748B]">Updated from current library records</p>
                    </article>
                @endforeach
            </section>

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
                <section class="space-y-4 rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm xl:col-span-8">
                    <div class="flex flex-col justify-between gap-3 border-b border-[#E2E8F0] pb-4 sm:flex-row sm:items-center">
                        <div>
                            <h2 class="font-display text-xl font-bold">{{ $isFaculty ? 'Course Reserve Requests' : 'Live Circulation Queue' }}</h2>
                            <p class="text-xs text-[#64748B]">{{ $isFaculty ? 'Requests connected to classes, reading lists, and faculty support.' : 'Desk transactions that need review, scanning, or release.' }}</p>
                        </div>
                        <a class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#065F2A] px-4 py-2 text-xs font-bold text-white" href="{{ route('staff.section', $isFaculty ? 'catalog' : 'circulation') }}">
                            <span class="material-symbols-outlined text-base">{{ $isFaculty ? 'playlist_add' : 'open_in_new' }}</span>
                            {{ $isFaculty ? 'Review Catalog' : 'Open Desk' }}
                        </a>
                    </div>

                    <div class="space-y-3">
                        @foreach ($isFaculty ? $facultyRequests : $circulationQueue as $row)
                            <article class="rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] p-4">
                                <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
                                    <div>
                                        <span class="inline-flex rounded-full bg-[#EEF2FF] px-2.5 py-1 text-[10px] font-bold text-[#334155]">
                                            {{ $isFaculty ? $row['course'] : $row['ticket'].' - '.$row['type'] }}
                                        </span>
                                        <h3 class="mt-2 font-display text-base font-bold">{{ $isFaculty ? $row['request'] : $row['item'] }}</h3>
                                        <p class="mt-1 text-xs text-[#64748B]">{{ $isFaculty ? $row['owner'] : $row['patron'] }}</p>
                                    </div>
                                    <span class="rounded-lg border border-[#BBF7D0] bg-[#F0FDF4] px-3 py-1 text-[10px] font-bold text-[#166534]">{{ $row['status'] }}</span>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>

                <aside class="space-y-6 xl:col-span-4">
                    <section class="rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between border-b border-[#E2E8F0] pb-3">
                            <h2 class="font-display text-base font-bold">Quick Actions</h2>
                            <span class="material-symbols-outlined text-[#065F2A]">bolt</span>
                        </div>
                        <div class="mt-4 space-y-2">
                            @foreach ([
                                ['label' => 'Open Public Catalog', 'icon' => 'menu_book', 'href' => route('books.catalog')],
                                ['label' => 'Manage Reservations', 'icon' => 'book_online', 'href' => route('staff.section', 'reservations')],
                                ['label' => 'View Patron Records', 'icon' => 'groups', 'href' => route('staff.section', 'patrons')],
                                ['label' => 'Generate Reports', 'icon' => 'monitoring', 'href' => route('staff.section', 'reports')],
                            ] as $action)
                                <a class="flex items-center justify-between rounded-xl border border-[#E2E8F0] p-3 text-sm font-semibold text-[#0F172A] transition-colors hover:bg-[#F8FAFC]" href="{{ $action['href'] }}">
                                    <span class="flex items-center gap-3">
                                        <span class="material-symbols-outlined text-[#065F2A]">{{ $action['icon'] }}</span>
                                        {{ $action['label'] }}
                                    </span>
                                    <span class="material-symbols-outlined text-[#94A3B8]">chevron_right</span>
                                </a>
                            @endforeach
                        </div>
                    </section>

                    <section class="rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between border-b border-[#E2E8F0] pb-3">
                            <h2 class="font-display text-base font-bold">Patron Snapshot</h2>
                            <span class="rounded-full bg-[#DCFCE7] px-2.5 py-1 text-[10px] font-bold text-[#166534]">{{ count($patronRecords) }} Active</span>
                        </div>
                        <div class="mt-4 space-y-3">
                            @foreach ($patronRecords as $patron)
                                <div class="rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] p-3">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <p class="text-sm font-bold">{{ $patron['name'] }}</p>
                                            <p class="text-xs text-[#64748B]">{{ $patron['id'] }} - {{ $patron['program'] }}</p>
                                        </div>
                                        <span class="rounded-full bg-white px-2 py-1 text-[10px] font-bold text-[#334155]">{{ $patron['loans'] }} loans</span>
                                    </div>
                                    <p class="mt-2 text-[11px] font-semibold text-[#065F2A]">{{ $patron['status'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </section>
                </aside>
            </div>
        </main>
    </div>
</body>
</html>
