{{-- Shared report body for the librarian, administrator and super administrator portals. --}}
<div class="space-y-6">
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($report['totals'] as $label => $value)
            <article class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm"><p class="text-xs font-semibold text-slate-500">{{ $label }}</p><p class="mt-2 text-2xl font-extrabold">{{ number_format($value) }}</p></article>
        @endforeach
    </section>

    <div class="grid gap-6 xl:grid-cols-12">
        <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm xl:col-span-7" aria-labelledby="monthly-heading">
            <h2 id="monthly-heading" class="text-lg font-bold">Loans per month</h2>
            <ul class="mt-4 space-y-3">
                @foreach ($report['months'] as $month)
                    <li class="grid grid-cols-[80px_1fr_40px] items-center gap-3 text-sm">
                        <span class="text-xs font-semibold text-slate-500">{{ $month['label'] }}</span>
                        <span class="h-4 overflow-hidden rounded bg-slate-100"><span class="block h-full rounded bg-emerald-700" style="width: {{ round($month['count'] / $report['maxMonth'] * 100) }}%"></span></span>
                        <span class="text-right font-bold">{{ $month['count'] }}</span>
                    </li>
                @endforeach
            </ul>
            <p class="mt-4 text-sm text-slate-600">On-time return rate: <strong>{{ $report['onTimeRate'] !== null ? $report['onTimeRate'].'%' : 'no returns yet' }}</strong></p>
        </section>

        <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm xl:col-span-5" aria-labelledby="top-heading">
            <h2 id="top-heading" class="text-lg font-bold">Most borrowed titles</h2>
            <ol class="mt-4 space-y-2 text-sm">
                @forelse ($report['topTitles'] as $index => $title)
                    <li class="flex items-center justify-between gap-3"><span><span class="mr-2 font-bold text-emerald-800">{{ $index + 1 }}.</span>{{ $title->title }}</span><span class="shrink-0 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold">{{ $title->loans }}</span></li>
                @empty
                    <li class="text-slate-500">No loans yet.</li>
                @endforelse
            </ol>
        </section>

        <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm xl:col-span-4">
            <h2 class="text-lg font-bold">Fines</h2>
            <dl class="mt-3 space-y-2 text-sm">@foreach ($report['fines'] as $label => $amount)<div class="flex justify-between"><dt>{{ $label }}</dt><dd class="font-bold">PHP {{ number_format($amount, 2) }}</dd></div>@endforeach</dl>
        </section>
        <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm xl:col-span-4">
            <h2 class="text-lg font-bold">Loans by patron type</h2>
            <dl class="mt-3 space-y-2 text-sm">@forelse ($report['byRole'] as $role => $total)<div class="flex justify-between"><dt>{{ ucfirst($role) }}</dt><dd class="font-bold">{{ $total }}</dd></div>@empty<p class="text-slate-500">No loans yet.</p>@endforelse</dl>
        </section>
        <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm xl:col-span-4">
            <h2 class="text-lg font-bold">Copy status</h2>
            <dl class="mt-3 space-y-2 text-sm">@forelse ($report['copyStatus'] as $status => $total)<div class="flex justify-between"><dt>{{ ucfirst(str_replace('_', ' ', $status)) }}</dt><dd class="font-bold">{{ $total }}</dd></div>@empty<p class="text-slate-500">No copies yet.</p>@endforelse</dl>
        </section>
    </div>
</div>
