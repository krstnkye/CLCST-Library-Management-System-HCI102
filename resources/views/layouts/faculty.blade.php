<!DOCTYPE html>
<html class="h-full" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>@yield('title') - CLCST Faculty Library Portal</title>
    @include('partials.patron-head')
</head>
<body class="clcst-portal clcst-role-faculty min-h-full bg-[#F6F3EC] text-[#1F1D1A] antialiased">
    @php($faculty = auth()->user())
    <header class="clcst-topbar sticky top-0 z-40 flex h-16 w-full items-center justify-between border-b border-[#DBD8D1] bg-white px-4 shadow-sm lg:px-6">
        <a class="flex items-center gap-3" href="{{ route('faculty.dashboard') }}">
            @include('partials.celtech-logo', ['class' => 'h-10 w-10 rounded-full object-contain'])
            <div>
                <span class="block font-headline text-sm font-extrabold leading-none text-[#1F3C27] sm:text-base">CLCST SCHOOL LIBRARY</span>
                <span class="mt-0.5 hidden text-[10px] font-bold uppercase tracking-wider text-[#817B6E] sm:block">Faculty Library Portal</span>
            </div>
        </a>
        <div class="flex items-center gap-3">
            <form class="hidden md:block" method="GET" action="{{ route('books.catalog') }}" role="search">
                <label class="sr-only" for="faculty-search">Search the catalog</label>
                <div class="relative"><span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[18px] text-[#817B6E]">search</span><input id="faculty-search" class="h-9 w-64 rounded-lg border border-[#DBD8D1] bg-[#F6F3EC] pl-9 pr-10 text-sm focus:border-[#1F3C27] focus:ring-[#1F3C27]" name="q" placeholder="Search title, author, ISBN..." type="search"><button class="absolute right-1.5 top-1/2 flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-full text-[#817B6E] hover:bg-white hover:text-primary" type="button" title="Scan a barcode or QR code" aria-label="Scan a barcode or QR code" data-scan data-scan-target="#faculty-search" data-scan-submit><span class="material-symbols-outlined text-[18px]">barcode_scanner</span></button></div>
            </form>
            <a aria-label="Notifications ({{ $patronSummary['notifications'] }})" class="relative rounded-lg p-2 text-[#5F5A51] hover:bg-[#F0ECE4]" href="{{ route('staff.section', 'notifications') }}">
                <span class="material-symbols-outlined">notifications</span>
                <span class="absolute right-1 top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-bold text-white" data-notification-badge @if ($patronSummary['notifications'] === 0) hidden @endif>{{ $patronSummary['notifications'] }}</span>
            </a>
            <a class="flex items-center gap-3" href="{{ route('staff.section', 'profile') }}">
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-[#1F3C27] text-sm font-bold text-white">{{ $faculty->initials() }}</div>
                <div class="hidden text-left lg:block"><p class="text-xs font-semibold leading-tight">{{ $faculty->name }}</p><p class="text-xs text-[#817B6E]">{{ $faculty->program_or_department ?: 'Faculty' }}</p></div>
            </a>
        </div>
    </header>

    <div class="flex min-h-[calc(100vh-4rem)]">
        @include('partials.staff-sidebar')
        <main class="clcst-main min-w-0 flex-1 space-y-6 p-4 pb-24 sm:p-6 sm:pb-24 md:ml-64 md:pb-8 lg:p-8">
            @include('partials.flash')
            @yield('content')
        </main>
    </div>
</body>
</html>
