<div class="space-y-4">
    @include('partials.policy-table')
    <p class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-950">
        These rules are enforced on every checkout, renewal and return.
        @if (auth()->user()->role === 'superadmin')
            You can change them under <a class="font-bold underline" href="{{ route('superadmin.section', 'borrowing-rules') }}">Borrowing Rules</a>.
        @else
            Only a super administrator can change them.
        @endif
    </p>
</div>
