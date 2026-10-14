@php
    $portalRole = $portalRole ?? 'staff';
    $activePage = $activePage ?? ($section ?? 'dashboard');
    $homeRoute = $portalRole === 'faculty' ? route('faculty.dashboard') : route('staff.dashboard');
    $pendingReservations = $stats['pendingReservations'] ?? count($reservations ?? []);
    $borrowedCount = $stats['activeLoans'] ?? count($activeLoans ?? []);

    $mainItems = [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard', 'href' => $homeRoute],
        ['key' => 'search-books', 'label' => 'Search Books', 'icon' => 'menu_book', 'href' => route('books.catalog'), 'activeWhen' => ['search-books']],
        ['key' => 'circulation', 'label' => 'My Borrowed Books', 'icon' => 'swap_horiz', 'href' => route('staff.section', 'circulation'), 'badge' => $borrowedCount, 'badgeTone' => 'info'],
        ['key' => 'reservations', 'label' => 'My Reservations', 'icon' => 'book_online', 'href' => route('staff.section', 'reservations'), 'badge' => $pendingReservations, 'badgeTone' => 'warning'],
        ['key' => 'reports', 'label' => 'Renewal Requests', 'icon' => 'autorenew', 'href' => route('staff.section', 'reports'), 'badge' => 1, 'badgeTone' => 'danger'],
        ['key' => 'history', 'label' => 'Borrowing History', 'icon' => 'history', 'href' => route('staff.section', 'history')],
    ];

    $academicItems = [
        ['key' => 'course-reserves', 'label' => 'Course Reserves Setup', 'icon' => 'assignment', 'href' => route('staff.section', 'course-reserves')],
        ['key' => 'reading-lists', 'label' => 'Syllabus References', 'icon' => 'format_list_bulleted', 'href' => route('staff.section', 'reading-lists')],
        ['key' => 'instruction-sessions', 'label' => 'Library Class Sessions', 'icon' => 'cast_for_education', 'href' => route('staff.section', 'instruction-sessions')],
        ['key' => 'inter-library-loan', 'label' => 'Inter-Library Loan', 'icon' => 'sync_alt', 'href' => route('staff.section', 'inter-library-loan')],
    ];

    $lowerItems = [
        ['key' => 'notifications', 'label' => 'Notifications', 'icon' => 'notifications', 'href' => route('staff.section', 'notifications'), 'badge' => 3, 'badgeTone' => 'danger'],
        ['key' => 'profile', 'label' => 'My Profile', 'icon' => 'group_add', 'href' => route('staff.section', 'profile')],
        ['key' => 'feedback', 'label' => 'Library Feedback', 'icon' => 'rate_review', 'href' => route('staff.section', 'feedback')],
        ['key' => 'support', 'label' => 'Help & Support', 'icon' => 'help_outline', 'href' => route('staff.section', 'support')],
    ];

    $academicActive = in_array($activePage, ['academic', 'catalog', 'course-reserves', 'reading-lists', 'instruction-sessions', 'inter-library-loan'], true);

    $badgeClass = function (?string $tone): string {
        return match ($tone) {
            'danger' => 'bg-[#FEE2E2] text-[#B91C1C]',
            'warning' => 'bg-[#FEF3C7] text-[#92400E]',
            'info' => 'bg-[#DBEAFE] text-[#1D4ED8]',
            default => 'bg-[#EEF2FF] text-[#334155]',
        };
    };
@endphp

<aside id="staff-sidebar" class="fixed bottom-0 left-0 top-16 z-30 hidden w-64 flex-shrink-0 flex-col justify-between border-r border-[#CBD5E1] bg-white md:flex">
    <div id="staff-sidebar-scroll" class="custom-scrollbar flex flex-1 flex-col gap-5 overflow-y-auto p-4">
        <a class="flex items-center gap-3" href="{{ $homeRoute }}">
            @include('partials.celtech-logo', ['class' => 'h-10 w-10 shrink-0 rounded-full object-contain'])
            <div class="min-w-0">
                <p class="truncate text-base font-extrabold leading-tight tracking-tight text-[#064E3B]">CLCST SCHOOL LIBRARY</p>
                <p class="truncate text-[10px] font-bold uppercase tracking-wide text-[#64748B]">Central Luzon College of Science and Technology</p>
            </div>
        </a>

        <section class="rounded-xl border border-[#CBD5E1] bg-[#F8FAFC] p-3">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#065F2A] text-white">
                    <span class="material-symbols-outlined">local_library</span>
                </div>
                <div>
                    <p class="text-xs font-bold text-[#064E3B]">Central Shelves</p>
                    <p class="text-[10px] text-[#64748B]">Olongapo Main Campus</p>
                </div>
            </div>
        </section>

        <a class="flex items-center justify-center gap-2 rounded-lg bg-[#065F2A] px-4 py-3 text-sm font-bold text-white shadow-sm hover:bg-[#064E3B]" href="{{ route('books.catalog') }}">
            <span class="material-symbols-outlined text-base">barcode_scanner</span>
            Scan Barcode
        </a>

        <nav class="space-y-5" aria-label="Faculty and staff navigation">
            <section>
                <p class="mb-2 px-2 text-[10px] font-bold uppercase tracking-wider text-[#64748B]">Catalog & Borrowing</p>
                <div class="space-y-1">
                    @foreach ($mainItems as $item)
                        @php
                            $isActive = $activePage === $item['key'] || in_array($activePage, $item['activeWhen'] ?? [], true);
                        @endphp
                        <a class="{{ $isActive ? 'bg-[#065F2A] text-white shadow-sm' : 'text-[#334155] hover:bg-[#F1F5F9] hover:text-[#0F172A]' }} flex items-center justify-between rounded-lg px-3 py-2.5 text-sm font-medium transition-colors" href="{{ $item['href'] }}">
                            <span class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-[20px] {{ $isActive ? '' : 'text-[#475569]' }}" @if ($isActive) style="font-variation-settings: 'FILL' 1;" @endif>{{ $item['icon'] }}</span>
                                <span>{{ $item['label'] }}</span>
                            </span>
                            @if (!empty($item['badge']))
                                <span class="{{ $isActive ? 'bg-white/20 text-white' : $badgeClass($item['badgeTone'] ?? null) }} rounded-full px-2 py-0.5 text-[10px] font-bold">{{ $item['badge'] }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </section>

            <section>
                <p class="mb-2 px-2 text-[10px] font-bold uppercase tracking-wider text-[#64748B]">Academics & Faculty</p>
                @php
                    $recommendActive = $activePage === 'catalog';
                @endphp
                <a class="{{ $recommendActive ? 'bg-[#065F2A] text-white shadow-sm' : 'text-[#334155] hover:bg-[#F1F5F9] hover:text-[#0F172A]' }} mb-1 flex items-center justify-between rounded-lg px-3 py-2.5 text-sm font-medium transition-colors" href="{{ route('staff.section', 'catalog') }}">
                    <span class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[20px] {{ $recommendActive ? '' : 'text-[#475569]' }}" @if ($recommendActive) style="font-variation-settings: 'FILL' 1;" @endif>assignment_add</span>
                        <span>Recommend a Book</span>
                    </span>
                </a>
                <a class="{{ $activePage === 'academic' ? 'bg-[#065F2A] text-white shadow-sm' : 'border border-[#CBD5E1] text-[#334155] hover:bg-[#F1F5F9]' }} flex items-center justify-between rounded-lg px-3 py-2.5 text-sm font-medium transition-colors" href="{{ route('staff.section', 'academic') }}">
                    <span class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[20px]" @if ($activePage === 'academic') style="font-variation-settings: 'FILL' 1;" @endif>school</span>
                        <span>Faculty Academic Requests</span>
                    </span>
                    <span class="material-symbols-outlined text-base">{{ $academicActive ? 'expand_less' : 'expand_more' }}</span>
                </a>
                <div class="mt-2 space-y-1 pl-8">
                    @foreach ($academicItems as $item)
                        @php $isActive = $activePage === $item['key']; @endphp
                        <a class="{{ $isActive ? 'font-bold text-[#065F2A]' : 'text-[#64748B] hover:text-[#065F2A]' }} flex items-center gap-2 rounded-md px-2 py-1.5 text-xs transition-colors" href="{{ $item['href'] }}">
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </section>

            <section>
                <div class="space-y-1">
                    @foreach ($lowerItems as $item)
                        @php $isActive = $activePage === $item['key']; @endphp
                        <a class="{{ $isActive ? 'bg-[#065F2A] text-white shadow-sm' : 'text-[#334155] hover:bg-[#F1F5F9] hover:text-[#0F172A]' }} flex items-center justify-between rounded-lg px-3 py-2.5 text-sm font-medium transition-colors" href="{{ $item['href'] }}">
                            <span class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-[20px] {{ $isActive ? '' : 'text-[#475569]' }}" @if ($isActive) style="font-variation-settings: 'FILL' 1;" @endif>{{ $item['icon'] }}</span>
                                <span>{{ $item['label'] }}</span>
                            </span>
                            @if (!empty($item['badge']))
                                <span class="{{ $isActive ? 'bg-white/20 text-white' : $badgeClass($item['badgeTone'] ?? null) }} rounded-full px-2 py-0.5 text-[10px] font-bold">{{ $item['badge'] }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </section>
        </nav>
    </div>

    <div class="border-t border-[#CBD5E1] bg-white p-5">
        <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold text-[#B91C1C] hover:bg-[#FEE2E2]" href="{{ route('login') }}">
            <span class="material-symbols-outlined text-[20px]">logout</span>
            <span>Log Out</span>
        </a>
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
