<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Vibe App') }}</title>
    @include('partials.favicon')

    <script src="https://cdn.tailwindcss.com"></script>
    @include('partials.ui-motion')
</head>
<body class="bg-slate-50 text-slate-900 antialiased">
    <main class="min-h-screen px-6 py-12">
        <div class="mx-auto max-w-3xl">
            <section class="rounded-lg border border-slate-200 bg-white p-8 shadow-sm">
                <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">Laravel Starter</p>
                <h1 class="mt-3 text-3xl font-bold">Vibe App</h1>
                <p class="mt-4 text-slate-600">
                    A clean Laravel homepage prepared for shared hosting, MySQL, and future AI-powered generation features.
                </p>

                <form method="POST" action="{{ route('generate') }}" class="mt-8 space-y-5">
                    @csrf

                    <div>
                        <label for="business_type" class="block text-sm font-medium text-slate-700">
                            Business type
                        </label>
                        <input
                            type="text"
                            id="business_type"
                            name="business_type"
                            value="{{ old('business_type') }}"
                            placeholder="Example: bakery, clinic, bookstore"
                            class="mt-2 w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                            required
                        >
                        @error('business_type')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        class="rounded-md bg-indigo-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2"
                    >
                        Generate
                    </button>
                </form>
            </section>
        </div>
    </main>

    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
