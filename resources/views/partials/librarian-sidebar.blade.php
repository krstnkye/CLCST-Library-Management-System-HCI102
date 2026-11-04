@php
    $itemClass = fn (string $page) => $activePage === $page
        ? 'bg-[#284A30] text-white shadow-sm'
        : 'text-[#302E29] hover:bg-[#F4F2EF] hover:text-[#284A30]';
@endphp

<aside id="librarian-sidebar" class="clcst-sidebar fixed bottom-0 left-0 top-16 z-30 hidden w-72 border-r border-[#E1DCD3] bg-white md:flex md:flex-col">
    <div id="librarian-sidebar-scroll" class="custom-scrollbar flex-1 overflow-y-auto px-4 py-5">
        <div class="mb-5 rounded-lg border border-[#D7D3CB] bg-[#F7F6F4] p-3">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined flex h-10 w-10 items-center justify-center rounded-lg bg-[#284A30] text-white">local_library</span>
                <div>
                    <p class="text-sm font-bold text-[#284A30]">Main Library</p>
                    <p class="text-xs text-[#716B60]">{{ auth()->user()->roleLabel() }} workspace</p>
                </div>
            </div>
        </div>

        <nav aria-label="Librarian navigation" class="space-y-5">
            <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold {{ $itemClass('dashboard') }}" href="{{ route('librarian.dashboard') }}" @if ($activePage === 'dashboard') aria-current="page" @endif>
                <span class="material-symbols-outlined text-xl">dashboard</span><span>Dashboard</span>
            </a>

            @foreach ([
                ['label' => 'Books', 'pages' => [['books', 'menu_book', 'Book Catalog'], ['add-book', 'library_add', 'Add New Book'], ['categories', 'category', 'Categories'], ['inventory', 'inventory_2', 'Inventory']]],
                ['label' => 'Circulation', 'pages' => [['borrow-requests', 'outbox', 'Check Out Books'], ['returns', 'assignment_return', 'Check In Returns'], ['overdue', 'schedule', 'Overdue Books'], ['reservations', 'bookmark', 'Reservations']]],
                ['label' => 'Users', 'pages' => [['users', 'how_to_reg', 'Account Review']]],
            ] as $group)
                <section>
                    <p class="mb-2 px-3 text-[11px] font-bold uppercase tracking-wider text-[#827C6F]">{{ $group['label'] }}</p>
                    <div class="space-y-1">
                        @foreach ($group['pages'] as [$page, $icon, $label])
                            <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold {{ $itemClass($page) }}" href="{{ route('librarian.section', $page) }}" @if ($activePage === $page) aria-current="page" @endif>
                                <span class="material-symbols-outlined text-xl">{{ $icon }}</span><span>{{ $label }}</span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endforeach

            <section>
                <p class="mb-2 px-3 text-[11px] font-bold uppercase tracking-wider text-[#827C6F]">Library Services</p>
                <div class="space-y-1">
                    @foreach ([
                        ['fines', 'payments', 'Fines and Payments'],
                        ['reports', 'monitoring', 'Reports'],
                        ['announcements', 'campaign', 'Announcements'],
                        ['feedback', 'forum', 'Feedback and Requests'],
                    ] as [$page, $icon, $label])
                        <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold {{ $itemClass($page) }}" href="{{ route('librarian.section', $page) }}" @if ($activePage === $page) aria-current="page" @endif>
                            <span class="material-symbols-outlined text-xl">{{ $icon }}</span><span>{{ $label }}</span>
                        </a>
                    @endforeach
                </div>
            </section>

            <section>
                <p class="mb-2 px-3 text-[11px] font-bold uppercase tracking-wider text-[#827C6F]">Administration</p>
                <div class="space-y-1">
                    @foreach ([
                        ['notifications', 'notifications', 'Alerts'],
                        ['activity-logs', 'history', 'Activity Logs'],
                        ['settings', 'rule', 'Borrowing Policy'],
                        ['profile', 'account_circle', 'My Profile'],
                        ['support', 'help', 'Help and Support'],
                    ] as [$page, $icon, $label])
                        <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold {{ $itemClass($page) }}" href="{{ route('librarian.section', $page) }}" @if ($activePage === $page) aria-current="page" @endif>
                            <span class="material-symbols-outlined text-xl">{{ $icon }}</span><span>{{ $label }}</span>
                        </a>
                    @endforeach
                </div>
            </section>
        </nav>
        <div class="mt-5 border-t border-[#E1DCD3] pt-4">
            <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold {{ $itemClass('bulk-data') }}" href="{{ route('management.data.index') }}" @if ($activePage === 'bulk-data') aria-current="page" @endif><span class="material-symbols-outlined text-xl">upload_file</span><span>Import and Export</span></a>
        </div>
    </div>

    <div class="border-t border-[#E1DCD3] p-4">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold text-red-700 hover:bg-red-50" type="submit">
                <span class="material-symbols-outlined text-xl">logout</span><span>Log Out</span>
            </button>
        </form>
    </div>
</aside>
