<div class="grid gap-6 xl:grid-cols-12">
    <section class="rounded-lg border border-[#D6DED8] bg-white p-5 shadow-sm xl:col-span-5" aria-labelledby="checkout-heading">
        <h2 id="checkout-heading" class="font-display text-lg font-bold">Lend a book</h2>
        <p class="mt-1 text-xs text-[#67766E]">Scan or type the patron's school ID (or email) and the copy barcode. Loan limits, overdue blocks and waiting lists are checked automatically.</p>
        <form class="mt-4 space-y-3" method="POST" action="{{ route('librarian.checkout') }}">
            @csrf
            <div><label class="mb-1.5 block text-sm font-bold" for="patron">Patron ID or school email</label><input id="patron" name="patron" value="{{ old('patron') }}" required maxlength="150" autocomplete="off" placeholder="STU-2026-001" class="w-full rounded-lg border-[#CBD6CF] text-sm focus:border-[#0F5C2E] focus:ring-[#0F5C2E]"></div>
            <div><label class="mb-1.5 block text-sm font-bold" for="barcode">Copy barcode or copy code</label><input id="barcode" name="barcode" value="{{ old('barcode') }}" required maxlength="32" autocomplete="off" placeholder="1049201 or PY-001" class="w-full rounded-lg border-[#CBD6CF] font-mono text-sm focus:border-[#0F5C2E] focus:ring-[#0F5C2E]"></div>
            <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-[#0F5C2E] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#0A4421]" type="submit"><span class="material-symbols-outlined text-lg">outbox</span>Check out</button>
        </form>
        <p class="mt-4 rounded-lg bg-[#F4F7F5] p-3 text-xs leading-5 text-[#5E6D65]">Students: {{ $policy['max_loans_student'] }} books for {{ $policy['loan_days_student'] }} days. Faculty: {{ $policy['max_loans_faculty'] }} books for {{ $policy['loan_days_faculty'] }} days.</p>
    </section>

    <section class="rounded-lg border border-[#D6DED8] bg-white shadow-sm xl:col-span-7" aria-labelledby="queue-heading">
        <h2 id="queue-heading" class="border-b border-[#E2E8E4] px-5 py-4 font-display text-lg font-bold">Reservation requests ({{ $reservations->count() }})</h2>
        <ul class="divide-y divide-[#E2E8E4]">
            @forelse ($reservations as $reservation)
                <li class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="font-bold">{{ $reservation->book->title }}</p>
                        <p class="text-xs text-[#67766E]">{{ $reservation->user->name }} ({{ $reservation->user->patron_id }}, {{ $reservation->user->role }}) - requested {{ $reservation->created_at->diffForHumans() }}</p>
                        <p class="mt-1 text-xs font-semibold {{ $reservation->book->available_count > 0 ? 'text-emerald-700' : 'text-amber-700' }}">{{ $reservation->status === 'ready' ? 'Set aside until '.$reservation->ready_until?->format('M d') : ($reservation->book->available_count > 0 ? $reservation->book->available_count.' copy(ies) on the shelf' : 'No copy on the shelf yet') }}</p>
                    </div>
                    <div class="flex shrink-0 flex-wrap gap-2">
                        @if ($reservation->status === 'pending' && $reservation->book->available_count > 0)
                            <form method="POST" action="{{ route('librarian.reservations.ready', $reservation) }}">@csrf<button class="rounded-lg border border-[#0F5C2E] px-3 py-1.5 text-xs font-bold text-[#0F5C2E] hover:bg-[#EEF5F0]" type="submit">Notify: ready</button></form>
                        @endif
                        @if ($reservation->book->available_count > 0)
                            <form method="POST" action="{{ route('librarian.reservations.issue', $reservation) }}">@csrf<button class="rounded-lg bg-[#0F5C2E] px-3 py-1.5 text-xs font-bold text-white hover:bg-[#0A4421]" type="submit">Lend now</button></form>
                        @endif
                        <form method="POST" action="{{ route('librarian.reservations.cancel', $reservation) }}" onsubmit="return confirm('Cancel this reservation?')">@csrf<button class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-bold text-red-700 hover:bg-red-50" type="submit">Cancel</button></form>
                    </div>
                </li>
            @empty
                <li class="px-5 py-10 text-center text-sm text-[#67766E]">No reservations are waiting.</li>
            @endforelse
        </ul>
    </section>

    <section class="rounded-lg border border-[#D6DED8] bg-white shadow-sm xl:col-span-12" aria-labelledby="recent-heading">
        <h2 id="recent-heading" class="border-b border-[#E2E8E4] px-5 py-4 font-display text-lg font-bold">Recent loans</h2>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-left text-sm">
                <thead class="bg-[#F4F7F5] text-xs uppercase text-[#66756D]"><tr><th class="px-5 py-3">Receipt</th><th class="px-5 py-3">Patron</th><th class="px-5 py-3">Book</th><th class="px-5 py-3">Borrowed</th><th class="px-5 py-3">Due</th><th class="px-5 py-3">Status</th></tr></thead>
                <tbody class="divide-y divide-[#E2E8E4]">
                    @forelse ($recentLoans as $loan)
                        <tr><td class="px-5 py-3 font-mono text-xs">{{ $loan->receipt_no }}</td><td class="px-5 py-3">{{ $loan->user->name }}</td><td class="px-5 py-3">{{ $loan->copy->book->title }} <span class="font-mono text-xs text-[#718078]">{{ $loan->copy->copy_code }}</span></td><td class="px-5 py-3 text-xs">{{ $loan->borrowed_at->format('M d, Y') }}</td><td class="px-5 py-3 text-xs">{{ $loan->due_at->format('M d, Y') }}</td><td class="px-5 py-3 text-xs font-bold">{{ $loan->returned_at ? 'Returned' : ($loan->isOverdue() ? 'Overdue' : 'On loan') }}</td></tr>
                    @empty
                        <tr><td class="px-5 py-8 text-center text-[#67766E]" colspan="6">No loans recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
