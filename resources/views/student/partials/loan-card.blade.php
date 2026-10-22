@php
    $book = $loan->copy->book;
    $days = $loan->daysRemaining();
    [$border, $pill, $pillText] = match (true) {
        $days < 0 => ['border-red-300 bg-red-50/50', 'bg-red-100 text-red-800', 'Overdue by '.abs($days).' '.\Illuminate\Support\Str::plural('day', abs($days))],
        $days <= 3 => ['border-[#FEF08A] bg-[#FEFCE8]/50', 'bg-[#FEF3C7] text-[#92400E]', $days === 0 ? 'Due today' : 'Due in '.$days.' '.\Illuminate\Support\Str::plural('day', $days)],
        default => ['border-[#E2E8F0]', 'bg-[#DCFCE7] text-[#166534]', $days.' days remaining'],
    };
@endphp
<article class="flex flex-col justify-between gap-4 rounded-xl border-2 {{ $border }} p-5 md:flex-row md:items-center">
    <div class="flex items-start gap-4">
        <img class="h-24 w-16 flex-shrink-0 rounded-lg border border-outline-variant bg-primary-container object-cover" src="{{ $book->cover_url }}" alt="" loading="lazy" onerror="this.style.visibility='hidden'">
        <div class="space-y-1">
            <div class="flex flex-wrap items-center gap-2">
                <h3 class="font-headline text-base font-bold">{{ $book->title }}</h3>
                <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $pill }}">{{ $pillText }}</span>
            </div>
            <p class="text-xs text-[#475569]">{{ $book->author }}</p>
            <p class="flex flex-wrap gap-x-4 text-xs text-[#64748B]"><span><strong>Call No:</strong> {{ $book->call_number }}</span><span><strong>Copy:</strong> {{ $loan->copy->copy_code }} ({{ $loan->copy->barcode }})</span></p>
            <p class="flex flex-wrap gap-x-4 pt-1 text-[11px] font-bold text-[#64748B]">
                <span>Borrowed: <span class="text-on-surface">{{ $loan->borrowed_at->format('M d, Y') }}</span></span>
                <span class="{{ $days < 0 ? 'text-red-700' : 'text-on-surface' }}">Due: {{ $loan->due_at->format('M d, Y') }}</span>
                @if ($loan->renewals > 0)<span>Renewed {{ $loan->renewals }}x</span>@endif
            </p>
            @if ($days < 0)
                <p class="text-xs font-semibold text-red-700">Fine so far: PHP {{ number_format($loan->accruedFine(), 2) }}. Return it at the circulation desk.</p>
            @endif
        </div>
    </div>
    <div class="flex shrink-0 flex-row justify-end gap-2 md:flex-col">
        @if ($loan->canRenew())
            <form method="POST" action="{{ route('loans.renew', $loan) }}">
                @csrf
                <button class="flex items-center justify-center gap-1.5 rounded-lg bg-[#EAB308] px-4 py-2 text-[11px] font-bold text-[#0F172A] shadow-sm hover:bg-[#CA8A04]" type="submit">
                    <span class="material-symbols-outlined text-sm">update</span>Renew for {{ $policy['renewal_days'] }} days
                </button>
            </form>
        @else
            <span class="rounded-lg bg-surface-container px-3 py-2 text-center text-[11px] font-semibold text-on-surface-variant">{{ $loan->isOverdue() ? 'Renewal unavailable (overdue)' : 'Renewal limit reached' }}</span>
        @endif
        <span class="text-center font-mono text-[10px] text-outline">Receipt {{ $loan->receipt_no }}</span>
    </div>
</article>
