@extends('layouts.student')

@section('title', $title)

@section('content')
    <header>
        <h1 class="font-headline text-2xl font-bold">Notifications</h1>
        <p class="mt-1 text-sm text-on-surface-variant">Due dates, pickups, fines and library announcements, updated every time you open this page.</p>
    </header>

    <section class="space-y-3" aria-label="Notifications">
        @forelse ($notifications as $notification)
            @include('partials.notification-item', ['notification' => $notification])
        @empty
            <p class="rounded-xl border border-dashed border-outline-variant p-8 text-center text-sm text-on-surface-variant">You are all caught up. New alerts will appear here.</p>
        @endforelse
    </section>
@endsection
