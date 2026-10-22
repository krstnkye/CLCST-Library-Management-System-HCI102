<section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    @forelse ($categories as $category)
        @php($share = $category->copies > 0 ? round(($category->copies - $category->available) / $category->copies * 100) : 0)
        <a class="rounded-lg border border-[#D6DED8] bg-white p-5 shadow-sm hover:border-[#0F5C2E]" href="{{ route('librarian.section', ['books', 'q' => $category->category]) }}">
            <div class="flex items-center justify-between"><h2 class="font-display text-lg font-bold">{{ $category->category ?: 'Uncategorized' }}</h2><span class="material-symbols-outlined text-[#0F5C2E]">category</span></div>
            <p class="mt-2 text-sm text-[#5E6D65]">{{ $category->titles }} titles - {{ $category->copies }} copies - {{ $category->available }} on shelf</p>
            <div class="mt-3 h-2 overflow-hidden rounded-full bg-[#E2E8E4]" role="progressbar" aria-valuenow="{{ $share }}" aria-valuemin="0" aria-valuemax="100" aria-label="Share of copies out of the library"><div class="h-full bg-[#0F5C2E]" style="width: {{ $share }}%"></div></div>
            <p class="mt-1 text-xs text-[#718078]">{{ $share }}% of copies are out of the library</p>
        </a>
    @empty
        <p class="text-sm text-[#67766E]">No categories yet. Add a book to create one.</p>
    @endforelse
</section>
