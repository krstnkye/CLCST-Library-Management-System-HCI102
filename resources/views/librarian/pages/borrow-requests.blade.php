<div class="grid gap-6 xl:grid-cols-12">
    <section class="rounded-lg border border-[#E1DCD3] bg-white p-5 shadow-sm xl:col-span-5" aria-labelledby="checkout-heading">
        <h2 id="checkout-heading" class="font-display text-lg font-bold">Lend a book</h2>
        <p class="mt-1 text-xs text-[#777166]">Scan or type the patron's school ID (or email) and the copy barcode. Loan limits, overdue blocks and waiting lists are checked automatically.</p>
        <form class="mt-4 space-y-3" method="POST" action="{{ route('librarian.checkout') }}">
            @csrf
            <div><label class="mb-1.5 block text-sm font-bold" for="patron">Patron ID or school email</label><div class="flex gap-2"><input id="patron" name="patron" value="{{ old('patron') }}" required maxlength="150" autocomplete="off" placeholder="STU-2026-001" class="w-full rounded-lg border-[#D6D2CB] text-sm focus:border-[#284A30] focus:ring-[#284A30]"><button class="ui-scan-btn" type="button" aria-label="Scan with the camera" data-scan data-scan-target="#patron" data-scan-title="Scan the patron e-Pass or ID"><span class="material-symbols-outlined text-lg">barcode_scanner</span>Scan</button></div></div>
            <div><label class="mb-1.5 block text-sm font-bold" for="barcode">Copy barcode or copy code</label><div class="flex gap-2"><input id="barcode" name="barcode" value="{{ old('barcode') }}" required maxlength="32" autocomplete="off" placeholder="1049201 or PY-001" class="w-full rounded-lg border-[#D6D2CB] font-mono text-sm focus:border-[#284A30] focus:ring-[#284A30]"><button class="ui-scan-btn" type="button" aria-label="Scan with the camera" data-scan data-scan-target="#barcode" data-scan-title="Scan the copy barcode"><span class="material-symbols-outlined text-lg">barcode_scanner</span>Scan</button></div></div>
            <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-[#284A30] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#183020]" type="submit"><span class="material-symbols-outlined text-lg">outbox</span>Check out</button>
        </form>
        <p class="mt-4 rounded-lg bg-[#F7F6F4] p-3 text-xs leading-5 text-[#6E685D]">Students: {{ $policy['max_loans_student'] }} books for {{ $policy['loan_days_student'] }} days. Faculty: {{ $policy['max_loans_faculty'] }} books for {{ $policy['loan_days_faculty'] }} days.</p>
    </section>

    <section class="rounded-lg border border-[#E1DCD3] bg-white shadow-sm xl:col-span-7" aria-labelledby="queue-heading">
        <h2 id="queue-heading" class="border-b border-[#EAE7E0] px-5 py-4 font-display text-lg font-bold">Reservation requests ({{ $reservations->count() }})</h2>
        <ul class="divide-y divide-[#EAE7E0]">
            @forelse ($reservations as $reservation)
                <li class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="font-bold">{{ $reservation->book->title }}</p>
                        <p class="text-xs text-[#777166]">{{ $reservation->user->name }} ({{ $reservation->user->patron_id }}, {{ $reservation->user->role }}) - requested {{ $reservation->created_at->diffForHumans() }}</p>
                        <p class="mt-1 text-xs font-semibold {{ $reservation->book->available_count > 0 ? 'text-emerald-700' : 'text-amber-700' }}">{{ $reservation->status === 'ready' ? 'Set aside until '.$reservation->ready_until?->format('M d') : ($reservation->book->available_count > 0 ? $reservation->book->available_count.' copy(ies) on the shelf' : 'No copy on the shelf yet') }}</p>
                    </div>
                    <div class="flex shrink-0 flex-wrap gap-2">
                        @if ($reservation->status === 'pending' && $reservation->book->available_count > 0)
                            <form method="POST" action="{{ route('librarian.reservations.ready', $reservation) }}">@csrf<button class="rounded-lg border border-[#284A30] px-3 py-1.5 text-xs font-bold text-[#284A30] hover:bg-[#F4F2EF]" type="submit">Notify: ready</button></form>
                        @endif
                        @if ($reservation->book->available_count > 0)
                            <form method="POST" action="{{ route('librarian.reservations.issue', $reservation) }}" data-confirm="A shelf copy will be checked out to {{ $reservation->user->name }} now and the reservation will be marked fulfilled." data-confirm-title="Lend this book now?" data-confirm-detail="{{ $reservation->book->title }}" data-confirm-label="Lend now" data-confirm-icon="outbox">@csrf<button class="rounded-lg bg-[#284A30] px-3 py-1.5 text-xs font-bold text-white hover:bg-[#183020]" type="submit">Lend now</button></form>
                        @endif
                        <form method="POST" action="{{ route('librarian.reservations.cancel', $reservation) }}" data-confirm="{{ $reservation->user->name }} will be taken off the list for this title." data-confirm-title="Cancel this reservation?" data-confirm-detail="{{ $reservation->book->title }}" data-confirm-label="Cancel reservation">@csrf<button class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-bold text-red-700 hover:bg-red-50" type="submit">Cancel</button></form>
                    </div>
                </li>
            @empty
                <li class="px-5 py-10 text-center text-sm text-[#777166]">No reservations are waiting.</li>
            @endforelse
        </ul>
    </section>

    <section class="rounded-lg border border-[#E1DCD3] bg-white shadow-sm xl:col-span-12" aria-labelledby="recent-heading">
        <h2 id="recent-heading" class="border-b border-[#EAE7E0] px-5 py-4 font-display text-lg font-bold">Recent loans</h2>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-left text-sm">
                <thead class="bg-[#F7F6F4] text-xs uppercase text-[#767065]"><tr><th class="px-5 py-3">Receipt</th><th class="px-5 py-3">Patron</th><th class="px-5 py-3">Book</th><th class="px-5 py-3">Borrowed</th><th class="px-5 py-3">Due</th><th class="px-5 py-3">Status</th></tr></thead>
                <tbody class="divide-y divide-[#EAE7E0]">
                    @forelse ($recentLoans as $loan)
                        <tr><td class="px-5 py-3 font-mono text-xs">{{ $loan->receipt_no }}</td><td class="px-5 py-3">{{ $loan->user->name }}</td><td class="px-5 py-3">{{ $loan->copy->book->title }} <span class="font-mono text-xs text-[#827C6F]">{{ $loan->copy->copy_code }}</span></td><td class="px-5 py-3 text-xs">{{ $loan->borrowed_at->format('M d, Y') }}</td><td class="px-5 py-3 text-xs">{{ $loan->due_at->format('M d, Y') }}</td><td class="px-5 py-3 text-xs font-bold">{{ $loan->returned_at ? 'Returned' : ($loan->isOverdue() ? 'Overdue' : 'On loan') }}</td></tr>
                    @empty
                        <tr><td class="px-5 py-8 text-center text-[#777166]" colspan="6">No loans recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
