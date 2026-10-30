<div class="grid gap-6 xl:grid-cols-12">
    <form class="space-y-3 rounded-lg border border-[#D6DED8] bg-white p-5 shadow-sm xl:col-span-5" method="POST" action="{{ route('librarian.announcements.store') }}">
        @csrf
        <h2 class="font-display text-lg font-bold">New announcement</h2>
        <div><label class="mb-1.5 block text-sm font-bold" for="announcement-title">Title</label><input id="announcement-title" name="title" value="{{ old('title') }}" required maxlength="150" class="w-full rounded-lg border-[#CBD6CF] text-sm"></div>
        <div><label class="mb-1.5 block text-sm font-bold" for="announcement-body">Message</label><textarea id="announcement-body" name="body" rows="5" required maxlength="2000" class="w-full rounded-lg border-[#CBD6CF] text-sm">{{ old('body') }}</textarea></div>
        <div><label class="mb-1.5 block text-sm font-bold" for="announcement-audience">Show to</label><select id="announcement-audience" name="audience" class="w-full rounded-lg border-[#CBD6CF] text-sm">@foreach (\App\Models\Announcement::AUDIENCES as $value => $label)<option value="{{ $value }}" @selected(old('audience') === $value)>{{ $label }}</option>@endforeach</select></div>
        <button class="rounded-lg bg-[#0F5C2E] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#0A4421]" type="submit">Publish</button>
        <p class="text-xs text-[#67766E]">Announcements appear immediately in student and faculty notifications.</p>
    </form>

    <section class="rounded-lg border border-[#D6DED8] bg-white shadow-sm xl:col-span-7">
        <h2 class="border-b border-[#E2E8E4] px-5 py-4 font-display text-lg font-bold">Published ({{ $announcements->count() }})</h2>
        <ul class="divide-y divide-[#E2E8E4]">
            @forelse ($announcements as $announcement)
                <li class="flex items-start justify-between gap-4 px-5 py-4">
                    <div>
                        <p class="font-bold">{{ $announcement->title }} <span class="ml-1 rounded bg-[#EEF5F0] px-1.5 py-0.5 text-[10px] font-bold text-[#0F5C2E]">{{ \App\Models\Announcement::AUDIENCES[$announcement->audience] ?? $announcement->audience }}</span></p>
                        <p class="mt-1 text-sm text-[#44534B]">{{ $announcement->body }}</p>
                        <p class="mt-1 text-xs text-[#718078]">{{ $announcement->published_at->format('M d, Y g:i A') }} by {{ $announcement->author?->name ?? 'Former staff' }}</p>
                    </div>
                    <form method="POST" action="{{ route('librarian.announcements.destroy', $announcement) }}" onsubmit="return confirm('Remove this announcement?')">@csrf @method('DELETE')<button class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-bold text-red-700 hover:bg-red-50" type="submit">Remove</button></form>
                </li>
            @empty
                <li class="px-5 py-10 text-center text-sm text-[#67766E]">Nothing published yet.</li>
            @endforelse
        </ul>
    </section>
</div>
