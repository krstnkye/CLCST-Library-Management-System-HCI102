<section class="rounded-lg border border-[#D6DED8] bg-white shadow-sm">
    <div class="flex items-center justify-between border-b border-[#E2E8E4] px-5 py-4">
        <h2 class="font-display text-lg font-bold">All reservations</h2>
        <a class="text-sm font-bold text-[#0F5C2E] hover:underline" href="{{ route('librarian.section', 'borrow-requests') }}">Serve at the desk</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[760px] text-left text-sm">
            <thead class="bg-[#F4F7F5] text-xs uppercase text-[#66756D]"><tr><th class="px-5 py-3">Patron</th><th class="px-5 py-3">Book</th><th class="px-5 py-3">Requested</th><th class="px-5 py-3">Status</th><th class="px-5 py-3 text-right">Action</th></tr></thead>
            <tbody class="divide-y divide-[#E2E8E4]">
                @forelse ($reservations as $reservation)
                    @php($expired = $reservation->status === 'ready' && $reservation->ready_until?->isPast())
                    <tr>
                        <td class="px-5 py-3"><p class="font-bold">{{ $reservation->user->name }}</p><p class="text-xs text-[#718078]">{{ $reservation->user->patron_id }}</p></td>
                        <td class="px-5 py-3">{{ $reservation->book->title }}</td>
                        <td class="px-5 py-3 text-xs">{{ $reservation->created_at->format('M d, Y g:i A') }}</td>
                        <td class="px-5 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $expired ? 'bg-red-100 text-red-800' : match ($reservation->status) { 'ready' => 'bg-emerald-100 text-emerald-800', 'pending' => 'bg-amber-100 text-amber-800', default => 'bg-slate-100 text-slate-700' } }}">{{ $expired ? 'Pickup date passed' : ($reservation->status === 'ready' ? 'Ready until '.$reservation->ready_until?->format('M d') : ucfirst($reservation->status)) }}</span></td>
                        <td class="px-5 py-3 text-right">
                            @if (in_array($reservation->status, \App\Models\Reservation::OPEN_STATUSES, true))
                                <form method="POST" action="{{ route('librarian.reservations.cancel', $reservation) }}" onsubmit="return confirm('Cancel this reservation?')">@csrf<button class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-bold text-red-700 hover:bg-red-50" type="submit">Cancel</button></form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td class="px-5 py-10 text-center text-[#67766E]" colspan="5">No reservations yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($reservations->hasPages())<div class="border-t border-[#E2E8E4] p-4">{{ $reservations->links() }}</div>@endif
</section>
