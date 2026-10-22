@php
    $activeLoanCount = $activeLoanCount ?? count($activeLoans ?? []);
    $reservationCount = $reservationCount ?? count($reservations ?? []);
    $outstanding = $outstanding ?? 0;
    $activePage = $activePage ?? (request()->routeIs('books.catalog') ? 'catalog' : ($section ?? 'dashboard'));
    $student = auth()->user();

    $navItems = [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard', 'href' => route('student.dashboard')],
        ['key' => 'catalog', 'label' => 'Books (Catalog)', 'icon' => 'menu_book', 'href' => route('books.catalog')],
        ['key' => 'borrow-return', 'label' => 'Borrow/Return', 'icon' => 'swap_horiz', 'href' => route('student.section', 'borrow-return'), 'badge' => $activeLoanCount, 'badgeTone' => 'warning'],
        ['key' => 'reservations', 'label' => 'Reservations', 'icon' => 'book_online', 'href' => route('student.section', 'reservations'), 'badge' => $reservationCount],
        ['key' => 'notifications', 'label' => 'Notifications', 'icon' => 'notifications', 'href' => route('student.section', 'notifications'), 'badge' => 2, 'badgeTone' => 'error'],
        ['key' => 'history', 'label' => 'Borrowing History', 'icon' => 'history', 'href' => route('student.section', 'history')],
        ['key' => 'fines', 'label' => 'Fines & Penalties', 'icon' => 'payments', 'href' => route('student.section', 'fines'), 'badge' => $outstanding > 0 ? 'PHP '.number_format($outstanding, 0) : null, 'badgeTone' => 'warning'],
        ['key' => 'account', 'label' => 'My Account', 'icon' => 'manage_accounts', 'href' => route('student.section', 'account')],
    ];
@endphp

<aside id="student-sidebar" class="fixed bottom-0 left-0 top-16 z-30 hidden w-64 flex-shrink-0 flex-col justify-between border-r border-outline-variant bg-surface-container-lowest md:flex">
    <div id="student-sidebar-scroll" class="custom-scrollbar flex flex-1 flex-col gap-1 overflow-y-auto p-4">
        <a class="mb-3 flex items-center gap-3 rounded-xl border border-primary/20 bg-primary/5 p-3" href="{{ route('student.section', 'account') }}">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary-container font-bold text-white">
                <span class="material-symbols-outlined">badge</span>
            </div>
            <div class="overflow-hidden">
                <p class="truncate text-xs font-bold text-primary">CLCST e-Pass</p>
                <p class="truncate text-[10px] text-on-surface-variant">ID: {{ $student->patron_id }}</p>
            </div>
        </a>

        <span class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-outline">Book Catalog Navigation</span>
        <nav class="space-y-1">
            @foreach ($navItems as $item)
                @php
                    $isActive = $activePage === $item['key'];
                    $badgeTone = $item['badgeTone'] ?? 'neutral';
                    $badgeClass = match ($badgeTone) {
                        'error' => 'bg-error-container text-error',
                        'warning' => 'bg-secondary-container text-on-secondary-container',
                        default => 'bg-surface-container text-on-surface-variant',
                    };
                @endphp
                <a class="{{ $isActive ? 'bg-primary-container text-on-primary shadow-sm' : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface' }} flex items-center justify-between rounded-lg px-3 py-2.5 font-medium transition-colors" href="{{ $item['href'] }}">
                    <span class="flex items-center gap-3">
                        <span class="material-symbols-outlined {{ $isActive ? '' : 'text-outline' }}" @if ($isActive) style="font-variation-settings: 'FILL' 1;" @endif>{{ $item['icon'] }}</span>
                        <span class="text-xs font-semibold">{{ $item['label'] }}</span>
                    </span>
                    @if (!empty($item['badge']))
                        <span class="{{ $badgeClass }} rounded-full px-2 py-0.5 text-[10px] font-bold">{{ $item['badge'] }}</span>
                    @endif
                </a>
            @endforeach
        </nav>

        <div class="mt-4 overflow-hidden rounded-xl border border-outline-variant bg-surface-container-low shadow-sm">
            <div class="border-b border-outline-variant/70 bg-surface-container-lowest p-3">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2 text-xs font-bold text-primary">
                        <span class="material-symbols-outlined text-base" style="font-variation-settings: 'FILL' 1;">barcode_scanner</span>
                        <span>Shelf Scanner</span>
                    </div>
                    <span class="inline-flex items-center gap-1 rounded-full bg-[#aaf4b5] px-2 py-0.5 text-[10px] font-bold text-primary">
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
                        <input class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest py-2 pl-8 pr-3 text-xs text-on-surface placeholder:text-outline focus:border-primary focus:ring-1 focus:ring-primary" name="q" placeholder="Enter barcode or ISBN" value="{{ request('q', '1049281') }}">
                    </div>
                    <button class="flex w-full items-center justify-center gap-1.5 rounded-lg bg-primary px-3 py-2 text-xs font-bold text-on-primary shadow-sm transition-colors hover:bg-primary-container" type="submit">
                        <span class="material-symbols-outlined text-base">barcode_reader</span>Scan Barcode
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="flex flex-col gap-2 border-t border-outline-variant bg-surface-container-lowest p-4">
        <a class="{{ $activePage === 'support' ? 'bg-primary-container text-on-primary shadow-sm' : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface' }} flex items-center gap-3 rounded-lg px-3 py-2 font-medium transition-colors" href="{{ route('student.section', 'support') }}">
            <span class="material-symbols-outlined" @if ($activePage === 'support') style="font-variation-settings: 'FILL' 1;" @endif>help_outline</span>
            <span class="text-xs font-semibold">Help & Support</span>
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="flex w-full items-center gap-3 rounded-lg px-3 py-2 font-medium text-error hover:bg-error-container/30" type="submit">
                <span class="material-symbols-outlined">logout</span><span class="text-xs font-semibold">Log Out</span>
            </button>
        </form>
        <p class="px-3 pt-2 text-[10px] leading-tight text-outline">Student Mode: Read and search only. Borrowing handled at Counter 1.</p>
    </div>
</aside>

<script>
    (() => {
        const sidebarScrollKey = 'clcst.student.sidebar.scrollTop';
        const pageScrollKey = 'clcst.student.page.scrollTop';

        window.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.getElementById('student-sidebar-scroll');
            const main = document.querySelector('main');
            const savedSidebarTop = Number(sessionStorage.getItem(sidebarScrollKey) || 0);
            const savedPageTop = Number(sessionStorage.getItem(pageScrollKey) || 0);

            if (sidebar && savedSidebarTop > 0) {
                sidebar.scrollTop = savedSidebarTop;
            }

            if (main && savedPageTop > 0) {
                requestAnimationFrame(() => {
                    main.scrollTop = savedPageTop;
                    window.scrollTo(0, savedPageTop);
                });
            }

            sidebar?.addEventListener('scroll', () => {
                sessionStorage.setItem(sidebarScrollKey, String(sidebar.scrollTop));
            }, { passive: true });

            document.querySelectorAll('#student-sidebar a[href]').forEach((link) => {
                link.addEventListener('click', () => {
                    sessionStorage.setItem(sidebarScrollKey, String(sidebar?.scrollTop || 0));
                    sessionStorage.setItem(pageScrollKey, String(main?.scrollTop || window.scrollY || 0));
                });
            });
        });
    })();
</script>
