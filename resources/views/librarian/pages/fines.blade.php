<div class="space-y-6">
    <section class="rounded-lg border border-[#E1DCD3] bg-white shadow-sm">
        <div class="border-b border-[#EAE7E0] px-5 py-4">
            <h2 class="font-display text-lg font-bold">Unpaid fines ({{ $outstanding->count() }})</h2>
            <p class="mt-0.5 text-xs text-[#777166]">Record a payment after the cashier receives it, or waive a fine with an approved reason. Both issue a receipt number.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[820px] text-left text-sm">
                <thead class="bg-[#F7F6F4] text-xs uppercase text-[#767065]"><tr><th class="px-5 py-3">Patron</th><th class="px-5 py-3">Reason</th><th class="px-5 py-3">Days late</th><th class="px-5 py-3">Amount</th><th class="px-5 py-3 text-right">Action</th></tr></thead>
                <tbody class="divide-y divide-[#EAE7E0]">
                    @forelse ($outstanding as $fine)
                        <tr>
                            <td class="px-5 py-3"><p class="font-bold">{{ $fine->user->name }}</p><p class="text-xs text-[#827C6F]">{{ $fine->user->patron_id }}</p></td>
                            <td class="px-5 py-3">{{ $fine->reason }}<p class="text-xs text-[#827C6F]">Charged {{ $fine->created_at->format('M d, Y') }}</p></td>
                            <td class="px-5 py-3">{{ $fine->days_late }}</td>
                            <td class="px-5 py-3 font-bold">PHP {{ number_format((float) $fine->amount, 2) }}</td>
                            <td class="px-5 py-3"><div class="flex justify-end gap-2">
                                <form method="POST" action="{{ route('librarian.fines.settle', $fine) }}" data-confirm="Only record this after the cashier has received the payment. An official receipt number will be issued." data-confirm-title="Record PHP {{ number_format((float) $fine->amount, 2) }} payment?" data-confirm-detail="{{ $fine->user->name }} · {{ $fine->reason }}" data-confirm-label="Record payment" data-confirm-icon="payments">@csrf<input type="hidden" name="outcome" value="paid"><button class="rounded-lg bg-[#284A30] px-3 py-1.5 text-xs font-bold text-white hover:bg-[#183020]" type="submit">Record payment</button></form>
                                <form method="POST" action="{{ route('librarian.fines.settle', $fine) }}" data-confirm="The fine will be cleared without payment. This is recorded in the activity log." data-confirm-title="Waive this fine?" data-confirm-detail="{{ $fine->user->name }} · PHP {{ number_format((float) $fine->amount, 2) }}" data-confirm-label="Waive fine">@csrf<input type="hidden" name="outcome" value="waived"><button class="rounded-lg border border-[#D6D2CB] px-3 py-1.5 text-xs font-bold hover:bg-[#F7F6F4]" type="submit">Waive</button></form>
                            </div></td>
                        </tr>
                    @empty
                        <tr><td class="px-5 py-10 text-center text-[#777166]" colspan="5">No unpaid fines.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="rounded-lg border border-[#E1DCD3] bg-white shadow-sm">
        <h2 class="border-b border-[#EAE7E0] px-5 py-4 font-display text-lg font-bold">Recently settled</h2>
        <ul class="divide-y divide-[#EAE7E0] text-sm">
            @forelse ($settled as $fine)
                <li class="flex flex-wrap items-center justify-between gap-2 px-5 py-3"><span><strong>{{ $fine->user->name }}</strong> - PHP {{ number_format((float) $fine->amount, 2) }} {{ $fine->status }}</span><span class="text-xs text-[#827C6F]">{{ $fine->receipt_no }} - {{ $fine->settled_at?->format('M d, Y') }} by {{ $fine->settledBy?->name ?? 'staff' }}</span></li>
            @empty
                <li class="px-5 py-6 text-center text-[#777166]">Nothing settled yet.</li>
            @endforelse
        </ul>
    </section>
</div>
