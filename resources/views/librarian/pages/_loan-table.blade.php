<div class="overflow-x-auto">
    <table class="w-full min-w-[860px] text-left text-sm">
        <thead class="bg-[#F4F7F5] text-xs uppercase text-[#66756D]"><tr><th class="px-5 py-3">Patron</th><th class="px-5 py-3">Book</th><th class="px-5 py-3">Borrowed</th><th class="px-5 py-3">Due</th><th class="px-5 py-3">Status</th><th class="px-5 py-3 text-right">Action</th></tr></thead>
        <tbody class="divide-y divide-[#E2E8E4]">
            @forelse ($loans as $loan)
                <tr class="{{ $loan->isOverdue() ? 'bg-red-50/40' : '' }}">
                    <td class="px-5 py-3"><p class="font-bold">{{ $loan->user->name }}</p><p class="text-xs text-[#718078]">{{ $loan->user->patron_id }} - {{ $loan->user->mobile_number }}</p></td>
                    <td class="px-5 py-3"><p class="font-medium">{{ $loan->copy->book->title }}</p><p class="font-mono text-xs text-[#718078]">{{ $loan->copy->copy_code }} / {{ $loan->copy->barcode }}</p></td>
                    <td class="px-5 py-3 text-xs">{{ $loan->borrowed_at->format('M d, Y') }}</td>
                    <td class="px-5 py-3 text-xs">{{ $loan->due_at->format('M d, Y') }}</td>
                    <td class="px-5 py-3 text-xs font-bold {{ $loan->isOverdue() ? 'text-red-700' : '' }}">{{ $loan->isOverdue() ? $loan->daysLate().' day(s) late - PHP '.number_format($loan->accruedFine(), 2) : $loan->daysRemaining().' day(s) left' }}</td>
                    <td class="px-5 py-3 text-right"><form method="POST" action="{{ route('librarian.loans.checkin', $loan) }}" data-confirm="{{ $loan->isOverdue() ? 'This book is '.$loan->daysLate().' day(s) late. A fine of PHP '.number_format($loan->accruedFine(), 2).' will be added to the patron\'s account.' : 'The copy will go back on the shelf and the loan will be closed.' }}" data-confirm-title="Check in this book?" data-confirm-detail="{{ $loan->copy->book->title }} · {{ $loan->user->name }}" data-confirm-label="Check in" data-confirm-icon="assignment_return">@csrf<button class="rounded-lg bg-[#0F5C2E] px-3 py-1.5 text-xs font-bold text-white hover:bg-[#0A4421]" type="submit">Check in</button></form></td>
                </tr>
            @empty
                <tr><td class="px-5 py-10 text-center text-[#67766E]" colspan="6">{{ $empty ?? 'No loans to show.' }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
