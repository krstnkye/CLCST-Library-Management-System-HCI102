<form class="grid gap-6 xl:grid-cols-12" method="POST" action="{{ route('librarian.books.store') }}">
    @csrf
    <section class="rounded-lg border border-[#D6DED8] bg-white p-5 shadow-sm xl:col-span-8">
        <h2 class="font-display text-lg font-bold">Catalog record</h2>
        <div class="mt-4">@include('librarian.pages._book-fields')</div>
    </section>
    <aside class="space-y-4 xl:col-span-4">
        <section class="rounded-lg border border-[#D6DED8] bg-white p-5 shadow-sm">
            <h2 class="font-display text-lg font-bold">Physical copies</h2>
            <label class="mt-3 block text-sm font-bold" for="copies">Number of copies <span class="text-red-600">*</span></label>
            <input id="copies" name="copies" type="number" min="1" max="50" value="{{ old('copies', 1) }}" required class="mt-1.5 w-full rounded-lg border-[#CBD6CF] text-sm">
            <p class="mt-2 text-xs leading-5 text-[#67766E]">Each copy gets its own barcode and copy code, so it can be lent and tracked separately.</p>
            <button class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-[#0F5C2E] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#0A4421]" type="submit"><span class="material-symbols-outlined text-lg">save</span>Save Book</button>
        </section>
        <p class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-xs leading-5 text-amber-950">Adding many titles at once? Use <a class="font-bold underline" href="{{ route('management.data.index') }}">Import and Export</a> with the CSV template.</p>
    </aside>
</form>
