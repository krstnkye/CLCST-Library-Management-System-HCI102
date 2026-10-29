<!DOCTYPE html>
<html class="h-full" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>@yield('title') - CLCST Library Portal</title>
    @include('partials.patron-head')
</head>
<body class="clcst-portal clcst-role-student min-h-full bg-[#F8FAFC] text-on-surface antialiased">
    @php($student = auth()->user())
    <header class="clcst-topbar sticky top-0 z-40 flex h-16 w-full items-center justify-between border-b border-[#E2E8F0] bg-surface-container-lowest px-4 shadow-sm lg:px-6">
        <a class="flex items-center gap-3" href="{{ route('student.dashboard') }}">
            @include('partials.celtech-logo', ['class' => 'h-10 w-10 rounded-full bg-white object-contain p-0.5 shadow-sm'])
            <div>
                <span class="block font-headline text-sm font-bold leading-none tracking-wide text-primary sm:text-base">CLCST SCHOOL LIBRARY</span>
                <span class="mt-0.5 hidden text-[10px] font-bold tracking-wider text-[#64748B] sm:block">STUDENT PATRON PORTAL</span>
            </div>
        </a>

        <div class="flex items-center gap-3 lg:gap-4">
            <form class="hidden md:block" method="GET" action="{{ route('books.catalog') }}" role="search">
                <label class="sr-only" for="header-search">Search the catalog</label>
                <div class="relative">
                    <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[18px] text-outline">search</span>
                    <input id="header-search" class="h-9 w-64 rounded-lg border border-outline-variant bg-surface-container-low pl-9 pr-10 text-sm focus:border-primary focus:ring-primary" name="q" placeholder="Search title, author, ISBN..." type="search"><button class="absolute right-1.5 top-1/2 flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-full text-[#64748B] hover:bg-white hover:text-primary" type="button" title="Scan a barcode or QR code" aria-label="Scan a barcode or QR code" data-scan data-scan-target="#header-search" data-scan-submit><span class="material-symbols-outlined text-[18px]">barcode_scanner</span></button>
                </div>
            </form>
            <a aria-label="Notifications ({{ $patronSummary['notifications'] }})" class="relative rounded-lg p-2 text-on-surface-variant transition-colors hover:bg-surface-container hover:text-on-surface" href="{{ route('student.section', 'notifications') }}">
                <span class="material-symbols-outlined">notifications</span>
                <span class="absolute right-1 top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-error px-1 text-[10px] font-bold text-white" data-notification-badge @if ($patronSummary['notifications'] === 0) hidden @endif>{{ $patronSummary['notifications'] }}</span>
            </a>
            <a aria-label="Help and Support" class="rounded-lg p-2 text-on-surface-variant transition-colors hover:bg-surface-container hover:text-on-surface" href="{{ route('student.section', 'support') }}">
                <span class="material-symbols-outlined">help</span>
            </a>
            <div class="h-6 w-px bg-[#E2E8F0]"></div>
            <a class="flex items-center gap-3" href="{{ route('student.section', 'account') }}">
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-primary-container text-sm font-bold text-on-primary shadow-sm ring-2 ring-[#DCFCE7]">{{ $student->initials() }}</div>
                <div class="hidden text-left lg:block">
                    <p class="text-xs font-semibold leading-tight text-on-surface">{{ $student->name }}</p>
                    <p class="text-xs text-[#64748B]">{{ $student->patron_id }}</p>
                </div>
            </a>
        </div>
    </header>

    <div class="flex min-h-[calc(100vh-4rem)]">
        @include('partials.student-sidebar')

        <main class="clcst-main min-w-0 flex-1 space-y-6 p-4 pb-24 sm:p-6 sm:pb-24 md:ml-64 md:pb-8 lg:p-8">
            @include('partials.flash')
            @yield('content')
        </main>
    </div>
</body>
</html>
