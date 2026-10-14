@extends('librarian.layout')

@section('title', 'Librarian Dashboard')

@section('content')
    <section class="mb-6 flex flex-col justify-between gap-4 border-b border-[#D6DED8] pb-5 lg:flex-row lg:items-end">
        <div>
            <div class="mb-2 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-[#0F5C2E]">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>Library services online
            </div>
            <h1 class="font-display text-2xl font-extrabold text-[#17251D] md:text-3xl">Librarian Dashboard</h1>
            <p class="mt-1 text-sm text-[#5E6D65]">Overview of books, borrowing activity, library users, and requests.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a class="inline-flex items-center gap-2 rounded-lg border border-[#B9C8BF] bg-white px-4 py-2.5 text-sm font-bold text-[#0F5C2E] hover:bg-[#EEF5F0]" href="{{ route('librarian.section', 'users') }}"><span class="material-symbols-outlined text-lg">person_add</span>Add User</a>
            <a class="inline-flex items-center gap-2 rounded-lg border border-[#B9C8BF] bg-white px-4 py-2.5 text-sm font-bold text-[#0F5C2E] hover:bg-[#EEF5F0]" href="{{ route('librarian.section', 'borrow-requests') }}"><span class="material-symbols-outlined text-lg">outbox</span>Issue Book</a>
            <a class="inline-flex items-center gap-2 rounded-lg bg-[#0F5C2E] px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-[#0A4421]" href="{{ route('librarian.section', 'add-book') }}"><span class="material-symbols-outlined text-lg">library_add</span>Add Book</a>
        </div>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
        @foreach ([
            ['Total Book Titles', $stats['titles'], 'menu_book', 'text-[#0F5C2E]', 'bg-emerald-50'],
            ['Available Copies', $stats['availableCopies'], 'inventory_2', 'text-blue-700', 'bg-blue-50'],
            ['Borrowed Books', $stats['borrowedBooks'], 'outbox', 'text-violet-700', 'bg-violet-50'],
            ['Overdue Books', $stats['overdueBooks'], 'schedule', 'text-red-700', 'bg-red-50'],
            ['Registered Users', $stats['registeredUsers'], 'group', 'text-cyan-700', 'bg-cyan-50'],
            ['Pending Requests', $stats['pendingRequests'], 'pending_actions', 'text-amber-700', 'bg-amber-50'],
        ] as [$label, $value, $icon, $tone, $surface])
            <article class="rounded-lg border border-[#D6DED8] bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div><p class="text-xs font-semibold text-[#67766E]">{{ $label }}</p><p class="mt-2 font-display text-2xl font-extrabold text-[#17251D]">{{ $value }}</p></div>
                    <span class="material-symbols-outlined rounded-lg p-2 {{ $surface }} {{ $tone }}">{{ $icon }}</span>
                </div>
            </article>
        @endforeach
    </section>

    <div class="mt-6 grid gap-6 xl:grid-cols-12">
        <section class="rounded-lg border border-[#D6DED8] bg-white shadow-sm xl:col-span-8">
            <div class="flex items-center justify-between border-b border-[#E2E8E4] px-5 py-4">
                <div><h2 class="font-display text-lg font-bold">Borrowing Desk Queue</h2><p class="mt-0.5 text-xs text-[#67766E]">Requests that need librarian action.</p></div>
                <a class="text-sm font-bold text-[#0F5C2E] hover:underline" href="{{ route('librarian.section', 'borrow-requests') }}">View all</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-left text-sm">
                    <thead class="bg-[#F4F7F5] text-xs uppercase text-[#66756D]"><tr><th class="px-5 py-3">Request</th><th class="px-5 py-3">Borrower</th><th class="px-5 py-3">Book</th><th class="px-5 py-3">Status</th><th class="px-5 py-3 text-right">Action</th></tr></thead>
                    <tbody class="divide-y divide-[#E2E8E4]">
                        @foreach ($circulationQueue as $item)
                            <tr class="hover:bg-[#F8FAF9]"><td class="px-5 py-4"><p class="font-bold text-[#17251D]">{{ $item['type'] }}</p><p class="text-xs text-[#718078]">{{ $item['ticket'] }}</p></td><td class="px-5 py-4">{{ $item['patron'] }}</td><td class="px-5 py-4 font-medium">{{ $item['item'] }}</td><td class="px-5 py-4"><span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-800">{{ $item['status'] }}</span></td><td class="px-5 py-4 text-right"><a class="font-bold text-[#0F5C2E] hover:underline" href="{{ route('librarian.section', 'borrow-requests') }}">Review</a></td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-lg border border-[#D6DED8] bg-white shadow-sm xl:col-span-4">
            <div class="border-b border-[#E2E8E4] px-5 py-4"><h2 class="font-display text-lg font-bold">Recent Activity</h2><p class="mt-0.5 text-xs text-[#67766E]">Latest changes recorded today.</p></div>
            <div class="divide-y divide-[#E2E8E4]">
                @foreach ($activities as $activity)
                    <article class="flex gap-3 px-5 py-4">
                        <span class="material-symbols-outlined mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#EEF5F0] text-lg text-[#0F5C2E]">{{ $activity['icon'] }}</span>
                        <div class="min-w-0"><div class="flex items-center justify-between gap-3"><p class="font-bold text-[#17251D]">{{ $activity['action'] }}</p><time class="shrink-0 text-[11px] text-[#718078]">{{ $activity['time'] }}</time></div><p class="mt-1 text-xs leading-5 text-[#5F6E66]">{{ $activity['detail'] }}</p></div>
                    </article>
                @endforeach
            </div>
        </section>
    </div>

    <section class="mt-6 rounded-lg border border-[#D6DED8] bg-white p-5 shadow-sm">
        <div class="mb-4"><h2 class="font-display text-lg font-bold">Quick Actions</h2><p class="mt-0.5 text-xs text-[#67766E]">Common tasks for the library desk.</p></div>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([['returns', 'assignment_return', 'Return a Book'], ['verification', 'how_to_reg', 'Verify New User'], ['inventory', 'barcode_scanner', 'Check Inventory'], ['reports', 'monitoring', 'View Reports']] as [$page, $icon, $label])
                <a class="flex items-center gap-3 rounded-lg border border-[#D2DCD5] bg-[#F8FAF9] p-4 font-bold text-[#24352C] hover:border-[#0F5C2E] hover:bg-[#EEF5F0]" href="{{ route('librarian.section', $page) }}"><span class="material-symbols-outlined text-[#0F5C2E]">{{ $icon }}</span>{{ $label }}<span class="material-symbols-outlined ml-auto text-lg text-[#718078]">arrow_forward</span></a>
            @endforeach
        </div>
    </section>
@endsection
