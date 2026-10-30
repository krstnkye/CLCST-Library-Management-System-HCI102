{{-- Shared activity log table. Expects a paginated $logs and the $searchRoute to submit to. --}}
<section class="rounded-lg border border-slate-200 bg-white shadow-sm">
    <form class="flex flex-col gap-3 border-b border-slate-200 p-4 sm:flex-row sm:items-center sm:justify-between" method="GET" action="{{ $searchRoute }}" role="search">
        <p class="text-sm text-slate-600">{{ number_format($logs->total()) }} recorded actions. Entries cannot be edited or deleted from the application.</p>
        <div class="flex gap-2"><label class="sr-only" for="log-search">Search the log</label><input id="log-search" class="rounded-lg border-slate-300 py-1.5 text-sm" name="q" value="{{ request('q') }}" placeholder="Search events" type="search"><button class="rounded-lg border border-slate-300 px-3 text-xs font-bold" type="submit">Search</button></div>
    </form>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[820px] text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">When</th><th class="px-5 py-3">Who</th><th class="px-5 py-3">Event</th><th class="px-5 py-3">Details</th><th class="px-5 py-3">IP address</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($logs as $log)
                    <tr class="{{ str_contains($log->event, 'failed') ? 'bg-red-50/50' : '' }}">
                        <td class="whitespace-nowrap px-5 py-3 text-xs">{{ $log->created_at->format('M d, Y g:i A') }}</td>
                        <td class="px-5 py-3">{{ $log->user?->name ?? 'Guest / system' }}</td>
                        <td class="px-5 py-3 font-mono text-xs">{{ $log->event }}</td>
                        <td class="px-5 py-3">{{ $log->description }}</td>
                        <td class="px-5 py-3 font-mono text-xs text-slate-500">{{ $log->ip_address }}</td>
                    </tr>
                @empty
                    <tr><td class="px-5 py-10 text-center text-slate-500" colspan="5">No matching activity.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($logs->hasPages())<div class="border-t border-slate-200 p-4">{{ $logs->links() }}</div>@endif
</section>
