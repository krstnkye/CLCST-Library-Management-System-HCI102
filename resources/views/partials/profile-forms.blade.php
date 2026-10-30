{{-- Shared "update my details" and "change password" forms for every portal. --}}
@php($account = auth()->user())
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
