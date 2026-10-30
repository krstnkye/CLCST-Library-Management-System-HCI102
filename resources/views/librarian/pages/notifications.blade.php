<section class="space-y-3">
    @forelse ($alerts as [$icon, $tone, $message, $href, $action])
        <article class="flex flex-col gap-3 rounded-lg border bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between {{ match ($tone) { 'red' => 'border-red-200', 'amber' => 'border-amber-200', 'blue' => 'border-blue-200', default => 'border-[#D6DED8]' } }}">
            <p class="flex items-center gap-3 text-sm font-semibold"><span class="material-symbols-outlined rounded-lg p-2 {{ match ($tone) { 'red' => 'bg-red-50 text-red-700', 'amber' => 'bg-amber-50 text-amber-700', 'blue' => 'bg-blue-50 text-blue-700', default => 'bg-slate-100 text-slate-700' } }}">{{ $icon }}</span>{{ $message }}</p>
            <a class="w-fit rounded-lg bg-[#0F5C2E] px-3 py-1.5 text-xs font-bold text-white hover:bg-[#0A4421]" href="{{ $href }}">{{ $action }}</a>
        </article>
    @empty
        <p class="rounded-lg border border-dashed border-[#B7C6BC] bg-white p-10 text-center text-sm text-[#5E6D65]">Nothing needs your attention right now.</p>
    @endforelse
</section>
