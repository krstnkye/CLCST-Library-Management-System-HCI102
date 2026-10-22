@extends('librarian.layout')

@section('title', $page['title'])

@section('content')
    <section class="flex flex-col justify-between gap-4 border-b border-[#D6DED8] pb-5 lg:flex-row lg:items-end">
        <div>
            <a class="mb-3 inline-flex items-center gap-1 text-xs font-bold text-[#0F5C2E] hover:underline" href="{{ route('librarian.dashboard') }}"><span class="material-symbols-outlined text-base">arrow_back</span>Dashboard</a>
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined flex h-11 w-11 items-center justify-center rounded-lg bg-[#E5F1E9] text-[#0F5C2E]">{{ $page['icon'] }}</span>
                <div><h1 class="font-display text-2xl font-extrabold text-[#17251D] md:text-3xl">{{ $page['title'] }}</h1><p class="mt-1 text-sm text-[#5E6D65]">{{ $page['description'] }}</p></div>
            </div>
        </div>
        @if (in_array($section, ['books', 'categories', 'inventory'], true))
            <a class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#0F5C2E] px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-[#0A4421]" href="{{ route('librarian.section', 'add-book') }}"><span class="material-symbols-outlined text-lg">add</span>Add Book</a>
        @endif
    </section>

    <section class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['On the Shelf', $stats['availableCopies'], 'inventory_2'],
            ['Active Loans', $stats['borrowedBooks'], 'outbox'],
            ['Overdue', $stats['overdueBooks'], 'schedule'],
            ['Unpaid Fines', 'PHP '.number_format($stats['outstandingFines'], 2), 'payments'],
        ] as [$label, $value, $icon])
            <article class="rounded-lg border border-[#D6DED8] bg-white p-4 shadow-sm"><div class="flex items-center justify-between"><div><p class="text-xs font-semibold text-[#67766E]">{{ $label }}</p><p class="mt-2 font-display text-2xl font-extrabold">{{ $value }}</p></div><span class="material-symbols-outlined rounded-lg bg-[#EEF5F0] p-2 text-[#0F5C2E]">{{ $icon }}</span></div></article>
        @endforeach
    </section>

    <div class="mt-6">
        @include('librarian.pages.'.$section)
    </div>
@endsection
