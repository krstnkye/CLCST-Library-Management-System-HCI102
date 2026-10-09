@php
    $portalRole = $portalRole ?? 'staff';
    $activePage = $activePage ?? ($section ?? 'dashboard');
    $homeRoute = $portalRole === 'faculty' ? route('faculty.dashboard') : route('staff.dashboard');
    $pendingReservations = $stats['pendingReservations'] ?? count($reservations ?? []);

    $navItems = [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard', 'href' => $homeRoute],
        ['key' => 'circulation', 'label' => 'My Borrowed Books', 'icon' => 'swap_horiz', 'href' => route('staff.section', 'circulation'), 'badge' => $stats['activeLoans'] ?? null, 'badgeTone' => 'warning'],
        ['key' => 'patrons', 'label' => 'Patron Records', 'icon' => 'groups', 'href' => route('staff.section', 'patrons')],
        ['key' => 'catalog', 'label' => 'Recommend a Book', 'icon' => 'recommend', 'href' => route('staff.section', 'catalog')],
        ['key' => 'reservations', 'label' => 'My Reservations', 'icon' => 'book_online', 'href' => route('staff.section', 'reservations'), 'badge' => $pendingReservations],
        ['key' => 'reports', 'label' => 'Renewal Requests', 'icon' => 'autorenew', 'href' => route('staff.section', 'reports'), 'badge' => 1],
        ['key' => 'history', 'label' => 'Borrowing History', 'icon' => 'history_edu', 'href' => route('staff.section', 'history')],
        ['key' => 'academic', 'label' => 'Faculty Academic Requests', 'icon' => 'school', 'href' => route('staff.section', 'academic'), 'badge' => count($facultyRequests ?? [])],
        ['key' => 'settings', 'label' => 'Settings', 'icon' => 'settings', 'href' => route('staff.section', 'settings')],
    ];
@endphp

<aside id="staff-sidebar" class="fixed bottom-0 left-0 top-16 z-30 hidden w-64 flex-shrink-0 flex-col justify-between border-r border-[#CBD5E1] bg-white md:flex">
    <div id="staff-sidebar-scroll" class="custom-scrollbar flex flex-1 flex-col gap-1 overflow-y-auto p-4">
        <a class="mb-3 flex items-center gap-3 rounded-xl border border-[#BBF7D0] bg-[#F0FDF4] p-3" href="{{ $homeRoute }}">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#065F2A] font-bold text-white">
                <span class="material-symbols-outlined">admin_panel_settings</span>
            </div>
            <div class="overflow-hidden">
                <p class="truncate text-xs font-bold text-[#065F2A]">
                    {{ $portalRole === 'faculty' ? 'Faculty Library Access' : 'Staff Console' }}
                </p>
                <p class="truncate text-[10px] text-[#475569]">
                    {{ $portalRole === 'faculty' ? 'Course reserves and requests' : 'Circulation and records desk' }}
                </p>
            </div>
        </a>

        <span class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-[#64748B]">Library Operations</span>
        <nav class="space-y-1">
            @foreach ($navItems as $item)
                @php
                    $isActive = $activePage === $item['key'];
                    $badgeTone = $item['badgeTone'] ?? 'neutral';
                    $badgeClass = match ($badgeTone) {
                        'warning' => 'bg-[#FEF3C7] text-[#92400E]',
                        default => 'bg-[#EEF2FF] text-[#334155]',
                    };
                @endphp
                <a class="{{ $isActive ? 'bg-[#065F2A] text-white shadow-sm' : 'text-[#334155] hover:bg-[#F1F5F9] hover:text-[#0F172A]' }} flex items-center justify-between rounded-lg px-3 py-2.5 font-medium transition-colors" href="{{ $item['href'] }}">
                    <span class="flex items-center gap-3">
                        <span class="material-symbols-outlined {{ $isActive ? '' : 'text-[#64748B]' }}" @if ($isActive) style="font-variation-settings: 'FILL' 1;" @endif>{{ $item['icon'] }}</span>
                        <span class="text-xs font-semibold">{{ $item['label'] }}</span>
                    </span>
                    @if (!empty($item['badge']))
                        <span class="{{ $badgeClass }} rounded-full px-2 py-0.5 text-[10px] font-bold">{{ $item['badge'] }}</span>
                    @endif
                </a>
            @endforeach
        </nav>

        <div class="mt-4 rounded-xl border border-[#CBD5E1] bg-[#F8FAFC] p-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 text-xs font-bold text-[#065F2A]">
                    <span class="material-symbols-outlined text-base">barcode_scanner</span>
                    <span>Desk Scanner</span>
                </div>
                <span class="rounded-full bg-[#DCFCE7] px-2 py-0.5 text-[10px] font-bold text-[#166534]">READY</span>
            </div>
            <p class="mt-2 text-[11px] leading-tight text-[#475569]">Scan patron IDs, copy barcodes, and reservation slips from one circulation station.</p>
            <form class="mt-3 space-y-2" method="GET" action="{{ route('books.catalog') }}">
                <input name="field" type="hidden" value="all">
                <input class="w-full rounded-lg border border-[#CBD5E1] bg-white px-3 py-2 text-xs text-[#0F172A] placeholder:text-[#94A3B8]" name="q" placeholder="Barcode or call number">
                <button class="flex w-full items-center justify-center gap-1.5 rounded-lg bg-[#065F2A] px-3 py-2 text-xs font-bold text-white" type="submit">
                    <span class="material-symbols-outlined text-base">search</span>Open Record
                </button>
            </form>
        </div>
    </div>

    <div class="flex flex-col gap-2 border-t border-[#CBD5E1] bg-white p-4">
        <a class="{{ $activePage === 'support' ? 'bg-[#065F2A] text-white shadow-sm' : 'text-[#334155] hover:bg-[#F1F5F9]' }} flex items-center gap-3 rounded-lg px-3 py-2 font-medium transition-colors" href="{{ route('staff.section', 'support') }}">
            <span class="material-symbols-outlined" @if ($activePage === 'support') style="font-variation-settings: 'FILL' 1;" @endif>help_outline</span>
            <span class="text-xs font-semibold">Help & Support</span>
        </a>
        <a class="flex items-center gap-3 rounded-lg px-3 py-2 font-medium text-[#B91C1C] hover:bg-[#FEE2E2]" href="{{ route('login') }}">
            <span class="material-symbols-outlined">logout</span><span class="text-xs font-semibold">Log Out</span>
        </a>
        <p class="px-3 pt-2 text-[10px] leading-tight text-[#64748B]">Authorized access for library transactions, patron records, and reporting.</p>
    </div>
</aside>

<script>
    (() => {
        const sidebarScrollKey = 'clcst.staff.sidebar.scrollTop';
        const pageScrollKey = 'clcst.staff.page.scrollTop';

        window.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.getElementById('staff-sidebar-scroll');
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

            document.querySelectorAll('#staff-sidebar a[href]').forEach((link) => {
                link.addEventListener('click', () => {
                    sessionStorage.setItem(sidebarScrollKey, String(sidebar?.scrollTop || 0));
                    sessionStorage.setItem(pageScrollKey, String(main?.scrollTop || window.scrollY || 0));
                });
            });
        });
    })();
</script>
