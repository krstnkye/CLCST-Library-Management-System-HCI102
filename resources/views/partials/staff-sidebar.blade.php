@php
    $activePage = $activePage ?? ($section ?? 'dashboard');
    $summary = $patronSummary;

    $groups = [
        'Library' => [
            ['dashboard', 'Dashboard', 'dashboard', route('faculty.dashboard'), null],
            ['search-books', 'Search Books', 'menu_book', route('books.catalog'), null],
            ['circulation', 'My Borrowed Books', 'swap_horiz', route('staff.section', 'circulation'), $summary['activeLoans'] ?: null],
            ['reservations', 'My Reservations', 'book_online', route('staff.section', 'reservations'), $summary['openReservations'] ?: null],
            ['history', 'Borrowing History', 'history', route('staff.section', 'history'), null],
            ['notifications', 'Notifications', 'notifications', route('staff.section', 'notifications'), $summary['notifications'] ?: null],
        ],
        'Teaching Support' => [
            ['catalog', 'Recommend a Book', 'add_shopping_cart', route('staff.section', 'catalog'), null],
            ['course-reserves', 'Course Reserves', 'assignment', route('staff.section', 'course-reserves'), null],
            ['reading-lists', 'Reading Lists', 'format_list_bulleted', route('staff.section', 'reading-lists'), null],
            ['instruction-sessions', 'Library Class Sessions', 'cast_for_education', route('staff.section', 'instruction-sessions'), null],
            ['inter-library-loan', 'Inter-Library Loan', 'sync_alt', route('staff.section', 'inter-library-loan'), null],
            ['academic', 'Academic Requests', 'school', route('staff.section', 'academic'), null],
        ],
        'Account' => [
            ['profile', 'My Profile', 'manage_accounts', route('staff.section', 'profile'), null],
            ['feedback', 'Library Feedback', 'rate_review', route('staff.section', 'feedback'), null],
            ['support', 'Help & Support', 'help_outline', route('staff.section', 'support'), null],
        ],
    ];
@endphp

<aside id="staff-sidebar" class="fixed bottom-0 left-0 top-16 z-30 hidden w-64 flex-shrink-0 flex-col justify-between border-r border-[#CBD5E1] bg-white md:flex" aria-label="Faculty navigation">
    <div class="custom-scrollbar flex flex-1 flex-col gap-4 overflow-y-auto p-4">
        @foreach ($groups as $heading => $items)
            <section>
                <p class="mb-1 px-3 text-[10px] font-bold uppercase tracking-wider text-[#64748B]">{{ $heading }}</p>
                <nav class="space-y-1">
                    @foreach ($items as [$key, $label, $icon, $href, $badge])
                        @php($isActive = $activePage === $key)
                        <a class="{{ $isActive ? 'bg-[#065F2A] text-white shadow-sm' : 'text-[#334155] hover:bg-[#F1F5F9]' }} flex items-center justify-between rounded-lg px-3 py-2 text-xs font-semibold" href="{{ $href }}" @if ($isActive) aria-current="page" @endif>
                            <span class="flex items-center gap-3"><span class="material-symbols-outlined text-[20px]">{{ $icon }}</span>{{ $label }}</span>
                            @if ($badge)<span class="rounded-full {{ $isActive ? 'bg-white/20' : 'bg-[#DBEAFE] text-[#1D4ED8]' }} px-2 py-0.5 text-[10px] font-bold">{{ $badge }}</span>@endif
                        </a>
                    @endforeach
                </nav>
            </section>
        @endforeach
    </div>
    <div class="border-t border-[#CBD5E1] p-4">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-50" type="submit"><span class="material-symbols-outlined">logout</span>Log Out</button>
        </form>
    </div>
</aside>

<nav class="custom-scrollbar fixed inset-x-0 bottom-0 z-40 flex gap-1 overflow-x-auto border-t border-[#CBD5E1] bg-white px-2 py-1.5 md:hidden" aria-label="Faculty navigation">
    @foreach (array_merge(...array_values($groups)) as [$key, $label, $icon, $href])
        <a class="{{ $activePage === $key ? 'bg-[#065F2A] text-white' : 'text-[#334155]' }} flex min-w-[4.5rem] flex-col items-center gap-0.5 rounded-lg px-2 py-1 text-center text-[10px] font-semibold" href="{{ $href }}"><span class="material-symbols-outlined text-[20px]">{{ $icon }}</span>{{ $label }}</a>
    @endforeach
</nav>
