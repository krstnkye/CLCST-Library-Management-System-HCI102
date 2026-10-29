<section class="rounded-lg border border-[#D6DED8] bg-white shadow-sm">
    <form class="flex flex-col gap-3 border-b border-[#E2E8E4] p-4 sm:flex-row sm:items-center" method="GET" action="{{ route('librarian.section', 'books') }}" role="search">
        <label class="relative block max-w-xl flex-1"><span class="sr-only">Search books</span><span class="material-symbols-outlined pointer-events-none absolute left-3 top-2.5 text-xl text-[#718078]">search</span><input id="books-search" class="w-full rounded-lg border-[#CBD6CF] py-2 pl-10 pr-3 text-sm focus:border-[#0F5C2E] focus:ring-[#0F5C2E]" name="q" value="{{ request('q') }}" placeholder="Title, author, ISBN, or call number" type="search"></label>
        <select class="rounded-lg border-[#CBD6CF] text-sm" name="status" aria-label="Record status"><option value="active">Active titles</option><option value="archived" @selected(request('status') === 'archived')>Archived titles</option></select>
        <button class="ui-scan-btn self-stretch" type="button" aria-label="Scan a book barcode" data-scan data-scan-target="#books-search" data-scan-submit><span class="material-symbols-outlined text-lg">barcode_scanner</span>Scan</button>
        <button class="rounded-lg border border-[#CBD6CF] px-4 py-2 text-sm font-bold hover:bg-[#F4F7F5]" type="submit">Search</button>
    </form>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[860px] text-left text-sm">
            <thead class="bg-[#F4F7F5] text-xs uppercase text-[#66756D]"><tr><th class="px-5 py-3">Title</th><th class="px-5 py-3">ISBN / Call no.</th><th class="px-5 py-3">Category</th><th class="px-5 py-3">Copies</th><th class="px-5 py-3">Shelf</th><th class="px-5 py-3 text-right">Action</th></tr></thead>
            <tbody class="divide-y divide-[#E2E8E4]">
                @forelse ($books as $book)
                    <tr class="hover:bg-[#F8FAF9]">
                        <td class="px-5 py-4"><p class="font-bold">{{ $book->title }}</p><p class="text-xs text-[#718078]">{{ $book->author }}{{ $book->publication_year ? ' - '.$book->publication_year : '' }}</p></td>
                        <td class="px-5 py-4"><p class="font-mono text-xs">{{ $book->isbn }}</p><p class="font-mono text-xs text-[#718078]">{{ $book->call_number }}</p></td>
                        <td class="px-5 py-4">{{ $book->category }}@if ($book->isReference())<span class="ml-1 rounded bg-blue-100 px-1.5 py-0.5 text-[10px] font-bold text-blue-800">Reference</span>@endif</td>
                        <td class="px-5 py-4"><span class="font-bold">{{ $book->available_copies }}</span> of {{ $book->total_copies }} on shelf</td>
                        <td class="px-5 py-4 text-xs">{{ $book->shelf_location }}</td>
                        <td class="px-5 py-4 text-right"><a class="font-bold text-[#0F5C2E] hover:underline" href="{{ route('librarian.books.edit', $book) }}">Edit</a></td>
                    </tr>
                @empty
                    <tr><td class="px-5 py-10 text-center text-[#67766E]" colspan="6">No books match your search.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($books->hasPages())<div class="border-t border-[#E2E8E4] p-4">{{ $books->links() }}</div>@endif
</section>
