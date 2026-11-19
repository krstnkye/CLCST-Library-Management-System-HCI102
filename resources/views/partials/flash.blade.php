{{-- Routine actions become non-blocking toasts. Quiet notices stay inline; validation errors remain persistent. --}}
@if (session('status'))
    @if (session('status_quiet'))
        <div class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-900" role="status">
            <span class="material-symbols-outlined text-xl" aria-hidden="true">check_circle</span><span>{{ session('status') }}</span>
        </div>
    @else
        <div hidden data-flash-toast data-toast-tone="success" data-toast-title="{{ session('status_title', 'Success') }}"
            @if (session('status_link')) data-toast-link="{{ session('status_link')['url'] }}" data-toast-link-label="{{ session('status_link')['label'] }}" @endif>
            <span data-flash-message>{{ session('status') }}</span>
        </div>
    @endif
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
