{{-- Inner content of an avatar circle: the uploaded photo, or the user's initials. --}}
@if ($photoUrl = $user->profilePhotoUrl())
    <img src="{{ $photoUrl }}" alt="" class="h-full w-full rounded-[inherit] object-cover" loading="lazy" decoding="async">
@else
    {{ $user->initials() }}
@endif
