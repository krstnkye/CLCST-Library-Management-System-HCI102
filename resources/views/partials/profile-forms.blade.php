{{-- Shared "update my details" and "change password" forms for every portal. --}}
@php($account = auth()->user())
<section class="profile-photo-card mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" aria-labelledby="photo-heading">
    <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
        <div class="profile-photo-frame relative flex h-28 w-28 shrink-0 items-center justify-center overflow-hidden rounded-full bg-emerald-900 text-3xl font-bold text-white" data-photo-frame>
            @include('partials.avatar', ['user' => $account])
        </div>
        <div class="min-w-0 flex-1">
            <h2 id="photo-heading" class="text-lg font-bold">Profile photo</h2>
            <p class="mt-1 text-sm text-slate-500">Shown beside your name across the library system. Use a clear, front-facing photo. JPG, PNG or WebP, up to 2 MB.</p>
            <div class="mt-4 flex flex-wrap items-center gap-2">
                <form method="POST" action="{{ route('profile.photo.store') }}" enctype="multipart/form-data" class="flex flex-wrap items-center gap-2" data-photo-form>
                    @csrf
                    <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-800 hover:bg-slate-50 focus-within:ring-2 focus-within:ring-emerald-700">
                        <span class="material-symbols-outlined text-lg" aria-hidden="true">photo_camera</span>
                        <span>{{ $account->profile_photo_path ? 'Choose a new photo' : 'Choose a photo' }}</span>
                        <input class="sr-only" type="file" name="photo" accept="image/jpeg,image/png,image/webp" required data-photo-input>
                    </label>
                    <button class="hidden rounded-lg bg-emerald-900 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-800" type="submit" data-photo-save>Save photo</button>
                </form>
                @if ($account->profile_photo_path)
                    <form method="POST" action="{{ route('profile.photo.destroy') }}" data-photo-remove>
                        @csrf
                        @method('DELETE')
                        <button class="rounded-lg px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50" type="submit">Remove photo</button>
                    </form>
                @endif
            </div>
            <p class="mt-2 hidden text-xs text-slate-500" data-photo-name aria-live="polite"></p>
            @error('photo')<p class="mt-2 text-sm font-semibold text-red-700" role="alert">{{ $message }}</p>@enderror
        </div>
    </div>
</section>
<script>
    (() => {
        const input = document.querySelector('[data-photo-input]');
        if (!input) return;
        const frame = document.querySelector('[data-photo-frame]');
        const save = document.querySelector('[data-photo-save]');
        const note = document.querySelector('[data-photo-name]');
        const original = frame.innerHTML;
        input.addEventListener('change', () => {
            const file = input.files[0];
            if (!file) { frame.innerHTML = original; save.classList.add('hidden'); note.classList.add('hidden'); return; }
            if (file.size > 2 * 1024 * 1024) {
                note.textContent = 'That photo is larger than 2 MB. Choose a smaller one.';
                note.classList.remove('hidden'); save.classList.add('hidden'); input.value = ''; frame.innerHTML = original; return;
            }
            const img = document.createElement('img');
            img.alt = 'Preview of your new photo';
            img.className = 'h-full w-full rounded-[inherit] object-cover';
            img.src = URL.createObjectURL(file);
            frame.replaceChildren(img);
            note.textContent = 'Preview of ' + file.name + '. Press Save photo to use it.';
            note.classList.remove('hidden'); save.classList.remove('hidden');
        });
    })();
</script>
<div class="grid gap-6 lg:grid-cols-2">
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" aria-labelledby="details-heading">
        <h2 id="details-heading" class="text-lg font-bold">Account details</h2>
        <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
            <div><dt class="text-xs font-semibold uppercase text-slate-500">Name</dt><dd class="font-semibold">{{ $account->name }}</dd></div>
            <div><dt class="text-xs font-semibold uppercase text-slate-500">School / employee ID</dt><dd class="font-mono">{{ $account->patron_id }}</dd></div>
            <div><dt class="text-xs font-semibold uppercase text-slate-500">School email</dt><dd>{{ $account->email }}</dd></div>
            <div><dt class="text-xs font-semibold uppercase text-slate-500">Role</dt><dd>{{ $account->roleLabel() }}</dd></div>
            <div><dt class="text-xs font-semibold uppercase text-slate-500">Program or department</dt><dd>{{ $account->program_or_department ?: 'Not provided' }}</dd></div>
            <div><dt class="text-xs font-semibold uppercase text-slate-500">Last sign-in</dt><dd>{{ $account->last_login_at?->format('M d, Y g:i A') ?? 'First session' }}</dd></div>
        </dl>
        <form class="mt-5 border-t border-slate-200 pt-4" method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PUT')
            <label class="block text-sm font-semibold" for="mobile_number">Mobile number</label>
            <div class="mt-1.5 flex gap-2">
                <input id="mobile_number" name="mobile_number" value="{{ old('mobile_number', $account->mobile_number) }}" required inputmode="tel" pattern="^(09|\+639)\d{9}$" maxlength="13" class="w-full rounded-lg border-slate-300 text-sm focus:border-emerald-700 focus:ring-emerald-700" aria-describedby="mobile-help">
                <button class="shrink-0 rounded-lg bg-emerald-900 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-800" type="submit">Save</button>
            </div>
            <p id="mobile-help" class="mt-1 text-xs text-slate-500">Format: 09171234567. Name, ID and email changes go through the library office.</p>
        </form>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" aria-labelledby="password-heading">
        <h2 id="password-heading" class="text-lg font-bold">Change password</h2>
        <form class="mt-4 space-y-3" method="POST" action="{{ route('profile.password') }}">
            @csrf
            @method('PUT')
            <div><label class="block text-sm font-semibold" for="current_password">Current password</label><input id="current_password" name="current_password" type="password" required autocomplete="current-password" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm focus:border-emerald-700 focus:ring-emerald-700"></div>
            <div><label class="block text-sm font-semibold" for="new_password">New password</label><input id="new_password" name="password" type="password" required minlength="10" autocomplete="new-password" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm focus:border-emerald-700 focus:ring-emerald-700" aria-describedby="password-help"></div>
            <div><label class="block text-sm font-semibold" for="password_confirmation">Confirm new password</label><input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm focus:border-emerald-700 focus:ring-emerald-700"></div>
            <p id="password-help" class="text-xs text-slate-500">At least 10 characters with upper and lower case letters and a number. Other signed-in devices are signed out.</p>
            <button class="rounded-lg bg-emerald-900 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-800" type="submit">Update password</button>
        </form>
    </section>
</div>
