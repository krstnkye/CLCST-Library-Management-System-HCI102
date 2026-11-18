<div class="overflow-x-auto">
    <table class="w-full min-w-[640px] text-left text-sm">
        <thead class="bg-[#F7F6F4] text-xs uppercase text-muted"><tr><th class="px-5 py-3">Patron</th><th class="px-5 py-3">Book</th><th class="px-5 py-3">Dates</th><th class="px-5 py-3">Status</th><th class="px-5 py-3 text-right">Action</th></tr></thead>
        <tbody class="divide-y divide-[#EAE7E0]">
            @forelse ($loans as $loan)
                <tr class="{{ $loan->isOverdue() ? 'bg-red-50/40' : '' }}">
                    <td class="px-5 py-3"><p class="font-bold">{{ $loan->user->name }}</p><p class="whitespace-nowrap text-xs text-muted">{{ $loan->user->patron_id }}</p>@if ($loan->user->mobile_number)<p class="whitespace-nowrap text-xs text-muted">{{ $loan->user->mobile_number }}</p>@endif</td>
                    <td class="px-5 py-3"><p class="font-medium">{{ $loan->copy->book->title }}</p><p class="font-mono text-xs text-muted">{{ $loan->copy->copy_code }} / {{ $loan->copy->barcode }}</p></td>
                    <td class="whitespace-nowrap px-5 py-3 text-xs"><p><span class="text-muted">Out</span> {{ $loan->borrowed_at->format('M d, Y') }}</p><p class="mt-0.5"><span class="text-muted">Due</span> <span class="font-semibold">{{ $loan->due_at->format('M d, Y') }}</span></p></td>
                    <td class="px-5 py-3 text-xs font-bold {{ $loan->isOverdue() ? 'text-red-700' : '' }}">{{ $loan->isOverdue() ? $loan->daysLate().' '.\Illuminate\Support\Str::plural('day', $loan->daysLate()).' late · PHP '.number_format($loan->accruedFine(), 2) : $loan->daysRemaining().' '.\Illuminate\Support\Str::plural('day', $loan->daysRemaining()).' left' }}</td>
                    <td class="px-5 py-3 text-right"><form method="POST" action="{{ route('librarian.loans.checkin', $loan) }}" data-confirm="{{ $loan->isOverdue() ? 'This book is '.$loan->daysLate().' '.\Illuminate\Support\Str::plural('day', $loan->daysLate()).' late. A fine of PHP '.number_format($loan->accruedFine(), 2).' will be added to the patron\'s account.' : 'The copy will go back on the shelf and the loan will be closed.' }}" data-confirm-title="Check in this book?" data-confirm-detail="{{ $loan->copy->book->title }} · {{ $loan->user->name }}" data-confirm-label="Check in" data-confirm-icon="assignment_return">@csrf<button class="whitespace-nowrap rounded-lg bg-[#284A30] px-3 py-1.5 text-xs font-bold text-white hover:bg-[#183020]" type="submit">Check in</button></form></td>
                </tr>
            @empty
                <tr><td class="px-5 py-10 text-center text-muted" colspan="5">{{ $empty ?? 'No loans to show.' }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
