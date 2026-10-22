@php
    $activePage = $activePage ?? (request()->routeIs('books.catalog') ? 'catalog' : (request()->route('section') ?? 'dashboard'));
    $student = auth()->user();
    $summary = $patronSummary;

    $navItems = [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard', 'href' => route('student.dashboard')],
        ['key' => 'catalog', 'label' => 'Books (Catalog)', 'icon' => 'menu_book', 'href' => route('books.catalog')],
        ['key' => 'borrow-return', 'label' => 'My Loans', 'icon' => 'swap_horiz', 'href' => route('student.section', 'borrow-return'), 'badge' => $summary['activeLoans'], 'badgeTone' => $summary['overdueLoans'] > 0 ? 'error' : 'warning'],
        ['key' => 'reservations', 'label' => 'Reservations', 'icon' => 'book_online', 'href' => route('student.section', 'reservations'), 'badge' => $summary['openReservations']],
        ['key' => 'notifications', 'label' => 'Notifications', 'icon' => 'notifications', 'href' => route('student.section', 'notifications'), 'badge' => $summary['notifications'], 'badgeTone' => 'error'],
        ['key' => 'history', 'label' => 'Borrowing History', 'icon' => 'history', 'href' => route('student.section', 'history')],
        ['key' => 'fines', 'label' => 'Fines & Penalties', 'icon' => 'payments', 'href' => route('student.section', 'fines'), 'badge' => $summary['outstandingFines'] > 0 ? 'PHP '.number_format($summary['outstandingFines'], 0) : null, 'badgeTone' => 'warning'],
        ['key' => 'account', 'label' => 'My Account', 'icon' => 'manage_accounts', 'href' => route('student.section', 'account')],
    ];
@endphp

<aside id="student-sidebar" class="fixed bottom-0 left-0 top-16 z-30 hidden w-64 flex-shrink-0 flex-col justify-between border-r border-outline-variant bg-surface-container-lowest md:flex" aria-label="Student navigation">
    <div class="custom-scrollbar flex flex-1 flex-col gap-1 overflow-y-auto p-4">
        <a class="mb-3 flex items-center gap-3 rounded-xl border border-primary/20 bg-primary/5 p-3" href="{{ route('student.section', 'account') }}">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary-container font-bold text-white">
                <span class="material-symbols-outlined">badge</span>
            </div>
            <div class="overflow-hidden">
                <p class="truncate text-xs font-bold text-primary">CLCST e-Pass</p>
                <p class="truncate text-[10px] text-on-surface-variant">ID: {{ $student->patron_id }}</p>
            </div>
        </a>

        <span class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-outline">Library Navigation</span>
        <nav class="space-y-1">
            @foreach ($navItems as $item)
                @php
                    $isActive = $activePage === $item['key'];
                    $badgeClass = match ($item['badgeTone'] ?? 'neutral') {
                        'error' => 'bg-error-container text-error',
                        'warning' => 'bg-secondary-container text-on-secondary-container',
                        default => 'bg-surface-container text-on-surface-variant',
                    };
                @endphp
                <a class="{{ $isActive ? 'bg-primary-container text-on-primary shadow-sm' : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface' }} flex items-center justify-between rounded-lg px-3 py-2.5 font-medium transition-colors" href="{{ $item['href'] }}" @if ($isActive) aria-current="page" @endif>
                    <span class="flex items-center gap-3">
                        <span class="material-symbols-outlined {{ $isActive ? '' : 'text-outline' }}" @if ($isActive) style="font-variation-settings: 'FILL' 1;" @endif>{{ $item['icon'] }}</span>
                        <span class="text-xs font-semibold">{{ $item['label'] }}</span>
                    </span>
                    @if (! empty($item['badge']))
                        <span class="{{ $badgeClass }} rounded-full px-2 py-0.5 text-[10px] font-bold">{{ $item['badge'] }}</span>
                    @endif
                </a>
            @endforeach
        </nav>

        <form class="mt-4 space-y-2 rounded-xl border border-outline-variant bg-surface-container-low p-3" method="GET" action="{{ route('books.catalog') }}">
            <label class="flex items-center gap-2 text-xs font-bold text-primary" for="sidebar-barcode">
                <span class="material-symbols-outlined text-base">barcode_scanner</span>Find by barcode or ISBN
            </label>
            <input id="sidebar-barcode" class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2 text-xs focus:border-primary focus:ring-1 focus:ring-primary" name="q" placeholder="e.g. 978-0132350884">
            <button class="flex w-full items-center justify-center gap-1.5 rounded-lg bg-primary px-3 py-2 text-xs font-bold text-on-primary hover:bg-primary-container" type="submit">
                <span class="material-symbols-outlined text-base">search</span>Look up
            </button>
        </form>
    </div>

    <div class="flex flex-col gap-2 border-t border-outline-variant bg-surface-container-lowest p-4">
        <a class="{{ $activePage === 'support' ? 'bg-primary-container text-on-primary shadow-sm' : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface' }} flex items-center gap-3 rounded-lg px-3 py-2 font-medium transition-colors" href="{{ route('student.section', 'support') }}">
            <span class="material-symbols-outlined">help_outline</span>
            <span class="text-xs font-semibold">Help & Support</span>
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="flex w-full items-center gap-3 rounded-lg px-3 py-2 font-medium text-error hover:bg-error-container/30" type="submit">
                <span class="material-symbols-outlined">logout</span><span class="text-xs font-semibold">Log Out</span>
            </button>
        </form>
    </div>
</aside>

<nav class="custom-scrollbar fixed inset-x-0 bottom-0 z-40 flex gap-1 overflow-x-auto border-t border-outline-variant bg-surface-container-lowest px-2 py-1.5 md:hidden" aria-label="Student navigation">
    @foreach ($navItems as $item)
        <a class="{{ $activePage === $item['key'] ? 'bg-primary-container text-on-primary' : 'text-on-surface-variant' }} flex min-w-[4.5rem] flex-col items-center gap-0.5 rounded-lg px-2 py-1 text-[10px] font-semibold" href="{{ $item['href'] }}">
            <span class="material-symbols-outlined text-[20px]">{{ $item['icon'] }}</span>{{ $item['label'] }}
        </a>
    @endforeach
</nav>
