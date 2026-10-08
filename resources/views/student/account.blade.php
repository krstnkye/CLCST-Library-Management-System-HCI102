<!DOCTYPE html>
<html class="h-full bg-surface" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} - CLCST Library Portal</title>
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
            background: repeating-linear-gradient(90deg, #131b2e, #131b2e 2px, transparent 2px, transparent 4px, #131b2e 4px, #131b2e 7px, transparent 7px, transparent 9px, #131b2e 9px, #131b2e 13px, transparent 13px, transparent 15px);
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="h-full overflow-x-hidden bg-surface font-body text-on-surface antialiased">
    @php
        $activeLoanCount = count($activeLoans);
        $reservationCount = count($reservations);
        $paidFineIds = array_keys($paidFines);
        $fineRows = collect($fineRows)->map(function ($fine) use ($paidFineIds) {
            if (in_array($fine['id'], $paidFineIds, true)) {
                $fine['status'] = 'settled';
            }

            return $fine;
        });
        $outstanding = $fineRows->where('status', 'outstanding')->sum('amount');
        $slotsLeft = max(0, 5 - $activeLoanCount);
    @endphp

    <header class="sticky top-0 z-40 flex h-16 w-full items-center justify-between border-b border-outline-variant bg-surface-container-lowest px-4 shadow-sm md:px-6">
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary text-sm font-extrabold text-white shadow-sm">CL</div>
                <div class="flex flex-col">
                    <span class="font-headline text-base font-bold leading-none tracking-wide text-primary">CLCST SCHOOL LIBRARY</span>
                    <span class="mt-0.5 text-xs font-medium text-on-surface-variant">Automated Learning Resource Center</span>
                </div>
            </div>
            <div class="hidden h-6 w-px bg-outline-variant lg:block"></div>
            <form class="relative hidden w-80 items-center md:flex lg:w-96" method="GET" action="{{ route('books.catalog') }}">
                <span class="material-symbols-outlined absolute left-3 text-lg text-outline">search</span>
                <input class="w-full rounded-lg border border-outline-variant bg-surface-container-low py-1.5 pl-9 pr-10 text-sm placeholder:text-outline focus:border-primary-container focus:ring-1 focus:ring-primary-container" name="q" placeholder="Search books, call numbers, e-journals...">
                <button class="absolute right-2.5 text-outline hover:text-primary" title="Scan ISBN" type="submit"><span class="material-symbols-outlined text-lg">barcode_scanner</span></button>
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
            <div class="flex items-center gap-3 rounded-lg py-1 pl-1 pr-2">
                <div class="relative"><div class="flex h-9 w-9 items-center justify-center rounded-full bg-primary text-sm font-bold text-on-primary shadow-sm">JD</div><span class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full bg-primary-container ring-2 ring-surface-container-lowest"></span></div>
                <div class="hidden flex-col text-left sm:flex">
                    <div class="flex items-center gap-1.5"><span class="text-sm font-bold leading-tight">Juan Dela Cruz</span><span class="rounded bg-primary-fixed px-1.5 py-0.5 text-[10px] font-bold uppercase text-primary">Verified</span></div>
                    <span class="text-xs text-on-surface-variant">2024-10492-OL - 3rd Year BSCS</span>
                </div>
            </div>
        </div>
    </header>

    <div class="flex min-h-[calc(100vh-4rem)] w-full">
        <aside class="fixed left-0 top-0 z-30 hidden h-screen w-64 flex-col border-r border-outline-variant bg-surface-container-lowest pt-16 md:flex">
            <div class="border-b border-outline-variant bg-surface-container-low/60 p-4">
                <div class="mb-1.5 flex items-center justify-between"><span class="text-[10px] font-bold uppercase tracking-wider text-primary">Patron Status</span><span class="inline-flex items-center gap-1 rounded bg-primary-fixed px-1.5 py-0.5 text-[10px] font-bold text-primary"><span class="h-1.5 w-1.5 rounded-full bg-primary"></span>ACTIVE</span></div>
                <p class="font-headline text-base font-bold leading-tight">BS Computer Science</p>
                <p class="text-xs text-on-surface-variant">Batch 2022-2026 - Undergrad</p>
                <div class="mt-3 flex items-center justify-between border-t border-outline-variant/60 pt-2.5 text-xs"><span class="font-medium text-on-surface-variant">Borrow Limit:</span><span class="font-bold text-primary">{{ $activeLoanCount }} / 5 active loans</span></div>
                <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-surface-container-high"><div class="h-full rounded-full bg-primary-container" style="width: {{ min(100, $activeLoanCount * 20) }}%"></div></div>
            </div>
            <div class="px-3 pt-3">
                <a class="flex w-full items-center justify-center gap-2 rounded-lg border border-outline-variant bg-surface-container px-3 py-2 text-sm font-semibold text-primary hover:bg-surface-container-high" href="{{ route('student.section', 'scan') }}"><span class="material-symbols-outlined text-lg">qr_code_scanner</span>Scan Barcode</a>
            </div>
            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-3">
                <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-xs font-semibold text-on-surface-variant hover:bg-surface-container hover:text-on-surface" href="{{ route('student.dashboard') }}"><span class="material-symbols-outlined text-xl">dashboard</span>Dashboard</a>
                <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-xs font-semibold text-on-surface-variant hover:bg-surface-container hover:text-on-surface" href="{{ route('books.catalog') }}"><span class="material-symbols-outlined text-xl">menu_book</span>Book Catalog</a>
                <a class="flex items-center justify-between rounded-lg px-3 py-2.5 text-xs font-semibold text-on-surface-variant hover:bg-surface-container hover:text-on-surface" href="{{ route('student.section', 'borrow-return') }}"><span class="flex items-center gap-3"><span class="material-symbols-outlined text-xl">swap_horiz</span>Borrowed Books</span><span class="rounded-full bg-surface-container-high px-1.5 py-0.5 text-[10px] font-bold text-primary">{{ $activeLoanCount }}</span></a>
                <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-xs font-semibold text-on-surface-variant hover:bg-surface-container hover:text-on-surface" href="{{ route('student.section', 'history') }}"><span class="material-symbols-outlined text-xl">history_edu</span>Borrowing History</a>
                <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-xs font-semibold text-on-surface-variant hover:bg-surface-container hover:text-on-surface" href="{{ route('student.section', 'reservations') }}"><span class="material-symbols-outlined text-xl">book_online</span>Reservations</a>
                <a class="flex items-center justify-between rounded-lg px-3 py-2.5 text-xs font-semibold text-on-surface-variant hover:bg-surface-container hover:text-on-surface" href="{{ route('student.section', 'notifications') }}"><span class="flex items-center gap-3"><span class="material-symbols-outlined text-xl">notifications</span>Notifications</span><span class="rounded-full bg-error-container px-1.5 py-0.5 text-[10px] font-bold text-error">2</span></a>
                <a class="flex items-center justify-between rounded-lg px-3 py-2.5 text-xs font-semibold text-on-surface-variant hover:bg-surface-container hover:text-on-surface" href="{{ route('student.section', 'fines') }}"><span class="flex items-center gap-3"><span class="material-symbols-outlined text-xl">payments</span>Fines & Penalties</span><span class="rounded-full bg-secondary-fixed px-1.5 py-0.5 text-[10px] font-bold text-on-secondary-container">PHP {{ number_format($outstanding, 2) }}</span></a>
                <a class="flex items-center gap-3 rounded-lg bg-primary-container px-3 py-2.5 text-xs font-semibold text-on-primary" href="{{ route('student.section', 'account') }}"><span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' 1;">account_circle</span>My Profile</a>
            </nav>
            <div class="space-y-1 border-t border-outline-variant bg-surface-container-lowest p-3">
                <a class="flex items-center gap-3 rounded-lg px-3 py-2 text-xs font-semibold text-on-surface-variant hover:bg-surface-container" href="{{ route('student.section', 'support') }}"><span class="material-symbols-outlined text-xl">help_outline</span>Help & Support</a>
                <a class="flex items-center gap-3 rounded-lg px-3 py-2 text-xs font-semibold text-error hover:bg-error-container" href="{{ route('login') }}"><span class="material-symbols-outlined text-xl">logout</span>Log Out</a>
            </div>
        </aside>

        <main class="flex-1 space-y-6 p-4 md:ml-64 md:p-8">
            <div class="flex flex-col justify-between gap-4 border-b border-outline-variant pb-2 md:flex-row md:items-end">
                <div>
                    <nav class="mb-1 flex items-center gap-2 text-xs text-on-surface-variant"><span>Student Portal</span><span class="material-symbols-outlined text-sm">chevron_right</span><span>Circulation & Accounts</span><span class="material-symbols-outlined text-sm">chevron_right</span><span class="font-semibold text-primary">My Patron Profile</span></nav>
                    <h1 class="font-headline text-3xl font-bold tracking-tight text-on-surface">Student Patron Profile & Digital Library Identity</h1>
                    <p class="mt-1 max-w-3xl text-sm text-on-surface-variant">Manage academic credentials, digital RFID/barcode turnstile pass, communication channels, borrowing privileges, and security settings.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <button class="inline-flex items-center gap-2 rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2 text-sm font-semibold hover:bg-surface-container" type="button"><span class="material-symbols-outlined text-lg text-primary">sync</span>Sync with Registrar</button>
                    <button class="inline-flex items-center gap-2 rounded-lg border border-primary-container px-3.5 py-2 text-sm font-semibold text-primary-container hover:bg-primary-container/10" type="button"><span class="material-symbols-outlined text-lg">edit</span>Edit Contact</button>
                    <button class="inline-flex items-center gap-2 rounded-lg bg-primary-container px-4 py-2 text-sm font-semibold text-on-primary shadow-sm hover:bg-primary" type="button"><span class="material-symbols-outlined text-lg">download</span>Download Digital ID</button>
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
                        <div><div class="flex items-center gap-1.5 text-xs text-on-surface-variant"><span class="material-symbols-outlined text-base text-primary">school</span>Enrollment</div><span class="mt-0.5 block text-sm font-bold">Enrolled</span><span class="text-[11px] text-outline">Registrar verified</span></div>
                        <div><div class="flex items-center gap-1.5 text-xs text-on-surface-variant"><span class="material-symbols-outlined text-base text-primary">contactless</span>Turnstile RFID</div><span class="mt-0.5 block text-sm font-bold text-primary-container">Granted</span><span class="font-mono text-[11px] text-outline">04-A9-C2-88</span></div>
                        <div><div class="flex items-center gap-1.5 text-xs text-on-surface-variant"><span class="material-symbols-outlined text-base text-primary">assignment_turned_in</span>Clearance</div><span class="mt-0.5 block text-sm font-bold">Clear to Borrow</span><span class="text-[11px] font-semibold text-primary">{{ $slotsLeft }} of 5 slots free</span></div>
                        <div><div class="flex items-center gap-1.5 text-xs text-on-surface-variant"><span class="material-symbols-outlined text-base text-outline">event</span>Account Expiry</div><span class="mt-0.5 block text-sm font-bold">May 31, 2025</span><span class="text-[11px] text-outline">Term renewal</span></div>
                    </div>
                </div>
            </section>

            <nav aria-label="Profile Sections" class="flex items-center gap-2 overflow-x-auto border-b border-outline-variant">
                <a class="flex whitespace-nowrap border-b-2 border-primary px-4 py-2.5 text-sm font-bold text-primary" href="#credentials"><span class="material-symbols-outlined mr-2 text-lg" style="font-variation-settings: 'FILL' 1;">badge</span>Overview & Credentials</a>
                <a class="flex whitespace-nowrap border-b-2 border-transparent px-4 py-2.5 text-sm font-semibold text-on-surface-variant hover:text-on-surface" href="#privileges"><span class="material-symbols-outlined mr-2 text-lg">tune</span>Borrowing Privileges</a>
                <a class="flex whitespace-nowrap border-b-2 border-transparent px-4 py-2.5 text-sm font-semibold text-on-surface-variant hover:text-on-surface" href="#notifications"><span class="material-symbols-outlined mr-2 text-lg">notifications_active</span>Notification Preferences</a>
                <a class="flex whitespace-nowrap border-b-2 border-transparent px-4 py-2.5 text-sm font-semibold text-on-surface-variant hover:text-on-surface" href="#security"><span class="material-symbols-outlined mr-2 text-lg">shield</span>Privacy & Security</a>
            </nav>

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
                <section id="credentials" class="space-y-6 xl:col-span-8">
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                        <article class="rounded-xl border border-outline-variant bg-surface-container-lowest p-5 shadow-sm">
                            <div class="mb-4 flex items-center justify-between border-b border-outline-variant pb-3"><h2 class="font-headline flex items-center gap-2 text-base font-bold"><span class="material-symbols-outlined text-primary">badge</span>Institutional Credentials</h2><span class="rounded bg-primary-fixed px-2 py-0.5 text-[10px] font-bold text-primary">VERIFIED</span></div>
                            <dl class="grid gap-3 text-sm">
                                <div class="flex items-center justify-between border-b border-outline-variant/40 pb-2"><dt class="text-on-surface-variant">Full Legal Name</dt><dd class="font-bold">Juan Dela Cruz</dd></div>
                                <div class="flex items-center justify-between border-b border-outline-variant/40 pb-2"><dt class="text-on-surface-variant">Student Number</dt><dd class="font-mono font-semibold">2024-10492-OL</dd></div>
                                <div class="flex items-center justify-between border-b border-outline-variant/40 pb-2"><dt class="text-on-surface-variant">Program</dt><dd class="font-semibold">BS Computer Science</dd></div>
                                <div class="flex items-center justify-between border-b border-outline-variant/40 pb-2"><dt class="text-on-surface-variant">Year & Section</dt><dd class="font-semibold">3rd Year - BSCS 3A</dd></div>
                                <div class="flex items-center justify-between"><dt class="text-on-surface-variant">Library Card Status</dt><dd class="font-bold text-primary-container">Active Patron</dd></div>
                            </dl>
                        </article>

                        <article class="rounded-xl border border-outline-variant bg-surface-container-lowest p-5 shadow-sm">
                            <div class="mb-4 flex items-center justify-between border-b border-outline-variant pb-3"><h2 class="font-headline flex items-center gap-2 text-base font-bold"><span class="material-symbols-outlined text-primary">alternate_email</span>Contact Channels</h2><button class="text-xs font-bold text-primary hover:underline" type="button">Edit</button></div>
                            <dl class="grid gap-3 text-sm">
                                <div class="rounded-lg border border-outline-variant bg-surface-container-low p-3"><dt class="text-xs font-bold text-on-surface-variant">Institutional Email</dt><dd class="mt-1 font-semibold">juan.delacruz@clcst.edu.ph</dd></div>
                                <div class="rounded-lg border border-outline-variant bg-surface-container-low p-3"><dt class="text-xs font-bold text-on-surface-variant">Mobile Number</dt><dd class="mt-1 font-semibold">+63 917 123 4567</dd></div>
                                <div class="rounded-lg border border-outline-variant bg-surface-container-low p-3"><dt class="text-xs font-bold text-on-surface-variant">Home Address</dt><dd class="mt-1 font-semibold">San Fernando, Pampanga</dd></div>
                            </dl>
                        </article>
                    </div>

                    <article id="privileges" class="rounded-xl border border-outline-variant bg-surface-container-lowest p-5 shadow-sm">
                        <div class="mb-4 flex items-center justify-between border-b border-outline-variant pb-3">
                            <div><h2 class="font-headline flex items-center gap-2 text-base font-bold"><span class="material-symbols-outlined text-primary">tune</span>Borrowing Privileges & Quota</h2><p class="text-xs text-on-surface-variant">Student circulation rules for undergraduate patrons</p></div>
                            <span class="rounded bg-surface-container px-2 py-0.5 text-[10px] font-bold text-primary">{{ $activeLoanCount }} / 5 Active</span>
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div class="rounded-lg border border-outline-variant bg-surface-container-low p-4"><span class="text-xs font-bold uppercase text-on-surface-variant">Book Quota</span><p class="mt-1 font-headline text-2xl font-bold text-primary">{{ $slotsLeft }} Slots Left</p><p class="mt-1 text-xs text-outline">Maximum 5 active loans</p></div>
                            <div class="rounded-lg border border-outline-variant bg-surface-container-low p-4"><span class="text-xs font-bold uppercase text-on-surface-variant">Loan Period</span><p class="mt-1 font-headline text-2xl font-bold">14 Days</p><p class="mt-1 text-xs text-outline">Regular circulation books</p></div>
                            <div class="rounded-lg border border-outline-variant bg-surface-container-low p-4"><span class="text-xs font-bold uppercase text-on-surface-variant">Renewal</span><p class="mt-1 font-headline text-2xl font-bold text-primary-container">1 Online</p><p class="mt-1 text-xs text-outline">If no active hold exists</p></div>
                        </div>
                    </article>

                    <article id="notifications" class="rounded-xl border border-outline-variant bg-surface-container-lowest p-5 shadow-sm">
                        <div class="mb-4 flex items-center justify-between border-b border-outline-variant pb-3"><h2 class="font-headline flex items-center gap-2 text-base font-bold"><span class="material-symbols-outlined text-primary">notifications_active</span>Notification Preferences</h2><span class="text-xs font-bold text-outline">SMS and Email Enabled</span></div>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            @foreach ([['Due date reminders', '3 days before due date'], ['Reservation pickup alerts', 'When a hold is ready'], ['Fine assessment notices', 'If an overdue charge is posted'], ['Account security alerts', 'On login or profile changes']] as $notice)
                                <div class="flex items-center justify-between rounded-lg border border-outline-variant bg-surface-container-low p-3"><div><p class="text-sm font-bold">{{ $notice[0] }}</p><p class="text-xs text-on-surface-variant">{{ $notice[1] }}</p></div><span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">toggle_on</span></div>
                            @endforeach
                        </div>
                    </article>
                </section>

                <aside class="space-y-6 xl:col-span-4">
                    <section class="overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm">
                        <div class="bg-primary-container p-4 text-on-primary">
                            <div class="mb-1 flex items-center justify-between"><span class="text-[10px] font-bold uppercase tracking-wider text-on-primary/80">Digital Library Identity</span><span class="rounded bg-secondary-fixed px-2 py-0.5 text-[10px] font-bold text-on-secondary-container">ACTIVE</span></div>
                            <h3 class="font-headline text-base font-bold text-white">CLCST Library Borrower Pass</h3>
                            <p class="mt-0.5 text-xs text-on-primary/90">RFID and barcode enabled</p>
                        </div>
                        <div class="space-y-4 p-5">
                            <div class="rounded-xl bg-gradient-to-br from-primary-container to-primary p-4 text-white shadow-md">
                                <div class="flex items-start justify-between"><div><p class="text-[10px] font-bold uppercase tracking-widest text-emerald-100">Central Luzon College</p><p class="text-xs font-bold">Learning Commons Digital ID</p></div><div class="flex h-9 w-9 items-center justify-center rounded-full bg-white/15 text-xs font-bold">CL</div></div>
                                <div class="mt-5 flex items-center gap-4"><div class="flex h-16 w-14 items-center justify-center rounded border border-white/40 bg-white/20 font-headline text-xl font-bold">JD</div><div><p class="font-headline text-lg font-bold">Juan Dela Cruz</p><p class="text-xs text-emerald-100">BS Computer Science - 3rd Year</p><p class="mt-0.5 font-mono text-xs text-emerald-100">2024-10492-OL</p></div></div>
                                <div class="mt-4 rounded bg-white p-2 text-center"><div class="barcode-stripe mx-auto h-8 w-full max-w-[220px]"></div><p class="mt-1 font-mono text-[10px] tracking-widest text-slate-700">*2024-10492-OL*</p></div>
                            </div>
                            <button class="flex w-full items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-xs font-bold text-on-primary hover:bg-primary-container" type="button"><span class="material-symbols-outlined text-lg">download</span>Download Digital ID PDF</button>
                        </div>
                    </section>

                    <section id="security" class="rounded-xl border border-outline-variant bg-surface-container-lowest p-5 shadow-sm">
                        <h3 class="font-headline mb-3 flex items-center gap-2 text-base font-bold"><span class="material-symbols-outlined text-primary">shield</span>Privacy & Security</h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex items-center justify-between border-b border-outline-variant/50 pb-2"><span class="text-on-surface-variant">Data Privacy Consent</span><span class="font-bold text-primary-container">Accepted</span></div>
                            <div class="flex items-center justify-between border-b border-outline-variant/50 pb-2"><span class="text-on-surface-variant">Last Profile Sync</span><span class="font-bold">Oct 08, 2026</span></div>
                            <div class="flex items-center justify-between border-b border-outline-variant/50 pb-2"><span class="text-on-surface-variant">Password Status</span><span class="font-bold text-primary-container">Protected</span></div>
                            <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-xs font-semibold text-emerald-900">Your library profile is verified and compliant with RA 10173 data privacy handling.</div>
                        </div>
                    </section>

                    <section class="rounded-xl border border-outline-variant bg-surface-container-lowest p-5 shadow-sm">
                        <h3 class="font-headline mb-3 flex items-center gap-2 text-base font-bold"><span class="material-symbols-outlined text-primary">assignment_turned_in</span>Account Clearance</h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex items-center justify-between border-b border-outline-variant/50 pb-2"><span class="text-on-surface-variant">Outstanding Fines</span><span class="font-bold {{ $outstanding > 0 ? 'text-error' : 'text-primary-container' }}">PHP {{ number_format($outstanding, 2) }}</span></div>
                            <div class="flex items-center justify-between border-b border-outline-variant/50 pb-2"><span class="text-on-surface-variant">Active Reservations</span><span class="font-bold">{{ $reservationCount }}</span></div>
                            <div class="flex items-center justify-between border-b border-outline-variant/50 pb-2"><span class="text-on-surface-variant">Current Loans</span><span class="font-bold">{{ $activeLoanCount }}</span></div>
                            <a class="flex w-full items-center justify-center gap-2 rounded-lg border border-outline-variant bg-surface-container px-4 py-2.5 text-xs font-bold text-primary hover:bg-surface-container-high" href="{{ route('student.section', 'fines') }}"><span class="material-symbols-outlined text-lg">payments</span>Open Fines & Penalties</a>
                        </div>
                    </section>
                </aside>
            </div>
        </main>
    </div>
</body>
</html>
