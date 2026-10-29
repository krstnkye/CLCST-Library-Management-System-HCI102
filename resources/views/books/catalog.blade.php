<!DOCTYPE html>
<html class="h-full bg-background text-on-surface" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CLCST Library - Search Books & Book Catalog Discovery</title>
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
    @include('partials.ui-motion')
</head>
<body class="clcst-portal {{ auth()->user()->role === 'student' ? 'clcst-role-student' : 'clcst-role-faculty' }} flex min-h-screen flex-col bg-[#F8FAFC] font-body-md text-on-surface antialiased">
    @php
        $currentParams = request()->except('page');
        $catalogUser = auth()->user();
        $isStudentPortal = $catalogUser->role === 'student';
        $notificationRoute = $isStudentPortal ? route('student.section', 'notifications') : route('staff.section', 'notifications');
        $supportRoute = $isStudentPortal ? route('student.section', 'support') : route('staff.section', 'support');
        $portalLabel = $isStudentPortal ? 'Student Portal' : 'Faculty Portal';
        $userSubtitle = $isStudentPortal
            ? trim(($catalogUser->patron_id ?: 'Student account').' - Student')
            : ($catalogUser->program_or_department ?: 'Faculty account');
        $copyLocation = fn ($copy) => $copy->status === 'on_loan' && $copy->activeLoan ? 'Due '.$copy->activeLoan->due_at->format('M d, Y') : ($copy->location ?: '-');
    @endphp

    <header class="clcst-topbar sticky top-0 z-40 flex h-16 w-full items-center justify-between border-b border-outline-variant bg-surface-container-lowest px-4 shadow-sm md:px-6">
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
                <input id="topbar-catalog-search" class="h-10 w-full rounded-lg border border-outline-variant bg-surface-container-low py-2 pl-10 pr-20 text-sm text-on-surface transition-all focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-primary" name="q" placeholder="Quick catalog lookup (Title, ISBN, Barcode, Author)..." type="text" value="{{ $query }}">
                <div class="absolute inset-y-0 right-0 flex items-center gap-1.5 pr-2">
                    <span class="hidden rounded border border-outline-variant bg-surface-container px-1.5 py-0.5 font-mono text-[11px] font-semibold text-outline sm:inline-flex">Book Catalog</span>
                    <button class="rounded p-1 text-outline transition-colors hover:bg-surface-container hover:text-primary" title="Scan a barcode or QR code" aria-label="Scan a barcode or QR code" type="button" data-scan data-scan-target="#topbar-catalog-search" data-scan-submit>
                        <span class="material-symbols-outlined text-[18px]">barcode_scanner</span>
                    </button>
                </div>
            </div>
        </form>

        <div class="flex items-center gap-3">
            <div class="clcst-status-pill hidden items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 xl:flex">
                <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-600"></span>
                <span class="text-[10px] font-semibold text-emerald-800">Book Catalog Online</span>
            </div>
            <a aria-label="Notifications ({{ $patronSummary['notifications'] }})" class="relative rounded-lg p-2 text-on-surface-variant transition-colors hover:bg-surface-container hover:text-on-surface" href="{{ $notificationRoute }}" title="Notifications">
                <span class="material-symbols-outlined text-[22px]">notifications</span>
                <span class="absolute right-1 top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-error px-1 text-[10px] font-bold text-white" data-notification-badge @if ($patronSummary['notifications'] === 0) hidden @endif>{{ $patronSummary['notifications'] }}</span>
            </a>
            <a class="rounded-lg p-2 text-on-surface-variant transition-colors hover:bg-surface-container hover:text-on-surface" href="{{ $supportRoute }}" title="Help and support">
                <span class="material-symbols-outlined text-[22px]">help</span>
            </a>
            <div class="mx-1 h-6 w-px bg-outline-variant"></div>
            <div class="flex items-center gap-3 pl-1">
                <div class="hidden text-right sm:block">
                    <p class="text-xs font-semibold leading-tight text-on-surface">{{ $catalogUser->name }}</p>
                    <p class="text-[10px] leading-tight text-outline">{{ $userSubtitle }}</p>
                </div>
                <div class="relative">
                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-primary font-headline-sm text-xs font-bold text-on-primary shadow-sm ring-2 ring-secondary-container">{{ $catalogUser->initials() }}</div>
                    <span class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full bg-emerald-500 ring-2 ring-white"></span>
                </div>
            </div>
        </div>
    </header>

    <div class="flex flex-1 overflow-hidden">
        @if(auth()->user()->role === 'student')
            @include('partials.student-sidebar', ['activePage' => 'catalog'])
        @else
            @include('partials.staff-sidebar', ['activePage' => 'search-books', 'portalRole' => 'faculty'])
        @endif

        <main class="clcst-main custom-scrollbar md:ml-64 flex flex-1 flex-col overflow-y-auto bg-[#F8FAFC]">
            <section class="border-b border-outline-variant bg-surface-container-lowest px-4 pb-6 pt-6 shadow-sm lg:px-8">
                @if (session('status') || $errors->any())
                    <div class="mb-4 space-y-2">@include('partials.flash')</div>
                @endif

                <nav class="mb-2 flex items-center gap-2 text-xs font-semibold text-outline">
                    <span>{{ $portalLabel }}</span>
                    <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                    <span>Catalog & Books</span>
                    <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                    <span class="font-semibold text-primary">Search Books</span>
                </nav>

                <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <div class="flex flex-wrap items-center gap-3">
                            <h1 class="font-headline-lg text-2xl font-bold tracking-tight text-on-surface">Search Books & Catalog Discovery</h1>
                            <span class="rounded-full border border-primary/20 bg-primary/10 px-2.5 py-0.5 text-[10px] font-bold text-primary">Book Catalog Online</span>
                        </div>
                        <p class="mt-1 max-w-3xl text-sm text-outline">Search {{ number_format($totalTitles) }} catalog titles and see which copies are on the shelf right now.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <button class="flex h-9 items-center gap-2 rounded-lg border border-outline-variant bg-surface-container-lowest px-3.5 text-sm font-semibold text-on-surface shadow-sm transition-colors hover:bg-surface-container" type="button" data-modal-open="advanced-search">
                            <span class="material-symbols-outlined text-[18px]">tune</span>
                            <span>Advanced Search</span>
                        </button>
                        <button class="flex h-9 items-center gap-2 rounded-lg border border-secondary-container bg-amber-50 px-3.5 text-sm font-semibold text-amber-950 transition-colors hover:bg-amber-100" type="button" data-scan data-scan-target="#catalog-search" data-scan-field="all" data-scan-submit>
                            <span class="material-symbols-outlined text-[18px] text-secondary">barcode_scanner</span>
                            <span>Scan Barcode / ISBN</span>
                        </button>
                    </div>
                </div>

                <form class="rounded-2xl border border-outline-variant bg-surface-container p-3 shadow-inner" method="GET" action="{{ route('books.catalog') }}">
                    <div class="grid grid-cols-1 gap-3 lg:grid-cols-[180px_1fr_160px]">
                        <select class="h-12 rounded-xl border border-outline-variant bg-surface-container-lowest px-3 text-sm font-semibold text-on-surface focus:border-primary focus:ring-primary" name="field">
                            @foreach (['all' => 'All Book Details', 'title' => 'Title', 'author' => 'Author', 'isbn' => 'ISBN / ISSN', 'call' => 'Library Call Number'] as $value => $label)
                                <option value="{{ $value }}" @selected($field === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-outline"><span class="material-symbols-outlined">search</span></span>
                            <button class="absolute inset-y-0 right-2 my-auto flex h-9 w-9 items-center justify-center rounded-lg text-outline transition-colors hover:bg-surface-container hover:text-primary" type="button" title="Scan a barcode or QR code" aria-label="Scan a barcode or QR code" data-scan data-scan-target="#catalog-search" data-scan-submit><span class="material-symbols-outlined">barcode_scanner</span></button>
                            <input id="catalog-search" class="h-12 w-full rounded-xl border border-outline-variant bg-surface-container-lowest pl-12 pr-12 text-sm text-on-surface placeholder:text-outline focus:border-primary focus:ring-primary" name="q" placeholder="Search by title, author, subject, call number, ISBN, barcode..." type="text" value="{{ $query }}">
                        </div>
                        <button class="flex h-12 items-center justify-center gap-2 rounded-xl bg-primary px-5 text-sm font-bold text-on-primary shadow-sm transition-colors hover:bg-primary-container" type="submit">
                            <span class="material-symbols-outlined text-[18px]">manage_search</span>
                            Search Books
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
                            <option value="all" @selected($availability === 'all')>Any Availability</option>
                            <option value="available" @selected($availability === 'available')>Available Now</option>
                            <option value="reference" @selected($availability === 'reference')>Use in Library Only</option>
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
                    <a class="rounded-full px-3.5 py-1.5 text-xs font-semibold {{ $category === 'all' && $availability === 'all' ? 'bg-primary text-on-primary shadow-sm' : 'border border-outline-variant bg-surface-container text-on-surface hover:bg-surface-container-high' }}" href="{{ route('books.catalog', array_merge($currentParams, ['category' => 'all', 'availability' => 'all'])) }}">All Books</a>
                    @foreach ($categories as $filterCategory)
                        <a class="rounded-full px-3.5 py-1.5 text-xs font-semibold {{ $category === $filterCategory ? 'bg-primary text-on-primary shadow-sm' : 'border border-outline-variant bg-surface-container text-on-surface hover:bg-surface-container-high' }}" href="{{ route('books.catalog', array_merge($currentParams, ['category' => $filterCategory, 'availability' => 'all'])) }}">{{ $filterCategory }}</a>
                    @endforeach
                    <a class="rounded-full px-3.5 py-1.5 text-xs font-semibold {{ $availability === 'available' ? 'bg-primary text-on-primary shadow-sm' : 'border border-outline-variant bg-surface-container text-on-surface hover:bg-surface-container-high' }}" href="{{ route('books.catalog', array_merge($currentParams, ['availability' => 'available'])) }}">Available Now</a>
                </div>
            </section>

            <section class="flex-1 px-4 py-6 lg:px-8">
                <div class="grid grid-cols-1 gap-6 xl:grid-cols-[1fr_360px]">
                    <div class="space-y-4">
                        <div class="flex flex-col justify-between gap-3 rounded-xl border border-outline-variant bg-surface-container-lowest px-4 py-3 shadow-sm sm:flex-row sm:items-center">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-secondary">Book Search Results</p>
                                <h2 class="font-headline-sm text-lg font-bold text-on-surface">{{ $books->total() }} {{ \Illuminate\Support\Str::plural('title', $books->total()) }} matched your search</h2>
                                @if ($advancedFilters)
                                    <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                        @foreach ($advancedFilters as $filterKey => $filterValue)
                                            <a class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-2.5 py-1 text-[11px] font-semibold text-primary hover:bg-primary/20" href="{{ route('books.catalog', \Illuminate\Support\Arr::except(request()->query(), [$filterKey, 'page'])) }}" title="Remove this filter">
                                                {{ ['title' => 'Title', 'author' => 'Author', 'isbn' => 'ISBN', 'year_from' => 'From', 'year_to' => 'Until'][$filterKey] }}: {{ $filterValue }}<span class="material-symbols-outlined text-[14px]" aria-hidden="true">close</span>
                                            </a>
                                        @endforeach
                                        <button class="text-[11px] font-bold text-primary hover:underline" type="button" data-modal-open="advanced-search">Edit</button>
                                    </div>
                                @endif
                            </div>
                            <span class="inline-flex items-center gap-1 rounded-lg bg-surface-container px-3 py-1.5 text-xs font-bold text-primary">
                                <span class="material-symbols-outlined text-[16px]">sensors</span>
                                Book copy list updated
                            </span>
                        </div>

                        @forelse ($books as $book)
                            @php
                                $available = $book->available_count;
                                $total = $book->copies->count();
                                $isReserved = in_array($book->id, $reservedBookIds, true);
                                $isBorrowed = in_array($book->id, $borrowedBookIds, true);
                                $statusTone = $book->isReference() ? 'blue' : ($available > 0 ? 'emerald' : 'amber');
                                $statusText = $book->isReference()
                                    ? 'For In-Library Use Only'
                                    : ($available > 0 ? 'Available - '.$available.' of '.$total.' Copies on Shelf' : 'All Copies Borrowed - Join Waiting List');
                            @endphp
                            <article class="rounded-xl border border-outline-variant bg-surface-container-lowest p-5 shadow-sm transition-all hover:border-primary/60 hover:shadow-md">
                                <div class="flex flex-col gap-5 sm:flex-row">
                                    <div class="book-cover relative h-40 w-full flex-shrink-0 overflow-hidden rounded-lg bg-gradient-to-br {{ 'from-emerald-700 to-slate-950' }} text-white sm:w-28">
                                        <img alt="{{ $book->title }} cover" class="h-full w-full object-cover" loading="lazy" onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');" src="{{ $book->cover_url }}">
                                        <div class="hidden h-full w-full flex-col justify-between p-2.5">
                                            <div>
                                                <span class="text-[9px] font-bold uppercase tracking-wider text-white/70">{{ $book->publisher }}</span>
                                                <h3 class="mt-1 font-headline-sm text-xs font-bold leading-tight text-white">{{ \Illuminate\Support\Str::limit($book->title, 42) }}</h3>
                                            </div>
                                            <div class="border-t border-white/20 pt-1.5">
                                                <span class="block font-mono text-[9px] text-white/70">{{ \Illuminate\Support\Str::limit($book->call_number, 16) }}</span>
                                                <span class="text-[10px] font-bold text-white/90">{{ $book->subject }}</span>
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
                                                    <span class="rounded bg-surface-container px-2 py-0.5 text-[10px] font-semibold text-primary">{{ $book->category }}</span>
                                                </div>
                                                <span class="font-mono text-[10px] font-semibold text-outline">ISBN: {{ $book->isbn }}</span>
                                            </div>

                                            <h2 class="font-headline-md text-xl font-bold leading-snug text-on-surface transition-colors hover:text-primary">{{ $book->title }}</h2>
                                            <p class="mt-0.5 text-sm text-outline">By <span class="font-semibold text-on-surface">{{ $book->author }}</span> - {{ $book->publisher }} - Published {{ $book->publication_year }}</p>

                                            <div class="mt-3 grid grid-cols-1 gap-2 rounded-lg border border-outline-variant bg-surface-container-low p-2.5 md:grid-cols-2">
                                                <div class="flex items-center gap-2 text-sm">
                                                    <span class="material-symbols-outlined text-[18px] text-primary">shelves</span>
                                                    <div><span class="block text-[10px] font-bold uppercase text-outline">Shelf Location</span><span class="font-semibold text-on-surface">{{ $book->shelf_location }}</span></div>
                                                </div>
                                                <div class="flex items-center gap-2 text-sm">
                                                    <span class="material-symbols-outlined text-[18px] text-secondary">label</span>
                                                    <div><span class="block text-[10px] font-bold uppercase text-outline">Call Number</span><span class="font-mono font-semibold text-on-surface">{{ $book->call_number }}</span></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-outline-variant/60 pt-3">
                                            <div class="flex flex-wrap items-center gap-2">
                                                @if (! $book->isReference())
                                                    <form method="POST" action="{{ route('books.hold', $book->id) }}"
                                                        data-confirm="{{ $available > 0 ? 'A copy will be set aside for you at the circulation desk. You will get a notification when it is ready for pickup.' : 'All copies are on loan right now. You will join the waiting list and get a notification when a copy is set aside for you.' }}"
                                                        data-confirm-title="{{ $available > 0 ? 'Reserve this book?' : 'Join the waiting list?' }}"
                                                        data-confirm-detail="{{ $book->title }} · {{ $book->author }} · {{ $book->shelf_location }}"
                                                        data-confirm-label="{{ $available > 0 ? 'Yes, reserve it' : 'Yes, join the list' }}" data-confirm-icon="bookmark_add">
                                                        @csrf
                                                        <button class="flex h-9 items-center gap-1.5 rounded-lg px-4 text-xs font-bold shadow-sm transition-all {{ $isReserved || $isBorrowed ? 'bg-emerald-100 text-emerald-800' : ($available > 0 ? 'bg-primary text-on-primary hover:bg-primary-container' : 'bg-secondary-container text-on-secondary-container hover:bg-amber-400') }}" type="submit" @disabled($isReserved || $isBorrowed)>
                                                            <span class="material-symbols-outlined text-[16px]">{{ $isReserved ? 'bookmark_added' : ($available > 0 ? 'book_online' : 'notification_add') }}</span>
                                                            {{ $isBorrowed ? 'On Loan to You' : ($isReserved ? 'Reserved' : ($available > 0 ? 'Reserve This Book' : 'Join Waiting List')) }}
                                                        </button>
                                                    </form>
                                                @else
                                                    <button class="flex h-9 items-center gap-1.5 rounded-lg bg-tertiary px-4 text-xs font-bold text-white" type="button" data-modal-open="counter-{{ $book->id }}"><span class="material-symbols-outlined text-[16px]">desk</span>Request at Counter</button>
                                                @endif
                                                <a class="flex h-9 items-center gap-1.5 rounded-lg border border-outline-variant px-3.5 text-xs font-semibold text-on-surface transition-colors hover:bg-surface-container" href="{{ route('books.catalog', array_merge($currentParams, ['selected' => $book->id])) }}"><span class="material-symbols-outlined text-[16px] text-primary">inventory_2</span>View Copies</a>
                                                <button class="flex h-9 items-center gap-1 rounded-lg border border-outline-variant px-3 text-xs font-medium text-outline transition-colors hover:bg-surface-container hover:text-on-surface" type="button" data-modal-open="cite-{{ $book->id }}"><span class="material-symbols-outlined text-[16px]">format_quote</span>Cite</button>
                                            </div>
                                            <span class="text-[10px] font-semibold text-outline">{{ $book->subject }} - {{ $book->isReference() ? 'In-library use only' : 'Standard loan' }}</span>
                                        </div>
                                    </div>
                                </div>
                                @include('books.partials.book-modals', ['book' => $book])
                            </article>
                        @empty
                            <div class="rounded-2xl border border-outline-variant bg-surface-container-lowest p-8 text-center">
                                <p class="font-headline-sm text-lg font-bold text-on-surface">No catalog titles found</p>
                                <p class="mt-2 text-sm text-on-surface-variant">Try a broader keyword or clear the filters.</p>
                                <a class="mt-4 inline-flex rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-on-primary" href="{{ route('books.catalog') }}">Clear Search</a>
                            </div>
                        @endforelse

                        @if ($books->hasPages())
                            <nav class="flex flex-col items-center justify-between gap-3 rounded-xl border border-outline-variant bg-surface-container-lowest p-4 shadow-sm sm:flex-row" aria-label="Catalog pages">
                                <span class="text-sm text-outline">Page <strong class="text-on-surface">{{ $books->currentPage() }}</strong> of <strong class="text-on-surface">{{ $books->lastPage() }}</strong></span>
                                <div class="flex items-center gap-2">
                                    @if ($books->onFirstPage())
                                        <span class="rounded-lg border border-outline-variant px-3 py-1.5 text-sm text-outline opacity-50">Previous</span>
                                    @else
                                        <a class="rounded-lg border border-outline-variant px-3 py-1.5 text-sm font-semibold hover:bg-surface-container" href="{{ $books->previousPageUrl() }}" rel="prev">Previous</a>
                                    @endif
                                    @if ($books->hasMorePages())
                                        <a class="rounded-lg border border-outline-variant px-3 py-1.5 text-sm font-semibold hover:bg-surface-container" href="{{ $books->nextPageUrl() }}" rel="next">Next</a>
                                    @else
                                        <span class="rounded-lg border border-outline-variant px-3 py-1.5 text-sm text-outline opacity-50">Next</span>
                                    @endif
                                </div>
                            </nav>
                        @endif
                    </div>

                    <aside class="space-y-4">
                        @if ($selectedBook)
                        <section class="rounded-xl border border-outline-variant bg-surface-container-lowest p-5 shadow-sm">
                            <div class="flex flex-col gap-2 border-b border-outline-variant pb-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-[20px] text-primary">inventory_2</span>
                                        <h3 class="font-headline-sm text-base font-bold text-on-surface">Book Copies & Shelf Location</h3>
                                    </div>
                                    <p class="text-xs text-outline">Selected volume: <strong class="text-on-surface">{{ $selectedBook->title }}</strong></p>
                                </div>
                                <span class="inline-flex items-center gap-1 rounded bg-surface-container px-2.5 py-1 text-[10px] font-semibold text-on-surface-variant"><span class="material-symbols-outlined text-[14px] text-primary">sensors</span>Updated</span>
                            </div>

                            <div class="mt-4 flex items-center gap-3">
                                <div class="book-cover relative h-24 w-16 flex-shrink-0 overflow-hidden rounded bg-primary-container text-white">
                                    <img alt="{{ $selectedBook->title }} cover" class="h-full w-full object-cover" loading="lazy" onerror="this.style.visibility='hidden'" src="{{ $selectedBook->cover_url }}">
                                </div>
                                <div>
                                    <h4 class="line-clamp-2 text-sm font-bold text-on-surface">{{ $selectedBook->title }}</h4>
                                    <p class="text-xs text-outline">{{ $selectedBook->author }} - {{ $selectedBook->publication_year }}</p>
                                    <p class="mt-1 text-[10px] font-bold text-primary">{{ $selectedBook->shelf_location }}</p>
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
                                        @foreach ($selectedBook->copies as $copy)
                                            @php
                                                $copyTone = match ($copy->status) { 'available' => 'emerald', 'on_loan' => 'amber', 'reference' => 'blue', default => 'slate' };
                                                $copyStatus = str($copy->status)->replace('_', ' ')->title();
                                            @endphp
                                            <tr class="hover:bg-surface-container-low/50">
                                                <td class="px-3 py-2 font-mono font-semibold {{ $copyTone === 'emerald' ? 'text-primary' : 'text-outline' }}">{{ $copy->barcode }}</td>
                                                <td class="px-3 py-2 text-outline">{{ $copy->copy_code }}</td>
                                                <td class="px-3 py-2"><span class="rounded px-2 py-0.5 text-[10px] font-bold {{ $copyTone === 'emerald' ? 'bg-emerald-100 text-emerald-800' : ($copyTone === 'amber' ? 'bg-amber-100 text-amber-900' : 'bg-blue-100 text-blue-900') }}">{{ $copyStatus }}</span></td>
                                                <td class="px-3 py-2 font-medium text-on-surface">{{ $copyLocation($copy) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @php
                                $selectedReserved = in_array($selectedBook->id, $reservedBookIds, true);
                                $selectedBorrowed = in_array($selectedBook->id, $borrowedBookIds, true);
                            @endphp
                            <form class="mt-4" method="POST" action="{{ route('books.hold', $selectedBook->id) }}"
                                data-confirm="{{ $selectedBook->copies->where('status', 'available')->count() > 0 ? 'A copy will be set aside for you at the circulation desk. You will get a notification when it is ready for pickup.' : 'All copies are on loan right now. You will join the waiting list and get a notification when a copy is set aside for you.' }}"
                                data-confirm-title="Reserve this book?" data-confirm-detail="{{ $selectedBook->title }} · {{ $selectedBook->author }} · {{ $selectedBook->shelf_location }}"
                                data-confirm-label="Yes, reserve it" data-confirm-icon="bookmark_add">
                                @csrf
                                <button class="flex w-full items-center justify-center gap-2 rounded-xl bg-primary-container px-4 py-2.5 text-xs font-bold text-on-primary shadow-sm hover:bg-primary disabled:cursor-not-allowed disabled:bg-emerald-100 disabled:text-emerald-800" type="submit" @disabled($selectedReserved || $selectedBorrowed || $selectedBook->isReference())>
                                    <span class="material-symbols-outlined text-base">bookmark_added</span>
                                    {{ $selectedBorrowed ? 'On Loan to You' : ($selectedReserved ? 'Reservation Sent' : ($selectedBook->isReference() ? 'Use in Library Only' : 'Reserve This Book')) }}
                                </button>
                            </form>
                        </section>
                        @endif

                    </aside>
                </div>
            </section>

            @include('books.partials.advanced-search')

            <footer class="mt-auto border-t border-outline-variant bg-surface-container-lowest px-4 py-6 text-sm text-outline lg:px-8">
                <div class="flex flex-col items-center justify-between gap-4 md:flex-row">
                    <div class="flex items-center gap-3">
                        @include('partials.celtech-logo', ['class' => 'h-8 w-8 rounded-full border border-secondary-container bg-white object-contain p-0.5'])
                        <div>
                            <p class="text-xs font-bold text-on-surface">Central Luzon College of Science and Technology (CLCST) - Founded 1959</p>
                            <p class="text-[10px] text-outline">CLCST Library Management System</p>
                        </div>
                    </div>
                    <p class="text-[10px] font-semibold">Personal data is handled under the Data Privacy Act of 2012 (RA 10173).</p>
                </div>
            </footer>
        </main>
    </div>
</body>
</html>
