@extends('layouts.student')

@section('title', $title)

@section('content')
    <header class="flex flex-col gap-4 rounded-2xl border border-[#E2E8F0] bg-surface-container-lowest p-5 shadow-sm sm:flex-row sm:items-center">
        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-primary-container to-primary text-xl font-bold text-white">{{ $patron->initials() }}</div>
        <div class="flex-1">
            <h1 class="font-headline text-2xl font-bold">{{ $patron->name }}</h1>
            <p class="text-sm text-on-surface-variant">{{ $patron->patron_id }} - {{ $patron->program_or_department }}</p>
        </div>
        <span class="w-fit rounded-full px-3 py-1 text-xs font-bold {{ $outstandingFines > 0 || $activeLoans->contains->isOverdue() ? 'bg-amber-100 text-amber-900' : 'bg-emerald-100 text-emerald-800' }}">
            {{ $outstandingFines > 0 || $activeLoans->contains->isOverdue() ? 'Action needed' : 'Good standing' }}
        </span>
    </header>

    <section class="grid grid-cols-2 gap-3 lg:grid-cols-4" aria-label="Borrowing privileges">
        @foreach ([
            ['Loan limit', $loanLimit.' books'],
            ['Loan period', $loanDays.' days'],
            ['Renewals per loan', $policy['max_renewals']],
            ['Open reservations', $openReservations->count().' of '.$policy['max_reservations']],
        ] as [$label, $value])
            <div class="rounded-xl border border-outline-variant bg-surface-container-lowest p-4 shadow-sm"><span class="block text-[10px] font-bold uppercase text-outline">{{ $label }}</span><span class="font-headline text-lg font-bold">{{ $value }}</span></div>
        @endforeach
    </section>

    @include('partials.profile-forms')
@endsection
