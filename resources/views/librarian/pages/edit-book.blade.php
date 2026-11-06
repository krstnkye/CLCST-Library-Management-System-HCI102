<div class="grid gap-6 xl:grid-cols-12">
    <form class="rounded-lg border border-[#E1DCD3] bg-white p-5 shadow-sm xl:col-span-7" method="POST" action="{{ route('librarian.books.update', $book) }}">
        @csrf
        @method('PUT')
        <h2 class="font-display text-lg font-bold">Catalog record</h2>
        <div class="mt-4">@include('librarian.pages._book-fields', ['book' => $book])</div>
        <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-[#EAE7E0] pt-4">
            <label class="flex items-center gap-2 text-sm"><span class="font-bold">Record status</span>
                <select name="status" class="rounded-lg border-[#D6D2CB] text-sm"><option value="active">Active</option><option value="archived" @selected($book->status === 'archived')>Archived (hidden from catalog)</option></select>
            </label>
            <button class="rounded-lg bg-[#284A30] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#183020]" type="submit">Save changes</button>
        </div>
    </form>

    <section class="rounded-lg border border-[#E1DCD3] bg-white shadow-sm xl:col-span-5">
        <div class="flex items-center justify-between border-b border-[#EAE7E0] px-5 py-4">
            <h2 class="font-display text-lg font-bold">Copies ({{ $book->copies->count() }})</h2>
            <form class="flex items-center gap-2" method="POST" action="{{ route('librarian.books.copies.store', $book) }}">
                @csrf
                <label class="sr-only" for="new-copies">Copies to add</label>
                <input id="new-copies" name="copies" type="number" min="1" max="50" value="1" class="w-16 rounded-lg border-[#D6D2CB] text-sm">
                <button class="rounded-lg border border-[#284A30] px-3 py-2 text-xs font-bold text-[#284A30] hover:bg-[#F4F2EF]" type="submit">Add copies</button>
            </form>
        </div>
        <ul class="divide-y divide-[#EAE7E0]">
            @foreach ($book->copies as $copy)
                <li class="flex flex-wrap items-center justify-between gap-2 px-5 py-3 text-sm">
                    <div><p class="font-mono font-bold">{{ $copy->copy_code }} <span class="font-normal text-[#827C6F]">#{{ $copy->barcode }}</span></p>
                        <p class="text-xs text-[#777166]">{{ $copy->status === 'on_loan' ? 'On loan to '.$copy->activeLoan?->user->name.', due '.$copy->activeLoan?->due_at->format('M d') : ucfirst(str_replace('_', ' ', $copy->status)) }}</p></div>
                    @unless ($copy->status === 'on_loan')
                        @include('librarian.pages._copy-status', ['copy' => $copy])
                    @endunless
                </li>
            @endforeach
        </ul>
    </section>
</div>
