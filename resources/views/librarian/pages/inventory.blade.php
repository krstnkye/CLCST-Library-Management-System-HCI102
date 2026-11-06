<section class="rounded-lg border border-[#E1DCD3] bg-white shadow-sm">
    <div class="flex flex-wrap gap-2 border-b border-[#EAE7E0] p-4">
        <a class="rounded-full px-3 py-1.5 text-xs font-bold {{ request('status') ? 'border border-[#D6D2CB]' : 'bg-[#284A30] text-white' }}" href="{{ route('librarian.section', 'inventory') }}">All ({{ $statusCounts->sum() }})</a>
        @foreach (['available' => 'On shelf', 'on_loan' => 'On loan', 'reference' => 'Reference', 'maintenance' => 'For repair', 'lost' => 'Lost'] as $value => $label)
            <a class="rounded-full px-3 py-1.5 text-xs font-bold {{ request('status') === $value ? 'bg-[#284A30] text-white' : 'border border-[#D6D2CB]' }}" href="{{ route('librarian.section', ['inventory', 'status' => $value]) }}">{{ $label }} ({{ $statusCounts[$value] ?? 0 }})</a>
        @endforeach
        <form class="ml-auto flex gap-2" method="GET" action="{{ route('librarian.section', 'inventory') }}" role="search">
            @if (request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
            <label class="sr-only" for="inventory-search">Search copies</label>
            <input id="inventory-search" class="rounded-lg border-[#D6D2CB] py-1.5 text-sm" name="q" value="{{ request('q') }}" placeholder="Barcode, copy code, or title" type="search"><button class="ui-scan-btn" type="button" aria-label="Scan a copy barcode" data-scan data-scan-target="#inventory-search" data-scan-submit><span class="material-symbols-outlined text-lg">barcode_scanner</span></button>
            <button class="rounded-lg border border-[#D6D2CB] px-3 text-xs font-bold" type="submit">Search</button>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[820px] text-left text-sm">
            <thead class="bg-[#F7F6F4] text-xs uppercase text-[#767065]"><tr><th class="px-5 py-3">Copy</th><th class="px-5 py-3">Title</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Location / Borrower</th><th class="px-5 py-3 text-right">Change status</th></tr></thead>
            <tbody class="divide-y divide-[#EAE7E0]">
                @forelse ($copies as $copy)
                    <tr>
                        <td class="px-5 py-3"><p class="font-mono font-bold">{{ $copy->copy_code }}</p><p class="font-mono text-xs text-[#827C6F]">{{ $copy->barcode }}</p></td>
                        <td class="px-5 py-3 font-medium">{{ $copy->book->title }}</td>
                        <td class="px-5 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-bold {{ match ($copy->status) { 'available' => 'bg-emerald-100 text-emerald-800', 'on_loan' => 'bg-amber-100 text-amber-800', 'reference' => 'bg-blue-100 text-blue-800', 'lost' => 'bg-red-100 text-red-800', default => 'bg-slate-100 text-slate-700' } }}">{{ ucfirst(str_replace('_', ' ', $copy->status)) }}</span></td>
                        <td class="px-5 py-3 text-xs">{{ $copy->activeLoan ? $copy->activeLoan->user->name.' (due '.$copy->activeLoan->due_at->format('M d').')' : $copy->location }}</td>
                        <td class="px-5 py-3"><div class="flex justify-end">@unless ($copy->status === 'on_loan')@include('librarian.pages._copy-status', ['copy' => $copy])@endunless</div></td>
                    </tr>
                @empty
                    <tr><td class="px-5 py-10 text-center text-[#777166]" colspan="5">No copies match.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($copies->hasPages())<div class="border-t border-[#EAE7E0] p-4">{{ $copies->links() }}</div>@endif
</section>
