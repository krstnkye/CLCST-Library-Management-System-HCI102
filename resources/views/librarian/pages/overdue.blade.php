<section class="rounded-lg border border-[#E1DCD3] bg-white shadow-sm">
    <div class="border-b border-[#EAE7E0] px-5 py-4">
        <h2 class="font-display text-lg font-bold">Overdue loans ({{ $loans->count() }})</h2>
        <p class="mt-0.5 text-xs text-[#777166]">Patrons with an overdue book cannot borrow more until it is returned. Contact numbers are shown so the desk can call or text a reminder.</p>
    </div>
    @include('librarian.pages._loan-table', ['empty' => 'Nothing is overdue. Great work!'])
</section>
