<section class="overflow-hidden rounded-2xl border border-[#E2E8F0] bg-surface-container-lowest shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[860px] text-left text-sm">
                <thead class="bg-surface-container-low text-[10px] uppercase text-outline">
                    <tr><th class="px-5 py-3">Book</th><th class="px-5 py-3">Copy</th><th class="px-5 py-3">Borrowed</th><th class="px-5 py-3">Due</th><th class="px-5 py-3">Returned</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Receipt</th></tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/50">
                    @forelse ($history as $loan)
                        @php
                            [$label, $class] = match (true) {
                                $loan->isOverdue() => ['Overdue', 'bg-red-100 text-red-800'],
                                $loan->isActive() => ['On loan', 'bg-amber-100 text-amber-900'],
                                $loan->daysLate() > 0 => ['Returned '.$loan->daysLate().' day(s) late', 'bg-orange-100 text-orange-900'],
                                default => ['Returned on time', 'bg-emerald-100 text-emerald-800'],
                            };
                        @endphp
                        <tr>
                            <td class="px-5 py-4"><p class="font-semibold">{{ $loan->copy->book->title }}</p><p class="text-xs text-outline">{{ $loan->copy->book->author }}</p></td>
                            <td class="px-5 py-4 font-mono text-xs">{{ $loan->copy->copy_code }}</td>
                            <td class="px-5 py-4 text-xs">{{ $loan->borrowed_at->format('M d, Y') }}</td>
                            <td class="px-5 py-4 text-xs">{{ $loan->due_at->format('M d, Y') }}</td>
                            <td class="px-5 py-4 text-xs">{{ $loan->returned_at?->format('M d, Y') ?? '-' }}</td>
                            <td class="px-5 py-4"><span class="whitespace-nowrap rounded-full px-2.5 py-1 text-[11px] font-bold {{ $class }}">{{ $label }}</span>@if ($loan->fine)<p class="mt-1 text-[11px] text-outline">Fine PHP {{ number_format((float) $loan->fine->amount, 2) }} ({{ $loan->fine->status }})</p>@endif</td>
                            <td class="px-5 py-4 font-mono text-[11px]">{{ $loan->receipt_no }}</td>
                        </tr>
                    @empty
                        <tr><td class="px-5 py-10 text-center text-on-surface-variant" colspan="7">No borrowing history yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
