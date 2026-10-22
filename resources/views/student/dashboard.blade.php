<!DOCTYPE html>
<html class="h-full bg-background" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>CLCST Library Management System - Student Dashboard</title>
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
                        'outline-variant': '#c0c9bd',
                        'inverse-on-surface': '#eef0ff',
                        'surface-container-highest': '#dae2fd',
                        'on-secondary-container': '#6d5200',
                        outline: '#707a6f',
                        'surface-variant': '#dae2fd',
                        'on-secondary-fixed': '#251a00',
                        'tertiary-container': '#0041cc',
                        'on-tertiary-container': '#b3c1ff',
                        error: '#ba1a1a',
                        'surface-container-low': '#f2f3ff',
                        'on-error-container': '#93000a',
                        'primary-fixed': '#aaf4b5',
                        'on-primary': '#ffffff',
                        'secondary-fixed-dim': '#f7be1d',
                        surface: '#faf8ff',
                        'on-secondary-fixed-variant': '#5a4300',
                        'inverse-surface': '#283044',
                        'surface-container': '#eaedff',
                        'surface-tint': '#256b3a',
                        'on-surface-variant': '#404940',
                        'on-error': '#ffffff',
                        'on-secondary': '#ffffff',
                        'secondary-fixed': '#ffdf9a',
                        'on-tertiary-fixed-variant': '#0039b5',
                        'on-background': '#131b2e',
                        'inverse-primary': '#8fd79b',
                        'on-primary-container': '#8cd499',
                        tertiary: '#002e97',
                        'surface-dim': '#d2d9f4',
                        primary: '#00441d',
                        'tertiary-fixed': '#dce1ff',
                        'on-primary-fixed': '#00210b',
                        'on-surface': '#131b2e',
                        'surface-bright': '#faf8ff',
                        'error-container': '#ffdad6',
                        'surface-container-lowest': '#ffffff',
                        'secondary-container': '#fdc425',
                        'tertiary-fixed-dim': '#b7c4ff',
                        'surface-container-high': '#e2e7ff',
                        'primary-container': '#135d2e',
                        'on-tertiary-fixed': '#001551',
                        'on-primary-fixed-variant': '#015225',
                        secondary: '#785a00',
                        background: '#faf8ff',
                        'on-tertiary': '#ffffff',
                        'primary-fixed-dim': '#8fd79b'
                    },
                    borderRadius: {
                        DEFAULT: '0.25rem',
                        lg: '0.5rem',
                        xl: '0.75rem',
                        full: '9999px'
                    },
                    fontFamily: {
                        'label-sm': ['Inter', 'sans-serif'],
                        'headline-sm': ['Plus Jakarta Sans', 'sans-serif'],
                        'label-lg': ['Inter', 'sans-serif'],
                        'headline-xl': ['Plus Jakarta Sans', 'sans-serif'],
                        'body-lg': ['Inter', 'sans-serif'],
                        'body-md': ['Inter', 'sans-serif'],
                        'headline-md': ['Plus Jakarta Sans', 'sans-serif'],
                        'body-sm': ['Inter', 'sans-serif'],
                        'label-md': ['Inter', 'sans-serif'],
                        'headline-lg': ['Plus Jakarta Sans', 'sans-serif']
                    }
                }
            }
        };
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            display: inline-block;
            vertical-align: middle;
            line-height: 1;
        }

        .barcode-lines {
            background: repeating-linear-gradient(
                90deg,
                #0f172a 0,
                #0f172a 2px,
                transparent 2px,
                transparent 4px,
                #0f172a 4px,
                #0f172a 7px,
                transparent 7px,
                transparent 9px,
                #0f172a 9px,
                #0f172a 12px,
                transparent 12px,
                transparent 15px,
                #0f172a 15px,
                #0f172a 16px,
                transparent 16px,
                transparent 18px,
                #0f172a 18px,
                #0f172a 22px
            );
        }
    </style>
    @include('partials.ui-motion')
</head>
<body class="h-full bg-[#F8FAFC] text-on-surface antialiased">
    @php
        $student = auth()->user();
        $studentProgram = $student->program_or_department ?: 'Program not specified';
    @endphp
    <header class="sticky top-0 z-40 flex h-16 w-full items-center justify-between border-b border-[#E2E8F0] bg-surface-container-lowest px-4 shadow-sm lg:px-6">
        <div class="flex items-center gap-3">
            @include('partials.celtech-logo', ['class' => 'flex h-10 w-10 items-center justify-center rounded-full bg-primary text-sm font-extrabold text-white shadow-sm object-contain bg-white p-0.5'])
            <div>
                <span class="block font-headline-sm text-sm font-bold leading-none tracking-wide text-primary sm:text-base">CLCST SCHOOL LIBRARY</span>
                <span class="mt-0.5 hidden text-[10px] font-bold tracking-wider text-[#64748B] sm:block">ONLINE PUBLIC ACCESS CATALOG & PATRON PORTAL</span>
            </div>
        </div>

        <div class="flex items-center gap-3 lg:gap-4">
            <div class="hidden items-center gap-2 rounded-full border border-[#BBF7D0] bg-[#F0FDF4] px-3 py-1.5 md:flex">
                <span class="h-2 w-2 animate-pulse rounded-full bg-[#16A34A]"></span>
                <span class="text-[10px] font-bold text-[#166534]">STUDENT PATRON PORTAL - HCI102 PROJECT</span>
            </div>

            <a aria-label="Notifications" class="relative rounded-lg p-2 text-on-surface-variant transition-colors hover:bg-surface-container hover:text-on-surface" href="{{ route('student.section', 'notifications') }}">
                <span class="material-symbols-outlined">notifications</span>
                <span class="absolute right-1.5 top-1.5 h-2 w-2 rounded-full bg-[#EAB308] ring-2 ring-white"></span>
            </a>
            <a aria-label="Help and Support" class="rounded-lg p-2 text-on-surface-variant transition-colors hover:bg-surface-container hover:text-on-surface" href="{{ route('student.section', 'support') }}">
                <span class="material-symbols-outlined">help</span>
            </a>

            <div class="h-6 w-px bg-[#E2E8F0]"></div>

            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-primary-container text-sm font-bold text-on-primary shadow-sm ring-2 ring-[#DCFCE7]">{{ $student->initials() }}</div>
                <div class="hidden text-left lg:block">
                    <p class="text-xs font-semibold leading-tight text-on-surface">{{ $student->name }}</p>
                    <p class="text-xs text-[#64748B]">{{ $student->patron_id }} ({{ $studentProgram }})</p>
                </div>
            </div>
        </div>
    </header>

    <div class="flex min-h-[calc(100vh-4rem)]">
        @include('partials.student-sidebar', ['activePage' => 'dashboard'])

        <main class="md:ml-64 min-w-0 flex-1 space-y-6 overflow-y-auto p-4 sm:p-6 lg:p-8" id="dashboard">
            @if (session('status'))
                <div class="rounded-lg border border-[#BBF7D0] bg-[#F0FDF4] px-4 py-3 text-sm font-semibold text-[#166534]">
                    {{ session('status') }}
                </div>
            @endif

            <section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-primary-container via-[#0e4723] to-[#0a351a] p-6 text-on-primary shadow-sm lg:p-8">
                <div class="absolute bottom-0 right-0 top-0 mr-6 hidden w-80 items-center justify-center text-8xl font-extrabold text-white/10 lg:flex">CLCST</div>
                <div class="relative z-10 max-w-3xl space-y-3">
                    <div class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/15 px-3 py-1 text-[10px] font-bold text-white backdrop-blur-sm">
                        <span class="material-symbols-outlined text-xs text-[#FDE047]">verified</span>
                        <span>CLCST Library Management System - Student Access</span>
                    </div>
                    <h1 class="font-headline-xl text-3xl font-bold text-white md:text-4xl">Welcome back, {{ $student->name }}!</h1>
                    <p class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-slate-100">
                        <span><strong>Student ID:</strong> {{ $student->patron_id }}</span>
                        <span>-</span>
                        <span>{{ $studentProgram }}</span>
                        <span>-</span>
                        <span class="rounded-full bg-[#FDE047] px-2.5 py-0.5 text-[10px] font-bold text-[#0F172A]">Borrowing Limit: 2 / 5 Books Borrowed</span>
                    </p>
                    <div class="mt-4 flex items-start gap-3 rounded-xl border border-white/20 bg-white/10 p-3 backdrop-blur-md">
                        <span class="material-symbols-outlined mt-0.5 text-lg text-[#FDE047]">info</span>
                        <p class="text-xs leading-5 text-slate-200">
                            <strong class="text-white">Student Account Mode:</strong> Read and self-service only. Inventory additions, cataloging, and physical checkout clearances are authorized by Library Desk librarians.
                        </p>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="flex flex-col justify-between rounded-xl border border-[#E2E8F0] bg-surface-container-lowest p-5 shadow-sm transition-all hover:border-primary-container/30">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-[#64748B]">Borrowed Books</span>
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#DCFCE7] text-[#166534]">
                            <span class="material-symbols-outlined">menu_book</span>
                        </div>
                    </div>
                    <div class="mt-4">
                        <h3 class="font-headline-lg text-2xl font-semibold text-on-surface">2 Active Loans</h3>
                        <div class="mt-2 space-y-1.5">
                            <div class="flex justify-between text-[10px] font-bold text-[#64748B]">
                                <span>Borrowing Limit Used</span>
                                <span class="text-primary-container">2 / 5 Books</span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-[#E2E8F0]">
                                <div class="h-full rounded-full bg-primary-container" style="width: 40%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative flex flex-col justify-between overflow-hidden rounded-xl border border-[#FDE68A] bg-surface-container-lowest p-5 shadow-sm transition-all hover:shadow-md">
                    <div class="absolute left-0 right-0 top-0 h-1 bg-[#EAB308]"></div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-[#92400E]">Due Soon</span>
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#FEF3C7] text-[#92400E]">
                            <span class="material-symbols-outlined">schedule</span>
                        </div>
                    </div>
                    <div class="mt-4">
                        <h3 class="font-headline-lg text-2xl font-semibold text-[#92400E]">1 Book Due Soon</h3>
                        <div class="mt-2 inline-flex items-center gap-1.5 rounded-md bg-[#FEF3C7] px-2.5 py-1 text-[10px] font-bold text-[#92400E]">
                            <span class="material-symbols-outlined text-sm">warning</span>
                            <span>Due Oct 26, 2024 (2 Days)</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col justify-between rounded-xl border border-[#E2E8F0] bg-surface-container-lowest p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-[#64748B]">Overdue Books</span>
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#DCFCE7] text-[#166534]">
                            <span class="material-symbols-outlined">check_circle</span>
                        </div>
                    </div>
                    <div class="mt-4">
                        <h3 class="font-headline-lg text-2xl font-semibold text-on-surface">0 Overdue</h3>
                        <div class="mt-2 inline-flex items-center gap-1 rounded-md bg-[#DCFCE7] px-2.5 py-1 text-[10px] font-bold text-[#166534]">
                            <span class="material-symbols-outlined text-sm">verified_user</span>
                            <span>Clean Record / No Penalties</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col justify-between rounded-xl border border-[#E2E8F0] bg-surface-container-lowest p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-[#64748B]">Books Returned</span>
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#EFF6FF] text-[#1D4ED8]">
                            <span class="material-symbols-outlined">auto_stories</span>
                        </div>
                    </div>
                    <div class="mt-4">
                        <h3 class="font-headline-lg text-2xl font-semibold text-on-surface">8 Books</h3>
                        <p class="mt-2 flex items-center gap-1 text-xs text-[#64748B]">
                            <span class="material-symbols-outlined text-sm text-[#16A34A]">trending_up</span>
                            <span>Total lifetime academic reads</span>
                        </p>
                    </div>
                </div>
            </section>

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
                <div class="space-y-6 xl:col-span-8">
                    <section class="space-y-5 rounded-2xl border border-[#E2E8F0] bg-surface-container-lowest p-5 shadow-sm lg:p-6" id="loans">
                        <div class="flex flex-col justify-between gap-2 border-b border-[#E2E8F0] pb-4 sm:flex-row sm:items-center">
                            <div>
                                <h2 class="font-headline-md text-xl font-bold text-on-surface">My Active Loans & Due-Date Deadlines</h2>
                                <p class="text-xs text-[#64748B]">Currently checked out academic texts under student account {{ $student->patron_id }}</p>
                            </div>
                            <span class="inline-flex items-center gap-1.5 self-start rounded-full bg-[#DCFCE7] px-3 py-1 text-[10px] font-bold text-[#166534] sm:self-auto">
                                <span class="h-2 w-2 rounded-full bg-[#16A34A]"></span>
                                2 Current Borrows
                            </span>
                        </div>

                        <div class="space-y-4">
                            <article class="flex flex-col justify-between gap-4 rounded-xl border-2 border-[#FEF08A] bg-[#FEFCE8]/40 p-5 transition-all hover:bg-[#FEFCE8]/70 md:flex-row md:items-center">
                                <div class="flex items-start gap-4">
                                    <div class="flex h-24 w-16 flex-shrink-0 flex-col items-center justify-center rounded-lg bg-primary-container p-2 text-center text-white shadow-sm">
                                        <span class="material-symbols-outlined mb-1 text-2xl">code</span>
                                        <span class="text-[9px] font-bold uppercase tracking-wider">CS-TEXT</span>
                                    </div>
                                    <div class="space-y-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h4 class="font-headline-sm text-base font-bold text-on-surface">Clean Code: A Handbook of Agile Software Craftsmanship</h4>
                                            <span class="rounded-full bg-[#FEF3C7] px-2 py-0.5 text-[10px] font-bold text-[#92400E]">Due Soon (2 Days Left)</span>
                                        </div>
                                        <p class="text-xs text-[#475569]">Author: Robert C. Martin</p>
                                        <p class="flex flex-wrap gap-x-4 text-xs text-[#64748B]">
                                            <span><strong>Call No:</strong> QA76.76.C64 M37</span>
                                            <span><strong>Barcode:</strong> CLEAN-001</span>
                                        </p>
                                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 pt-2 text-[10px] font-bold text-[#64748B]">
                                            <span>Borrowed: <strong class="text-on-surface">Oct 12, 2024</strong></span>
                                            <span class="text-[#DC2626]">Due Date: <strong>Oct 26, 2024</strong></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-row justify-end gap-2 border-t border-[#FEF08A] pt-2 md:flex-col md:border-t-0 md:pt-0">
                                    <button class="flex items-center justify-center gap-1.5 rounded-lg bg-[#EAB308] px-4 py-2 text-[10px] font-bold text-[#0F172A] shadow-sm transition-colors hover:bg-[#CA8A04]" type="button">
                                        <span class="material-symbols-outlined text-sm">update</span>
                                        Request 7-Day Renewal
                                    </button>
                                    <button class="flex items-center justify-center gap-1.5 rounded-lg border border-[#CBD5E1] bg-white px-4 py-2 text-[10px] font-semibold text-[#334155] transition-colors hover:bg-slate-50" type="button">
                                        <span class="material-symbols-outlined text-sm">download</span>
                                        Download Receipt
                                    </button>
                                </div>
                            </article>

                            <article class="flex flex-col justify-between gap-4 rounded-xl border border-[#E2E8F0] bg-surface-container-lowest p-5 transition-all hover:border-primary-container/40 md:flex-row md:items-center">
                                <div class="flex items-start gap-4">
                                    <div class="flex h-24 w-16 flex-shrink-0 flex-col items-center justify-center rounded-lg bg-[#1D4ED8] p-2 text-center text-white shadow-sm">
                                        <span class="material-symbols-outlined mb-1 text-2xl">functions</span>
                                        <span class="text-[9px] font-bold uppercase tracking-wider">ALGO-REF</span>
                                    </div>
                                    <div class="space-y-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h4 class="font-headline-sm text-base font-bold text-on-surface">Introduction to Algorithms (4th Edition)</h4>
                                            <span class="rounded-full bg-[#DCFCE7] px-2 py-0.5 text-[10px] font-bold text-[#166534]">Active / On Time (8 Days Remaining)</span>
                                        </div>
                                        <p class="text-xs text-[#475569]">Authors: Cormen, Leiserson, Rivest, Stein</p>
                                        <p class="flex flex-wrap gap-x-4 text-xs text-[#64748B]">
                                            <span><strong>Call No:</strong> QA76.6 .I585</span>
                                            <span><strong>Barcode:</strong> CLRS-002</span>
                                        </p>
                                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 pt-2 text-[10px] font-bold text-[#64748B]">
                                            <span>Borrowed: <strong class="text-on-surface">Oct 18, 2024</strong></span>
                                            <span class="text-[#166534]">Due Date: <strong>Nov 01, 2024</strong></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-row justify-end gap-2 border-t border-[#E2E8F0] pt-2 md:flex-col md:border-t-0 md:pt-0">
                                    <button class="flex items-center justify-center gap-1.5 rounded-lg border border-primary-container px-4 py-2 text-[10px] font-bold text-primary-container transition-colors hover:bg-[#F0FDF4]" type="button">
                                        <span class="material-symbols-outlined text-sm">visibility</span>
                                        View Details
                                    </button>
                                    <button class="flex items-center justify-center gap-1.5 rounded-lg border border-[#CBD5E1] bg-white px-4 py-2 text-[10px] font-semibold text-[#334155] transition-colors hover:bg-slate-50" type="button">
                                        <span class="material-symbols-outlined text-sm">download</span>
                                        Download Receipt
                                    </button>
                                </div>
                            </article>
                        </div>
                    </section>

                    <section class="space-y-5 rounded-2xl border border-[#E2E8F0] bg-surface-container-lowest p-5 shadow-sm lg:p-6" id="catalog">
                        <div class="flex flex-col justify-between gap-2 border-b border-[#E2E8F0] pb-4 sm:flex-row sm:items-center">
                            <div>
                                <h2 class="font-headline-md text-xl font-bold text-on-surface">Quick Book Catalog Search & Availability Checker</h2>
                                <p class="text-xs text-[#64748B]">Browse real-time holdings, shelf availability, and place hold requests</p>
                            </div>
                            <span class="rounded-md border border-[#E2E8F0] bg-[#F1F5F9] px-2.5 py-1 text-[10px] font-bold text-[#64748B]">Live Stack Status</span>
                        </div>

                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-[#64748B]">
                                <span class="material-symbols-outlined">search</span>
                            </span>
                            <input class="w-full rounded-xl border border-[#CBD5E1] bg-[#F8FAFC] py-3 pl-11 pr-28 text-sm transition-all focus:border-transparent focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-container" placeholder="Search by book title, author, ISBN, or category..." type="text">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-2">
                                <button class="flex items-center gap-1 rounded-lg bg-primary-container px-3 py-1.5 text-[10px] font-bold text-on-primary transition-colors hover:bg-[#0e4723]" type="button">
                                    <span class="material-symbols-outlined text-xs">qr_code_scanner</span>
                                    Scan ISBN
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 overflow-x-auto pb-1">
                            @foreach (['All Categories', 'Computer Science & IT', 'Engineering', 'General Education', 'Business & Accountancy'] as $index => $category)
                                <button class="whitespace-nowrap rounded-full px-3 py-1.5 text-[10px] font-bold transition-colors {{ $index === 0 ? 'bg-primary-container text-white shadow-sm' : 'border border-[#E2E8F0] bg-[#F1F5F9] text-[#475569] hover:bg-[#E2E8F0]' }}" type="button">
                                    {{ $category }}
                                </button>
                            @endforeach
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            @foreach ([
                                ['available' => true, 'status' => 'Available (5 Copies)', 'shelf' => '005.13 P9', 'title' => 'Introduction to Python Programming', 'author' => 'J. Smith', 'isbn' => '978-0134076430', 'location' => 'Stack A-3', 'action' => 'Request to Reserve'],
                                ['available' => true, 'status' => 'Available (3 Copies)', 'shelf' => '005.74 D2', 'title' => 'Database Systems: Design, Implementation & Management', 'author' => 'Coronel & Morris', 'isbn' => '978-1337627900', 'location' => 'Stack B-1', 'action' => 'Request to Reserve'],
                                ['available' => false, 'status' => 'All Copies Checked Out', 'shelf' => '004.6 T2', 'title' => 'Computer Networks (6th Edition)', 'author' => 'Tanenbaum & Wetherall', 'isbn' => '978-0133766189', 'location' => 'Expected Nov 4', 'action' => 'Notify When Available'],
                                ['available' => true, 'status' => 'Available (2 Copies)', 'shelf' => '005.43 M6', 'title' => 'Modern Operating Systems', 'author' => 'Tanenbaum', 'isbn' => '978-0133591620', 'location' => 'Stack A-1', 'action' => 'Request to Reserve'],
                            ] as $book)
                                <article class="flex flex-col justify-between rounded-xl border {{ $book['available'] ? 'border-[#E2E8F0] bg-white hover:border-primary-container/40' : 'border-[#FEE2E2] bg-[#FFF5F5]' }} p-4 transition-all hover:shadow-sm">
                                    <div>
                                        <div class="mb-2 flex items-start justify-between gap-2">
                                            <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $book['available'] ? 'bg-[#DCFCE7] text-[#166534]' : 'bg-[#FEE2E2] text-[#991B1B]' }}">{{ $book['status'] }}</span>
                                            <span class="text-[10px] font-semibold text-[#64748B]">Shelf: {{ $book['shelf'] }}</span>
                                        </div>
                                        <h4 class="font-headline-sm text-base font-bold text-on-surface">{{ $book['title'] }}</h4>
                                        <p class="mt-0.5 text-xs text-[#64748B]">Author: {{ $book['author'] }}</p>
                                        <p class="mt-1 font-mono text-[11px] text-[#94A3B8]">ISBN: {{ $book['isbn'] }}</p>
                                    </div>
                                    <div class="mt-4 flex items-center justify-between border-t {{ $book['available'] ? 'border-[#F1F5F9]' : 'border-[#FEE2E2]' }} pt-3">
                                        <span class="flex items-center gap-1 text-[10px] font-bold {{ $book['available'] ? 'text-[#166534]' : 'text-[#991B1B]' }}">
                                            <span class="material-symbols-outlined text-sm">{{ $book['available'] ? 'inventory_2' : 'schedule' }}</span>
                                            {{ $book['location'] }}
                                        </span>
                                        <button class="rounded-lg px-3 py-1.5 text-[10px] font-bold transition-colors {{ $book['available'] ? 'bg-primary-container text-on-primary hover:bg-[#0e4723]' : 'bg-[#E2E8F0] text-[#334155] hover:bg-[#CBD5E1]' }}" type="button">
                                            {{ $book['action'] }}
                                        </button>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                </div>

                <aside class="space-y-6 xl:col-span-4">
                    <section class="space-y-4 overflow-hidden rounded-2xl border border-[#CBD5E1] bg-gradient-to-b from-[#135d2e] via-[#0e4723] to-[#092c16] p-5 text-white shadow-md">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                @include('partials.celtech-logo', ['class' => 'flex h-8 w-8 items-center justify-center rounded-full border border-white/40 bg-white text-xs font-extrabold text-primary object-contain bg-white p-0.5'])
                                <div>
                                    <h5 class="text-[10px] font-bold uppercase tracking-wide text-[#FDE047]">CLCST Digital Library Pass</h5>
                                    <p class="text-[10px] text-slate-200">Optical Library Entry & Self-Service Check Station</p>
                                </div>
                            </div>
                            <span class="rounded bg-white/20 px-2 py-0.5 text-[10px] font-bold tracking-widest text-[#AAF4B5]">VALID AY 24-25</span>
                        </div>

                        <div class="space-y-1 rounded-xl border border-white/20 bg-white/10 p-3.5 text-center backdrop-blur-sm">
                            <p class="text-[11px] uppercase tracking-widest text-slate-300">Registered Library User</p>
                            <h3 class="font-headline-md text-xl font-extrabold tracking-wide text-white">{{ mb_strtoupper($student->name) }}</h3>
                            <p class="font-mono text-xs font-bold text-[#FDE047]">{{ $student->patron_id }}</p>
                            <p class="text-[11px] text-slate-200">{{ $studentProgram }}</p>
                        </div>

                        <div class="flex flex-col items-center justify-center space-y-1 rounded-xl border border-slate-200 bg-white p-3 shadow-inner">
                            <div class="barcode-lines h-14 w-full rounded"></div>
                            <span class="font-mono text-xs font-bold tracking-[0.25em] text-slate-900">{{ $student->patron_id }}</span>
                            <span class="text-[10px] font-medium text-slate-500">Flash code under barcode scanner to enter</span>
                        </div>

                        <div class="flex items-center justify-between pt-1 text-[11px] text-slate-300">
                            <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-[#4ADE80]"></span> Active Account Pass</span>
                            <span>Max Quota: 5 Books</span>
                        </div>
                    </section>

                    <section class="space-y-4 rounded-2xl border border-[#E2E8F0] bg-surface-container-lowest p-5 shadow-sm" id="alerts">
                        <div class="flex items-center justify-between">
                            <h3 class="font-headline-sm text-base font-bold text-on-surface">Upcoming Due Date & Alerts</h3>
                            <span class="material-symbols-outlined text-[#EAB308]">notifications_active</span>
                        </div>
                        <div class="space-y-3">
                            <div class="space-y-1.5 rounded-xl border border-[#FDE047] bg-[#FEFCE8] p-3.5">
                                <div class="flex items-center justify-between">
                                    <span class="flex items-center gap-1 text-[10px] font-bold text-[#92400E]">
                                        <span class="material-symbols-outlined text-sm">schedule</span>
                                        DUE IN 2 DAYS
                                    </span>
                                    <span class="text-[10px] font-bold text-[#DC2626]">Oct 26, 5:00 PM</span>
                                </div>
                                <p class="text-xs font-semibold text-[#78350F]">Clean Code: A Handbook of Agile Software Craftsmanship</p>
                                <p class="text-xs text-[#92400E]">Return by Saturday 5:00 PM to avoid standard PHP 20/day overdue fine.</p>
                            </div>

                            <div class="space-y-1 rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] p-3.5">
                                <div class="flex items-center gap-1.5 text-[10px] font-bold text-[#334155]">
                                    <span class="material-symbols-outlined text-sm text-primary-container">meeting_room</span>
                                    Library Desk Hours
                                </div>
                                <p class="text-xs text-[#64748B]">Mon - Fri: 8:00 AM - 8:00 PM<br>Physical returns accepted at Main Ground Floor Library Entry drop box 24/7.</p>
                            </div>
                        </div>
                    </section>

                    <section class="space-y-3 rounded-2xl border border-[#E2E8F0] bg-surface-container-lowest p-5 shadow-sm">
                        <h3 class="font-headline-sm text-base font-bold text-on-surface">Quick Library User Actions</h3>
                        <div class="space-y-2">
                            @foreach ([
                                ['icon' => 'history', 'title' => 'View Borrowing History', 'desc' => 'Check returned books and logs', 'color' => 'bg-[#EFF6FF] text-[#1D4ED8]', 'href' => route('student.section', 'history')],
                                ['icon' => 'chair', 'title' => 'Reserve Study Carrel', 'desc' => 'Book a quiet study station', 'color' => 'bg-[#F0FDF4] text-[#166534]', 'href' => route('student.section', 'reservations')],
                                ['icon' => 'policy', 'title' => 'Library Guidelines & Policies', 'desc' => 'Fines, renewals, and conduct', 'color' => 'bg-[#FEF3C7] text-[#92400E]', 'href' => route('student.section', 'circulation-policy')],
                            ] as $action)
                                <a class="flex items-center justify-between rounded-xl border border-[#E2E8F0] p-3 text-on-surface transition-colors hover:bg-slate-50" href="{{ $action['href'] }}">
                                    <span class="flex items-center gap-3">
                                        <span class="rounded-lg p-2 {{ $action['color'] }}">
                                            <span class="material-symbols-outlined text-sm">{{ $action['icon'] }}</span>
                                        </span>
                                        <span>
                                            <span class="block text-xs font-bold">{{ $action['title'] }}</span>
                                            <span class="block text-xs text-[#64748B]">{{ $action['desc'] }}</span>
                                        </span>
                                    </span>
                                    <span class="material-symbols-outlined text-[#94A3B8]">chevron_right</span>
                                </a>
                            @endforeach
                        </div>
                    </section>
                </aside>
            </div>
        </main>
    </div>
</body>
</html>
