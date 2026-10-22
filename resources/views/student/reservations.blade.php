@extends('layouts.student')

@section('title', $title)

@section('content')
    <header class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
        <div>
            <h1 class="font-headline text-2xl font-bold">My Reservations</h1>
            <p class="mt-1 text-sm text-on-surface-variant">You can hold up to {{ $policy['max_reservations'] }} titles at once. Ready books are kept for {{ $policy['pickup_days'] }} days.</p>
        </div>
        <a class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-on-primary hover:bg-primary-container" href="{{ route('books.catalog') }}"><span class="material-symbols-outlined text-base">add</span>Reserve a book</a>
    </header>

    @include('partials.patron-reservations')

    <section class="overflow-hidden rounded-2xl border border-[#E2E8F0] bg-surface-container-lowest shadow-sm" aria-labelledby="past-heading">
        <h2 id="past-heading" class="border-b border-[#E2E8F0] px-5 py-4 font-headline text-lg font-bold">Recent closed reservations</h2>
        <ul class="divide-y divide-outline-variant/50">
            @forelse ($reservationHistory as $reservation)
                <li class="flex flex-wrap items-center justify-between gap-2 px-5 py-3 text-sm">
                    <span class="font-semibold">{{ $reservation->book->title }}</span>
                    <span class="text-xs text-on-surface-variant">{{ ucfirst($reservation->status) }} on {{ ($reservation->closed_at ?? $reservation->updated_at)->format('M d, Y') }}</span>
                </li>
            @empty
                <li class="px-5 py-6 text-center text-sm text-on-surface-variant">No closed reservations yet.</li>
            @endforelse
        </ul>
    </section>
@endsection
