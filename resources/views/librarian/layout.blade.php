<!DOCTYPE html>
<html class="h-full bg-[#F5F7F6]" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Librarian Dashboard') - CLCST Library Management System</title>
    @include('partials.favicon')
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script>
        tailwind.config = { theme: { extend: { fontFamily: { sans: ['Inter', 'sans-serif'], display: ['Plus Jakarta Sans', 'sans-serif'] } } } };
    </script>
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 450, 'GRAD' 0, 'opsz' 24; line-height: 1; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #b9c7be; border-radius: 999px; }
    </style>
    @include('partials.ui-motion')
</head>
<body class="clcst-portal clcst-role-librarian min-h-full bg-[#F5F7F6] font-sans text-[#17251D] antialiased">
    @php
        $librarian = auth()->user();
    @endphp
    <header class="clcst-topbar fixed inset-x-0 top-0 z-40 flex h-16 items-center justify-between border-b border-[#D6DED8] bg-white px-4 shadow-sm md:px-6">
        <div class="flex min-w-0 items-center gap-3">
            <button aria-label="Open navigation" class="rounded-md p-2 text-[#0F5C2E] hover:bg-[#EEF5F0] md:hidden" id="librarian-menu-button" type="button">
                <span class="material-symbols-outlined">menu</span>
            </button>
            <a class="flex min-w-0 items-center gap-3" href="{{ route('librarian.dashboard') }}">
                @include('partials.celtech-logo', ['class' => 'h-10 w-10 shrink-0 rounded-full object-contain'])
                <div class="min-w-0">
                    <p class="truncate font-display text-sm font-extrabold text-[#0F5C2E] sm:text-base">CLCST Library Management System</p>
                    <p class="truncate text-[11px] font-medium text-[#66756D]">Librarian Operations Portal</p>
                </div>
            </a>
        </div>

        <form class="mx-6 hidden max-w-xl flex-1 lg:block" method="GET" action="{{ route('librarian.section', 'books') }}" role="search">
            <label class="relative block">
                <span class="material-symbols-outlined pointer-events-none absolute left-3 top-2.5 text-xl text-[#6D7C74]">search</span>
                <input id="librarian-header-search" class="w-full rounded-lg border-[#CBD6CF] bg-[#F7F9F8] py-2 pl-10 pr-11 text-sm focus:border-[#0F5C2E] focus:ring-[#0F5C2E]" name="q" placeholder="Search books by title, author, ISBN, barcode, or call number" type="search" aria-label="Search books">
                <button class="absolute right-1.5 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full text-[#6D7C74] hover:bg-white hover:text-[#0F5C2E]" type="button" title="Scan a barcode" aria-label="Scan a book barcode" data-scan data-scan-target="#librarian-header-search" data-scan-submit><span class="material-symbols-outlined text-xl">barcode_scanner</span></button>
            </label>
        </form>

        <div class="flex items-center gap-2">
            <a aria-label="Notifications" class="relative rounded-lg p-2 text-[#44534B] hover:bg-[#EEF5F0]" href="{{ route('librarian.section', 'notifications') }}">
                <span class="material-symbols-outlined">notifications</span>
            </a>
            <div class="hidden text-right sm:block">
                <p class="text-xs font-bold text-[#17251D]">{{ $librarian->name }}</p>
                <p class="text-[11px] text-[#66756D]">{{ $librarian->program_or_department ?: 'Library Staff' }}</p>
            </div>
            <a aria-label="Open profile" class="flex h-9 w-9 items-center justify-center rounded-full bg-[#0F5C2E] text-xs font-bold text-white" href="{{ route('librarian.section', 'profile') }}">{{ $librarian->initials() }}</a>
        </div>
    </header>

    @include('partials.librarian-sidebar', ['activePage' => $activePage])
    <div class="fixed inset-0 z-20 hidden bg-black/30 md:hidden" id="librarian-menu-overlay"></div>

    <main class="clcst-main min-h-screen pt-16 md:ml-72">
        <div class="mx-auto max-w-[1600px] p-4 md:p-6 lg:p-8">
            @if (session('status') || $errors->any())
                <div class="mb-5 space-y-3">@include('partials.flash')</div>
            @endif
            @yield('content')
        </div>
    </main>

    <script>
        (() => {
            const button = document.getElementById('librarian-menu-button');
            const sidebar = document.getElementById('librarian-sidebar');
            const overlay = document.getElementById('librarian-menu-overlay');
            if (!button || !sidebar || !overlay) return;
            const toggle = () => {
                sidebar.classList.toggle('hidden');
                sidebar.classList.toggle('flex');
                overlay.classList.toggle('hidden');
            };
            button.addEventListener('click', toggle);
            overlay.addEventListener('click', toggle);
        })();
    </script>
</body>
</html>
