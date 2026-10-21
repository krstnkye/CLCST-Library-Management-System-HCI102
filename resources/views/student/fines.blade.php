<!DOCTYPE html>
<html class="h-full bg-surface" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} - CLCST Library Portal</title>
    @include('partials.favicon')
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#00441d',
                        'primary-container': '#135d2e',
                        'primary-fixed': '#aaf4b5',
                        'on-primary': '#ffffff',
                        secondary: '#785a00',
                        'secondary-container': '#fdc425',
                        'secondary-fixed': '#ffdf9a',
                        background: '#faf8ff',
                        surface: '#faf8ff',
                        'surface-container-lowest': '#ffffff',
                        'surface-container-low': '#f2f3ff',
                        'surface-container': '#eaedff',
                        'surface-container-high': '#e2e7ff',
                        'on-surface': '#131b2e',
                        'on-surface-variant': '#404940',
                        outline: '#707a6f',
                        'outline-variant': '#c0c9bd',
                        error: '#ba1a1a',
                        'error-container': '#ffdad6',
                        tertiary: '#002e97',
                        'tertiary-fixed': '#dce1ff'
                    },
                    fontFamily: {
                        body: ['Inter', 'sans-serif'],
                        headline: ['Plus Jakarta Sans', 'sans-serif']
                    }
                }
            }
        };
    </script>
    <style>
        .material-symbols-outlined {
            display: inline-block;
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            line-height: 1;
            vertical-align: middle;
        }
        .barcode-stripe {
            background: repeating-linear-gradient(90deg, #131b2e, #131b2e 2px, transparent 2px, transparent 4px, #131b2e 4px, #131b2e 7px, transparent 7px, transparent 9px);
        }
    </style>
    @include('partials.ui-motion')
</head>
<body class="h-full overflow-x-hidden bg-surface font-body text-on-surface antialiased">
    @php
        $activeLoanCount = count($activeLoans);
        $reservationCount = count($reservations);
        $paidFineIds = array_keys($paidFines);
        $rows = collect($fineRows)->map(function ($fine) use ($paidFineIds, $paidFines) {
            if (in_array($fine['id'], $paidFineIds, true)) {
                $fine['status'] = 'settled';
                $fine['paid_at'] = $paidFines[$fine['id']];
                $fine['receipt'] = 'OR-'.strtoupper(substr(md5($fine['id']), 0, 6));
            }

            return $fine;
        });
        $outstanding = $rows->where('status', 'outstanding')->sum('amount');
        $settled = $rows->where('status', 'settled')->sum('amount');
        $waived = $rows->where('status', 'waived')->sum('amount');
        $watchCount = $rows->where('status', 'watchlist')->count();
    @endphp

    <header class="sticky top-0 z-40 flex h-16 w-full items-center justify-between border-b border-outline-variant bg-surface-container-lowest px-4 shadow-sm md:px-6">
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-3">
                @include('partials.celtech-logo', ['class' => 'flex h-10 w-10 items-center justify-center rounded-full bg-primary text-sm font-extrabold text-white shadow-sm object-contain bg-white p-0.5'])
                <div class="flex flex-col">
                    <span class="font-headline text-base font-bold leading-none tracking-wide text-primary">CLCST SCHOOL LIBRARY</span>
                    <span class="mt-0.5 text-xs font-medium text-on-surface-variant">Library Notifications</span>
                </div>
            </div>
            <div class="hidden h-6 w-px bg-outline-variant lg:block"></div>
            <form class="relative hidden w-80 items-center md:flex lg:w-96" method="GET" action="{{ route('student.section', 'fines') }}">
                <span class="material-symbols-outlined absolute left-3 text-lg text-outline">search</span>
                <input class="w-full rounded-lg border border-outline-variant bg-surface-container-low py-1.5 pl-9 pr-10 text-sm placeholder:text-outline focus:border-primary-container focus:ring-1 focus:ring-primary-container" name="q" placeholder="Search fines, receipts, call numbers..." value="{{ request('q') }}">
                <button class="absolute right-2.5 text-outline hover:text-primary" title="Search" type="submit"><span class="material-symbols-outlined text-lg">barcode_scanner</span></button>
            </form>
        </div>
        <div class="flex items-center gap-3">
            <div class="hidden items-center gap-1.5 rounded-full border border-outline-variant bg-surface-container px-2.5 py-1 xl:flex">
                <span class="h-2 w-2 rounded-full bg-primary-container"></span>
                <span class="text-xs font-medium text-on-surface-variant">AY 2024-2025 - 1st Sem</span>
            </div>
            <a class="relative rounded-lg p-2 text-on-surface-variant hover:bg-surface-container-low" href="{{ route('student.section', 'notifications') }}" title="Notifications">
                <span class="material-symbols-outlined text-xl">notifications</span>
                @if ($reservationCount > 0)
                    <span class="absolute right-1.5 top-1.5 h-2 w-2 rounded-full bg-error ring-2 ring-surface-container-lowest"></span>
                @endif
            </a>
            <a class="rounded-lg p-2 text-on-surface-variant hover:bg-surface-container-low" href="{{ route('student.section', 'support') }}" title="Help"><span class="material-symbols-outlined text-xl">help</span></a>
            <div class="h-6 w-px bg-outline-variant"></div>
            <a class="flex items-center gap-3 rounded-lg py-1 pl-1 pr-2 hover:bg-surface-container-low" href="{{ route('student.section', 'account') }}">
                <div class="relative"><div class="flex h-9 w-9 items-center justify-center rounded-full bg-primary text-sm font-bold text-on-primary shadow-sm">JD</div><span class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full bg-primary-container ring-2 ring-surface-container-lowest"></span></div>
                <div class="hidden flex-col text-left sm:flex">
                    <div class="flex items-center gap-1.5"><span class="text-sm font-bold leading-tight">Juan Dela Cruz</span><span class="rounded bg-primary-fixed px-1.5 py-0.5 text-[10px] font-bold uppercase text-primary">Verified</span></div>
                    <span class="text-xs text-on-surface-variant">2024-10492-OL - 3rd Year BSCS</span>
                </div>
            </a>
        </div>
    </header>

    <div class="flex min-h-[calc(100vh-4rem)] w-full">
        @include('partials.student-sidebar', ['activePage' => 'fines'])

        <main class="flex-1 space-y-6 p-4 md:ml-64 md:p-8">
            @if (session('status'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>
            @endif

            <div class="flex flex-col justify-between gap-4 border-b border-outline-variant pb-2 md:flex-row md:items-end">
                <div>
                    <nav class="mb-1 flex items-center gap-2 text-xs text-on-surface-variant"><span>Student Portal</span><span class="material-symbols-outlined text-sm">chevron_right</span><span>Borrowing & Account</span><span class="material-symbols-outlined text-sm">chevron_right</span><span class="font-semibold text-primary">Fines & Penalties</span></nav>
                    <h1 class="font-headline text-3xl font-bold tracking-tight text-on-surface">Fines, Penalties & Library Clearance</h1>
                    <p class="mt-1 max-w-3xl text-sm text-on-surface-variant">Review overdue assessments, settled payments, waived grace-period charges, and clearance eligibility for Juan Dela Cruz.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <a class="inline-flex items-center gap-2 rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2 text-sm font-semibold hover:bg-surface-container" href="{{ route('student.section', 'history') }}"><span class="material-symbols-outlined text-lg text-primary">receipt_long</span>View Borrowing Record</a>
                    <a class="inline-flex items-center gap-2 rounded-lg bg-primary-container px-4 py-2 text-sm font-semibold text-on-primary shadow-sm hover:bg-primary" href="#payment-center"><span class="material-symbols-outlined text-lg">payments</span>Payment Center</a>
                </div>
            </div>

            <section class="relative overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest p-6 shadow-sm">
                <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-primary-container/5"></div>
                <div class="relative z-10 flex flex-col justify-between gap-6 lg:flex-row lg:items-center">
                    <div class="flex items-start gap-5 sm:items-center">
                        <div class="relative shrink-0"><div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-gradient-to-br from-primary-container to-primary font-headline text-3xl font-bold text-on-primary shadow-md ring-4 ring-primary-fixed/50">JD</div><div class="absolute -bottom-1 -right-1 rounded-full bg-surface-container-lowest p-0.5 shadow"><span class="material-symbols-outlined block text-xl text-primary-container" style="font-variation-settings: 'FILL' 1;">verified</span></div></div>
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2.5"><h2 class="font-headline text-2xl font-bold">Juan Dela Cruz</h2><span class="rounded-full bg-primary-fixed px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-primary">Undergraduate - Regular</span><span class="rounded-full bg-secondary-fixed px-2.5 py-0.5 text-[10px] font-bold text-on-secondary-container">AY 2024-2025</span></div>
                            <p class="text-sm font-medium text-on-surface-variant">Student ID: <span class="font-mono font-semibold text-on-surface">2024-10492-OL</span> - College of Information and Communications Technology</p>
                            <p class="font-headline text-base font-semibold text-primary">Bachelor of Science in Computer Science (BSCS)</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3 rounded-xl border border-outline-variant/60 bg-surface-container-low p-3.5 sm:grid-cols-4">
                        <div><div class="flex items-center gap-1.5 text-xs text-on-surface-variant"><span class="material-symbols-outlined text-base text-primary">account_balance_wallet</span>Balance</div><span class="mt-0.5 block text-sm font-bold {{ $outstanding > 0 ? 'text-error' : 'text-primary-container' }}">PHP {{ number_format($outstanding, 2) }}</span><span class="text-[11px] text-outline">Current due</span></div>
                        <div><div class="flex items-center gap-1.5 text-xs text-on-surface-variant"><span class="material-symbols-outlined text-base text-primary">verified</span>Clearance</div><span class="mt-0.5 block text-sm font-bold {{ $outstanding > 0 ? 'text-secondary' : 'text-primary-container' }}">{{ $outstanding > 0 ? 'Action Needed' : 'Clear' }}</span><span class="text-[11px] text-outline">Library account</span></div>
                        <div><div class="flex items-center gap-1.5 text-xs text-on-surface-variant"><span class="material-symbols-outlined text-base text-primary">paid</span>Settled</div><span class="mt-0.5 block text-sm font-bold">PHP {{ number_format($settled, 2) }}</span><span class="text-[11px] text-outline">Official receipts</span></div>
                        <div><div class="flex items-center gap-1.5 text-xs text-on-surface-variant"><span class="material-symbols-outlined text-base text-outline">policy</span>Grace Watch</div><span class="mt-0.5 block text-sm font-bold">{{ $watchCount }} Item</span><span class="text-[11px] text-outline">Due monitoring</span></div>
                    </div>
                </div>
            </section>

            <nav aria-label="Fine Sections" class="flex items-center gap-2 overflow-x-auto border-b border-outline-variant">
                <a class="flex whitespace-nowrap border-b-2 border-primary px-4 py-2.5 text-sm font-bold text-primary" href="#ledger"><span class="material-symbols-outlined mr-2 text-lg" style="font-variation-settings: 'FILL' 1;">payments</span>Penalty Record</a>
                <a class="flex whitespace-nowrap border-b-2 border-transparent px-4 py-2.5 text-sm font-semibold text-on-surface-variant hover:text-on-surface" href="#payment-center"><span class="material-symbols-outlined mr-2 text-lg">point_of_sale</span>Payment Options</a>
                <a class="flex whitespace-nowrap border-b-2 border-transparent px-4 py-2.5 text-sm font-semibold text-on-surface-variant hover:text-on-surface" href="#rules"><span class="material-symbols-outlined mr-2 text-lg">gavel</span>Penalty Rules</a>
            </nav>

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
                <section class="space-y-6 xl:col-span-8">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-xl border border-outline-variant bg-surface-container-lowest p-4 shadow-sm"><div class="mb-2 flex items-center justify-between"><span class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Outstanding Balance</span><span class="material-symbols-outlined rounded-lg bg-error-container p-1.5 text-error">priority_high</span></div><p class="font-headline text-2xl font-bold {{ $outstanding > 0 ? 'text-error' : 'text-primary-container' }}">PHP {{ number_format($outstanding, 2) }}</p><p class="mt-2 text-xs text-on-surface-variant">{{ $outstanding > 0 ? 'Payment required for full clearance' : 'No amount currently due' }}</p></div>
                        <div class="rounded-xl border border-outline-variant bg-surface-container-lowest p-4 shadow-sm"><div class="mb-2 flex items-center justify-between"><span class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Settled Payments</span><span class="material-symbols-outlined rounded-lg bg-surface-container p-1.5 text-primary">receipt</span></div><p class="font-headline text-2xl font-bold">PHP {{ number_format($settled, 2) }}</p><p class="mt-2 text-xs text-on-surface-variant">Receipts stored in ledger</p></div>
                        <div class="rounded-xl border border-outline-variant bg-surface-container-lowest p-4 shadow-sm"><div class="mb-2 flex items-center justify-between"><span class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Waived Charges</span><span class="material-symbols-outlined rounded-lg bg-surface-container p-1.5 text-secondary">policy</span></div><p class="font-headline text-2xl font-bold">PHP {{ number_format($waived, 2) }}</p><p class="mt-2 text-xs text-on-surface-variant">Grace policy applied</p></div>
                        <div class="rounded-xl border border-outline-variant bg-surface-container-lowest p-4 shadow-sm"><div class="mb-2 flex items-center justify-between"><span class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Daily Fine Rate</span><span class="material-symbols-outlined rounded-lg bg-primary-fixed p-1.5 text-primary">calendar_month</span></div><p class="font-headline text-2xl font-bold text-primary">PHP 5.00</p><p class="mt-2 text-xs text-on-surface-variant">Per overdue book/day</p></div>
                    </div>

                    <div id="ledger" class="overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm">
                        <div class="flex items-center justify-between border-b border-outline-variant p-4"><div><h2 class="font-headline text-base font-bold">Fine Record</h2><p class="text-xs text-on-surface-variant">Official penalty, waiver, and payment records for this account</p></div><span class="text-[10px] font-bold text-outline">Updated {{ now()->format('M d, Y h:i A') }}</span></div>
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse text-left">
                                <thead><tr class="border-b border-outline-variant bg-surface-container-low text-[10px] font-bold uppercase text-on-surface-variant"><th class="px-4 py-3">Book / Assessment</th><th class="px-4 py-3">Dates</th><th class="px-4 py-3">Computation</th><th class="px-4 py-3">Amount</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Action</th></tr></thead>
                                <tbody class="divide-y divide-outline-variant/60 text-sm">
                                    @foreach ($rows as $fine)
                                        @php
                                            $statusClasses = match ($fine['status']) {
                                                'outstanding' => 'bg-error-container text-error',
                                                'settled' => 'bg-green-100 text-green-800',
                                                'waived' => 'bg-surface-container-high text-on-surface',
                                                'watchlist' => 'bg-secondary-fixed text-on-secondary-container',
                                                default => 'bg-surface-container text-on-surface',
                                            };
                                            $statusLabel = match ($fine['status']) {
                                                'outstanding' => 'Outstanding',
                                                'settled' => 'Paid / Settled',
                                                'waived' => 'Grace Waived',
                                                'watchlist' => 'Due Watchlist',
                                                default => 'Recorded',
                                            };
                                        @endphp
                                        <tr class="hover:bg-surface-container-low/50">
                                            <td class="px-4 py-3.5"><div class="flex items-start gap-3"><div class="flex h-12 w-9 shrink-0 items-center justify-center rounded border border-outline-variant bg-surface-container text-primary"><span class="material-symbols-outlined">book</span></div><div><p class="font-headline text-sm font-bold">{{ $fine['title'] }}</p><p class="text-xs text-on-surface-variant">{{ $fine['copy'] }} - {{ $fine['call'] }}</p><span class="mt-1 inline-flex rounded bg-surface-container px-1.5 py-0.5 font-mono text-[10px] text-outline">{{ $fine['receipt'] }}</span></div></div></td>
                                            <td class="px-4 py-3.5 text-xs"><p><strong>Borrowed:</strong> {{ $fine['borrowed'] }}</p><p><strong>Due:</strong> {{ $fine['due'] }}</p><p><strong>Returned:</strong> {{ $fine['returned'] }}</p></td>
                                            <td class="px-4 py-3.5 text-xs"><p>{{ $fine['days'] }} overdue day(s)</p><p class="text-on-surface-variant">PHP {{ number_format($fine['rate'], 2) }} per day</p></td>
                                            <td class="px-4 py-3.5 font-headline text-base font-bold {{ $fine['amount'] > 0 && $fine['status'] === 'outstanding' ? 'text-error' : 'text-on-surface' }}">PHP {{ number_format($fine['amount'], 2) }}</td>
                                            <td class="px-4 py-3.5"><span class="inline-flex items-center gap-1 rounded-full px-2 py-1 text-[11px] font-bold {{ $statusClasses }}"><span class="material-symbols-outlined text-sm">{{ $fine['status'] === 'outstanding' ? 'warning' : 'check_circle' }}</span>{{ $statusLabel }}</span>@if ($fine['paid_at'])<p class="mt-1 text-[10px] text-outline">{{ $fine['paid_at'] }}</p>@endif</td>
                                            <td class="px-4 py-3.5 text-right">
                                                @if ($fine['status'] === 'outstanding')
                                                    <form method="POST" action="{{ route('fines.pay', $fine['id']) }}">
                                                        @csrf
                                                        <button class="rounded bg-primary-container px-3 py-1.5 text-xs font-bold text-on-primary hover:bg-primary" type="submit">Record Payment</button>
                                                    </form>
                                                @else
                                                    <a class="inline-flex items-center gap-1 rounded border border-outline-variant px-2.5 py-1 text-xs font-semibold hover:bg-surface-container" href="#payment-center"><span class="material-symbols-outlined text-base text-primary">receipt</span>Receipt</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <aside class="space-y-6 xl:col-span-4">
                    <section id="payment-center" class="overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm">
                        <div class="bg-primary-container p-4 text-on-primary"><span class="text-[10px] font-bold uppercase tracking-wider text-on-primary/80">Official Payment Center</span><h3 class="font-headline text-base font-bold text-white">Library Cashier Assessment</h3><p class="mt-0.5 text-xs text-on-primary/90">Counter 1 - CLCST Learning Commons</p></div>
                        <div class="space-y-4 p-5">
                            <div class="rounded-xl border border-outline-variant bg-surface-container-low p-4"><span class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Current Amount Due</span><p class="mt-1 font-headline text-3xl font-bold {{ $outstanding > 0 ? 'text-error' : 'text-primary-container' }}">PHP {{ number_format($outstanding, 2) }}</p><p class="mt-1 text-xs text-on-surface-variant">{{ $outstanding > 0 ? 'Pay at cashier or ask library staff to validate settlement.' : 'No cashier action required.' }}</p></div>
                            <div class="grid grid-cols-2 gap-3 text-xs"><div class="rounded-lg border border-outline-variant bg-surface-container p-3"><p class="font-bold">Cashier Window</p><p class="text-on-surface-variant">8:00 AM - 5:00 PM</p></div><div class="rounded-lg border border-outline-variant bg-surface-container p-3"><p class="font-bold">Receipt Prefix</p><p class="font-mono text-on-surface-variant">LIB-OR</p></div></div>
                            <a class="flex w-full items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-xs font-bold text-on-primary hover:bg-primary-container" href="{{ route('student.section', 'support') }}"><span class="material-symbols-outlined text-lg">support_agent</span>Contact Library Cashier</a>
                        </div>
                    </section>

                    <section class="rounded-xl border border-outline-variant bg-surface-container-lowest p-5 shadow-sm">
                        <h3 class="font-headline mb-3 flex items-center gap-2 text-base font-bold"><span class="material-symbols-outlined text-primary">assignment_turned_in</span>Clearance Monitor</h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex items-center justify-between border-b border-outline-variant/50 pb-2"><span class="text-on-surface-variant">Outstanding Fines</span><span class="font-bold {{ $outstanding > 0 ? 'text-error' : 'text-primary-container' }}">PHP {{ number_format($outstanding, 2) }}</span></div>
                            <div class="flex items-center justify-between border-b border-outline-variant/50 pb-2"><span class="text-on-surface-variant">Overdue Items</span><span class="font-bold text-primary-container">0 Books</span></div>
                            <div class="flex items-center justify-between border-b border-outline-variant/50 pb-2"><span class="text-on-surface-variant">Borrowing Slots</span><span class="font-bold">{{ max(0, 5 - $activeLoanCount) }} of 5 Free</span></div>
                            <div class="rounded-lg {{ $outstanding > 0 ? 'bg-amber-50 border-amber-200 text-amber-900' : 'bg-emerald-50 border-emerald-200 text-emerald-900' }} border p-3 text-xs font-semibold">{{ $outstanding > 0 ? 'Clearance is pending until balance is recorded as paid.' : 'Library clearance is active. No penalties block borrowing.' }}</div>
                        </div>
                    </section>

                    <section id="rules" class="rounded-xl border border-secondary/40 bg-surface-container-lowest p-5 shadow-sm">
                        <div class="mb-2 flex items-center gap-2 text-secondary"><span class="material-symbols-outlined text-xl">gavel</span><h4 class="text-sm font-bold text-on-surface">Fine & Penalty Rules</h4></div>
                        <ul class="space-y-2 text-sm text-on-surface-variant">
                            <li class="flex items-start gap-2"><span class="material-symbols-outlined mt-0.5 shrink-0 text-base text-primary">check_circle</span><span><strong>Daily rate:</strong> PHP 5.00 per overdue circulation book.</span></li>
                            <li class="flex items-start gap-2"><span class="material-symbols-outlined mt-0.5 shrink-0 text-base text-primary">policy</span><span><strong>Grace review:</strong> one-day returns may be waived after librarian validation.</span></li>
                            <li class="flex items-start gap-2"><span class="material-symbols-outlined mt-0.5 shrink-0 text-base text-secondary">warning</span><span><strong>Clearance hold:</strong> unpaid penalties block final library clearance.</span></li>
                        </ul>
                        <div class="mt-4 flex items-center justify-between border-t border-outline-variant/60 pt-3"><a class="flex items-center gap-1 text-xs font-bold text-primary hover:underline" href="{{ route('student.section', 'circulation-policy') }}">View Full Policy<span class="material-symbols-outlined text-sm">arrow_forward</span></a><span class="font-mono text-[11px] text-outline">Rev. 2024</span></div>
                    </section>
                </aside>
            </div>
        </main>
    </div>
</body>
</html>
