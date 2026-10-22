@extends('layouts.faculty')

@section('title', 'Faculty Dashboard')

@section('content')
    @php
        $dueSoon = $activeLoans->filter(fn ($loan) => $loan->daysRemaining() <= 3);
        $openRequests = $requests->whereIn('status', ['submitted', 'in_review'])->count();
    @endphp

    <section class="rounded-2xl bg-gradient-to-r from-[#065F2A] to-[#064E3B] p-6 text-white shadow-sm lg:p-8">
        <h1 class="font-headline text-3xl font-bold">Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 18 ? 'afternoon' : 'evening') }}, {{ $patron->name }}</h1>
        <p class="mt-2 max-w-3xl text-sm text-emerald-50">{{ $patron->program_or_department }}. Faculty may borrow up to {{ $loanLimit }} books for {{ $loanDays }} days, and can request course reserves, class sessions, and inter-library loans from this portal.</p>
    </section>

    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Summary">
        @foreach ([
            ['Books on loan', $activeLoans->count().' of '.$loanLimit, 'menu_book', route('staff.section', 'circulation')],
            ['Due within 3 days', $dueSoon->count(), 'schedule', route('staff.section', 'circulation')],
            ['Open reservations', $openReservations->count(), 'book_online', route('staff.section', 'reservations')],
            ['Requests in progress', $openRequests, 'forum', route('staff.section', 'course-reserves')],
        ] as [$label, $value, $icon, $href])
            <a class="rounded-xl border border-[#E2E8F0] bg-white p-5 shadow-sm hover:border-[#065F2A]" href="{{ $href }}">
                <div class="flex items-center justify-between"><span class="text-xs font-semibold text-[#64748B]">{{ $label }}</span><span class="material-symbols-outlined rounded-lg bg-[#DCFCE7] p-2 text-[#166534]">{{ $icon }}</span></div>
                <p class="mt-4 font-headline text-2xl font-semibold">{{ $value }}</p>
            </a>
        @endforeach
    </section>

    <div class="grid gap-6 xl:grid-cols-12">
        <section class="space-y-4 rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm xl:col-span-8" aria-labelledby="faculty-loans">
            <div class="flex items-center justify-between border-b border-[#E2E8F0] pb-3"><h2 id="faculty-loans" class="font-headline text-lg font-bold">My borrowed books</h2><a class="text-xs font-bold text-[#065F2A] hover:underline" href="{{ route('books.catalog') }}">Search books</a></div>
            @forelse ($activeLoans as $loan)
                @include('student.partials.loan-card', ['loan' => $loan])
            @empty
                <p class="rounded-xl border border-dashed border-[#CBD5E1] p-6 text-center text-sm text-[#64748B]">You have no books on loan.</p>
            @endforelse
        </section>

        <aside class="space-y-6 xl:col-span-4">
            <section class="space-y-3 rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm" aria-labelledby="faculty-alerts">
                <div class="flex items-center justify-between"><h2 id="faculty-alerts" class="font-headline text-base font-bold">Notifications</h2><a class="text-xs font-bold text-[#065F2A] hover:underline" href="{{ route('staff.section', 'notifications') }}">See all</a></div>
                @forelse ($notifications->take(3) as $notification)
                    @include('partials.notification-item', ['notification' => $notification, 'compact' => true])
                @empty
                    <p class="text-sm text-[#64748B]">You are all caught up.</p>
                @endforelse
            </section>
            <section class="rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm" aria-labelledby="faculty-requests">
                <h2 id="faculty-requests" class="font-headline text-base font-bold">Recent requests</h2>
                <ul class="mt-3 divide-y divide-[#E2E8F0] text-sm">
                    @forelse ($requests as $libraryRequest)
                        <li class="py-2"><p class="font-semibold">{{ $libraryRequest->subject }}</p><p class="text-xs text-[#64748B]">{{ $libraryRequest->typeLabel() }} - {{ $libraryRequest->statusLabel() }}</p></li>
                    @empty
                        <li class="py-2 text-[#64748B]">No requests yet. Use Teaching Support in the menu to send one.</li>
                    @endforelse
                </ul>
            </section>
        </aside>
    </div>
@endsection
