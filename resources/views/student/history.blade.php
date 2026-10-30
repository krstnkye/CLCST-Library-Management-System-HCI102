@extends('layouts.student')

@section('title', $title)

@section('content')
    @php
        $returned = $history->whereNotNull('returned_at');
        $onTime = $returned->filter(fn ($loan) => $loan->daysLate() === 0)->count();
        $onTimeRate = $returned->count() > 0 ? round($onTime / $returned->count() * 100) : 100;
    @endphp

    <header class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
        <div>
            <h1 class="font-headline text-2xl font-bold">Borrowing History</h1>
            <p class="mt-1 text-sm text-on-surface-variant">Every book you have borrowed, with receipts and return dates.</p>
        </div>
        <a class="inline-flex items-center gap-2 rounded-lg border border-outline-variant bg-white px-4 py-2.5 text-sm font-bold hover:bg-surface-container" href="{{ route('student.section', ['history', 'export' => 'csv']) }}"><span class="material-symbols-outlined text-base">download</span>Download CSV</a>
    </header>

    <section class="grid grid-cols-2 gap-3 lg:grid-cols-4" aria-label="History summary">
        @foreach ([['Total loans', $history->count()], ['Returned', $returned->count()], ['On loan now', $activeLoans->count()], ['On-time returns', $onTimeRate.'%']] as [$label, $value])
            <div class="rounded-xl border border-outline-variant bg-surface-container-lowest p-4 shadow-sm"><span class="block text-[10px] font-bold uppercase text-outline">{{ $label }}</span><span class="font-headline text-lg font-bold">{{ $value }}</span></div>
        @endforeach
    </section>

    @include('partials.patron-history')
@endsection
