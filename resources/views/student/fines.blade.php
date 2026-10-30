@extends('layouts.student')

@section('title', $title)

@section('content')
    @php
        $paid = $fines->where('status', 'paid')->sum('amount');
        $waived = $fines->where('status', 'waived')->sum('amount');
        $accruing = $activeLoans->filter->isOverdue();
    @endphp

    <header>
        <h1 class="font-headline text-2xl font-bold">Fines & Penalties</h1>
        <p class="mt-1 text-sm text-on-surface-variant">Overdue books cost PHP {{ number_format($policy['fine_per_day'], 2) }} per day, up to PHP {{ number_format($policy['max_fine_per_item'], 2) }} per book. Pay at the library cashier; the librarian issues an official receipt.</p>
    </header>

    <section class="grid grid-cols-2 gap-3 lg:grid-cols-4" aria-label="Fine summary">
        @foreach ([
            ['Outstanding', 'PHP '.number_format($outstandingFines, 2), $outstandingFines > 0 ? 'text-red-700' : ''],
            ['Still accruing', $accruing->count().' overdue book(s)', $accruing->isNotEmpty() ? 'text-red-700' : ''],
            ['Paid', 'PHP '.number_format((float) $paid, 2), ''],
            ['Waived', 'PHP '.number_format((float) $waived, 2), ''],
        ] as [$label, $value, $class])
            <div class="rounded-xl border border-outline-variant bg-surface-container-lowest p-4 shadow-sm"><span class="block text-[10px] font-bold uppercase text-outline">{{ $label }}</span><span class="font-headline text-lg font-bold {{ $class }}">{{ $value }}</span></div>
        @endforeach
    </section>

    @if ($accruing->isNotEmpty())
        <section class="rounded-xl border border-red-200 bg-red-50 p-5 text-sm text-red-900">
            <h2 class="font-bold">Overdue books still adding fines</h2>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach ($accruing as $loan)
                    <li>{{ $loan->copy->book->title }}: {{ $loan->daysLate() }} day(s) late, PHP {{ number_format($loan->accruedFine(), 2) }} so far. The final amount is charged when you return it.</li>
                @endforeach
            </ul>
        </section>
    @endif

    <section class="overflow-hidden rounded-2xl border border-[#E2E8F0] bg-surface-container-lowest shadow-sm">
        <h2 class="border-b border-[#E2E8F0] px-5 py-4 font-headline text-lg font-bold">Fine ledger</h2>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-left text-sm">
                <thead class="bg-surface-container-low text-[10px] uppercase text-outline">
                    <tr><th class="px-5 py-3">Book</th><th class="px-5 py-3">Due / Returned</th><th class="px-5 py-3">Days late</th><th class="px-5 py-3">Amount</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Receipt</th></tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/50">
                    @forelse ($fines as $fine)
                        <tr>
                            <td class="px-5 py-4"><p class="font-semibold">{{ $fine->loan?->copy->book->title ?? $fine->reason }}</p><p class="text-xs text-outline">{{ $fine->loan?->copy->copy_code }}</p></td>
                            <td class="px-5 py-4 text-xs">{{ $fine->loan?->due_at->format('M d, Y') }} / {{ $fine->loan?->returned_at?->format('M d, Y') }}</td>
                            <td class="px-5 py-4 text-xs">{{ $fine->days_late }}</td>
                            <td class="px-5 py-4 font-semibold">PHP {{ number_format((float) $fine->amount, 2) }}</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ match ($fine->status) { 'outstanding' => 'bg-red-100 text-red-800', 'paid' => 'bg-emerald-100 text-emerald-800', default => 'bg-slate-100 text-slate-700' } }}">{{ ucfirst($fine->status) }}</span>
                                @if ($fine->settled_at)<p class="mt-1 text-[11px] text-outline">{{ $fine->settled_at->format('M d, Y') }}</p>@endif
                            </td>
                            <td class="px-5 py-4 font-mono text-[11px]">{{ $fine->receipt_no ?? 'Pay at cashier' }}</td>
                        </tr>
                    @empty
                        <tr><td class="px-5 py-10 text-center text-on-surface-variant" colspan="6">Clean record. You have never been charged a fine.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
