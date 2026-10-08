<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} - CLCST Library Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <main class="mx-auto max-w-4xl px-6 py-10">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">CLCST Student Portal</p>
                <h1 class="mt-1 text-3xl font-bold">{{ $title }}</h1>
            </div>
            <div class="flex gap-2">
                <a class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold hover:bg-white" href="{{ route('books.catalog') }}">Catalog</a>
                <a class="rounded-lg bg-emerald-800 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-900" href="{{ route('student.dashboard') }}">Dashboard</a>
            </div>
        </div>

        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            @if ($section === 'reservations')
                <h2 class="text-lg font-bold">Active Reservations</h2>
                @forelse ($reservations as $reservation)
                    <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                        <p class="font-semibold text-emerald-900">{{ $reservation['title'] }}</p>
                        <p class="text-sm text-emerald-700">Requested {{ $reservation['requested_at'] }}</p>
                    </div>
                @empty
                    <p class="mt-3 text-slate-600">No active reservations yet. Use the catalog to place a hold.</p>
                @endforelse
            @elseif ($section === 'borrow-return')
                <h2 class="text-lg font-bold">Current Loans</h2>
                @foreach ($activeLoans as $loan)
                    <div class="mt-4 rounded-lg border border-slate-200 p-4">
                        <p class="font-semibold">{{ $loan['title'] }}</p>
                        <p class="text-sm text-slate-600">Due {{ $renewedLoans[$loan['id']] ?? $loan['due'] }}</p>
                    </div>
                @endforeach
            @elseif ($section === 'history')
                <h2 class="text-lg font-bold">Borrowing History</h2>
                <p class="mt-3 text-slate-600">Returned books and historical loan logs will appear here.</p>
            @elseif ($section === 'fines')
                <h2 class="text-lg font-bold">Fines & Penalties</h2>
                <p class="mt-3 rounded-lg bg-emerald-50 p-4 text-emerald-800">Clean record. No outstanding penalties.</p>
            @elseif ($section === 'account')
                <h2 class="text-lg font-bold">My Account</h2>
                <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                    <div><dt class="font-semibold text-slate-500">Name</dt><dd>Juan Dela Cruz</dd></div>
                    <div><dt class="font-semibold text-slate-500">Student ID</dt><dd>2024-10492-OL</dd></div>
                    <div><dt class="font-semibold text-slate-500">Program</dt><dd>BS Computer Science III</dd></div>
                    <div><dt class="font-semibold text-slate-500">Loan quota</dt><dd>{{ count($activeLoans) }} / 5 active loans</dd></div>
                </dl>
            @elseif ($section === 'scan')
                <h2 class="text-lg font-bold">Shelf Barcode Scanner</h2>
                <form class="mt-4 flex gap-2" method="GET" action="{{ route('books.catalog') }}">
                    <input class="flex-1 rounded-lg border border-slate-300 px-4 py-2" name="q" placeholder="Enter barcode or ISBN" value="1049281">
                    <button class="rounded-lg bg-emerald-800 px-4 py-2 font-semibold text-white" type="submit">Search</button>
                </form>
            @else
                <h2 class="text-lg font-bold">{{ $title }}</h2>
                <p class="mt-3 text-slate-600">This section is connected and ready for the next database-backed workflow.</p>
            @endif
        </section>
    </main>
</body>
</html>
