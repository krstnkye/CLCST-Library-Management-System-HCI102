{{-- Read-only view of the current circulation policy ($policy from LibrarySettings::all()). --}}
<section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
    <table class="w-full text-left text-sm">
        <thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">Rule</th><th class="px-5 py-3">Students</th><th class="px-5 py-3">Faculty</th></tr></thead>
        <tbody class="divide-y divide-slate-100">
            <tr><td class="px-5 py-3 font-semibold">Books on loan at once</td><td class="px-5 py-3">{{ $policy['max_loans_student'] }}</td><td class="px-5 py-3">{{ $policy['max_loans_faculty'] }}</td></tr>
            <tr><td class="px-5 py-3 font-semibold">Loan period</td><td class="px-5 py-3">{{ $policy['loan_days_student'] }} days</td><td class="px-5 py-3">{{ $policy['loan_days_faculty'] }} days</td></tr>
            <tr><td class="px-5 py-3 font-semibold">Renewals per loan</td><td class="px-5 py-3" colspan="2">{{ $policy['max_renewals'] }} (adds {{ $policy['renewal_days'] }} days each)</td></tr>
            <tr><td class="px-5 py-3 font-semibold">Open reservations per patron</td><td class="px-5 py-3" colspan="2">{{ $policy['max_reservations'] }}, held {{ $policy['pickup_days'] }} days once ready</td></tr>
            <tr><td class="px-5 py-3 font-semibold">Overdue fine</td><td class="px-5 py-3" colspan="2">PHP {{ number_format($policy['fine_per_day'], 2) }} per day, at most PHP {{ number_format($policy['max_fine_per_item'], 2) }} per book</td></tr>
        </tbody>
    </table>
</section>
