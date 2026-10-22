@extends('librarian.layout')

@section('title', 'Librarian Dashboard')

@section('content')
    <section class="mb-6 flex flex-col justify-between gap-4 border-b border-[#D6DED8] pb-5 lg:flex-row lg:items-end">
        <div>
            <div class="mb-2 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-[#0F5C2E]">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>{{ now()->format('l, F j, Y') }}
            </div>
            <h1 class="font-display text-2xl font-extrabold text-[#17251D] md:text-3xl">Librarian Dashboard</h1>
            <p class="mt-1 text-sm text-[#5E6D65]">Overview of books, borrowing activity, library users, and requests.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a class="inline-flex items-center gap-2 rounded-lg border border-[#B9C8BF] bg-white px-4 py-2.5 text-sm font-bold text-[#0F5C2E] hover:bg-[#EEF5F0]" href="{{ route('librarian.section', 'returns') }}"><span class="material-symbols-outlined text-lg">assignment_return</span>Check In</a>
            <a class="inline-flex items-center gap-2 rounded-lg border border-[#B9C8BF] bg-white px-4 py-2.5 text-sm font-bold text-[#0F5C2E] hover:bg-[#EEF5F0]" href="{{ route('librarian.section', 'borrow-requests') }}"><span class="material-symbols-outlined text-lg">outbox</span>Check Out</a>
            <a class="inline-flex items-center gap-2 rounded-lg bg-[#0F5C2E] px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-[#0A4421]" href="{{ route('librarian.section', 'add-book') }}"><span class="material-symbols-outlined text-lg">library_add</span>Add Book</a>
        </div>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
        @foreach ([
            ['Total Book Titles', $stats['titles'], 'menu_book', 'text-[#0F5C2E]', 'bg-emerald-50'],
            ['Available Copies', $stats['availableCopies'], 'inventory_2', 'text-blue-700', 'bg-blue-50'],
            ['Borrowed Books', $stats['borrowedBooks'], 'outbox', 'text-violet-700', 'bg-violet-50'],
            ['Overdue Books', $stats['overdueBooks'], 'schedule', 'text-red-700', 'bg-red-50'],
            ['Active Patrons', $stats['registeredUsers'], 'group', 'text-cyan-700', 'bg-cyan-50'],
            ['Open Reservations', $stats['openReservations'], 'bookmark', 'text-amber-700', 'bg-amber-50'],
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
                <div><h2 class="font-display text-lg font-bold">Reservation Queue</h2><p class="mt-0.5 text-xs text-[#67766E]">Patrons waiting for a title, oldest first.</p></div>
                <a class="text-sm font-bold text-[#0F5C2E] hover:underline" href="{{ route('librarian.section', 'borrow-requests') }}">Open desk</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-left text-sm">
                    <thead class="bg-[#F4F7F5] text-xs uppercase text-[#66756D]"><tr><th class="px-5 py-3">Patron</th><th class="px-5 py-3">Book</th><th class="px-5 py-3">Requested</th><th class="px-5 py-3">Status</th></tr></thead>
                    <tbody class="divide-y divide-[#E2E8E4]">
                        @forelse ($pendingReservations as $reservation)
                            <tr><td class="px-5 py-4"><p class="font-bold">{{ $reservation->user->name }}</p><p class="text-xs text-[#718078]">{{ $reservation->user->patron_id }} - {{ ucfirst($reservation->user->role) }}</p></td><td class="px-5 py-4 font-medium">{{ $reservation->book->title }}</td><td class="px-5 py-4 text-xs">{{ $reservation->created_at->diffForHumans() }}</td><td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $reservation->status === 'ready' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">{{ $reservation->status === 'ready' ? 'Ready for pickup' : 'Waiting' }}</span></td></tr>
                        @empty
                            <tr><td class="px-5 py-8 text-center text-[#67766E]" colspan="4">No one is waiting for a book.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($dueToday->isNotEmpty())
                <div class="border-t border-[#E2E8E4] px-5 py-4 text-sm"><p class="font-bold">Due back today ({{ $dueToday->count() }})</p><ul class="mt-2 space-y-1 text-[#5F6E66]">@foreach ($dueToday as $loan)<li>{{ $loan->copy->book->title }} - {{ $loan->user->name }}</li>@endforeach</ul></div>
            @endif
        </section>

        <section class="rounded-lg border border-[#D6DED8] bg-white shadow-sm xl:col-span-4">
            <div class="border-b border-[#E2E8E4] px-5 py-4"><h2 class="font-display text-lg font-bold">Recent Desk Activity</h2><p class="mt-0.5 text-xs text-[#67766E]">Latest circulation actions from the activity log.</p></div>
            <div class="divide-y divide-[#E2E8E4]">
                @forelse ($activities as $activity)
                    <article class="flex gap-3 px-5 py-4">
                        <span class="material-symbols-outlined mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#EEF5F0] text-lg text-[#0F5C2E]">{{ str_starts_with($activity->event, 'fine') ? 'payments' : (str_starts_with($activity->event, 'reservation') ? 'bookmark' : ($activity->event === 'loan.returned' ? 'assignment_return' : 'outbox')) }}</span>
                        <div class="min-w-0"><p class="text-sm leading-5 text-[#24352C]">{{ $activity->description }}</p><p class="mt-1 text-[11px] text-[#718078]">{{ $activity->user?->name ?? 'System' }} - {{ $activity->created_at->diffForHumans() }}</p></div>
                    </article>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-[#67766E]">No circulation activity yet.</p>
                @endforelse
            </div>
        </section>
    </div>

    <section class="mt-6 rounded-lg border border-[#D6DED8] bg-white p-5 shadow-sm">
        <div class="mb-4"><h2 class="font-display text-lg font-bold">Quick Actions</h2><p class="mt-0.5 text-xs text-[#67766E]">Common tasks for the library desk.</p></div>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([['returns', 'assignment_return', 'Return a Book'], ['users', 'how_to_reg', 'Review Accounts ('.$stats['pendingAccounts'].')'], ['fines', 'payments', 'Record a Payment'], ['reports', 'monitoring', 'View Reports']] as [$page, $icon, $label])
                <a class="flex items-center gap-3 rounded-lg border border-[#D2DCD5] bg-[#F8FAF9] p-4 font-bold text-[#24352C] hover:border-[#0F5C2E] hover:bg-[#EEF5F0]" href="{{ route('librarian.section', $page) }}"><span class="material-symbols-outlined text-[#0F5C2E]">{{ $icon }}</span>{{ $label }}<span class="material-symbols-outlined ml-auto text-lg text-[#718078]">arrow_forward</span></a>
            @endforeach
        </div>
    </section>
@endsection
