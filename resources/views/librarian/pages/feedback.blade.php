<section class="rounded-lg border border-[#D6DED8] bg-white shadow-sm">
    <div class="flex flex-wrap gap-2 border-b border-[#E2E8E4] p-4">
        <a class="rounded-full px-3 py-1.5 text-xs font-bold {{ request('type') ? 'border border-[#CBD6CF]' : 'bg-[#0F5C2E] text-white' }}" href="{{ route('librarian.section', 'feedback') }}">All</a>
        @foreach (\App\Models\LibraryRequest::TYPES as $value => $label)
            <a class="rounded-full px-3 py-1.5 text-xs font-bold {{ request('type') === $value ? 'bg-[#0F5C2E] text-white' : 'border border-[#CBD6CF]' }}" href="{{ route('librarian.section', ['feedback', 'type' => $value]) }}">{{ $label }}</a>
        @endforeach
    </div>
    <ul class="divide-y divide-[#E2E8E4]">
        @forelse ($requests as $libraryRequest)
            <li class="grid gap-4 px-5 py-4 lg:grid-cols-12">
                <div class="lg:col-span-7">
                    <p class="text-xs font-bold uppercase text-[#0F5C2E]">{{ $libraryRequest->typeLabel() }} - REQ-{{ str_pad((string) $libraryRequest->id, 5, '0', STR_PAD_LEFT) }}</p>
                    <p class="mt-1 font-bold">{{ $libraryRequest->subject }}</p>
                    @if ($libraryRequest->details)<p class="mt-1 whitespace-pre-line text-sm text-[#44534B]">{{ $libraryRequest->details }}</p>@endif
                    <p class="mt-2 text-xs text-[#718078]">From {{ $libraryRequest->user->name }} ({{ $libraryRequest->user->roleLabel() }}) - {{ $libraryRequest->created_at->format('M d, Y g:i A') }}@if ($libraryRequest->handler) - last handled by {{ $libraryRequest->handler->name }}@endif</p>
                </div>
                <form class="space-y-2 lg:col-span-5" method="POST" action="{{ route('librarian.requests.update', $libraryRequest) }}">
                    @csrf
                    @method('PATCH')
                    <label class="sr-only" for="request-status-{{ $libraryRequest->id }}">Status</label>
                    <select id="request-status-{{ $libraryRequest->id }}" name="status" class="w-full rounded-lg border-[#CBD6CF] text-sm">
                        @foreach (\App\Models\LibraryRequest::STATUSES as $status)
                            <option value="{{ $status }}" @selected($libraryRequest->status === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                    <label class="sr-only" for="request-response-{{ $libraryRequest->id }}">Reply to the patron</label>
                    <textarea id="request-response-{{ $libraryRequest->id }}" name="response" rows="2" maxlength="2000" placeholder="Reply shown to the patron (optional)" class="w-full rounded-lg border-[#CBD6CF] text-sm">{{ $libraryRequest->response }}</textarea>
                    <button class="rounded-lg bg-[#0F5C2E] px-3 py-1.5 text-xs font-bold text-white hover:bg-[#0A4421]" type="submit">Save</button>
                </form>
            </li>
        @empty
            <li class="px-5 py-10 text-center text-sm text-[#67766E]">No requests yet.</li>
        @endforelse
    </ul>
    @if ($requests->hasPages())<div class="border-t border-[#E2E8E4] p-4">{{ $requests->links() }}</div>@endif
</section>
