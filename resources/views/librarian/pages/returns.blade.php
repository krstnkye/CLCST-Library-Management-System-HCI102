<div class="grid gap-6 xl:grid-cols-12">
    <section class="rounded-lg border border-[#D6DED8] bg-white p-5 shadow-sm xl:col-span-4" aria-labelledby="checkin-heading">
        <h2 id="checkin-heading" class="font-display text-lg font-bold">Receive a returned book</h2>
        <p class="mt-1 text-xs text-[#67766E]">Scan the copy barcode. Late returns are charged PHP {{ number_format($policy['fine_per_day'], 2) }} per day automatically, and the next patron on the waiting list is shown.</p>
        <form class="mt-4 space-y-3" method="POST" action="{{ route('librarian.checkin') }}">
            @csrf
            <label class="block text-sm font-bold" for="return-barcode">Copy barcode or copy code</label>
            <div class="flex gap-2"><input id="return-barcode" name="barcode" value="{{ old('barcode') }}" required maxlength="32" autocomplete="off" autofocus placeholder="1049201 or PY-001" class="w-full rounded-lg border-[#CBD6CF] font-mono text-sm focus:border-[#0F5C2E] focus:ring-[#0F5C2E]"><button class="ui-scan-btn" type="button" aria-label="Scan with the camera" data-scan data-scan-target="#return-barcode" data-scan-title="Scan the returned copy" data-scan-submit><span class="material-symbols-outlined text-lg">barcode_scanner</span>Scan</button></div>
            <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-[#0F5C2E] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#0A4421]" type="submit"><span class="material-symbols-outlined text-lg">assignment_return</span>Check in</button>
        </form>
    </section>

    <section class="rounded-lg border border-[#D6DED8] bg-white shadow-sm xl:col-span-8" aria-labelledby="out-heading">
        <form class="flex flex-col gap-3 border-b border-[#E2E8E4] p-4 sm:flex-row sm:items-center sm:justify-between" method="GET" action="{{ route('librarian.section', 'returns') }}" role="search">
            <h2 id="out-heading" class="font-display text-lg font-bold">Books currently out ({{ $loans->total() }})</h2>
            <div class="flex gap-2"><label class="sr-only" for="loan-search">Search loans</label><input id="loan-search" class="rounded-lg border-[#CBD6CF] py-1.5 text-sm" name="q" value="{{ request('q') }}" placeholder="Patron or barcode" type="search"><button class="rounded-lg border border-[#CBD6CF] px-3 text-xs font-bold" type="submit">Search</button></div>
        </form>
        @include('librarian.pages._loan-table', ['empty' => 'No books are currently out.'])
        @if ($loans->hasPages())<div class="border-t border-[#E2E8E4] p-4">{{ $loans->links() }}</div>@endif
    </section>
</div>
