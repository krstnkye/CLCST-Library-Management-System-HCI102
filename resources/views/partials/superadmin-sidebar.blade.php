@php
    $itemClass = fn (string $page) => $activePage === $page
        ? 'bg-[#201F1C] text-white shadow-sm'
        : 'text-[#34312C] hover:bg-[#F3F1ED] hover:text-[#284A30]';
@endphp

<aside id="admin-sidebar" class="clcst-sidebar fixed bottom-0 left-0 top-16 z-30 hidden w-72 border-r border-[#E1DCD3] bg-white md:flex md:flex-col">
    <div id="admin-sidebar-scroll" class="custom-scrollbar flex-1 overflow-y-auto px-4 py-5">
        <div class="mb-5 rounded-lg border border-amber-200 bg-amber-50 p-3">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined flex h-10 w-10 items-center justify-center rounded-lg bg-[#201F1C] text-white">shield_person</span>
                <div><p class="text-sm font-bold text-[#201F1C]">Super Admin Access</p><p class="text-xs text-[#666156]">System control and security</p></div>
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
                    <p class="mb-2 px-3 text-[11px] font-bold uppercase tracking-wider text-[#827C6F]">{{ $group['label'] }}</p>
                    <div class="space-y-1">
                        @foreach ($group['pages'] as [$page, $icon, $label])
                            <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold {{ $itemClass($page) }}" href="{{ route('superadmin.section', $page) }}" @if ($activePage === $page) aria-current="page" @endif><span class="material-symbols-outlined text-xl">{{ $icon }}</span><span>{{ $label }}</span></a>
                        @endforeach
                    </div>
                </section>
            @endforeach

            <section>
                <p class="mb-2 px-3 text-[11px] font-bold uppercase tracking-wider text-[#827C6F]">Monitoring and Data</p>
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
                <p class="mb-2 px-3 text-[11px] font-bold uppercase tracking-wider text-[#827C6F]">Communication and System</p>
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

    <div class="border-t border-[#E1DCD3] p-4">
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold text-red-700 hover:bg-red-50" type="submit"><span class="material-symbols-outlined text-xl">logout</span><span>Log Out</span></button></form>
    </div>
</aside>
