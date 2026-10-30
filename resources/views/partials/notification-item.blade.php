@php
    $tone = match ($notification['tone']) {
        'error' => 'border-red-200 bg-red-50 text-red-900',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-900',
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-900',
        default => 'border-slate-200 bg-slate-50 text-slate-800',
    };
@endphp
<article class="rounded-xl border {{ $tone }} {{ ($compact ?? false) ? 'p-3' : 'p-4' }}">
    <div class="flex items-start gap-3">
        <span class="material-symbols-outlined mt-0.5 text-lg">{{ $notification['icon'] }}</span>
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-baseline justify-between gap-2">
                <h3 class="text-sm font-bold">{{ $notification['title'] }}</h3>
                <time class="text-[10px] font-semibold opacity-75" datetime="{{ $notification['at']->toIso8601String() }}">{{ $notification['at']->diffForHumans() }}</time>
            </div>
            <p class="mt-1 text-xs leading-5 {{ ($compact ?? false) ? 'line-clamp-2' : '' }}">{{ $notification['body'] }}</p>
            @if ($notification['href'] && ! ($compact ?? false))
                <a class="mt-2 inline-flex rounded-lg bg-white/70 px-3 py-1.5 text-xs font-bold hover:bg-white" href="{{ $notification['href'] }}">{{ $notification['action'] }}</a>
            @endif
        </div>
    </div>
</article>
