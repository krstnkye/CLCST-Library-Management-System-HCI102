@if (session('status'))
    <div class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-900" role="status">
        <span class="material-symbols-outlined text-xl">check_circle</span><span>{{ session('status') }}</span>
    </div>
@endif
@if ($errors->any())
    <div class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-900" role="alert">
        <span class="material-symbols-outlined text-xl">error</span>
        <div>
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    </div>
@endif
