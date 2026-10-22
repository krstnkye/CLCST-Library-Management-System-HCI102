@extends('layouts.student')

@section('title', $title)

@section('content')
    @php($ready = $openReservations->where('status', 'ready'))

    <header class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
        <div>
            <h1 class="font-headline text-2xl font-bold">My Loans</h1>
            <p class="mt-1 text-sm text-on-surface-variant">Renew books online, see what is ready for pickup, and check your due dates.</p>
        </div>
        <a class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-on-primary hover:bg-primary-container" href="{{ route('books.catalog') }}"><span class="material-symbols-outlined text-base">search</span>Find a book</a>
    </header>

    <section class="grid grid-cols-2 gap-3 lg:grid-cols-4" aria-label="Borrowing summary">
        @foreach ([
            ['Currently on loan', $activeLoans->count().' of '.$loanLimit],
            ['Slots left', max(0, $loanLimit - $activeLoans->count())],
            ['Ready for pickup', $ready->count()],
            ['Unpaid fines', 'PHP '.number_format($outstandingFines, 2)],
        ] as [$label, $value])
            <div class="rounded-xl border border-outline-variant bg-surface-container-lowest p-4 shadow-sm">
                <span class="block text-[10px] font-bold uppercase text-outline">{{ $label }}</span>
                <span class="font-headline text-lg font-bold">{{ $value }}</span>
            </div>
        @endforeach
    </section>

    @if ($ready->isNotEmpty())
        <section class="rounded-xl border border-emerald-300 bg-emerald-50 p-5" aria-labelledby="pickup-heading">
            <h2 id="pickup-heading" class="flex items-center gap-2 font-headline text-base font-bold text-emerald-900"><span class="material-symbols-outlined">local_library</span>Ready for pickup at the circulation desk</h2>
            <ul class="mt-3 space-y-2 text-sm text-emerald-900">
                @foreach ($ready as $reservation)
                    <li><strong>{{ $reservation->book->title }}</strong> - held until {{ $reservation->ready_until?->format('M d, Y') }}</li>
                @endforeach
            </ul>
        </section>
    @endif

    <section class="space-y-4 rounded-2xl border border-[#E2E8F0] bg-surface-container-lowest p-5 shadow-sm lg:p-6" aria-labelledby="active-heading">
        <h2 id="active-heading" class="border-b border-[#E2E8F0] pb-3 font-headline text-lg font-bold">Books on loan ({{ $activeLoans->count() }})</h2>
        @forelse ($activeLoans as $loan)
            @include('student.partials.loan-card', ['loan' => $loan])
        @empty
            <p class="rounded-xl border border-dashed border-outline-variant p-6 text-center text-sm text-on-surface-variant">You have no books on loan right now.</p>
        @endforelse
    </section>

    <section class="rounded-2xl border border-[#E2E8F0] bg-surface-container-lowest p-5 shadow-sm lg:p-6" aria-labelledby="how-heading">
        <h2 id="how-heading" class="font-headline text-lg font-bold">How borrowing and returning works</h2>
        <ol class="mt-4 grid gap-4 md:grid-cols-4">
            @foreach ([
                ['search', 'Find it', 'Search the catalog and check which copies are on the shelf.'],
                ['book_online', 'Reserve it', 'Reserve the title online. We set a copy aside and notify you.'],
                ['badge', 'Borrow at the desk', 'Show your school ID. The librarian checks it out for '.$loanDays.' days.'],
                ['assignment_return', 'Return or renew', 'Renew online up to '.$policy['max_renewals'].' time(s), or return it at the desk. Late returns cost PHP '.number_format($policy['fine_per_day'], 2).' per day.'],
            ] as $index => [$icon, $label, $text])
                <li class="rounded-xl border border-outline-variant bg-surface-container-low p-4">
                    <span class="flex items-center gap-2 text-sm font-bold text-primary"><span class="flex h-6 w-6 items-center justify-center rounded-full bg-primary text-xs text-white">{{ $index + 1 }}</span>{{ $label }}</span>
                    <p class="mt-2 text-xs leading-5 text-on-surface-variant">{{ $text }}</p>
                </li>
            @endforeach
        </ol>
    </section>
@endsection
