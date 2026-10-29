<section class="overflow-hidden rounded-2xl border border-[#E2E8F0] bg-surface-container-lowest shadow-sm" aria-labelledby="open-heading">
        <h2 id="open-heading" class="border-b border-[#E2E8F0] px-5 py-4 font-headline text-lg font-bold">Open reservations ({{ $openReservations->count() }})</h2>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-left text-sm">
                <thead class="bg-surface-container-low text-[10px] uppercase text-outline">
                    <tr><th class="px-5 py-3">Book</th><th class="px-5 py-3">Requested</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Queue</th><th class="px-5 py-3 text-right">Action</th></tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/50">
                    @forelse ($openReservations as $reservation)
                        <tr>
                            <td class="px-5 py-4"><p class="font-semibold">{{ $reservation->book->title }}</p><p class="text-xs text-outline">{{ $reservation->book->author }} - {{ $reservation->book->call_number }}</p></td>
                            <td class="px-5 py-4 text-xs">{{ $reservation->created_at->format('M d, Y g:i A') }}</td>
                            <td class="px-5 py-4">
                                @if ($reservation->status === 'ready')
                                    <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-800">Ready until {{ $reservation->ready_until?->format('M d') }}</span>
                                @else
                                    <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-900">Waiting</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-xs">#{{ $reservation->queuePosition() }} in line</td>
                            <td class="px-5 py-4 text-right">
                                <form method="POST" action="{{ route('reservations.cancel', $reservation) }}" data-confirm="You will lose your place in the waiting list for this title." data-confirm-title="Cancel your reservation?" data-confirm-detail="{{ $reservation->book->title }}" data-confirm-label="Cancel reservation">
                                    @csrf
                                    <button class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-bold text-red-700 hover:bg-red-50" type="submit">Cancel</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="px-5 py-10 text-center text-on-surface-variant" colspan="5">You have no open reservations. Reserve a book from the catalog.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
