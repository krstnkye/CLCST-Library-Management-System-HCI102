<!DOCTYPE html>
<html class="h-full bg-background" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CLCST School Library - Online Public Access Catalog</title>
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
                        'body-md': ['Inter', 'sans-serif'],
                        'headline-sm': ['Plus Jakarta Sans', 'sans-serif'],
                        'headline-lg': ['Plus Jakarta Sans', 'sans-serif']
                    }
                }
            }
        };
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            font-size: 20px;
            line-height: 1;
            vertical-align: middle;
        }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f2f3ff; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #c0c9bd; border-radius: 4px; }
        .book-cover {
            box-shadow: 9px 10px 18px rgba(15, 23, 42, 0.18);
            transform: perspective(900px) rotateY(-5deg);
        }
        .book-cover::before {
            background: linear-gradient(90deg, rgba(0,0,0,0.35), rgba(255,255,255,0.12), rgba(0,0,0,0.08));
            content: "";
            inset: 0 auto 0 0;
            position: absolute;
            width: 14px;
            z-index: 2;
        }
        .book-cover::after {
            background: linear-gradient(90deg, #e5e7eb, #ffffff);
            content: "";
            inset: 7px -7px 7px auto;
            position: absolute;
            width: 7px;
            border-radius: 0 8px 8px 0;
            z-index: -1;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="flex h-full flex-col bg-surface-container-low font-body-md text-on-surface antialiased">
    @php
        $activeLoanCount = count($activeLoans);
        $reservationCount = count($reservations);
        $selectedAvailable = collect($selectedBook['copies'])->where('status', 'available')->count();
        $currentParams = request()->except('page');
    @endphp

    <header class="sticky top-0 z-40 flex h-16 w-full items-center justify-between border-b border-outline-variant bg-surface-container-lowest px-4 shadow-sm md:px-6">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary text-sm font-extrabold text-white shadow-sm">CL</div>
            <div>
                <div class="font-headline-sm text-base font-bold leading-tight tracking-wide text-primary">CLCST SCHOOL LIBRARY</div>
                <div class="text-[10px] font-bold uppercase tracking-wider text-secondary">Online Public Access Catalog (OPAC)</div>
            </div>
        </div>
        <div class="flex items-center gap-3 md:gap-4">
            <a class="hidden items-center gap-2 rounded-full border border-outline-variant/60 bg-surface-container px-3 py-1 text-xs font-medium text-on-surface-variant sm:flex" href="{{ route('student.section', 'account') }}">
                <span class="h-2 w-2 rounded-full bg-primary"></span>
                Student Patron: <strong class="font-semibold text-primary">Juan Dela Cruz (2024-10492-OL)</strong>
            </a>
            <a class="relative rounded-full p-2 text-on-surface-variant transition-colors hover:bg-surface-container-low hover:text-on-surface" href="{{ route('student.section', 'notifications') }}" title="Notifications">
                <span class="material-symbols-outlined">notifications</span>
                @if ($reservationCount > 0)
                    <span class="absolute right-1.5 top-1.5 h-2 w-2 rounded-full bg-error"></span>
                @endif
            </a>
            <a class="hidden rounded-full p-2 text-on-surface-variant transition-colors hover:bg-surface-container-low hover:text-on-surface sm:block" href="{{ route('student.section', 'support') }}" title="Help & Reference Desk">
                <span class="material-symbols-outlined">help</span>
            </a>
            <div class="flex items-center gap-2 border-l border-outline-variant pl-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary-container text-xs font-bold text-on-primary shadow-sm">JD</div>
                <div class="hidden flex-col text-left lg:flex">
                    <span class="text-xs font-semibold text-primary">Juan Dela Cruz</span>
                    <span class="text-[10px] text-on-surface-variant">BS Computer Science III</span>
                </div>
            </div>
        </div>
    </header>

    <div class="flex flex-1 overflow-hidden">
        <aside class="z-30 hidden w-64 flex-shrink-0 flex-col justify-between border-r border-outline-variant bg-surface-container-lowest md:flex">
            <div class="custom-scrollbar flex flex-col gap-1 overflow-y-auto p-4">
                <a class="mb-3 flex items-center gap-3 rounded-xl border border-primary/20 bg-primary/5 p-3" href="{{ route('student.section', 'account') }}">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary-container font-bold text-white">
                        <span class="material-symbols-outlined">badge</span>
                    </div>
                    <div class="overflow-hidden">
                        <p class="truncate text-xs font-bold text-primary">CLCST e-Pass</p>
                        <p class="truncate text-[10px] text-on-surface-variant">ID: 2024-10492-OL</p>
                    </div>
                </a>

                <span class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-outline">OPAC Navigation</span>
                <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 font-medium text-on-surface-variant hover:bg-surface-container hover:text-on-surface" href="{{ route('student.dashboard') }}">
                    <span class="material-symbols-outlined">dashboard</span><span class="text-xs font-semibold">Dashboard</span>
                </a>
                <a class="flex items-center gap-3 rounded-lg bg-primary-container px-3 py-2.5 font-semibold text-on-primary shadow-sm" href="{{ route('books.catalog') }}">
                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">menu_book</span><span class="text-xs font-semibold">Books (Catalog)</span>
                </a>
                <a class="flex items-center justify-between rounded-lg px-3 py-2.5 font-medium text-on-surface-variant hover:bg-surface-container hover:text-on-surface" href="{{ route('student.section', 'borrow-return') }}">
                    <span class="flex items-center gap-3"><span class="material-symbols-outlined">swap_horiz</span><span class="text-xs font-semibold">Borrow/Return</span></span>
                    <span class="rounded-full bg-secondary-container px-2 py-0.5 text-[10px] font-bold text-on-secondary-container">{{ $activeLoanCount }}</span>
                </a>
                <a class="flex items-center justify-between rounded-lg px-3 py-2.5 font-medium text-on-surface-variant hover:bg-surface-container hover:text-on-surface" href="{{ route('student.section', 'reservations') }}">
                    <span class="flex items-center gap-3"><span class="material-symbols-outlined">book_online</span><span class="text-xs font-semibold">Reservations</span></span>
                    <span class="rounded-full bg-surface-container px-2 py-0.5 text-[10px] font-bold text-on-surface-variant">{{ $reservationCount }}</span>
                </a>
                <a class="flex items-center justify-between rounded-lg px-3 py-2.5 font-medium text-on-surface-variant hover:bg-surface-container hover:text-on-surface" href="{{ route('student.section', 'notifications') }}">
                    <span class="flex items-center gap-3"><span class="material-symbols-outlined">notifications</span><span class="text-xs font-semibold">Notifications</span></span>
                    <span class="rounded-full bg-error-container px-2 py-0.5 text-[10px] font-bold text-error">2</span>
                </a>
                <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 font-medium text-on-surface-variant hover:bg-surface-container hover:text-on-surface" href="{{ route('student.section', 'history') }}">
                    <span class="material-symbols-outlined">history</span><span class="text-xs font-semibold">Borrowing History</span>
                </a>
                <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 font-medium text-on-surface-variant hover:bg-surface-container hover:text-on-surface" href="{{ route('student.section', 'fines') }}">
                    <span class="material-symbols-outlined">payments</span><span class="text-xs font-semibold">Fines & Penalties</span>
                </a>
                <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 font-medium text-on-surface-variant hover:bg-surface-container hover:text-on-surface" href="{{ route('student.section', 'account') }}">
                    <span class="material-symbols-outlined">manage_accounts</span><span class="text-xs font-semibold">My Account</span>
                </a>

                <div class="mt-4 overflow-hidden rounded-xl border border-outline-variant bg-surface-container-low shadow-sm">
                    <div class="border-b border-outline-variant/70 bg-surface-container-lowest p-3">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 text-xs font-bold text-primary">
                                <span class="material-symbols-outlined text-base" style="font-variation-settings: 'FILL' 1;">barcode_scanner</span>
                                <span>Shelf Scanner</span>
                            </div>
                            <span class="inline-flex items-center gap-1 rounded-full bg-primary-fixed px-2 py-0.5 text-[10px] font-bold text-primary">
                                <span class="h-1.5 w-1.5 rounded-full bg-primary"></span>LIVE
                            </span>
                        </div>
                        <p class="mt-1 text-[11px] leading-tight text-on-surface-variant">Scan a spine ISBN, accession barcode, or shelf copy label to open the matching catalog record.</p>
                    </div>

                    <div class="space-y-3 p-3">
                        <div class="relative overflow-hidden rounded-lg border border-primary/20 bg-surface-container-lowest p-3">
                            <div class="absolute left-0 right-0 top-1/2 h-0.5 bg-secondary-container shadow-[0_0_10px_rgba(253,196,37,0.8)]"></div>
                            <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-outline">
                                <span>Scanner Ready</span>
                                <span class="font-mono text-primary">Station 04</span>
                            </div>
                            <div class="mt-3 flex h-10 items-center justify-center rounded bg-surface-container">
                                <div class="flex h-7 items-stretch gap-[2px]">
                                    @foreach ([1, 3, 1, 2, 1, 4, 2, 1, 3, 1, 2, 4, 1, 2, 1, 3] as $barWidth)
                                        <span class="bg-on-surface" style="width: {{ $barWidth }}px"></span>
                                    @endforeach
                                </div>
                            </div>
                            <div class="mt-2 text-center font-mono text-[10px] font-bold tracking-widest text-on-surface-variant">1049281</div>
                        </div>

                        <form class="space-y-2" method="GET" action="{{ route('books.catalog') }}">
                            <input name="field" type="hidden" value="all">
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-base text-outline">qr_code_scanner</span>
                                <input class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest py-2 pl-8 pr-3 text-xs text-on-surface placeholder:text-outline focus:border-primary focus:ring-1 focus:ring-primary" name="q" placeholder="Enter barcode or ISBN" value="1049281">
                            </div>
                            <button class="flex w-full items-center justify-center gap-1.5 rounded-lg bg-primary px-3 py-2 text-xs font-bold text-on-primary shadow-sm transition-colors hover:bg-primary-container" type="submit">
                                <span class="material-symbols-outlined text-base">barcode_reader</span>Scan Barcode
                            </button>
                        </form>

                        <div class="grid grid-cols-2 gap-2 text-[10px]">
                            <div class="rounded-lg border border-outline-variant/70 bg-surface-container-lowest p-2">
                                <span class="block font-bold text-primary">Accession Sync</span>
                                <span class="text-outline">Ready</span>
                            </div>
                            <div class="rounded-lg border border-outline-variant/70 bg-surface-container-lowest p-2">
                                <span class="block font-bold text-primary">Stacks Mode</span>
                                <span class="text-outline">Level 2</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-2 border-t border-outline-variant bg-surface-container-lowest p-4">
                <a class="flex items-center gap-3 rounded-lg px-3 py-2 font-medium text-on-surface-variant hover:bg-surface-container hover:text-on-surface" href="{{ route('student.section', 'support') }}">
                    <span class="material-symbols-outlined">help_outline</span><span class="text-xs font-semibold">Help & Support</span>
                </a>
                <a class="flex items-center gap-3 rounded-lg px-3 py-2 font-medium text-error hover:bg-error-container/30" href="{{ route('login') }}">
                    <span class="material-symbols-outlined">logout</span><span class="text-xs font-semibold">Log Out</span>
                </a>
                <p class="px-3 pt-2 text-[10px] leading-tight text-outline">Patron Mode: Read and search only. Circulation handled at Counter 1.</p>
            </div>
        </aside>

        <main class="custom-scrollbar flex flex-1 flex-col overflow-y-auto">
            <section class="border-b border-outline-variant bg-surface-container-lowest px-4 py-6 shadow-sm sm:px-8">
                <div class="mx-auto flex max-w-7xl flex-col gap-4">
                    @if (session('status'))
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                        <div>
                            <div class="flex items-center gap-2 text-primary">
                                <span class="material-symbols-outlined">local_library</span>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-secondary">CLCST Central Library & Learning Resource Center</span>
                            </div>
                            <h1 class="font-headline-lg mt-1 text-2xl font-bold text-on-surface">Book Catalog & Resource Discovery</h1>
                            <p class="mt-0.5 text-sm text-on-surface-variant">Search 14,250+ print volumes, textbooks, thesis manuscripts, and reference journals across CLCST Library Stacks.</p>
                        </div>

                        <form class="flex items-center gap-3 self-start rounded-xl border border-secondary/30 bg-yellow-50 px-3.5 py-2 sm:self-auto" method="POST" action="{{ route('loans.renew', 'loan-clean-code') }}">
                            @csrf
                            <span class="material-symbols-outlined text-secondary" style="font-variation-settings: 'FILL' 1;">notification_important</span>
                            <div class="text-left">
                                <p class="text-[10px] font-bold leading-tight text-secondary">1 Book Due in 2 Days</p>
                                <p class="text-xs leading-tight text-on-surface-variant">Clean Code (QA76.76) - Oct 28</p>
                            </div>
                            <button class="ml-2 rounded bg-primary px-2.5 py-1 text-[10px] font-bold text-on-primary hover:bg-primary-container" type="submit">Renew</button>
                        </form>
                    </div>

                    <form class="flex flex-col items-stretch gap-2 rounded-2xl border border-outline-variant bg-surface-container-low p-2 shadow-inner sm:p-2.5 md:flex-row" method="GET" action="{{ route('books.catalog') }}">
                        <div class="flex flex-shrink-0 items-center rounded-xl border border-outline-variant bg-surface-container-lowest px-3 py-1.5 focus-within:ring-2 focus-within:ring-primary">
                            <span class="material-symbols-outlined mr-2 text-base text-outline">filter_alt</span>
                            <select class="cursor-pointer border-0 bg-transparent p-0 pr-6 text-xs font-semibold text-on-surface focus:ring-0" name="field">
                                @foreach (['all' => 'All Fields', 'title' => 'Title', 'author' => 'Author', 'isbn' => 'ISBN / ISSN', 'call' => 'Subject / Call Number'] as $value => $label)
                                    <option value="{{ $value }}" @selected($field === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex flex-1 items-center rounded-xl border border-outline-variant bg-surface-container-lowest px-3 py-2 focus-within:border-primary focus-within:ring-1 focus-within:ring-primary">
                            <span class="material-symbols-outlined mr-2.5 text-outline">search</span>
                            <input class="w-full border-0 bg-transparent p-0 text-sm text-on-surface placeholder:text-outline focus:ring-0" name="q" placeholder="Search by keywords, title, author, call number, ISBN, or barcode..." type="text" value="{{ $query }}">
                            <a class="rounded-full p-1 text-outline hover:text-on-surface" href="{{ route('books.catalog') }}" title="Clear search">
                                <span class="material-symbols-outlined text-base">close</span>
                            </a>
                        </div>

                        <input name="category" type="hidden" value="{{ $category }}">
                        <input name="availability" type="hidden" value="{{ $availability }}">
                        <input name="sort" type="hidden" value="{{ $sort }}">

                        <div class="flex items-center gap-2">
                            <button class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-primary-container px-6 py-2.5 text-sm font-semibold text-on-primary shadow-sm hover:bg-primary md:flex-none" type="submit">
                                <span class="material-symbols-outlined text-base">search</span>Search Catalog
                            </button>
                            <a class="rounded-xl border border-outline-variant bg-surface-container-lowest p-2.5 text-primary hover:bg-surface-container" href="{{ route('books.catalog', ['q' => '1049281']) }}" title="Scan Shelf Barcode">
                                <span class="material-symbols-outlined">photo_camera</span>
                            </a>
                        </div>
                    </form>

                    <div class="custom-scrollbar flex items-center gap-2 overflow-x-auto pb-1 text-nowrap">
                        <span class="pr-1 text-[10px] font-bold uppercase text-outline">Filter by:</span>
                        <a class="rounded-full px-3.5 py-1.5 text-xs font-semibold {{ $category === 'all' && $availability === 'all' ? 'bg-primary text-on-primary shadow-sm' : 'border border-outline-variant/60 bg-surface-container text-on-surface hover:bg-surface-container-high' }}" href="{{ route('books.catalog', array_merge($currentParams, ['category' => 'all', 'availability' => 'all'])) }}">All Books</a>
                        @foreach ($categories as $filterCategory)
                            @php
                                $filterLabel = match ($filterCategory) {
                                    'Computer Studies & IT' => 'Computer Studies & IT (004-006)',
                                    'Engineering' => 'Engineering (620)',
                                    'Business & Accountancy' => 'Business & Accountancy (650)',
                                    default => $filterCategory,
                                };
                            @endphp
                            <a class="rounded-full px-3.5 py-1.5 text-xs font-semibold {{ $category === $filterCategory ? 'bg-primary text-on-primary shadow-sm' : 'border border-outline-variant/60 bg-surface-container text-on-surface hover:bg-surface-container-high' }}" href="{{ route('books.catalog', array_merge($currentParams, ['category' => $filterCategory, 'availability' => 'all'])) }}">{{ $filterLabel }}</a>
                        @endforeach
                        <a class="rounded-full px-3.5 py-1.5 text-xs font-semibold {{ $availability === 'reference' ? 'bg-primary text-on-primary shadow-sm' : 'border border-outline-variant/60 bg-surface-container text-on-surface hover:bg-surface-container-high' }}" href="{{ route('books.catalog', array_merge($currentParams, ['availability' => 'reference', 'category' => 'all'])) }}">Reference Only</a>
                        <a class="rounded-full px-3.5 py-1.5 text-xs font-semibold {{ $availability === 'available' ? 'bg-primary text-on-primary shadow-sm' : 'border border-outline-variant/60 bg-surface-container text-on-surface hover:bg-surface-container-high' }}" href="{{ route('books.catalog', array_merge($currentParams, ['availability' => 'available'])) }}">Available Today</a>
                    </div>
                </div>
            </section>

            <section class="flex-1 px-4 py-6 sm:px-8">
                <div class="mx-auto flex max-w-7xl flex-col gap-6 xl:flex-row">
                    <div class="min-w-0 flex-1 space-y-4">
                        <div class="flex flex-col justify-between gap-3 rounded-xl border border-outline-variant bg-surface-container-lowest px-4 py-3 shadow-sm sm:flex-row sm:items-center">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-secondary">Search Results</p>
                                <h2 class="font-headline-sm text-base font-bold text-on-surface">{{ $books->count() }} titles matched your query</h2>
                            </div>
                            <form method="GET" action="{{ route('books.catalog') }}">
                                @foreach (request()->except('sort') as $key => $value)
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endforeach
                                <select class="rounded-lg border border-outline-variant bg-surface-container px-3 py-1.5 text-xs font-semibold text-on-surface" name="sort" onchange="this.form.submit()">
                                    <option value="relevance" @selected($sort === 'relevance')>Sort: Relevance</option>
                                    <option value="title" @selected($sort === 'title')>Sort: Title</option>
                                    <option value="newest" @selected($sort === 'newest')>Sort: Newest</option>
                                    <option value="available" @selected($sort === 'available')>Sort: Available Copies</option>
                                </select>
                            </form>
                        </div>

                        @forelse ($books as $book)
                            @php
                                $available = collect($book['copies'])->where('status', 'available')->count();
                                $total = count($book['copies']);
                                $isReserved = array_key_exists($book['id'], $reservations);
                                $availabilityTone = $book['policy'] === 'reference' ? 'slate' : ($available > 0 ? 'emerald' : 'amber');
                            @endphp
                            <article class="flex flex-col gap-4 rounded-2xl border border-outline-variant bg-surface-container-lowest p-4 shadow-sm hover:shadow-md sm:flex-row">
                                <div class="book-cover relative h-40 w-full flex-shrink-0 overflow-hidden rounded-xl bg-gradient-to-br {{ $book['cover'] }} text-white sm:h-36 sm:w-24">
                                    <img
                                        alt="{{ $book['title'] }} cover"
                                        class="h-full w-full object-cover"
                                        loading="lazy"
                                        onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');"
                                        src="{{ $book['cover_url'] }}"
                                    >
                                    <div class="hidden h-full w-full flex-col justify-between p-2">
                                        <div class="flex items-center justify-between">
                                            <span class="rounded bg-white/80 px-1 text-[9px] font-bold text-slate-900">{{ $book['badge'] }}</span>
                                            <span class="material-symbols-outlined text-sm text-white/80">{{ $book['icon'] }}</span>
                                        </div>
                                        <div class="my-auto text-center">
                                            <p class="font-headline-sm text-xs font-bold leading-tight">{{ \Illuminate\Support\Str::limit($book['title'], 34) }}</p>
                                            <p class="mt-1 text-[9px] text-white/80">{{ \Illuminate\Support\Str::limit($book['author'], 18) }}</p>
                                        </div>
                                        <div class="text-center font-mono text-[8px] tracking-widest text-white/70">{{ \Illuminate\Support\Str::before($book['call'], ' ') }}</div>
                                    </div>
                                </div>

                                <div class="flex flex-1 flex-col justify-between">
                                    <div>
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-secondary">{{ $book['subject'] }} - {{ $book['publisher'] }}</span>
                                        <h2 class="font-headline-sm text-base font-bold text-on-surface">{{ $book['title'] }}</h2>
                                        <p class="mt-0.5 text-sm text-on-surface-variant">by <span class="font-semibold text-on-surface">{{ $book['author'] }}</span> - {{ $book['year'] }}</p>
                                        <div class="mt-2.5 grid grid-cols-1 gap-x-4 gap-y-1 border-t border-outline-variant/50 pt-2 text-xs sm:grid-cols-2">
                                            <div><span class="font-medium text-outline">Call No:</span> <span class="rounded bg-primary/10 px-1.5 py-0.5 font-mono font-semibold text-primary">{{ $book['call'] }}</span></div>
                                            <div><span class="font-medium text-outline">ISBN-13:</span> <span class="font-mono font-medium text-on-surface">{{ $book['isbn'] }}</span></div>
                                            <div><span class="font-medium text-outline">Location:</span> <span class="font-semibold text-on-surface">{{ $book['location'] }}</span></div>
                                            <div><span class="font-medium text-outline">Loan:</span> <span class="{{ $book['policy'] === 'reference' ? 'font-medium text-error' : 'text-on-surface' }}">{{ $book['loan'] }}</span></div>
                                        </div>
                                    </div>
                                    <div class="mt-3 flex flex-wrap items-center justify-between gap-2 border-t border-outline-variant/40 pt-2.5">
                                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[10px] font-bold {{ $availabilityTone === 'emerald' ? 'bg-emerald-100 text-emerald-800' : ($availabilityTone === 'amber' ? 'bg-amber-100 text-amber-900' : 'bg-slate-100 text-slate-800') }}">
                                            <span class="h-2 w-2 rounded-full {{ $availabilityTone === 'emerald' ? 'bg-emerald-600' : ($availabilityTone === 'amber' ? 'bg-amber-600' : 'bg-slate-500') }}"></span>
                                            {{ $book['policy'] === 'reference' ? 'Reference Only' : $available.' of '.$total.' Copies Available' }}
                                        </span>
                                        <div class="flex items-center gap-2">
                                            <a class="flex items-center gap-1 rounded-lg bg-surface-container px-3 py-1.5 text-xs font-semibold text-primary hover:bg-surface-container-high" href="{{ route('books.catalog', array_merge($currentParams, ['selected' => $book['id']])) }}"><span class="material-symbols-outlined text-sm">visibility</span>View Details & Copies</a>
                                            @if ($book['policy'] !== 'reference')
                                                <form method="POST" action="{{ route('books.hold', $book['id']) }}">
                                                    @csrf
                                                    <button class="flex items-center gap-1 rounded-lg px-3.5 py-1.5 text-xs font-semibold shadow-sm {{ $isReserved ? 'bg-emerald-100 text-emerald-800' : 'bg-primary-container text-on-primary hover:bg-primary' }}" type="submit" @disabled($isReserved)><span class="material-symbols-outlined text-sm">{{ $isReserved ? 'bookmark_added' : 'bookmark_add' }}</span>{{ $isReserved ? 'Reserved' : ($available > 0 ? 'Place Hold / Reserve' : 'Place Hold Queue') }}</button>
                                                </form>
                                            @else
                                                <a class="flex items-center gap-1 rounded-lg bg-surface-container px-3.5 py-1.5 text-xs font-semibold text-primary hover:bg-surface-container-high" href="{{ route('books.catalog', array_merge($currentParams, ['selected' => $book['id']])) }}"><span class="material-symbols-outlined text-sm">menu_book</span>View In-Library Copy</a>
                                            @endif
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
                    </div>

                    <aside class="w-full flex-shrink-0 xl:w-80">
                        <div class="sticky top-20 flex flex-col gap-4 rounded-2xl border border-outline-variant bg-surface-container-lowest p-5 shadow-md">
                            <div class="flex items-center justify-between border-b border-outline-variant/60 pb-2">
                                <div class="flex items-center gap-1.5 text-sm font-bold text-primary"><span class="material-symbols-outlined">inventory_2</span><span>Physical Copies & Stacks</span></div>
                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800">{{ $selectedAvailable }} Available</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="book-cover relative h-20 w-14 flex-shrink-0 overflow-hidden rounded bg-primary-container text-[8px] text-white">
                                    <img alt="{{ $selectedBook['title'] }} cover" class="h-full w-full object-cover" loading="lazy" src="{{ $selectedBook['cover_url'] }}">
                                </div>
                                <div><h3 class="line-clamp-1 text-sm font-bold text-on-surface">{{ $selectedBook['title'] }}</h3><p class="text-xs text-on-surface-variant">{{ $selectedBook['author'] }} - {{ $selectedBook['year'] }}</p><p class="mt-0.5 text-[10px] font-bold text-primary">{{ $selectedBook['location'] }}</p></div>
                            </div>
                            <div class="flex flex-col gap-2">
                                <span class="text-[10px] font-bold uppercase text-outline">Active Copy Inventory</span>
                                <div class="custom-scrollbar max-h-60 space-y-2 overflow-y-auto pr-1">
                                    @foreach ($selectedBook['copies'] as $copy)
                                        @php
                                            $tone = match ($copy['status']) { 'available' => 'emerald', 'on_loan' => 'amber', 'reference' => 'slate', default => 'blue' };
                                            $statusLabel = str($copy['status'])->replace('_', ' ')->title();
                                        @endphp
                                        <div class="flex items-center justify-between rounded-xl border border-outline-variant/70 bg-surface-container-low p-2.5 text-xs {{ $tone === 'amber' ? 'opacity-80' : '' }}">
                                            <div><div class="flex items-center gap-1.5"><span class="font-mono font-bold {{ $tone === 'amber' ? 'text-outline' : 'text-primary' }}">{{ $copy['copy'] }}</span><span class="rounded px-1.5 py-0.5 text-[10px] font-bold {{ $tone === 'emerald' ? 'bg-emerald-100 text-emerald-800' : ($tone === 'amber' ? 'bg-amber-100 text-amber-900' : ($tone === 'slate' ? 'bg-slate-100 text-slate-800' : 'bg-blue-100 text-blue-900')) }}">{{ $statusLabel }}</span></div><span class="mt-0.5 block text-[11px] text-outline">{{ $copy['location'] }} - Barcode: {{ $copy['barcode'] }}</span></div>
                                            <span class="material-symbols-outlined text-base {{ $tone === 'emerald' ? 'text-primary' : ($tone === 'amber' ? 'text-amber-600' : 'text-blue-600') }}">{{ $tone === 'emerald' ? 'check_circle' : ($tone === 'amber' ? 'schedule' : 'lock') }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="rounded-xl border border-outline-variant/60 bg-surface-container p-3"><div class="flex items-center justify-between text-[10px] font-bold"><span>Your Loan Quota</span><span class="text-primary">{{ $activeLoanCount }} / 5 Books Active</span></div><div class="mt-1 h-2 w-full overflow-hidden rounded-full bg-outline-variant/40"><div class="h-full rounded-full bg-primary" style="width: {{ min($activeLoanCount / 5 * 100, 100) }}%"></div></div><p class="mt-1 text-[11px] text-on-surface-variant">You can borrow {{ max(5 - $activeLoanCount, 0) }} more books or place 1 active hold.</p></div>
                            <form method="POST" action="{{ route('books.hold', $selectedBook['id']) }}">@csrf<button class="flex w-full items-center justify-center gap-2 rounded-xl bg-primary-container px-4 py-2.5 text-xs font-bold text-on-primary shadow-sm hover:bg-primary disabled:cursor-not-allowed disabled:bg-emerald-100 disabled:text-emerald-800" type="submit" @disabled(array_key_exists($selectedBook['id'], $reservations) || $selectedBook['policy'] === 'reference')><span class="material-symbols-outlined text-base">bookmark_added</span>{{ array_key_exists($selectedBook['id'], $reservations) ? 'Reservation Requested' : ($selectedBook['policy'] === 'reference' ? 'Reference Only' : 'Request Book Reservation') }}</button></form>
                            <div class="grid grid-cols-2 gap-2"><a class="flex items-center justify-center gap-1 rounded-xl border border-outline-variant bg-surface-container px-2.5 py-2 text-xs font-semibold text-on-surface hover:bg-surface-container-high" href="https://www.google.com/search?q={{ urlencode($selectedBook['title'].' citation APA IEEE') }}" target="_blank" rel="noopener"><span class="material-symbols-outlined text-sm">format_quote</span>Cite</a><a class="flex items-center justify-center gap-1 rounded-xl border border-outline-variant bg-surface-container px-2.5 py-2 text-xs font-semibold text-on-surface hover:bg-surface-container-high" href="#stack-map"><span class="material-symbols-outlined text-sm">map</span>Stack Map</a></div>
                            <div id="stack-map" class="rounded-xl border border-outline-variant bg-surface-container-low p-3 text-[11px] text-on-surface-variant"><strong class="block text-primary">Stack direction:</strong>Main Library, {{ $selectedBook['location'] }}. Pick up confirmed reservations at Circulation Counter 1 within 48 hours.</div>
                        </div>
                    </aside>
                </div>
            </section>
            <footer class="mt-auto border-t border-outline-variant bg-surface-container-lowest px-4 py-6 text-on-surface-variant sm:px-8"><div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 text-xs md:flex-row"><div class="flex items-center gap-3"><div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full border border-outline-variant bg-primary text-[10px] font-extrabold text-white">CL</div><div><p class="font-bold text-on-surface">Central Luzon College of Science and Technology (CLCST)</p><p class="text-[11px] text-outline">Compliant with CHED CMO No. 25 - Computer Science & Library Information Standards</p></div></div><div class="flex flex-wrap items-center gap-4 font-medium text-outline"><span class="flex items-center gap-1"><span class="material-symbols-outlined text-sm text-primary">schedule</span>Turnstiles Open: 7:30 AM - 6:30 PM</span><span>|</span><a class="hover:text-primary" href="{{ route('student.section', 'circulation-policy') }}">Circulation Policy</a><span>|</span><a class="hover:text-primary" href="{{ route('student.section', 'opac-guide') }}">OPAC Guide</a><span>|</span><a class="hover:text-primary" href="{{ route('books.catalog', ['q' => 'repository']) }}">Digital Repository</a></div></div></footer>
        </main>
    </div>
</body>
</html>
