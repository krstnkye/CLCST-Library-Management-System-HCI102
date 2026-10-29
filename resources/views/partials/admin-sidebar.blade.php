@php
    $items = [
        ['dashboard', 'dashboard', 'Dashboard', route('admin.dashboard')],
        ['users', 'group', 'User Accounts', route('admin.section', 'users')],
        ['approvals', 'how_to_reg', 'Account Approvals', route('admin.section', 'approvals')],
        ['books', 'menu_book', 'Book Records', route('admin.section', 'books')],
        ['circulation', 'sync_alt', 'Library Transactions', route('admin.section', 'circulation')],
        ['reports', 'bar_chart', 'Reports', route('admin.section', 'reports')],
        ['bulk-data', 'upload_file', 'Import and Export', route('management.data.index')],
        ['audit-logs', 'history', 'Activity Log', route('admin.section', 'audit-logs')],
        ['announcements', 'campaign', 'Announcements', route('admin.section', 'announcements')],
        ['profile', 'account_circle', 'My Profile', route('admin.section', 'profile')],
    ];
@endphp
<aside id="administrator-sidebar" class="clcst-sidebar fixed bottom-0 left-0 top-16 z-30 hidden w-64 flex-col border-r border-slate-200 bg-white md:flex">
    <div id="administrator-sidebar-scroll" class="flex-1 overflow-y-auto p-4">
        <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 p-3"><p class="text-sm font-bold text-emerald-900">Administrator Access</p><p class="mt-1 text-xs text-emerald-800">Accounts, records, reports, and data tools</p></div>
        <nav aria-label="Administrator navigation" class="space-y-1">
            @foreach($items as [$page,$icon,$label,$href])
                <a href="{{ $href }}" class="flex items-center gap-3 rounded-md px-3 py-2.5 text-sm font-medium {{ $activePage === $page ? 'bg-emerald-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}" @if ($activePage === $page) aria-current="page" @endif><span class="material-symbols-outlined text-xl">{{ $icon }}</span><span>{{ $label }}</span></a>
            @endforeach
        </nav>
    </div>
    <div class="border-t border-slate-200 p-4"><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="flex w-full items-center gap-3 rounded-md px-3 py-2.5 text-sm font-medium text-red-700 hover:bg-red-50"><span class="material-symbols-outlined text-xl">logout</span>Log Out</button></form></div>
</aside>
<script>
    (() => { const el=document.getElementById('administrator-sidebar-scroll'), key='clcst.admin.sidebar'; if(!el)return; el.scrollTop=Number(sessionStorage.getItem(key)||0); el.addEventListener('scroll',()=>sessionStorage.setItem(key,String(el.scrollTop)),{passive:true}); document.querySelectorAll('#administrator-sidebar a').forEach(a=>a.addEventListener('click',()=>sessionStorage.setItem(key,String(el.scrollTop)))); })();
</script>
