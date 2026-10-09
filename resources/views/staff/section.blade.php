<!DOCTYPE html>
<html class="h-full bg-[#F8FAFC]" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} - CLCST Library Management System</title>

    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Plus Jakarta Sans', 'sans-serif']
                    }
                }
            }
        };
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            display: inline-block;
            line-height: 1;
            vertical-align: middle;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="h-full bg-[#F8FAFC] text-[#0F172A] antialiased">
    <header class="sticky top-0 z-40 flex h-16 w-full items-center justify-between border-b border-[#CBD5E1] bg-white px-4 shadow-sm lg:px-6">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#065F2A] text-sm font-extrabold text-white shadow-sm">CL</div>
            <div>
                <span class="block font-display text-sm font-bold leading-none tracking-wide text-[#064E3B] sm:text-base">CLCST LIBRARY MANAGEMENT SYSTEM</span>
                <span class="mt-0.5 hidden text-[10px] font-bold uppercase tracking-wider text-[#64748B] sm:block">{{ $title }}</span>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a class="hidden rounded-lg border border-[#CBD5E1] px-3 py-2 text-xs font-bold text-[#334155] hover:bg-[#F8FAFC] sm:inline-flex" href="{{ route('staff.dashboard') }}">Staff Dashboard</a>
            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-[#065F2A] text-sm font-bold text-white">MS</div>
        </div>
    </header>

    <div class="flex min-h-[calc(100vh-4rem)]">
        @include('partials.staff-sidebar', ['activePage' => $section])

        <main class="md:ml-64 min-w-0 flex-1 space-y-6 overflow-y-auto p-4 sm:p-6 lg:p-8">
            <section class="rounded-2xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
                <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center">
                    <div>
                        <span class="inline-flex rounded-full bg-[#DCFCE7] px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-[#166534]">Operations Workspace</span>
                        <h1 class="mt-3 font-display text-3xl font-bold">{{ $title }}</h1>
                        <p class="mt-2 max-w-3xl text-sm leading-6 text-[#475569]">
                            Manage this area using the live records already available in the library portal. Each action below opens the matching catalog, queue, or record view.
                        </p>
                    </div>
                    <a class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#065F2A] px-4 py-2 text-sm font-bold text-white" href="{{ route('books.catalog') }}">
                        <span class="material-symbols-outlined text-base">menu_book</span>
                        Open Catalog
                    </a>
                </div>
            </section>

            @if ($section === 'circulation')
                <section class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                    @foreach ($circulationQueue as $row)
                        <article class="rounded-xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
                            <span class="rounded-full bg-[#EEF2FF] px-2.5 py-1 text-[10px] font-bold text-[#334155]">{{ $row['ticket'] }}</span>
                            <h2 class="mt-3 font-display text-lg font-bold">{{ $row['type'] }}</h2>
                            <p class="mt-1 text-sm text-[#475569]">{{ $row['patron'] }} - {{ $row['item'] }}</p>
                            <div class="mt-4 flex items-center justify-between">
                                <span class="text-xs font-bold text-[#065F2A]">{{ $row['status'] }}</span>
                                <button class="rounded-lg bg-[#065F2A] px-3 py-2 text-xs font-bold text-white" type="button">Process</button>
                            </div>
                        </article>
                    @endforeach
                </section>
            @elseif ($section === 'patrons')
                <section class="rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[760px] text-left text-sm">
                            <thead class="border-b border-[#E2E8F0] text-xs uppercase tracking-wider text-[#64748B]">
                                <tr>
                                    <th class="py-3">Patron</th>
                                    <th class="py-3">ID</th>
                                    <th class="py-3">Program</th>
                                    <th class="py-3">Loans</th>
                                    <th class="py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E2E8F0]">
                                @foreach ($patronRecords as $patron)
                                    <tr>
                                        <td class="py-4 font-bold">{{ $patron['name'] }}</td>
                                        <td class="py-4 font-mono text-xs">{{ $patron['id'] }}</td>
                                        <td class="py-4">{{ $patron['program'] }}</td>
                                        <td class="py-4">{{ $patron['loans'] }}</td>
                                        <td class="py-4"><span class="rounded-full bg-[#F0FDF4] px-2.5 py-1 text-xs font-bold text-[#166534]">{{ $patron['status'] }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @elseif ($section === 'catalog')
                <section class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                    @foreach ($catalogBooks as $book)
                        <article class="rounded-xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
                            <div class="flex gap-4">
                                <img class="h-28 w-20 rounded-lg border border-[#E2E8F0] object-cover shadow-sm" src="{{ $book['cover_url'] }}" alt="{{ $book['title'] }} cover">
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#A16207]">{{ $book['subject'] }}</span>
                                    <h2 class="mt-1 font-display text-lg font-bold">{{ $book['title'] }}</h2>
                                    <p class="text-sm text-[#475569]">by {{ $book['author'] }} - {{ $book['year'] }}</p>
                                    <p class="mt-2 text-xs text-[#64748B]">Call No: <strong class="text-[#0F172A]">{{ $book['call'] }}</strong></p>
                                    <p class="text-xs text-[#64748B]">Available copies: <strong class="text-[#065F2A]">{{ collect($book['copies'])->where('status', 'available')->count() }}</strong></p>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </section>
            @elseif ($section === 'reservations')
                <section class="rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
                    <h2 class="font-display text-xl font-bold">Active Reservation Queue</h2>
                    <div class="mt-4 space-y-3">
                        @forelse ($reservations as $reservation)
                            <div class="rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] p-4">
                                <p class="font-bold">{{ $reservation['title'] }}</p>
                                <p class="text-xs text-[#64748B]">Requested {{ $reservation['requested_at'] }}</p>
                            </div>
                        @empty
                            @foreach ($facultyRequests as $request)
                                <div class="rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] p-4">
                                    <p class="font-bold">{{ $request['course'] }}</p>
                                    <p class="text-sm text-[#475569]">{{ $request['request'] }}</p>
                                    <p class="mt-1 text-xs font-semibold text-[#065F2A]">{{ $request['status'] }}</p>
                                </div>
                            @endforeach
                        @endforelse
                    </div>
                </section>
            @else
                <section class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                    @foreach ([
                        ['icon' => 'description', 'title' => 'Daily Summary', 'text' => 'Review circulation counts, open fines, and catalog activity.'],
                        ['icon' => 'verified_user', 'title' => 'Access Control', 'text' => 'Keep staff-only pages limited to authorized library personnel.'],
                        ['icon' => 'support_agent', 'title' => 'Desk Support', 'text' => 'Use this area for library desk notes, help requests, and operating procedures.'],
                    ] as $card)
                        <article class="rounded-xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
                            <span class="flex h-11 w-11 items-center justify-center rounded-lg bg-[#DCFCE7] text-[#166534]">
                                <span class="material-symbols-outlined">{{ $card['icon'] }}</span>
                            </span>
                            <h2 class="mt-4 font-display text-lg font-bold">{{ $card['title'] }}</h2>
                            <p class="mt-2 text-sm leading-6 text-[#475569]">{{ $card['text'] }}</p>
                        </article>
                    @endforeach
                </section>
            @endif
        </main>
    </div>
</body>
</html>
