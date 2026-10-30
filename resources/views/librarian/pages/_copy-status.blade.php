<form class="flex items-center gap-1" method="POST" action="{{ route('librarian.copies.update', $copy) }}">
    @csrf
    @method('PATCH')
    <label class="sr-only" for="copy-status-{{ $copy->id }}">Status for {{ $copy->copy_code }}</label>
    <select id="copy-status-{{ $copy->id }}" name="status" class="rounded-lg border-[#CBD6CF] py-1 text-xs">
        <option value="available" @selected(in_array($copy->status, ['available', 'reference'], true))>On shelf</option>
        <option value="maintenance" @selected($copy->status === 'maintenance')>For repair</option>
        <option value="lost" @selected($copy->status === 'lost')>Lost</option>
    </select>
    <button class="rounded-lg border border-[#CBD6CF] px-2 py-1 text-xs font-bold hover:bg-[#F4F7F5]" type="submit">Set</button>
</form>
