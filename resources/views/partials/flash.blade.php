{{-- Inline banner plus a pop-up (see public/js/ui-actions.js) so the result of an action is hard to miss. --}}
@if (session('status'))
    <div class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-900" role="status"
        data-flash="success" data-flash-title="{{ session('status_title', 'Success') }}"
        @if (session('status_link')) data-flash-link="{{ session('status_link')['url'] }}" data-flash-link-label="{{ session('status_link')['label'] }}" @endif>
        <span class="material-symbols-outlined text-xl">check_circle</span><span data-flash-message>{{ session('status') }}</span>
    </div>
@endif
@if ($errors->any())
    <div class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-900" role="alert"
        data-flash="error" data-flash-title="{{ session('error_title', 'That did not go through') }}">
        <span class="material-symbols-outlined text-xl">error</span>
        <div data-flash-message>
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    </div>
@endif
