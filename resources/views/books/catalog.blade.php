<!DOCTYPE html>
<html class="h-full bg-background text-on-surface" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CLCST Library - Search Books & OPAC Catalog Discovery</title>
    @include('partials.favicon')

    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'surface-container-lowest': '#ffffff',
                        'surface-container-low': '#f2f3ff',
                        'surface-container': '#eaedff',
                        'surface-container-high': '#e2e7ff',
                        background: '#faf8ff',
                        surface: '#faf8ff',
                        primary: '#00441d',
                        'primary-container': '#135d2e',
                        'on-primary': '#ffffff',
                        'on-surface': '#131b2e',
                        'on-surface-variant': '#404940',
                        outline: '#707a6f',
                        'outline-variant': '#c0c9bd',
                        secondary: '#785a00',
                        'secondary-container': '#fdc425',
                        'on-secondary-container': '#6d5200',
                        error: '#ba1a1a',
                        'error-container': '#ffdad6',
                        tertiary: '#002e97',
                        'tertiary-fixed': '#dce1ff'
                    },
                    borderRadius: {
                        DEFAULT: '0.25rem',
                        lg: '0.5rem',
                        xl: '0.75rem',
                        full: '9999px'
                    },
                    fontFamily: {
                        'body-md': ['Inter', 'sans-serif'],
                        'headline-sm': ['Plus Jakarta Sans', 'sans-serif'],
                        'headline-md': ['Plus Jakarta Sans', 'sans-serif'],
                        'headline-lg': ['Plus Jakarta Sans', 'sans-serif'],
                        'label-sm': ['Inter', 'sans-serif'],
                        'label-md': ['Inter', 'sans-serif']
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

        .material-symbols-outlined.fill-1 {
            font-variation-settings: 'FILL' 1;
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
            border-radius: 4px;
        }

        .book-cover {
            box-shadow: 9px 10px 18px rgba(15, 23, 42, 0.18);
            transform: perspective(900px) rotateY(-5deg);
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="flex min-h-screen flex-col bg-[#F8FAFC] font-body-md text-on-surface antialiased">
    @php
        $activeLoanCount = count($activeLoans);
        $reservationCount = count($reservations);
        $selectedAvailable = collect($selectedBook['copies'])->where('status', 'available')->count();
        $currentParams = request()->except('page');
        $totalCatalogCount = collect($categories)->count() * 520 + 14250;
    @endphp

    <header class="sticky top-0 z-40 flex h-16 w-full items-center justify-between border-b border-outline-variant bg-surface-container-lowest px-4 shadow-sm md:px-6">
        <div class="flex min-w-[280px] items-center gap-3">
            @include('partials.celtech-logo', ['class' => 'h-10 w-10 flex-shrink-0 rounded-full border border-secondary-container bg-white object-contain p-0.5 shadow-sm'])
            <div class="flex flex-col">
                <span class="font-headline-sm text-base font-bold leading-tight tracking-tight text-primary">CLCST SCHOOL LIBRARY</span>
                <span class="text-[10px] font-bold uppercase tracking-wider text-outline">Central Luzon College of Science and Technology</span>
            </div>
        </div>

        <form class="mx-8 hidden max-w-xl flex-1 items-center lg:flex" method="GET" action="{{ route('books.catalog') }}">
            <div class="relative w-full">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-outline">
                    <span class="material-symbols-outlined text-[18px]">search</span>
                </span>
                <input class="h-10 w-full rounded-lg border border-outline-variant bg-surface-container-low py-2 pl-10 pr-20 text-sm text-on-surface transition-all focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-primary" name="q" placeholder="Quick catalog lookup (Title, ISBN, Barcode, Author)..." type="text" value="{{ $query }}">
                <div class="absolute inset-y-0 right-0 flex items-center gap-1.5 pr-2">
                    <span class="hidden rounded border border-outline-variant bg-surface-container px-1.5 py-0.5 font-mono text-[11px] font-semibold text-outline sm:inline-flex">OPAC</span>
                    <button class="rounded p-1 text-outline transition-colors hover:bg-surface-container hover:text-primary" title="Scan Barcode" type="submit">
                        <span class="material-symbols-outlined text-[18px]">barcode_scanner</span>
                    </button>
                </div>
            </div>
        </form>

        <div class="flex items-center gap-3">
            <div class="hidden items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 xl:flex">
                <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-600"></span>
                <span class="text-[10px] font-semibold text-emerald-800">OPAC Node Online - Stacks v4.2</span>
            </div>
            <a class="relative rounded-lg p-2 text-on-surface-variant transition-colors hover:bg-surface-container hover:text-on-surface" href="{{ route('student.section', 'notifications') }}" title="Notifications">
                <span class="material-symbols-outlined text-[22px]">notifications</span>
                @if ($reservationCount > 0)
                    <span class="absolute right-1.5 top-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-error text-[10px] font-bold text-white">{{ $reservationCount }}</span>
                @endif
            </a>
            <a class="rounded-lg p-2 text-on-surface-variant transition-colors hover:bg-surface-container hover:text-on-surface" href="{{ route('student.section', 'support') }}" title="Help Desk">
                <span class="material-symbols-outlined text-[22px]">help</span>
            </a>
            <div class="mx-1 h-6 w-px bg-outline-variant"></div>
            <div class="flex items-center gap-3 pl-1">
                <div class="hidden text-right sm:block">
                    <p class="text-xs font-semibold leading-tight text-on-surface">Engr. Roberto M. Mendoza</p>
                    <p class="text-[10px] leading-tight text-outline">CICT Faculty - BSCS Chair</p>
                </div>
                <div class="relative">
                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-primary font-headline-sm text-xs font-bold text-on-primary shadow-sm ring-2 ring-secondary-container">RM</div>
                    <span class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full bg-emerald-500 ring-2 ring-white"></span>
                </div>
            </div>
        </div>
    </header>

    <div class="flex flex-1 overflow-hidden">
        <aside class="custom-scrollbar hidden w-64 flex-shrink-0 flex-col justify-between overflow-y-auto border-r border-outline-variant bg-surface-container-lowest md:flex">
            <div class="space-y-5 p-4">
                <div class="flex items-center gap-3 rounded-xl border border-outline-variant bg-surface-container-low p-2.5">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary-container text-on-primary shadow-sm">
                        <span class="material-symbols-outlined text-[20px]">local_library</span>
                    </div>
                    <div class="overflow-hidden">
                        <h2 class="truncate text-xs font-bold leading-tight text-primary">Central Stacks</h2>
                        <p class="truncate text-[10px] leading-tight text-outline">Olongapo Main Campus</p>
                    </div>
                </div>

                <a class="flex h-10 w-full items-center justify-center gap-2 rounded-lg bg-primary px-3.5 text-xs font-bold text-on-primary shadow-sm transition-all hover:bg-[#0E4723]" href="{{ route('books.catalog', ['q' => '1049281']) }}">
                    <span class="material-symbols-outlined text-[18px]">qr_code_scanner</span>
                    <span>Scan Barcode</span>
                </a>

                <nav aria-label="Library Navigation" class="space-y-1">
                    <div class="px-2 py-1 text-[11px] font-bold uppercase tracking-wider text-outline">Catalog & Circulation</div>
                    <a class="flex items-center justify-between rounded-lg px-3 py-2 text-on-surface-variant transition-colors hover:bg-surface-container hover:text-on-surface" href="{{ route('faculty.dashboard') }}">
                        <span class="flex items-center gap-3"><span class="material-symbols-outlined text-[20px]">dashboard</span><span class="text-sm font-medium">Dashboard</span></span>
                    </a>
                    <a class="flex items-center justify-between rounded-lg bg-primary-container px-3 py-2.5 font-semibold text-on-primary shadow-sm" href="{{ route('books.catalog') }}">
                        <span class="flex items-center gap-3"><span class="material-symbols-outlined fill-1 text-[20px]">menu_book</span><span class="text-sm font-semibold">Search Books</span></span>
                        <span class="h-2 w-2 rounded-full bg-secondary-container"></span>
                    </a>
                    <a class="flex items-center justify-between rounded-lg px-3 py-2 text-on-surface-variant transition-colors hover:bg-surface-container hover:text-on-surface" href="{{ route('staff.section', 'circulation') }}">
                        <span class="flex items-center gap-3"><span class="material-symbols-outlined text-[20px]">swap_horiz</span><span class="text-sm font-medium">My Borrowed Books</span></span>
                        <span class="rounded-full bg-surface-container-high px-1.5 py-0.5 text-[10px] font-bold text-primary">{{ $activeLoanCount }}</span>
                    </a>
                    <a class="flex items-center justify-between rounded-lg px-3 py-2 text-on-surface-variant transition-colors hover:bg-surface-container hover:text-on-surface" href="{{ route('staff.section', 'reservations') }}">
                        <span class="flex items-center gap-3"><span class="material-symbols-outlined text-[20px]">book_online</span><span class="text-sm font-medium">My Reservations</span></span>
                        <span class="rounded-full bg-amber-100 px-1.5 py-0.5 text-[10px] font-bold text-amber-900">{{ $reservationCount }}</span>
                    </a>
                    <a class="flex items-center justify-between rounded-lg px-3 py-2 text-on-surface-variant transition-colors hover:bg-surface-container hover:text-on-surface" href="{{ route('staff.section', 'reports') }}">
                        <span class="flex items-center gap-3"><span class="material-symbols-outlined text-[20px]">update</span><span class="text-sm font-medium">Renewal Requests</span></span>
                        <span class="rounded-full bg-rose-100 px-1.5 py-0.5 text-[10px] font-bold text-rose-800">1</span>
                    </a>
                    <a class="flex items-center justify-between rounded-lg px-3 py-2 text-on-surface-variant transition-colors hover:bg-surface-container hover:text-on-surface" href="{{ route('student.section', 'history') }}">
                        <span class="flex items-center gap-3"><span class="material-symbols-outlined text-[20px]">history</span><span class="text-sm font-medium">Borrowing History</span></span>
                    </a>
                    <div class="px-2 pb-1 pt-3 text-[11px] font-bold uppercase tracking-wider text-outline">Academics & Faculty</div>
                    <a class="flex items-center justify-between rounded-lg px-3 py-2 text-on-surface-variant transition-colors hover:bg-surface-container hover:text-on-surface" href="{{ route('staff.section', 'catalog') }}">
                        <span class="flex items-center gap-3"><span class="material-symbols-outlined text-[20px]">post_add</span><span class="text-sm font-medium">Recommend a Book</span></span>
                    </a>
                    <details class="group">
                        <summary class="flex cursor-pointer items-center justify-between rounded-lg px-3 py-2 text-on-surface-variant transition-colors hover:bg-surface-container">
                            <span class="flex items-center gap-3"><span class="material-symbols-outlined text-[20px]">school</span><span class="text-sm font-medium">Faculty Academic Requests</span></span>
                            <span class="material-symbols-outlined text-[16px] text-outline transition-transform group-open:rotate-180">expand_more</span>
                        </summary>
                        <div class="space-y-1 py-1 pl-9 pr-2">
                            <a class="block py-1 text-[13px] text-outline hover:text-primary" href="{{ route('staff.section', 'reservations') }}">Course Reserves Setup</a>
                            <a class="block py-1 text-[13px] text-outline hover:text-primary" href="{{ route('staff.section', 'catalog') }}">Syllabus References</a>
                            <a class="block py-1 text-[13px] text-outline hover:text-primary" href="{{ route('staff.section', 'support') }}">Inter-Library Loan</a>
                        </div>
                    </details>
                    <a class="flex items-center justify-between rounded-lg px-3 py-2 text-on-surface-variant transition-colors hover:bg-surface-container hover:text-on-surface" href="{{ route('staff.section', 'support') }}">
                        <span class="flex items-center gap-3"><span class="material-symbols-outlined text-[20px]">notifications</span><span class="text-sm font-medium">Notifications</span></span>
                        <span class="rounded-full bg-error-container px-1.5 py-0.5 text-[10px] font-bold text-error">3</span>
                    </a>
                    <a class="flex items-center justify-between rounded-lg px-3 py-2 text-on-surface-variant transition-colors hover:bg-surface-container hover:text-on-surface" href="{{ route('staff.section', 'patrons') }}">
                        <span class="flex items-center gap-3"><span class="material-symbols-outlined text-[20px]">manage_accounts</span><span class="text-sm font-medium">My Profile</span></span>
                    </a>
                </nav>
            </div>

            <div class="space-y-1 border-t border-outline-variant bg-surface-container-low/50 p-4">
                <a class="flex items-center gap-3 rounded-lg px-3 py-2 text-on-surface-variant transition-colors hover:bg-surface-container hover:text-on-surface" href="{{ route('staff.section', 'support') }}">
                    <span class="material-symbols-outlined text-[20px]">help_outline</span><span class="text-sm font-medium">Library Policies</span>
                </a>
                <a class="flex items-center gap-3 rounded-lg px-3 py-2 text-error transition-colors hover:bg-error-container/40" href="{{ route('login') }}">
                    <span class="material-symbols-outlined text-[20px]">logout</span><span class="text-sm font-semibold">Log Out</span>
                </a>
            </div>
        </aside>

        <main class="custom-scrollbar flex flex-1 flex-col overflow-y-auto bg-[#F8FAFC]">
            <section class="border-b border-outline-variant bg-surface-container-lowest px-4 pb-6 pt-6 shadow-sm lg:px-8">
                @if (session('status'))
                    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                        {{ session('status') }}
                    </div>
                @endif

                <nav class="mb-2 flex items-center gap-2 text-xs font-semibold text-outline">
                    <span>Academic Portal</span>
                    <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                    <span>Catalog & OPAC</span>
                    <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                    <span class="font-semibold text-primary">Search Books</span>
                </nav>

                <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <div class="flex flex-wrap items-center gap-3">
                            <h1 class="font-headline-lg text-2xl font-bold tracking-tight text-on-surface">Search Books & Catalog Discovery</h1>
                            <span class="rounded-full border border-primary/20 bg-primary/10 px-2.5 py-0.5 text-[10px] font-bold text-primary">OPAC Live System</span>
                        </div>
                        <p class="mt-1 max-w-3xl text-sm text-outline">Browse and search {{ number_format($totalCatalogCount) }}+ physical volumes, course syllabus references, and institutional e-books across CLCST Central and Extension Stacks.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <a class="flex h-9 items-center gap-2 rounded-lg border border-outline-variant bg-surface-container-lowest px-3.5 text-sm font-semibold text-on-surface shadow-sm transition-colors hover:bg-surface-container" href="{{ route('books.catalog', array_merge($currentParams, ['field' => 'all'])) }}">
                            <span class="material-symbols-outlined text-[18px]">tune</span>
                            <span>Advanced Search</span>
                        </a>
                        <a class="flex h-9 items-center gap-2 rounded-lg border border-secondary-container bg-amber-50 px-3.5 text-sm font-semibold text-amber-950 transition-colors hover:bg-amber-100" href="{{ route('books.catalog', ['q' => '1049281']) }}">
                            <span class="material-symbols-outlined text-[18px] text-secondary">barcode</span>
                            <span>RFID / ISBN Lookup</span>
                        </a>
                    </div>
                </div>

                <form class="rounded-2xl border border-outline-variant bg-surface-container p-3 shadow-inner" method="GET" action="{{ route('books.catalog') }}">
                    <div class="grid grid-cols-1 gap-3 lg:grid-cols-[180px_1fr_160px]">
                        <select class="h-12 rounded-xl border border-outline-variant bg-surface-container-lowest px-3 text-sm font-semibold text-on-surface focus:border-primary focus:ring-primary" name="field">
                            @foreach (['all' => 'All Fields', 'title' => 'Title', 'author' => 'Author', 'isbn' => 'ISBN / ISSN', 'call' => 'Call Number'] as $value => $label)
                                <option value="{{ $value }}" @selected($field === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-outline"><span class="material-symbols-outlined">search</span></span>
                            <input class="h-12 w-full rounded-xl border border-outline-variant bg-surface-container-lowest pl-12 pr-4 text-sm text-on-surface placeholder:text-outline focus:border-primary focus:ring-primary" name="q" placeholder="Search by title, author, subject, call number, ISBN, barcode..." type="text" value="{{ $query }}">
                        </div>
                        <button class="flex h-12 items-center justify-center gap-2 rounded-xl bg-primary px-5 text-sm font-bold text-on-primary shadow-sm transition-colors hover:bg-primary-container" type="submit">
                            <span class="material-symbols-outlined text-[18px]">manage_search</span>
                            Search OPAC
                        </button>
                    </div>
                    <div class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-3">
                        <select class="rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2 text-xs font-semibold text-on-surface" name="category">
                            <option value="all">All Categories</option>
                            @foreach ($categories as $filterCategory)
                                <option value="{{ $filterCategory }}" @selected($category === $filterCategory)>{{ $filterCategory }}</option>
                            @endforeach
                        </select>
                        <select class="rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2 text-xs font-semibold text-on-surface" name="availability">
                            <option value="all" @selected($availability === 'all')>All Availability</option>
                            <option value="available" @selected($availability === 'available')>Available Today</option>
                            <option value="reference" @selected($availability === 'reference')>Reference Only</option>
                        </select>
                        <select class="rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2 text-xs font-semibold text-on-surface" name="sort">
                            <option value="relevance" @selected($sort === 'relevance')>Sort: Relevance</option>
                            <option value="title" @selected($sort === 'title')>Sort: Title</option>
                            <option value="newest" @selected($sort === 'newest')>Sort: Newest</option>
                            <option value="available" @selected($sort === 'available')>Sort: Available Copies</option>
                        </select>
                    </div>
                </form>

                <div class="custom-scrollbar mt-4 flex items-center gap-2 overflow-x-auto pb-1">
                    <span class="pr-1 text-[10px] font-bold uppercase text-outline">Quick Filters:</span>
                    <a class="rounded-full px-3.5 py-1.5 text-xs font-semibold {{ $category === 'all' && $availability === 'all' ? 'bg-primary text-on-primary shadow-sm' : 'border border-outline-variant bg-surface-container text-on-surface hover:bg-surface-container-high' }}" href="{{ route('books.catalog', array_merge($currentParams, ['category' => 'all', 'availability' => 'all'])) }}">All Titles</a>
                    @foreach ($categories as $filterCategory)
                        <a class="rounded-full px-3.5 py-1.5 text-xs font-semibold {{ $category === $filterCategory ? 'bg-primary text-on-primary shadow-sm' : 'border border-outline-variant bg-surface-container text-on-surface hover:bg-surface-container-high' }}" href="{{ route('books.catalog', array_merge($currentParams, ['category' => $filterCategory, 'availability' => 'all'])) }}">{{ $filterCategory }}</a>
                    @endforeach
                    <a class="rounded-full px-3.5 py-1.5 text-xs font-semibold {{ $availability === 'available' ? 'bg-primary text-on-primary shadow-sm' : 'border border-outline-variant bg-surface-container text-on-surface hover:bg-surface-container-high' }}" href="{{ route('books.catalog', array_merge($currentParams, ['availability' => 'available'])) }}">Available Today</a>
                </div>
            </section>

            <section class="flex-1 px-4 py-6 lg:px-8">
                <div class="grid grid-cols-1 gap-6 xl:grid-cols-[1fr_360px]">
                    <div class="space-y-4">
                        <div class="flex flex-col justify-between gap-3 rounded-xl border border-outline-variant bg-surface-container-lowest px-4 py-3 shadow-sm sm:flex-row sm:items-center">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-secondary">OPAC Search Results</p>
                                <h2 class="font-headline-sm text-lg font-bold text-on-surface">{{ $books->count() }} titles matched your query</h2>
                            </div>
                            <span class="inline-flex items-center gap-1 rounded-lg bg-surface-container px-3 py-1.5 text-xs font-bold text-primary">
                                <span class="material-symbols-outlined text-[16px]">sensors</span>
                                RFID stack inventory synced
                            </span>
                        </div>

                        @forelse ($books as $book)
                            @php
                                $available = collect($book['copies'])->where('status', 'available')->count();
                                $total = count($book['copies']);
                                $isReserved = array_key_exists($book['id'], $reservations);
                                $statusTone = $book['policy'] === 'reference' ? 'blue' : ($available > 0 ? 'emerald' : 'amber');
                                $statusText = $book['policy'] === 'reference'
                                    ? 'Course Reserve / Reference Use'
                                    : ($available > 0 ? 'Available - '.$available.' of '.$total.' Copies on Shelf' : 'All Copies Borrowed - Join Hold Queue');
                            @endphp
                            <article class="rounded-xl border border-outline-variant bg-surface-container-lowest p-5 shadow-sm transition-all hover:border-primary/60 hover:shadow-md">
                                <div class="flex flex-col gap-5 sm:flex-row">
                                    <div class="book-cover relative h-40 w-full flex-shrink-0 overflow-hidden rounded-lg bg-gradient-to-br {{ $book['cover'] }} text-white sm:w-28">
                                        <img alt="{{ $book['title'] }} cover" class="h-full w-full object-cover" loading="lazy" onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');" src="{{ $book['cover_url'] }}">
                                        <div class="hidden h-full w-full flex-col justify-between p-2.5">
                                            <div>
                                                <span class="text-[9px] font-bold uppercase tracking-wider text-white/70">{{ $book['publisher'] }}</span>
                                                <h3 class="mt-1 font-headline-sm text-xs font-bold leading-tight text-white">{{ \Illuminate\Support\Str::limit($book['title'], 42) }}</h3>
                                            </div>
                                            <div class="border-t border-white/20 pt-1.5">
                                                <span class="block font-mono text-[9px] text-white/70">{{ \Illuminate\Support\Str::limit($book['call'], 16) }}</span>
                                                <span class="text-[10px] font-bold text-white/90">{{ $book['badge'] }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex flex-1 flex-col justify-between">
                                        <div>
                                            <div class="mb-1.5 flex flex-wrap items-center justify-between gap-2">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-[10px] font-bold {{ $statusTone === 'emerald' ? 'border-emerald-300 bg-emerald-100 text-emerald-800' : ($statusTone === 'amber' ? 'border-amber-300 bg-amber-100 text-amber-900' : 'border-blue-300 bg-blue-100 text-blue-900') }}">
                                                        <span class="material-symbols-outlined text-[14px]">{{ $statusTone === 'emerald' ? 'task_alt' : ($statusTone === 'amber' ? 'schedule' : 'lock_clock') }}</span>
                                                        {{ $statusText }}
                                                    </span>
                                                    <span class="rounded bg-surface-container px-2 py-0.5 text-[10px] font-semibold text-primary">{{ $book['category'] }}</span>
                                                </div>
                                                <span class="font-mono text-[10px] font-semibold text-outline">ISBN: {{ $book['isbn'] }}</span>
                                            </div>

                                            <h2 class="font-headline-md text-xl font-bold leading-snug text-on-surface transition-colors hover:text-primary">{{ $book['title'] }}</h2>
                                            <p class="mt-0.5 text-sm text-outline">By <span class="font-semibold text-on-surface">{{ $book['author'] }}</span> - {{ $book['publisher'] }} - Published {{ $book['year'] }}</p>

                                            <div class="mt-3 grid grid-cols-1 gap-2 rounded-lg border border-outline-variant bg-surface-container-low p-2.5 md:grid-cols-2">
                                                <div class="flex items-center gap-2 text-sm">
                                                    <span class="material-symbols-outlined text-[18px] text-primary">shelves</span>
                                                    <div><span class="block text-[10px] font-bold uppercase text-outline">Shelf Location</span><span class="font-semibold text-on-surface">{{ $book['location'] }}</span></div>
                                                </div>
                                                <div class="flex items-center gap-2 text-sm">
                                                    <span class="material-symbols-outlined text-[18px] text-secondary">label</span>
                                                    <div><span class="block text-[10px] font-bold uppercase text-outline">LC / Call Number</span><span class="font-mono font-semibold text-on-surface">{{ $book['call'] }}</span></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-outline-variant/60 pt-3">
                                            <div class="flex flex-wrap items-center gap-2">
                                                @if ($book['policy'] !== 'reference')
                                                    <form method="POST" action="{{ route('books.hold', $book['id']) }}">
                                                        @csrf
                                                        <button class="flex h-9 items-center gap-1.5 rounded-lg px-4 text-xs font-bold shadow-sm transition-all {{ $isReserved ? 'bg-emerald-100 text-emerald-800' : ($available > 0 ? 'bg-primary text-on-primary hover:bg-primary-container' : 'bg-secondary-container text-on-secondary-container hover:bg-amber-400') }}" type="submit" @disabled($isReserved)>
                                                            <span class="material-symbols-outlined text-[16px]">{{ $isReserved ? 'bookmark_added' : ($available > 0 ? 'book_online' : 'notification_add') }}</span>
                                                            {{ $isReserved ? 'Reserved' : ($available > 0 ? 'Reserve / Place Hold' : 'Join Hold Queue') }}
                                                        </button>
                                                    </form>
                                                @else
                                                    <a class="flex h-9 items-center gap-1.5 rounded-lg bg-tertiary px-4 text-xs font-bold text-white" href="{{ route('books.catalog', array_merge($currentParams, ['selected' => $book['id']])) }}"><span class="material-symbols-outlined text-[16px]">desk</span>Request at Counter</a>
                                                @endif
                                                <a class="flex h-9 items-center gap-1.5 rounded-lg border border-outline-variant px-3.5 text-xs font-semibold text-on-surface transition-colors hover:bg-surface-container" href="{{ route('books.catalog', array_merge($currentParams, ['selected' => $book['id']])) }}"><span class="material-symbols-outlined text-[16px] text-primary">inventory_2</span>View Copies</a>
                                                <a class="flex h-9 items-center gap-1 rounded-lg border border-outline-variant px-3 text-xs font-medium text-outline transition-colors hover:bg-surface-container hover:text-on-surface" href="https://www.google.com/search?q={{ urlencode($book['title'].' citation') }}" target="_blank" rel="noopener"><span class="material-symbols-outlined text-[16px]">format_quote</span>Cite</a>
                                            </div>
                                            <span class="text-[10px] font-semibold text-outline">{{ $book['subject'] }} - {{ $book['loan'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-2xl border border-outline-variant bg-surface-container-lowest p-8 text-center">
                                <p class="font-headline-sm text-lg font-bold text-on-surface">No catalog titles found</p>
                                <p class="mt-2 text-sm text-on-surface-variant">Try a broader keyword or clear the filters.</p>
                                <a class="mt-4 inline-flex rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-on-primary" href="{{ route('books.catalog') }}">Clear Search</a>
                            </div>
                        @endforelse

                        <div class="flex flex-col items-center justify-between gap-3 rounded-xl border border-outline-variant bg-surface-container-lowest p-4 shadow-sm sm:flex-row">
                            <span class="text-sm text-outline">Page <strong class="text-on-surface">1</strong> of <strong class="text-on-surface">1</strong> ({{ $books->count() }} titles)</span>
                            <div class="flex items-center gap-1">
                                <span class="rounded-lg border border-outline-variant px-3 py-1.5 text-sm font-medium text-outline opacity-50">Previous</span>
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary text-sm font-bold text-on-primary">1</span>
                                <span class="rounded-lg border border-outline-variant px-3 py-1.5 text-sm font-medium text-outline opacity-50">Next</span>
                            </div>
                        </div>
                    </div>

                    <aside class="space-y-4">
                        <section class="rounded-xl border border-outline-variant bg-surface-container-lowest p-5 shadow-sm">
                            <div class="flex flex-col gap-2 border-b border-outline-variant pb-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-[20px] text-primary">inventory_2</span>
                                        <h3 class="font-headline-sm text-base font-bold text-on-surface">Physical Copy Tracking & Shelf Audit</h3>
                                    </div>
                                    <p class="text-xs text-outline">Selected volume: <strong class="text-on-surface">{{ $selectedBook['title'] }}</strong></p>
                                </div>
                                <span class="inline-flex items-center gap-1 rounded bg-surface-container px-2.5 py-1 text-[10px] font-semibold text-on-surface-variant"><span class="material-symbols-outlined text-[14px] text-primary">sensors</span>RFID Synced</span>
                            </div>

                            <div class="mt-4 flex items-center gap-3">
                                <div class="book-cover relative h-24 w-16 flex-shrink-0 overflow-hidden rounded bg-primary-container text-white">
                                    <img alt="{{ $selectedBook['title'] }} cover" class="h-full w-full object-cover" loading="lazy" src="{{ $selectedBook['cover_url'] }}">
                                </div>
                                <div>
                                    <h4 class="line-clamp-2 text-sm font-bold text-on-surface">{{ $selectedBook['title'] }}</h4>
                                    <p class="text-xs text-outline">{{ $selectedBook['author'] }} - {{ $selectedBook['year'] }}</p>
                                    <p class="mt-1 text-[10px] font-bold text-primary">{{ $selectedBook['location'] }}</p>
                                </div>
                            </div>

                            <div class="custom-scrollbar mt-4 max-h-[420px] overflow-auto">
                                <table class="w-full text-left text-xs">
                                    <thead class="border-y border-outline-variant bg-surface-container-low text-[10px] uppercase tracking-wider text-outline">
                                        <tr>
                                            <th class="px-3 py-2">Barcode</th>
                                            <th class="px-3 py-2">Copy</th>
                                            <th class="px-3 py-2">Status</th>
                                            <th class="px-3 py-2">Location</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-outline-variant/60">
                                        @foreach ($selectedBook['copies'] as $copy)
                                            @php
                                                $copyTone = match ($copy['status']) { 'available' => 'emerald', 'on_loan' => 'amber', 'reference' => 'blue', default => 'slate' };
                                                $copyStatus = str($copy['status'])->replace('_', ' ')->title();
                                            @endphp
                                            <tr class="hover:bg-surface-container-low/50">
                                                <td class="px-3 py-2 font-mono font-semibold {{ $copyTone === 'emerald' ? 'text-primary' : 'text-outline' }}">{{ $copy['barcode'] }}</td>
                                                <td class="px-3 py-2 text-outline">{{ $copy['copy'] }}</td>
                                                <td class="px-3 py-2"><span class="rounded px-2 py-0.5 text-[10px] font-bold {{ $copyTone === 'emerald' ? 'bg-emerald-100 text-emerald-800' : ($copyTone === 'amber' ? 'bg-amber-100 text-amber-900' : 'bg-blue-100 text-blue-900') }}">{{ $copyStatus }}</span></td>
                                                <td class="px-3 py-2 font-medium text-on-surface">{{ $copy['location'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <form class="mt-4" method="POST" action="{{ route('books.hold', $selectedBook['id']) }}">
                                @csrf
                                <button class="flex w-full items-center justify-center gap-2 rounded-xl bg-primary-container px-4 py-2.5 text-xs font-bold text-on-primary shadow-sm hover:bg-primary disabled:cursor-not-allowed disabled:bg-emerald-100 disabled:text-emerald-800" type="submit" @disabled(array_key_exists($selectedBook['id'], $reservations) || $selectedBook['policy'] === 'reference')>
                                    <span class="material-symbols-outlined text-base">bookmark_added</span>
                                    {{ array_key_exists($selectedBook['id'], $reservations) ? 'Reservation Requested' : ($selectedBook['policy'] === 'reference' ? 'Reference Only' : 'Request Book Reservation') }}
                                </button>
                            </form>
                        </section>

                        <section class="rounded-xl bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 p-4 text-white shadow-sm">
                            <div class="flex items-start gap-3">
                                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-white/10 text-amber-300">
                                    <span class="material-symbols-outlined text-[24px]">hub</span>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-white">Institutional Online Database Subscriptions</h4>
                                    <p class="mt-1 text-xs text-slate-300">Authenticated access to IEEE Xplore, ScienceDirect, ACM Digital Library, and national repositories.</p>
                                    <a class="mt-3 inline-flex rounded-lg bg-secondary-container px-3 py-2 text-xs font-bold text-on-secondary-container hover:bg-amber-400" href="{{ route('staff.section', 'support') }}">OpenAthens Single Sign-On</a>
                                </div>
                            </div>
                        </section>
                    </aside>
                </div>
            </section>

            <footer class="mt-auto border-t border-outline-variant bg-surface-container-lowest px-4 py-6 text-sm text-outline lg:px-8">
                <div class="flex flex-col items-center justify-between gap-4 md:flex-row">
                    <div class="flex items-center gap-3">
                        @include('partials.celtech-logo', ['class' => 'h-8 w-8 rounded-full border border-secondary-container bg-white object-contain p-0.5'])
                        <div>
                            <p class="text-xs font-bold text-on-surface">Central Luzon College of Science and Technology (CLCST) - Founded 1959</p>
                            <p class="text-[10px] text-outline">Olongapo City Main Campus & San Fernando Extension - Integrated Library Automation System</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 text-[10px] font-semibold">
                        <span class="inline-flex items-center gap-1 text-emerald-800"><span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>CHED CMO No. 25 Compliant</span>
                        <span>-</span>
                        <span>ISO 9001:2015 QA Certified</span>
                        <span>-</span>
                        <span>RA 10173 Data Privacy</span>
                    </div>
                </div>
            </footer>
        </main>
    </div>
</body>
</html>
