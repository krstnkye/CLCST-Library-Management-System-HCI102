@extends('layouts.student')

@section('title', $title)

@section('content')
    <header>
        <h1 class="font-headline text-2xl font-bold">Borrowing Policy</h1>
        <p class="mt-1 text-sm text-on-surface-variant">These rules come straight from the library's current settings, so they are always up to date.</p>
    </header>

    <section class="overflow-hidden rounded-2xl border border-[#E2E8F0] bg-surface-container-lowest shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="bg-surface-container-low text-[10px] uppercase text-outline"><tr><th class="px-5 py-3">Rule</th><th class="px-5 py-3">Students</th><th class="px-5 py-3">Faculty</th></tr></thead>
            <tbody class="divide-y divide-outline-variant/50">
                <tr><td class="px-5 py-3 font-semibold">Books on loan at once</td><td class="px-5 py-3">{{ $policy['max_loans_student'] }}</td><td class="px-5 py-3">{{ $policy['max_loans_faculty'] }}</td></tr>
                <tr><td class="px-5 py-3 font-semibold">Loan period</td><td class="px-5 py-3">{{ $policy['loan_days_student'] }} days</td><td class="px-5 py-3">{{ $policy['loan_days_faculty'] }} days</td></tr>
                <tr><td class="px-5 py-3 font-semibold">Online renewals per loan</td><td class="px-5 py-3" colspan="2">{{ $policy['max_renewals'] }}, adding {{ $policy['renewal_days'] }} days each</td></tr>
                <tr><td class="px-5 py-3 font-semibold">Open reservations</td><td class="px-5 py-3" colspan="2">Up to {{ $policy['max_reservations'] }}, held {{ $policy['pickup_days'] }} days once ready</td></tr>
                <tr><td class="px-5 py-3 font-semibold">Overdue fine</td><td class="px-5 py-3" colspan="2">PHP {{ number_format($policy['fine_per_day'], 2) }} per day, at most PHP {{ number_format($policy['max_fine_per_item'], 2) }} per book</td></tr>
            </tbody>
        </table>
    </section>

    <section class="rounded-2xl border border-[#E2E8F0] bg-surface-container-lowest p-5 text-sm leading-6 text-on-surface-variant shadow-sm">
        <h2 class="font-headline text-lg font-bold text-on-surface">Good to know</h2>
        <ul class="mt-2 list-disc space-y-1 pl-5">
            <li>You cannot borrow new books while one of your books is overdue.</li>
            <li>Overdue books cannot be renewed online; return them at the circulation desk.</li>
            <li>A title cannot be renewed when other patrons are waiting for it.</li>
            <li>Reference books are for in-library use only.</li>
            <li>Lost or damaged books are assessed by the librarian at the desk.</li>
        </ul>
    </section>
@endsection
