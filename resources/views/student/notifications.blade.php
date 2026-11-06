@extends('layouts.student')

@section('title', $title)

@section('content')
    <header>
        <h1 class="font-headline text-2xl font-bold">Notifications</h1>
        <p class="mt-1 text-sm text-on-surface-variant">Due dates, pickups, fines and library announcements, updated every time you open this page. Open any notification to read the full message.</p>
    </header>

    @include('partials.notification-center')
@endsection
