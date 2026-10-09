<!DOCTYPE html>
<html class="h-full bg-[#f8fafc]" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>CLCST Library Management System - Faculty & Academic Staff Portal</title>
    @include('partials.favicon')

    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#135d2e',
                        'primary-container': '#135d2e',
                        'on-primary': '#ffffff',
                        'on-surface': '#111827',
                        'on-surface-variant': '#4b5563',
                        outline: '#9ca3af',
                        'outline-variant': '#e5e7eb'
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        headline: ['Plus Jakarta Sans', 'sans-serif']
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

        .custom-scrollbar::-webkit-scrollbar {
            height: 6px;
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="flex min-h-screen flex-col bg-[#f8fafc] font-sans text-on-surface antialiased selection:bg-[#135d2e] selection:text-white">
    @php
        $isFaculty = $portalRole === 'faculty';
        $currentUser = $isFaculty
            ? ['initials' => 'RM', 'name' => 'Engr. Roberto M. Mendoza, PECE', 'short' => 'Engr. Roberto Mendoza', 'id' => 'FAC-2018-0412', 'role' => 'Faculty Member', 'department' => 'CICT Dept Chair', 'subtitle' => 'Associate Professor & Chair, CICT & Computer Engineering']
            : ['initials' => 'MS', 'name' => 'Maria Santos', 'short' => 'Maria Santos', 'id' => 'LIB-STAFF-042', 'role' => 'Library Staff', 'department' => 'Head Librarian', 'subtitle' => 'Circulation & Interlibrary Loan Desk'];
        $borrowedCount = $isFaculty ? max($stats['activeLoans'], 4) : $stats['activeLoans'];
        $reservationCount = $stats['pendingReservations'];
        $renewalCount = 1;
        $overdueCount = 0;
        $firstBook = $catalogBooks[0] ?? null;
        $secondBook = $catalogBooks[1] ?? null;
    @endphp

    <header class="sticky top-0 z-40 flex h-16 w-full items-center justify-between border-b border-gray-200 bg-white px-4 shadow-sm md:px-6">
        <a class="flex items-center gap-2.5" href="{{ $isFaculty ? route('faculty.dashboard') : route('staff.dashboard') }}">
            @include('partials.celtech-logo', ['class' => 'flex h-10 w-10 items-center justify-center rounded-full bg-[#135d2e] font-headline text-sm font-extrabold text-white ring-2 ring-amber-400 object-contain bg-white p-0.5'])
            <div>
                <div class="flex items-center gap-2">
                    <span class="font-headline text-[15px] font-extrabold leading-none tracking-tight text-[#135d2e]">CLCST LIBRARY SYSTEM</span>
                    <span class="hidden rounded border border-amber-300 bg-amber-100 px-1.5 py-0.5 text-[10px] font-bold text-amber-800 sm:inline-block">{{ $isFaculty ? 'FACULTY PORTAL' : 'STAFF PORTAL' }}</span>
                </div>
                <span class="mt-0.5 block text-[11px] font-medium leading-tight text-gray-500">Central Luzon College of Science and Technology</span>
            </div>
        </a>

        <form class="mx-4 hidden max-w-xl flex-1 lg:mx-8 md:block" method="GET" action="{{ route('books.catalog') }}">
            <div class="relative flex w-full items-center">
                <span class="pointer-events-none absolute left-3.5 flex items-center text-gray-400">
                    <span class="material-symbols-outlined text-[20px]">search</span>
                </span>
                <input class="w-full rounded-lg border border-gray-200 bg-slate-50 py-2 pl-10 pr-28 text-sm text-gray-800 placeholder:text-gray-400 transition-all focus:border-[#135d2e] focus:outline-none focus:ring-1 focus:ring-[#135d2e]" name="q" placeholder="Search book catalog by title, author, category, ISBN, or call no..." type="text">
                <div class="absolute right-2 flex items-center gap-1">
                    <span class="rounded bg-gray-200 px-1.5 py-0.5 text-[10px] font-semibold text-gray-600">OPAC</span>
                    <button class="p-1 text-gray-400 transition-colors hover:text-[#135d2e]" title="Scan Barcode / ISBN" type="submit">
                        <span class="material-symbols-outlined text-[18px]">barcode_scanner</span>
                    </button>
                </div>
            </div>
        </form>

        <div class="flex items-center gap-2.5">
            <a class="hidden items-center gap-1.5 rounded-lg bg-[#135d2e] px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition-all hover:bg-[#0c3b1d] active:scale-95 lg:inline-flex" href="{{ route('staff.section', 'catalog') }}">
                <span class="material-symbols-outlined text-[17px]">add_circle</span>
                <span>{{ $isFaculty ? 'Request Academic Resource' : 'Add Catalog Task' }}</span>
            </a>
            <a aria-label="Notifications" class="relative rounded-lg p-2 text-gray-600 transition-colors hover:bg-gray-100 hover:text-gray-900" href="{{ route('staff.section', 'support') }}">
                <span class="material-symbols-outlined text-[22px]">notifications</span>
                <span class="absolute right-1.5 top-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-red-600 text-[10px] font-bold text-white ring-2 ring-white">3</span>
            </a>
            <a aria-label="Help" class="rounded-lg p-2 text-gray-600 transition-colors hover:bg-gray-100" href="{{ route('staff.section', 'support') }}">
                <span class="material-symbols-outlined text-[22px]">help_outline</span>
            </a>
            <div class="flex items-center gap-2.5 border-l border-gray-200 pl-2">
                <div class="hidden text-right sm:block">
                    <div class="text-xs font-bold leading-tight text-gray-900">{{ $currentUser['name'] }}</div>
                    <div class="text-[11px] font-medium text-gray-500">{{ $currentUser['department'] }} <span class="font-mono text-[10px] font-semibold text-[#135d2e]">{{ $currentUser['id'] }}</span></div>
                </div>
                <div class="relative">
                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-[#135d2e] font-headline text-xs font-bold text-white shadow-sm ring-2 ring-amber-400">{{ $currentUser['initials'] }}</div>
                    <span class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full bg-emerald-500 ring-2 ring-white"></span>
                </div>
            </div>
        </div>
    </header>

    <div class="flex flex-1 overflow-hidden">
        <aside class="custom-scrollbar z-30 hidden w-64 flex-shrink-0 flex-col justify-between overflow-y-auto border-r border-gray-200 bg-white md:flex">
            <div class="flex flex-col gap-3 p-4">
                <div class="flex items-center gap-3 border-b border-gray-200 pb-3">
                    @include('partials.celtech-logo', ['class' => 'flex h-9 w-9 items-center justify-center rounded-full bg-[#135d2e] text-xs font-extrabold text-white object-contain bg-white p-0.5'])
                    <div class="overflow-hidden">
                        <h2 class="truncate text-xs font-extrabold uppercase tracking-wide text-[#135d2e]">CLCST LMS</h2>
                        <p class="truncate text-[11px] text-gray-500">Library Management System</p>
                    </div>
                </div>

                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-2.5">
                    <div class="mb-1.5 flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-900">Patron Classification</span>
                        <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-600"></span>
                    </div>
                    <div class="flex items-center gap-1.5 text-xs font-bold text-emerald-900">
                        <span class="material-symbols-outlined text-[16px] text-[#135d2e]">{{ $isFaculty ? 'school' : 'admin_panel_settings' }}</span>
                        <span>{{ $isFaculty ? 'Faculty Account - Teaching' : 'Library Staff Account' }}</span>
                    </div>
                    <div class="mt-2 flex items-center justify-between border-t border-emerald-200/60 pt-2">
                        <span class="text-[10px] font-medium text-emerald-800">Switch view mode:</span>
                        <button class="rounded border border-emerald-300 bg-white px-2 py-0.5 text-[10px] font-semibold text-emerald-800 transition-colors hover:bg-emerald-100" id="toggleStaffViewBtn" type="button">{{ $isFaculty ? 'Non-Teaching Staff' : 'Teaching Faculty' }}</button>
                    </div>
                </div>

                <nav class="mt-1 flex flex-col gap-0.5 text-xs">
                    <a class="flex items-center gap-3 rounded-lg bg-[#135d2e] px-3 py-2 font-semibold text-white shadow-sm" href="{{ $isFaculty ? route('faculty.dashboard') : route('staff.dashboard') }}">
                        <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">dashboard</span>
                        <span>Dashboard</span>
                    </a>
                    <a class="flex items-center gap-3 rounded-lg px-3 py-2 font-medium text-gray-700 transition-colors hover:bg-slate-100 hover:text-gray-900" href="{{ route('books.catalog') }}">
                        <span class="material-symbols-outlined text-[18px]">search</span>
                        <span>Search Books</span>
                    </a>
                    <a class="flex items-center justify-between rounded-lg px-3 py-2 font-medium text-gray-700 transition-colors hover:bg-slate-100 hover:text-gray-900" href="{{ route('staff.section', 'circulation') }}">
                        <span class="flex items-center gap-3"><span class="material-symbols-outlined text-[18px]">menu_book</span><span>{{ $isFaculty ? 'My Borrowed Books' : 'Circulation Desk' }}</span></span>
                        <span class="rounded-full bg-emerald-100 px-1.5 py-0.5 text-[10px] font-bold text-[#135d2e]">{{ $borrowedCount }}</span>
                    </a>
                    <a class="flex items-center justify-between rounded-lg px-3 py-2 font-medium text-gray-700 transition-colors hover:bg-slate-100 hover:text-gray-900" href="{{ route('staff.section', 'reservations') }}">
                        <span class="flex items-center gap-3"><span class="material-symbols-outlined text-[18px]">bookmark</span><span>My Reservations</span></span>
                        <span class="rounded-full bg-amber-100 px-1.5 py-0.5 text-[10px] font-bold text-amber-800">{{ $reservationCount }}</span>
                    </a>
                    <a class="flex items-center justify-between rounded-lg px-3 py-2 font-medium text-gray-700 transition-colors hover:bg-slate-100 hover:text-gray-900" href="{{ route('staff.section', 'reports') }}">
                        <span class="flex items-center gap-3"><span class="material-symbols-outlined text-[18px]">autorenew</span><span>Renewal Requests</span></span>
                        <span class="rounded-full bg-blue-100 px-1.5 py-0.5 text-[10px] font-bold text-blue-700">{{ $renewalCount }}</span>
                    </a>
                    <a class="flex items-center gap-3 rounded-lg px-3 py-2 font-medium text-gray-700 transition-colors hover:bg-slate-100 hover:text-gray-900" href="{{ route('staff.section', 'history') }}"><span class="material-symbols-outlined text-[18px]">history</span><span>Borrowing History</span></a>
                    <a class="flex items-center gap-3 rounded-lg px-3 py-2 font-medium text-gray-700 transition-colors hover:bg-slate-100 hover:text-gray-900" href="{{ route('staff.section', 'catalog') }}"><span class="material-symbols-outlined text-[18px]">thumb_up</span><span>Recommend a Book</span></a>

                    <div class="my-1 border-t border-gray-100 pt-1" id="faculty-academic-module">
                        <a class="flex items-center justify-between rounded-lg border border-emerald-200/80 bg-emerald-50/70 px-3 py-2 font-bold text-[#135d2e] transition-colors" href="{{ route('staff.section', 'academic') }}">
                            <span class="flex items-center gap-2.5"><span class="material-symbols-outlined text-[18px]">school</span><span class="truncate">Faculty Academic Requests</span></span>
                            <span class="material-symbols-outlined text-[16px]">expand_more</span>
                        </a>
                        <div class="ml-4 space-y-1 border-l-2 border-emerald-200 py-1 pl-3">
                            <a class="flex items-center gap-2 py-0.5 text-[11px] font-medium text-gray-600 hover:text-[#135d2e]" href="{{ route('staff.section', 'course-reserves') }}"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span><span>Course Reserves</span></a>
                            <a class="flex items-center gap-2 py-0.5 text-[11px] font-medium text-gray-600 hover:text-[#135d2e]" href="{{ route('staff.section', 'reading-lists') }}"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span><span>Course Reading Lists</span></a>
                            <a class="flex items-center gap-2 py-0.5 text-[11px] font-medium text-gray-600 hover:text-[#135d2e]" href="{{ route('staff.section', 'academic') }}#instruction-session"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span><span>Instruction Sessions</span></a>
                        </div>
                    </div>

                    <a class="flex items-center justify-between rounded-lg px-3 py-2 font-medium text-gray-700 transition-colors hover:bg-slate-100 hover:text-gray-900" href="{{ route('staff.section', 'support') }}">
                        <span class="flex items-center gap-3"><span class="material-symbols-outlined text-[18px]">notifications</span><span>Notifications</span></span>
                        <span class="rounded-full bg-red-100 px-1.5 py-0.5 text-[10px] font-bold text-red-700">3</span>
                    </a>
                    <a class="flex items-center gap-3 rounded-lg px-3 py-2 font-medium text-gray-700 transition-colors hover:bg-slate-100 hover:text-gray-900" href="{{ route('staff.section', 'patrons') }}"><span class="material-symbols-outlined text-[18px]">person</span><span>My Profile</span></a>
                    <button class="flex items-center gap-3 rounded-lg px-3 py-2 text-left font-medium text-gray-700 transition-colors hover:bg-slate-100 hover:text-gray-900" onclick="document.getElementById('feedbackModal').classList.remove('hidden')" type="button"><span class="material-symbols-outlined text-[18px]">rate_review</span><span>Library Feedback</span></button>
                </nav>
            </div>

            <div class="space-y-1 border-t border-gray-200 p-4">
                <a class="flex items-center gap-3 rounded-lg px-3 py-2 text-xs font-medium text-gray-600 transition-colors hover:bg-slate-100" href="{{ route('staff.section', 'support') }}"><span class="material-symbols-outlined text-[18px]">policy</span><span>Library Policies</span></a>
                <a class="flex items-center gap-3 rounded-lg px-3 py-2 text-xs font-medium text-red-600 transition-colors hover:bg-red-50" href="{{ route('login') }}"><span class="material-symbols-outlined text-[18px]">logout</span><span>Logout</span></a>
                <div class="pt-2 text-center"><span class="block font-mono text-[10px] text-gray-400">CLCST ILS - HCI102 v2.4</span></div>
            </div>
        </aside>

        <main class="custom-scrollbar flex-1 space-y-6 overflow-y-auto p-4 md:p-6 lg:p-8">
            @if (session('status'))
                <div class="rounded-r-xl border-l-4 border-emerald-500 bg-emerald-50 p-3.5 text-sm font-semibold text-emerald-900 shadow-sm">{{ session('status') }}</div>
            @endif

            <section class="space-y-3">
                <div class="flex items-start justify-between gap-3 rounded-r-xl border-l-4 border-amber-500 bg-amber-50 p-3.5 shadow-sm">
                    <div class="flex items-start gap-2.5">
                        <span class="material-symbols-outlined mt-0.5 shrink-0 text-[20px] text-amber-600">campaign</span>
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wide text-amber-900">Campus Library Announcement</span>
                            <p class="mt-0.5 text-xs font-medium text-amber-800">Faculty Notice: Course Reserve requests for Midterm Exam Term must be submitted by <strong class="font-bold underline decoration-amber-400">Oct 25</strong>. Thesis defense draft deposit is now open at the Cataloging Desk.</p>
                        </div>
                    </div>
                    <button class="shrink-0 text-xs font-semibold text-amber-700 hover:text-amber-900" onclick="this.closest('div').remove()" type="button">Dismiss</button>
                </div>

                <div class="relative overflow-hidden rounded-xl border border-gray-200 bg-white p-5 shadow-sm md:p-6">
                    <div class="relative z-10 flex flex-col items-start justify-between gap-4 lg:flex-row lg:items-center">
                        <div class="space-y-1.5">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full border border-emerald-300 bg-emerald-100 px-2.5 py-0.5 text-[11px] font-bold text-[#135d2e]">{{ strtoupper($currentUser['role']) }} ID: {{ $currentUser['id'] }}</span>
                                <span class="rounded-full bg-blue-100 px-2.5 py-0.5 text-[11px] font-semibold text-blue-800">1st Semester AY 2024-2025</span>
                                <span class="rounded-full border border-amber-300 bg-amber-100 px-2.5 py-0.5 text-[11px] font-semibold text-amber-800">{{ $isFaculty ? 'Active Teaching Load - 60-Day Loan Privilege' : 'Authorized Desk Access - Circulation Privilege' }}</span>
                            </div>
                            <h1 class="font-headline text-2xl font-extrabold tracking-tight text-gray-900 md:text-3xl">Faculty & Staff Dashboard</h1>
                            <p class="text-sm font-medium text-gray-600">Welcome back, <strong class="font-semibold text-gray-900">{{ $currentUser['short'] }}</strong> ({{ $currentUser['role'] }} - {{ $currentUser['subtitle'] }}).</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <a class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-slate-100 px-3.5 py-2 text-xs font-bold text-[#135d2e] transition-colors hover:bg-slate-200" href="{{ route('staff.section', 'reservations') }}"><span class="material-symbols-outlined text-[18px]">bookmark_add</span><span>Reserve Syllabus Book</span></a>
                            <a class="inline-flex items-center gap-1.5 rounded-lg bg-[#135d2e] px-3.5 py-2 text-xs font-bold text-white shadow-sm transition-all hover:bg-[#0c3b1d] active:scale-95" href="{{ route('staff.section', 'catalog') }}"><span class="material-symbols-outlined text-[18px]">add_box</span><span>Recommend New Acquisition</span></a>
                        </div>
                    </div>
                    <div class="pointer-events-none absolute -bottom-14 -right-10 flex h-60 w-60 items-center justify-center rounded-full border-8 border-[#135d2e] text-6xl font-extrabold text-[#135d2e] opacity-5">CL</div>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['label' => $isFaculty ? 'Borrowed Books' : 'Active Loans', 'value' => $borrowedCount, 'icon' => 'menu_book', 'meta' => $borrowedCount.' / 15 Volumes', 'tone' => 'emerald'],
                    ['label' => 'Pending Reservations', 'value' => $reservationCount, 'icon' => 'bookmark', 'meta' => 'For pickup / approval', 'tone' => 'amber'],
                    ['label' => 'Renewal Requests', 'value' => $renewalCount, 'icon' => 'autorenew', 'meta' => '1 waiting librarian review', 'tone' => 'blue'],
                    ['label' => 'Overdue Items', 'value' => $overdueCount, 'icon' => 'verified', 'meta' => $stats['outstandingFines'] > 0 ? 'PHP '.number_format($stats['outstandingFines'], 2).' open' : 'Clear account', 'tone' => 'slate'],
                ] as $card)
                    <article class="flex flex-col justify-between rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition-all hover:border-emerald-300">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-gray-500">{{ $card['label'] }}</p>
                                <h3 class="mt-1 font-headline text-3xl font-extrabold {{ $card['tone'] === 'amber' ? 'text-amber-700' : ($card['tone'] === 'blue' ? 'text-blue-700' : 'text-[#135d2e]') }}">{{ $card['value'] }}</h3>
                            </div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg border {{ $card['tone'] === 'amber' ? 'border-amber-100 bg-amber-50 text-amber-700' : ($card['tone'] === 'blue' ? 'border-blue-100 bg-blue-50 text-blue-700' : 'border-emerald-100 bg-emerald-50 text-[#135d2e]') }}"><span class="material-symbols-outlined text-[22px]">{{ $card['icon'] }}</span></div>
                        </div>
                        <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-2.5 text-xs text-gray-600"><span>Current status</span><span class="font-semibold text-gray-900">{{ $card['meta'] }}</span></div>
                    </article>
                @endforeach
            </section>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                <div class="space-y-6 lg:col-span-8">
                    <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm" id="borrowed">
                        <div class="mb-4 flex flex-col justify-between gap-3 border-b border-gray-200 pb-3 sm:flex-row sm:items-center">
                            <div>
                                <h2 class="font-headline text-lg font-bold text-gray-900">{{ $isFaculty ? 'My Borrowed Books & Renewal Queue' : 'Circulation Queue & Loan Desk' }}</h2>
                                <p class="text-xs text-gray-500">{{ $isFaculty ? 'Faculty loan privileges, due dates, and active renewal requests.' : 'Current items requiring counter action and patron follow-up.' }}</p>
                            </div>
                            <a class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-[#135d2e] px-3 py-2 text-xs font-bold text-white hover:bg-[#0c3b1d]" href="{{ route('staff.section', 'circulation') }}"><span class="material-symbols-outlined text-[16px]">open_in_new</span>Open Full Queue</a>
                        </div>

                        <div class="space-y-3">
                            @foreach ($activeLoans as $loan)
                                @php $book = collect($catalogBooks)->firstWhere('id', $loan['book_id']); @endphp
                                <article class="flex flex-col gap-4 rounded-lg border border-gray-200 bg-slate-50/70 p-4 transition-colors hover:bg-slate-50 sm:flex-row">
                                    @if ($book)
                                        <img class="h-28 w-20 rounded-lg border border-gray-200 object-cover shadow-sm" src="{{ $book['cover_url'] }}" alt="{{ $book['title'] }} cover">
                                    @else
                                        <div class="flex h-28 w-20 items-center justify-center rounded-lg bg-[#135d2e] text-white"><span class="material-symbols-outlined">menu_book</span></div>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="rounded bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-[#135d2e]">{{ $loan['barcode'] }}</span>
                                            <span class="rounded bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-800">Due {{ $loan['due'] }}</span>
                                        </div>
                                        <h3 class="mt-2 font-headline text-base font-bold text-gray-900">{{ $loan['title'] }}</h3>
                                        <p class="mt-1 text-xs text-gray-600">Call No: <strong>{{ $loan['call'] }}</strong></p>
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            <form method="POST" action="{{ route('loans.renew', $loan['id']) }}">@csrf<button class="inline-flex items-center gap-1.5 rounded-lg bg-[#135d2e] px-3 py-1.5 text-xs font-bold text-white hover:bg-[#0c3b1d]" type="submit"><span class="material-symbols-outlined text-[15px]">autorenew</span>Renew Loan</button></form>
                                            <a class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-bold text-gray-700 hover:bg-slate-100" href="{{ route('books.catalog', ['selected' => $loan['book_id']]) }}"><span class="material-symbols-outlined text-[15px]">visibility</span>View Catalog Record</a>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>

                    <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm" id="faculty-academic">
                        <div class="mb-4 border-b border-gray-200 pb-3">
                            <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[22px] text-[#135d2e]">school</span><h2 class="font-headline text-lg font-bold text-gray-900">Faculty Academic Services</h2></div>
                            <p class="mt-1 text-xs text-gray-500">Course reserves, syllabus reading lists, acquisition requests, and instruction sessions.</p>
                        </div>

                        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                            <article class="rounded-lg border border-gray-200 bg-slate-50/70 p-4" id="course-reserves">
                                <div class="mb-2 flex items-center justify-between"><div class="flex items-center gap-1.5"><span class="material-symbols-outlined text-[18px] text-[#135d2e]">library_add</span><h3 class="text-xs font-bold uppercase tracking-wide text-gray-900">1. Course Reserves</h3></div><span class="text-[10px] font-medium text-gray-400">Reserve Shelf</span></div>
                                <div class="rounded border border-gray-200 bg-white p-3 text-xs"><div class="flex items-center justify-between gap-3"><span class="font-bold text-gray-900">{{ $firstBook['title'] ?? 'Introduction to Python Programming' }}</span><span class="rounded bg-emerald-100 px-1.5 py-0.5 text-[10px] font-bold text-emerald-800">Active</span></div><p class="mt-2 text-[11px] text-gray-600">Linked to CS 211 reading list. Copies are visible to students in the catalog and reserve desk.</p></div>
                                <div class="mt-3 flex justify-end border-t border-gray-200 pt-2.5"><a class="text-xs font-semibold text-[#135d2e] hover:underline" href="{{ route('staff.section', 'course-reserves') }}">Manage Reserve Shelf</a></div>
                            </article>

                            <article class="rounded-lg border border-gray-200 bg-slate-50/70 p-4" id="reading-lists">
                                <div class="mb-2 flex items-center justify-between"><div class="flex items-center gap-1.5"><span class="material-symbols-outlined text-[18px] text-[#135d2e]">format_list_bulleted</span><h3 class="text-xs font-bold uppercase tracking-wide text-gray-900">2. Course Reading Lists</h3></div><span class="text-[10px] font-medium text-gray-400">OPAC Discoverable</span></div>
                                <div class="rounded border border-gray-200 bg-white p-3 text-xs"><div class="flex items-center justify-between gap-3"><span class="font-bold text-gray-900">AY 2024-2025 1st Sem: BSCS Core Syllabus</span><span class="rounded bg-blue-100 px-1.5 py-0.5 text-[10px] font-bold text-blue-800">Published</span></div><p class="mt-2 text-[11px] text-gray-600">12 titles linked for students to discover directly in the CLCST web catalog.</p></div>
                                <div class="mt-3 flex items-center justify-between border-t border-gray-200 pt-2.5"><span class="text-[11px] text-gray-500">Student reads this term: <strong>214</strong></span><a class="text-xs font-semibold text-[#135d2e] hover:underline" href="{{ route('staff.section', 'reading-lists') }}">Manage Lists</a></div>
                            </article>

                            <article class="rounded-lg border border-gray-200 bg-slate-50/70 p-4">
                                <div class="mb-2 flex items-center justify-between"><div class="flex items-center gap-1.5"><span class="material-symbols-outlined text-[18px] text-[#135d2e]">shopping_cart</span><h3 class="text-xs font-bold uppercase tracking-wide text-gray-900">3. Book Recommendations</h3></div><span class="rounded bg-emerald-100 px-1.5 text-[10px] font-bold text-emerald-800">CICT Fund</span></div>
                                <div class="rounded border border-gray-200 bg-white p-3 text-xs"><div class="flex items-center justify-between gap-3"><span class="font-bold text-gray-900">{{ $secondBook['title'] ?? 'Clean Code: A Handbook of Agile Software Craftsmanship' }}</span><span class="rounded bg-purple-100 px-1.5 py-0.5 text-[10px] font-bold text-purple-800">In Review</span></div><p class="mt-2 text-[11px] text-gray-600">Endorsed for purchase and review by the Library Committee.</p></div>
                                <div class="mt-3 flex justify-end border-t border-gray-200 pt-2.5"><a class="rounded border border-emerald-300 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-[#135d2e] transition-colors hover:bg-emerald-100" href="{{ route('staff.section', 'catalog') }}">+ Recommend New Title</a></div>
                            </article>

                            <article class="rounded-lg border border-gray-200 bg-slate-50/70 p-4" id="instruction-session">
                                <div class="mb-2 flex items-center justify-between"><div class="flex items-center gap-1.5"><span class="material-symbols-outlined text-[18px] text-[#135d2e]">co_present</span><h3 class="text-xs font-bold uppercase tracking-wide text-gray-900">4. Instruction Session Request</h3></div><span class="text-[10px] font-medium text-gray-400">Information Literacy</span></div>
                                <div class="rounded border border-gray-200 bg-white p-3 text-xs"><div class="flex items-center justify-between gap-3"><span class="font-bold text-gray-900">CS301 Capstone Literature Review Session</span><span class="rounded bg-emerald-100 px-1.5 py-0.5 text-[10px] font-bold text-emerald-800">Scheduled</span></div><p class="mt-2 text-[11px] text-gray-600">Requested for Oct 28, 2024, 10:00 AM to 11:30 AM at Multimedia AVR Room 302.</p></div>
                                <div class="mt-3 flex justify-end border-t border-gray-200 pt-2.5"><button class="text-xs font-semibold text-[#135d2e] hover:underline" onclick="document.getElementById('feedbackModal').classList.remove('hidden')" type="button">Request Class Orientation</button></div>
                            </article>
                        </div>
                    </section>
                </div>

                <aside class="space-y-6 lg:col-span-4">
                    <section class="relative overflow-hidden rounded-xl border border-[#135d2e]/30 bg-gradient-to-br from-[#135d2e] via-[#0c3b1d] to-[#05210e] p-5 text-white shadow-md">
                        <div class="pointer-events-none absolute right-[-15px] top-[-15px] text-8xl font-extrabold text-white/10">CL</div>
                        <div class="relative z-10 flex items-center justify-between border-b border-white/20 pb-3"><div class="flex items-center gap-2">@include('partials.celtech-logo', ['class' => 'flex h-8 w-8 items-center justify-center rounded-full bg-white text-xs font-extrabold text-[#135d2e] object-contain bg-white p-0.5'])<div><div class="text-[10px] font-black uppercase tracking-widest text-amber-300">CLCST Patron Card</div><div class="text-xs font-semibold text-white/90">{{ $isFaculty ? 'Faculty Library Access' : 'Staff Library Access' }}</div></div></div><span class="rounded bg-amber-400 px-2 py-0.5 text-[10px] font-black text-amber-950">RFID ACTIVE</span></div>
                        <div class="relative z-10 mt-3 space-y-0.5"><div class="text-[10px] font-semibold uppercase tracking-wider text-emerald-200">{{ $currentUser['role'] }} / {{ $currentUser['department'] }}</div><div class="font-headline text-base font-bold tracking-tight text-white">{{ $currentUser['name'] }}</div><div class="text-xs text-white/80">{{ $currentUser['subtitle'] }}</div></div>
                        <div class="relative z-10 mt-4 rounded-lg bg-white p-3 text-center text-gray-900 shadow-inner"><div class="mb-1 font-mono text-[10px] font-bold tracking-wider text-gray-500">TURNSTILE CIRCULATION BARCODE</div><div class="flex h-11 w-full items-center justify-center gap-0.5 bg-white px-2">@foreach ([1,2,1,2,3,1,2,1,3,2,1,3,1,2,1,4,1,2,2,1,3,1,2,1,3,2] as $bar)<span class="h-9 bg-black" style="width: {{ $bar }}px"></span>@endforeach</div><div class="mt-1 font-mono text-xs font-extrabold tracking-widest text-gray-900">{{ $currentUser['id'] }}</div></div>
                        <div class="relative z-10 mt-3 flex items-center justify-between text-[11px] text-white/80"><span>Valid: AY 2024-2025</span><span class="flex items-center gap-1 font-semibold text-emerald-300"><span class="material-symbols-outlined text-[14px]">verified</span>Active Account</span></div>
                    </section>

                    <section class="space-y-3.5 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between border-b border-gray-200 pb-2"><div class="flex items-center gap-2"><span class="material-symbols-outlined text-[20px] text-[#135d2e]">vpn_lock</span><h3 class="font-headline text-sm font-bold text-gray-900">Institutional E-Resources</h3></div><span class="rounded bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-[#135d2e]">SSO ACTIVE</span></div>
                        <p class="text-xs text-gray-500">Direct scholarly access through CLCST library services.</p>
                        <div class="space-y-2">
                            @foreach ([
                                ['abbr' => 'IEEE', 'name' => 'IEEE Xplore Digital Library', 'desc' => 'Engineering and computer science journals', 'href' => route('books.catalog', ['q' => 'engineering'])],
                                ['abbr' => 'ACM', 'name' => 'ACM Digital Library', 'desc' => 'Computing machinery and software architecture', 'href' => route('books.catalog', ['q' => 'software'])],
                                ['abbr' => 'eLib', 'name' => 'Philippine eLib Portal', 'desc' => 'DOST, NLP and CHED repository', 'href' => route('books.catalog', ['q' => 'reference'])],
                            ] as $resource)
                                <a class="group flex items-center justify-between rounded-lg border border-gray-200 p-2 transition-colors hover:border-emerald-400 hover:bg-slate-50" href="{{ $resource['href'] }}"><span class="flex items-center gap-2.5"><span class="flex h-7 w-8 items-center justify-center rounded bg-emerald-100 text-[10px] font-bold text-[#135d2e]">{{ $resource['abbr'] }}</span><span><span class="block text-xs font-bold text-gray-900 group-hover:text-[#135d2e]">{{ $resource['name'] }}</span><span class="block text-[10px] text-gray-500">{{ $resource['desc'] }}</span></span></span><span class="material-symbols-outlined text-[16px] text-gray-400 group-hover:text-[#135d2e]">open_in_new</span></a>
                            @endforeach
                        </div>
                    </section>

                    <section class="space-y-3 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between border-b border-gray-200 pb-2"><div class="flex items-center gap-2"><span class="material-symbols-outlined text-[20px] text-[#135d2e]">admin_panel_settings</span><h3 class="font-headline text-sm font-bold text-gray-900">Privileges & Policy</h3></div><span class="rounded border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-[#135d2e]">Verified</span></div>
                        <ul class="space-y-2 text-xs text-gray-700">
                            <li class="flex items-start gap-2"><span class="material-symbols-outlined mt-0.5 shrink-0 text-[16px] text-emerald-600">check_circle</span><span><strong>{{ $isFaculty ? '60-Day Loan Period:' : 'Desk Access:' }}</strong> {{ $isFaculty ? 'Applicable to instructional and research titles.' : 'Authorized for circulation, patron records, and reports.' }}</span></li>
                            <li class="flex items-start gap-2"><span class="material-symbols-outlined mt-0.5 shrink-0 text-[16px] text-emerald-600">check_circle</span><span><strong>Reserve Support:</strong> Course and hold shelf requests are routed to the library desk.</span></li>
                            <li class="flex items-start gap-2 rounded border border-amber-200 bg-amber-50 p-2 text-amber-900"><span class="material-symbols-outlined mt-0.5 shrink-0 text-[16px] text-amber-600">info</span><span><strong>Policy Rule:</strong> Physical counter scanning is required for official circulation checkout.</span></li>
                        </ul>
                        <button class="flex w-full items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-slate-100 px-3 py-2 text-xs font-semibold text-gray-800 transition-colors hover:bg-slate-200" onclick="document.getElementById('feedbackModal').classList.remove('hidden')" type="button"><span class="material-symbols-outlined text-[16px] text-[#135d2e]">help</span><span>Ask Librarian / Quick Feedback</span></button>
                    </section>
                </aside>
            </div>

            <footer class="mt-10 border-t border-gray-200 pb-4 pt-6 text-gray-500">
                <div class="flex flex-col items-center justify-between gap-4 text-xs md:flex-row"><div class="flex items-center gap-3">@include('partials.celtech-logo', ['class' => 'flex h-7 w-7 items-center justify-center rounded-full bg-[#135d2e] text-[10px] font-bold text-white object-contain bg-white p-0.5'])<div><span class="font-bold text-gray-900">Central Luzon College of Science and Technology (CLCST)</span><span class="text-gray-500"> - Learning Resource Center</span></div></div><div class="flex flex-wrap items-center justify-center gap-3 text-[11px] text-gray-500"><span class="font-medium text-gray-700">CHED CMO No. 25</span><span>-</span><span class="font-medium text-gray-700">ISO 9001:2015 QA</span><span>-</span><span class="rounded border border-emerald-200 bg-emerald-50 px-1.5 py-0.5 font-medium text-emerald-800">RA 10173 Data Privacy</span></div></div>
            </footer>
        </main>
    </div>

    <div class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4" id="feedbackModal">
        <div class="w-full max-w-md space-y-4 rounded-xl border border-gray-200 bg-white p-6 shadow-xl">
            <div class="flex items-center justify-between border-b border-gray-200 pb-2"><div class="flex items-center gap-2"><span class="material-symbols-outlined text-[#135d2e]">rate_review</span><h3 class="font-headline text-base font-bold text-gray-900">Library Feedback & Assistance</h3></div><button class="text-gray-400 hover:text-gray-600" onclick="document.getElementById('feedbackModal').classList.add('hidden')" type="button"><span class="material-symbols-outlined">close</span></button></div>
            <form class="space-y-3 text-xs" onsubmit="event.preventDefault(); alert('Feedback sent to Head Librarian Ms. Santos.'); document.getElementById('feedbackModal').classList.add('hidden');">
                <div><label class="mb-1 block font-semibold text-gray-700">Inquiry / Feedback Category</label><select class="w-full rounded border-gray-300 text-xs focus:border-[#135d2e] focus:ring-[#135d2e]"><option>Course Reserves Question</option><option>Remote Access Assistance</option><option>Acquisition Request Follow-Up</option><option>General Library Feedback</option></select></div>
                <div><label class="mb-1 block font-semibold text-gray-700">Message</label><textarea class="w-full rounded border-gray-300 text-xs focus:border-[#135d2e] focus:ring-[#135d2e]" placeholder="Describe your concern or inquiry for the head librarian..." rows="3"></textarea></div>
                <div class="flex items-center justify-end gap-2 pt-2"><button class="rounded px-3 py-1.5 text-xs text-gray-600 hover:bg-gray-100" onclick="document.getElementById('feedbackModal').classList.add('hidden')" type="button">Cancel</button><button class="rounded bg-[#135d2e] px-4 py-1.5 text-xs font-bold text-white hover:bg-[#0c3b1d]" type="submit">Submit Ticket</button></div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggleStaffBtn = document.getElementById('toggleStaffViewBtn');
            let teachingView = @json($isFaculty);

            toggleStaffBtn?.addEventListener('click', () => {
                teachingView = !teachingView;
                document.getElementById('faculty-academic-module')?.classList.toggle('opacity-40', !teachingView);
                document.getElementById('faculty-academic')?.classList.toggle('opacity-50', !teachingView);
                toggleStaffBtn.innerText = teachingView ? 'Non-Teaching Staff' : 'Teaching Faculty';
            });
        });
    </script>
</body>
</html>
