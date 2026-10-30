@php
    $itemClass = fn (string $page) => $activePage === $page
        ? 'bg-[#17251D] text-white shadow-sm'
        : 'text-[#29372F] hover:bg-[#EEF2EF] hover:text-[#0F5C2E]';
@endphp

<aside id="admin-sidebar" class="clcst-sidebar fixed bottom-0 left-0 top-16 z-30 hidden w-72 border-r border-[#D6DED8] bg-white md:flex md:flex-col">
    <div id="admin-sidebar-scroll" class="custom-scrollbar flex-1 overflow-y-auto px-4 py-5">
        <div class="mb-5 rounded-lg border border-amber-200 bg-amber-50 p-3">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined flex h-10 w-10 items-center justify-center rounded-lg bg-[#17251D] text-white">shield_person</span>
                <div><p class="text-sm font-bold text-[#17251D]">Super Admin Access</p><p class="text-xs text-[#6B6251]">System control and security</p></div>
            </div>
        </div>

        <nav aria-label="Super Admin navigation" class="space-y-5">
            <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold {{ $itemClass('dashboard') }}" href="{{ route('superadmin.dashboard') }}" @if ($activePage === 'dashboard') aria-current="page" @endif><span class="material-symbols-outlined text-xl">dashboard</span><span>Dashboard</span></a>

            @foreach ([
                ['label' => 'User Management', 'pages' => [['users', 'group', 'All Users'], ['roles', 'admin_panel_settings', 'Roles and Permissions'], ['approvals', 'approval', 'Account Approvals']]],
                ['label' => 'Staff Management', 'pages' => [['librarians', 'local_library', 'Librarians'], ['library-staff', 'badge', 'Administrators']]],
                ['label' => 'System Configuration', 'pages' => [['borrowing-rules', 'rule', 'Borrowing Rules'], ['fine-settings', 'payments', 'Fine Settings'], ['system-policies', 'policy', 'System Policies']]],
            ] as $group)
                <section>
                    <p class="mb-2 px-3 text-[11px] font-bold uppercase tracking-wider text-[#718078]">{{ $group['label'] }}</p>
                    <div class="space-y-1">
                        @foreach ($group['pages'] as [$page, $icon, $label])
                            <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold {{ $itemClass($page) }}" href="{{ route('superadmin.section', $page) }}" @if ($activePage === $page) aria-current="page" @endif><span class="material-symbols-outlined text-xl">{{ $icon }}</span><span>{{ $label }}</span></a>
                        @endforeach
                    </div>
                </section>
            @endforeach

            <section>
                <p class="mb-2 px-3 text-[11px] font-bold uppercase tracking-wider text-[#718078]">Monitoring and Data</p>
                <div class="space-y-1">
                    @foreach ([
                        ['reports', 'monitoring', 'Reports and Analytics'],
                        ['data-export', 'download', 'Data Export'],
                        ['security', 'security', 'Security Management'],
                        ['audit-logs', 'history', 'Audit Logs'],
                        ['maintenance', 'build', 'System Status'],
                    ] as [$page, $icon, $label])
                        <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold {{ $itemClass($page) }}" href="{{ $page === 'data-export' ? route('management.data.index') : route('superadmin.section', $page) }}" @if ($activePage === $page) aria-current="page" @endif><span class="material-symbols-outlined text-xl">{{ $icon }}</span><span>{{ $label }}</span></a>
                    @endforeach
                </div>
            </section>

            <section>
                <p class="mb-2 px-3 text-[11px] font-bold uppercase tracking-wider text-[#718078]">Communication and System</p>
                <div class="space-y-1">
                    @foreach ([
                        ['announcements', 'campaign', 'Announcements'],
                        ['circulation', 'local_library', 'Library Operations'],
                        ['profile', 'account_circle', 'Profile Settings'],
                    ] as [$page, $icon, $label])
                        <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold {{ $itemClass($page) }}" href="{{ route('superadmin.section', $page) }}" @if ($activePage === $page) aria-current="page" @endif><span class="material-symbols-outlined text-xl">{{ $icon }}</span><span>{{ $label }}</span></a>
                    @endforeach
                </div>
            </section>
        </nav>
    </div>

    <div class="border-t border-[#D6DED8] p-4">
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold text-red-700 hover:bg-red-50" type="submit"><span class="material-symbols-outlined text-xl">logout</span><span>Log Out</span></button></form>
    </div>
</aside>


<script>
    (() => {
        const sidebar = document.getElementById('admin-sidebar-scroll');
        const storageKey = 'clcst-super-admin-sidebar-scroll';
        if (!sidebar) return;
        sidebar.scrollTop = Number(sessionStorage.getItem(storageKey) || 0);
        sidebar.addEventListener('scroll', () => sessionStorage.setItem(storageKey, String(sidebar.scrollTop)), { passive: true });
        document.querySelectorAll('#admin-sidebar a[href]').forEach(link => link.addEventListener('click', () => sessionStorage.setItem(storageKey, String(sidebar.scrollTop))));
    })();
</script>
