@php
    $itemClass = fn (string $page) => $activePage === $page
        ? 'bg-[#0F5C2E] text-white shadow-sm'
        : 'text-[#24352C] hover:bg-[#EEF5F0] hover:text-[#0F5C2E]';
    $groupOpen = fn (array $pages) => collect($pages)->contains($activePage);
@endphp

<aside id="librarian-sidebar" class="fixed bottom-0 left-0 top-16 z-30 hidden w-72 border-r border-[#D6DED8] bg-white md:flex md:flex-col">
    <div id="librarian-sidebar-scroll" class="custom-scrollbar flex-1 overflow-y-auto px-4 py-5">
        <div class="mb-5 rounded-lg border border-[#C9D9CE] bg-[#F3F8F5] p-3">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined flex h-10 w-10 items-center justify-center rounded-lg bg-[#0F5C2E] text-white">local_library</span>
                <div>
                    <p class="text-sm font-bold text-[#0F5C2E]">Main Library</p>
                    <p class="text-xs text-[#617068]">Librarian Workstation 04</p>
                </div>
            </div>
        </div>

        <nav aria-label="Librarian navigation" class="space-y-5">
            <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold {{ $itemClass('dashboard') }}" href="{{ route('librarian.dashboard') }}">
                <span class="material-symbols-outlined text-xl">dashboard</span><span>Dashboard</span>
            </a>

            @foreach ([
                ['label' => 'Books', 'icon' => 'menu_book', 'pages' => [['books', 'Book Catalog'], ['add-book', 'Add New Book'], ['categories', 'Categories'], ['inventory', 'Inventory']]],
                ['label' => 'Transactions', 'icon' => 'swap_horiz', 'pages' => [['borrow-requests', 'Borrow Requests'], ['returns', 'Return Books'], ['overdue', 'Overdue Books']]],
                ['label' => 'Users', 'icon' => 'group', 'pages' => [['users', 'User Accounts'], ['verification', 'Account Verification'], ['memberships', 'Memberships']]],
            ] as $group)
                <section>
                    <p class="mb-2 px-3 text-[11px] font-bold uppercase tracking-wider text-[#718078]">{{ $group['label'] }}</p>
                    <div class="space-y-1">
                        @foreach ($group['pages'] as [$page, $label])
                            <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold {{ $itemClass($page) }}" href="{{ route('librarian.section', $page) }}">
                                <span class="material-symbols-outlined text-xl">{{ $loop->first ? $group['icon'] : 'chevron_right' }}</span><span>{{ $label }}</span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endforeach

            <section>
                <p class="mb-2 px-3 text-[11px] font-bold uppercase tracking-wider text-[#718078]">Library Services</p>
                <div class="space-y-1">
                    @foreach ([
                        ['reservations', 'bookmark', 'Reservations'],
                        ['digital-resources', 'cloud', 'Digital Resources'],
                        ['fines', 'payments', 'Fines and Payments'],
                        ['reports', 'monitoring', 'Reports'],
                        ['announcements', 'campaign', 'Announcements'],
                        ['feedback', 'forum', 'Feedback and Requests'],
                    ] as [$page, $icon, $label])
                        <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold {{ $itemClass($page) }}" href="{{ route('librarian.section', $page) }}">
                            <span class="material-symbols-outlined text-xl">{{ $icon }}</span><span>{{ $label }}</span>
                        </a>
                    @endforeach
                </div>
            </section>

            <section>
                <p class="mb-2 px-3 text-[11px] font-bold uppercase tracking-wider text-[#718078]">Administration</p>
                <div class="space-y-1">
                    @foreach ([
                        ['staff-management', 'badge', 'Library Staff'],
                        ['activity-logs', 'history', 'Activity Logs'],
                        ['settings', 'settings', 'Settings'],
                        ['profile', 'account_circle', 'My Profile'],
                        ['support', 'help', 'Help and Support'],
                    ] as [$page, $icon, $label])
                        <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold {{ $itemClass($page) }}" href="{{ route('librarian.section', $page) }}">
                            <span class="material-symbols-outlined text-xl">{{ $icon }}</span><span>{{ $label }}</span>
                        </a>
                    @endforeach
                </div>
            </section>
        </nav>
    </div>

    <div class="border-t border-[#D6DED8] p-4">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold text-red-700 hover:bg-red-50" type="submit">
                <span class="material-symbols-outlined text-xl">logout</span><span>Log Out</span>
            </button>
        </form>
    </div>
</aside>

<script>
    (() => {
        const sidebar = document.getElementById('librarian-sidebar-scroll');
        const storageKey = 'clcst-librarian-sidebar-scroll';
        if (!sidebar) return;
        const restore = () => sidebar.scrollTop = Number(sessionStorage.getItem(storageKey) || 0);
        restore();
        sidebar.addEventListener('scroll', () => sessionStorage.setItem(storageKey, String(sidebar.scrollTop)), { passive: true });
        document.querySelectorAll('#librarian-sidebar a[href]').forEach(link => {
            link.addEventListener('click', () => sessionStorage.setItem(storageKey, String(sidebar.scrollTop)));
        });
    })();
</script>
