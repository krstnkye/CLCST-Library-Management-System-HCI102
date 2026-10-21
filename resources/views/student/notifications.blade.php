<!DOCTYPE html>
<html class="h-full bg-background" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} - CLCST Library Portal</title>
    @include('partials.favicon')
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
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
                        'on-secondary-container': '#6d5200',
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
        .fill-icon { font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        ::-webkit-scrollbar { height: 6px; width: 6px; }
        ::-webkit-scrollbar-track { background: #faf8ff; }
        ::-webkit-scrollbar-thumb { background: #c0c9bd; border-radius: 9999px; }
    </style>
    @include('partials.ui-motion')
</head>
<body class="flex h-full flex-col overflow-x-hidden bg-background font-body text-on-surface antialiased">
    @php
        $activeLoanCount = count($activeLoans);
        $reservationCount = count($reservations);
        $notifications = [
            ['type' => 'Overdue & Fine Notice', 'title' => 'Daily Fine Accruing: Discrete Mathematics and Its Applications', 'body' => 'The checkout period for barcode DM-082 expired on Oct 20, 2024. The book is overdue by 4 days. Current unsettled balance: PHP 20.00.', 'time' => 'Today, 8:30 AM', 'source' => 'Automated Borrowing', 'icon' => 'warning', 'tone' => 'error', 'primary' => 'Pay Fine', 'secondary' => 'View Fine Record', 'href' => route('student.section', 'fines')],
            ['type' => 'Ready for Pickup for Pickup', 'title' => 'Book Ready for Pickup: Database System Concepts', 'body' => 'Your reserved title has been pulled from Library Shelves Level 2 and placed at Pickup Shelf B-04. Please claim this item before the pickup window expires.', 'time' => 'Yesterday, 3:15 PM', 'source' => 'Reservations Desk', 'icon' => 'inventory_2', 'tone' => 'success', 'primary' => 'View Pickup Pass', 'secondary' => 'Open Reservations', 'href' => route('student.section', 'reservations')],
            ['type' => 'Due Date Reminder', 'title' => 'Return Reminder: Clean Code', 'body' => 'Barcode CLEAN-001 is due in 2 days. Return the book at the library desk or renew online if no reservation exists.', 'time' => 'Yesterday, 9:00 AM', 'source' => 'Loan Monitoring', 'icon' => 'notification_important', 'tone' => 'warning', 'primary' => 'Renew Loan', 'secondary' => 'Borrow/Return', 'href' => route('student.section', 'borrow-return')],
            ['type' => 'Advisory / Schedule', 'title' => 'Midterm Examination Extended Library Hours', 'body' => 'The CLCST Main Library reference area and discussion carrels will extend operational hours until 9:00 PM from Oct 28 to Nov 4.', 'time' => 'Oct 22, 2024', 'source' => 'Office of University Librarian', 'icon' => 'campaign', 'tone' => 'info', 'primary' => 'View Schedule', 'secondary' => 'Catalog', 'href' => route('books.catalog')],
            ['type' => 'SMS & Email Synced', 'title' => 'SMS Due Alert Sent to +63 917 123 4567', 'body' => 'Automated SMS delivery confirmed via telco gateway for loan account 2024-10492-OL.', 'time' => 'Oct 21, 2024', 'source' => 'Message Service', 'icon' => 'sms', 'tone' => 'muted', 'primary' => 'Preferences', 'secondary' => 'Profile', 'href' => route('student.section', 'account')],
        ];
    @endphp

    <header class="sticky top-0 z-40 flex h-16 w-full items-center justify-between border-b border-outline-variant bg-surface-container-lowest px-4 shadow-sm md:px-6">
        <div class="flex items-center gap-5">
            <div class="flex items-center gap-3">
                @include('partials.celtech-logo', ['class' => 'flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-extrabold text-white shadow-sm object-contain bg-white p-0.5'])
                <div>
                    <span class="block font-headline text-base font-bold leading-tight tracking-wide text-primary">CLCST SCHOOL LIBRARY</span>
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-outline">Library Notifications</span>
                </div>
            </div>
            <div class="hidden items-center gap-2 border-l border-outline-variant pl-4 lg:flex">
                <span class="flex items-center gap-1 rounded-full bg-surface-container px-2.5 py-0.5 text-[10px] font-bold text-primary"><span class="h-1.5 w-1.5 rounded-full bg-primary-container"></span>AY 2024-2025 - 1st Sem</span>
                <span class="rounded px-2 py-0.5 text-[10px] font-bold text-outline">HCI102 Student Portal</span>
            </div>
        </div>

        <form class="mx-6 hidden max-w-md flex-1 md:block" method="GET" action="{{ route('books.catalog') }}">
            <div class="relative flex items-center">
                <span class="material-symbols-outlined absolute left-3 text-xl text-outline">search</span>
                <input class="w-full rounded-lg border border-outline-variant bg-surface-container-low py-1.5 pl-10 pr-10 text-sm placeholder:text-outline focus:border-primary focus:ring-2 focus:ring-primary" name="q" placeholder="Search books, call numbers, thesis archives, or notices...">
                <button class="absolute right-2.5 text-outline hover:text-primary" title="Scan Barcode / ISBN" type="submit"><span class="material-symbols-outlined text-lg">barcode_scanner</span></button>
            </div>
        </form>

        <div class="flex items-center gap-3">
            <a class="relative rounded-lg p-2 text-on-surface-variant hover:bg-surface-container-low" href="{{ route('student.section', 'notifications') }}" aria-label="Notifications Center">
                <span class="material-symbols-outlined fill-icon text-primary">notifications</span>
                <span class="absolute right-1.5 top-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-error text-[10px] font-bold text-white">3</span>
            </a>
            <a class="rounded-lg p-2 text-on-surface-variant hover:bg-surface-container-low" href="{{ route('student.section', 'support') }}" aria-label="Help and Knowledge Base"><span class="material-symbols-outlined">help</span></a>
            <div class="mx-1 h-6 w-px bg-outline-variant"></div>
            <a class="flex items-center gap-3" href="{{ route('student.section', 'account') }}">
                <div class="hidden text-right sm:block">
                    <div class="flex items-center justify-end gap-1.5"><span class="text-xs font-semibold">Juan Dela Cruz</span><span class="rounded bg-surface-container px-1.5 py-0.5 text-[9px] font-bold uppercase text-primary">Verified</span></div>
                    <span class="block text-[11px] text-outline">2024-10492-OL - 3rd Year BSCS</span>
                </div>
                <div class="relative"><div class="flex h-9 w-9 items-center justify-center rounded-full bg-primary-container text-sm font-semibold text-on-primary shadow-sm ring-2 ring-surface-container-lowest">JD</div><span class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full bg-primary ring-2 ring-surface-container-lowest"></span></div>
            </a>
        </div>
    </header>

    <div class="relative flex flex-1 overflow-hidden">
        @include('partials.student-sidebar', ['activePage' => 'notifications'])

        <main class="md:ml-64 flex-1 overflow-y-auto bg-background px-4 py-6 lg:px-8">
            <div class="mx-auto max-w-7xl space-y-6">
                <section>
                    <nav class="mb-1.5 flex items-center gap-2 text-[10px] font-bold text-outline">
                        <span>Student Portal</span><span class="material-symbols-outlined text-sm">chevron_right</span><span>Borrowing & Account</span><span class="material-symbols-outlined text-sm">chevron_right</span><span class="text-on-surface">Notifications & Alerts</span>
                    </nav>
                    <div class="flex flex-col justify-between gap-4 pt-1 md:flex-row md:items-center">
                        <div>
                            <h1 class="font-headline text-2xl font-bold tracking-tight text-on-surface">Library Notifications & Dispatch Center</h1>
                            <p class="mt-0.5 text-sm text-outline">Stay updated on book return countdowns, reserve hold readiness, fine assessments, and campus library advisories.</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-2.5">
                            <button class="flex items-center gap-2 rounded-lg border border-outline-variant bg-surface-container-lowest px-3.5 py-2 text-xs font-semibold shadow-sm hover:bg-surface-container" type="button"><span class="material-symbols-outlined text-lg">mark_email_read</span>Mark All as Read</button>
                            <a class="flex items-center gap-2 rounded-lg border border-outline-variant bg-surface-container-lowest px-3.5 py-2 text-xs font-semibold shadow-sm hover:bg-surface-container" href="{{ route('student.section', 'account') }}"><span class="material-symbols-outlined text-lg">tune</span>Settings</a>
                            <button class="rounded-lg border border-outline-variant bg-surface-container-lowest p-2 text-outline shadow-sm hover:bg-surface-container hover:text-on-surface" title="Export Notification Log" type="button"><span class="material-symbols-outlined text-lg">download</span></button>
                        </div>
                    </div>
                </section>

                <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="relative overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest p-4 shadow-sm"><div class="absolute left-0 top-0 h-full w-1.5 bg-primary-container"></div><div class="flex items-start justify-between"><div><span class="block text-[10px] font-bold uppercase tracking-wider text-outline">Unread Alerts</span><span class="mt-1 block font-headline text-2xl font-bold">3 Pending</span><p class="mt-1 flex items-center gap-1 text-xs font-semibold text-error"><span class="material-symbols-outlined text-sm">error</span>Requires attention</p></div><div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary"><span class="material-symbols-outlined text-2xl">mark_chat_unread</span></div></div></div>
                    <div class="relative overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest p-4 shadow-sm"><div class="absolute left-0 top-0 h-full w-1.5 bg-secondary"></div><div class="flex items-start justify-between"><div><span class="block text-[10px] font-bold uppercase tracking-wider text-outline">Due Soon</span><span class="mt-1 block font-headline text-2xl font-bold">1 Book Due</span><p class="mt-1 flex items-center gap-1 text-xs font-semibold text-secondary"><span class="material-symbols-outlined text-sm">schedule</span>2 days left - Clean Code</p></div><div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-secondary"><span class="material-symbols-outlined text-2xl">event_upcoming</span></div></div></div>
                    <div class="relative overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest p-4 shadow-sm"><div class="absolute left-0 top-0 h-full w-1.5 bg-emerald-600"></div><div class="flex items-start justify-between"><div><span class="block text-[10px] font-bold uppercase tracking-wider text-outline">Ready for Pickup</span><span class="mt-1 block font-headline text-2xl font-bold">1 for Pickup</span><p class="mt-1 flex items-center gap-1 text-xs font-semibold text-emerald-700"><span class="material-symbols-outlined text-sm">store</span>Shelf B-04 - 18h left</p></div><div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700"><span class="material-symbols-outlined text-2xl">check_circle</span></div></div></div>
                    <div class="relative overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest p-4 shadow-sm"><div class="absolute left-0 top-0 h-full w-1.5 bg-error"></div><div class="flex items-start justify-between"><div><span class="block text-[10px] font-bold uppercase tracking-wider text-outline">Account Amount Due</span><span class="mt-1 block font-headline text-2xl font-bold text-error">PHP 20.00</span><p class="mt-1 flex items-center gap-1 text-xs font-semibold text-error"><span class="material-symbols-outlined text-sm">gavel</span>Overdue fine</p></div><div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-rose-50 text-error"><span class="material-symbols-outlined text-2xl">receipt_long</span></div></div></div>
                </section>

                <section class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                    <div class="space-y-4 lg:col-span-8">
                        <div class="flex flex-col justify-between gap-3 rounded-xl border border-outline-variant bg-surface-container-lowest p-3.5 shadow-sm sm:flex-row sm:items-center">
                            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0">
                                <button class="whitespace-nowrap rounded-lg bg-primary-container px-3 py-1.5 text-xs font-semibold text-on-primary shadow-sm" type="button">All Notifications ({{ count($notifications) }})</button>
                                <button class="whitespace-nowrap rounded-lg border border-outline-variant bg-surface-container-low px-3 py-1.5 text-xs font-semibold text-on-surface-variant hover:bg-surface-container" type="button">Unread (3)</button>
                                <button class="whitespace-nowrap rounded-lg border border-outline-variant bg-surface-container-low px-3 py-1.5 text-xs font-semibold text-on-surface-variant hover:bg-surface-container" type="button">Due Dates (2)</button>
                                <button class="whitespace-nowrap rounded-lg border border-outline-variant bg-surface-container-low px-3 py-1.5 text-xs font-semibold text-on-surface-variant hover:bg-surface-container" type="button">Reservations (1)</button>
                                <button class="whitespace-nowrap rounded-lg border border-outline-variant bg-surface-container-low px-3 py-1.5 text-xs font-semibold text-on-surface-variant hover:bg-surface-container" type="button">Fines (1)</button>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="relative w-full sm:w-56"><span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-base text-outline">filter_list</span><input class="w-full rounded-lg border border-outline-variant bg-surface-container-low py-1.5 pl-8 pr-3 text-sm focus:ring-1 focus:ring-primary" placeholder="Filter alerts..."></div>
                                <button class="shrink-0 rounded-lg border border-outline-variant p-1.5 text-outline hover:bg-surface-container hover:text-on-surface" title="Sort Priority" type="button"><span class="material-symbols-outlined text-lg">swap_vert</span></button>
                            </div>
                        </div>

                        <div class="space-y-3.5">
                            @foreach ($notifications as $notification)
                                @php
                                    $tone = match ($notification['tone']) {
                                        'error' => ['border' => 'border-l-error', 'icon' => 'bg-rose-100 text-error', 'pill' => 'bg-rose-100 text-error', 'dot' => 'bg-error', 'button' => 'bg-error text-white hover:bg-red-800'],
                                        'success' => ['border' => 'border-l-primary-container', 'icon' => 'bg-emerald-100 text-primary-container', 'pill' => 'bg-emerald-100 text-primary', 'dot' => 'bg-primary-container', 'button' => 'bg-primary-container text-on-primary hover:bg-primary'],
                                        'warning' => ['border' => 'border-l-secondary', 'icon' => 'bg-amber-100 text-secondary', 'pill' => 'bg-amber-100 text-secondary', 'dot' => 'bg-secondary', 'button' => 'bg-secondary-container text-on-secondary-container hover:bg-yellow-400'],
                                        'info' => ['border' => 'border-l-transparent', 'icon' => 'bg-surface-container text-tertiary', 'pill' => 'bg-surface-container text-tertiary', 'dot' => 'bg-tertiary', 'button' => 'bg-surface-container-low text-on-surface hover:bg-surface-container'],
                                        default => ['border' => 'border-l-transparent', 'icon' => 'bg-surface-container text-outline', 'pill' => 'bg-slate-100 text-outline', 'dot' => 'bg-outline', 'button' => 'bg-surface-container-low text-on-surface hover:bg-surface-container'],
                                    };
                                @endphp
                                <article class="rounded-xl border border-l-4 {{ $tone['border'] }} border-outline-variant bg-surface-container-lowest p-5 shadow-sm transition-all hover:shadow-md">
                                    <div class="flex items-start gap-3.5">
                                        <div class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-lg {{ $tone['icon'] }}"><span class="material-symbols-outlined text-2xl">{{ $notification['icon'] }}</span></div>
                                        <div class="min-w-0 flex-1">
                                            <div class="mb-1 flex flex-wrap items-center gap-2">
                                                <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $tone['pill'] }}">{{ $notification['type'] }}</span>
                                                @if ($loop->iteration <= 3)
                                                    <span class="flex items-center gap-1 text-[10px] font-bold text-outline"><span class="h-1.5 w-1.5 rounded-full {{ $tone['dot'] }}"></span>Unread</span>
                                                @endif
                                                <span class="text-xs text-outline">- {{ $notification['time'] }} - {{ $notification['source'] }}</span>
                                            </div>
                                            <h2 class="font-headline text-base font-bold text-on-surface">{{ $notification['title'] }}</h2>
                                            <p class="mt-1 text-sm text-on-surface-variant">{{ $notification['body'] }}</p>
                                            <div class="mt-4 flex flex-wrap items-center gap-3 border-t border-outline-variant/60 pt-3">
                                                <a class="flex items-center gap-1.5 rounded-lg px-4 py-2 text-xs font-semibold shadow-sm {{ $tone['button'] }}" href="{{ $notification['href'] }}"><span class="material-symbols-outlined text-base">arrow_forward</span>{{ $notification['primary'] }}</a>
                                                <a class="rounded-lg border border-outline-variant bg-surface-container-low px-3 py-2 text-xs font-semibold text-on-surface hover:bg-surface-container" href="{{ $notification['href'] }}">{{ $notification['secondary'] }}</a>
                                                <button class="ml-auto px-3 py-2 text-xs font-semibold text-outline hover:text-on-surface" type="button">Dismiss</button>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>

                    <aside class="space-y-5 lg:col-span-4">
                        <section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-primary to-primary-container p-5 text-on-primary shadow-lg">
                            <div class="mb-3 flex items-center justify-between"><span class="rounded bg-white/20 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">Ready for Pickup</span><span class="flex items-center gap-1 font-mono text-[11px] text-secondary-container"><span class="material-symbols-outlined text-sm">timer</span>18h 42m Left</span></div>
                            <h2 class="font-headline text-base font-bold leading-snug text-white">Database System Concepts (7th Ed)</h2>
                            <p class="mt-1 text-xs text-white/80">Located at Pickup Shelf B-04. Present your library ID pass or scan QR at the counter.</p>
                            <div class="mt-4 rounded-xl bg-white p-3 text-center shadow-inner">
                                <div class="mb-1 font-mono text-[10px] font-bold uppercase tracking-widest text-slate-500">Pass Code: HOLD-7729-CS</div>
                                <div class="flex h-12 items-center justify-center rounded bg-slate-50 px-2"><div class="flex h-9 items-stretch gap-[2px]">@foreach ([1,2,1,1,3,1,2,1,2,3,1,2,1,2,3,1,2,1,3,1,2] as $width)<div class="bg-black" style="width: {{ $width * 2 }}px"></div>@endforeach</div></div>
                                <div class="mt-1 font-mono text-[11px] font-bold text-slate-800">2024-10492-HOLD</div>
                            </div>
                            <div class="mt-4 flex gap-2"><a class="flex-1 rounded-lg bg-secondary-container px-3 py-2 text-center text-xs font-bold text-on-secondary-container shadow-sm hover:bg-yellow-400" href="{{ route('student.section', 'reservations') }}">Full Pass</a><a class="rounded-lg bg-white/10 px-3 py-2 text-xs font-semibold text-white hover:bg-white/20" href="{{ route('student.section', 'reservations') }}">Extend Window</a></div>
                        </section>

                        <section class="rounded-xl border border-outline-variant bg-surface-container-lowest p-5 shadow-sm">
                            <div class="mb-2 flex items-center justify-between"><span class="text-[10px] font-bold uppercase tracking-wider text-outline">Unpaid Fine</span><span class="rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-bold text-error">Overdue</span></div>
                            <div class="mt-1 flex items-baseline justify-between"><div><div class="font-headline text-2xl font-bold">PHP 20.00</div><div class="text-xs text-outline">Discrete Math (DM-082)</div></div><a class="rounded-lg bg-primary-container px-3.5 py-1.5 text-xs font-semibold text-on-primary hover:bg-primary" href="{{ route('student.section', 'fines') }}">Quick Pay</a></div>
                        </section>

                        <section class="space-y-4 rounded-xl border border-outline-variant bg-surface-container-lowest p-5 shadow-sm">
                            <div class="flex items-center justify-between border-b border-outline-variant pb-3"><div><h3 class="font-headline text-base font-bold">Alert Channels</h3><p class="text-xs text-outline">Where you receive circulation updates</p></div><span class="material-symbols-outlined text-xl text-primary">phonelink_ring</span></div>
                            @foreach ([['sms', 'SMS Notifications', '+63 917 123 4567 (Verified)'], ['mail', 'School Email', 'juan.delacruz@clcst.edu.ph'], ['desktop_windows', 'In-Portal Banners', 'Real-time alerts on dashboard']] as $channel)
                                <div class="flex items-center justify-between py-1">
                                    <div class="pr-2"><div class="flex items-center gap-1.5 text-xs font-semibold"><span class="material-symbols-outlined text-base text-outline">{{ $channel[0] }}</span>{{ $channel[1] }}</div><div class="mt-0.5 max-w-[180px] truncate text-[11px] text-outline">{{ $channel[2] }}</div></div>
                                    <label class="relative inline-flex shrink-0 cursor-pointer items-center"><input class="peer sr-only" type="checkbox" checked><div class="h-5 w-9 rounded-full bg-surface-container after:absolute after:left-[2px] after:top-[2px] after:h-4 after:w-4 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-primary-container peer-checked:after:translate-x-full peer-checked:after:border-white"></div></label>
                                </div>
                            @endforeach
                            <div class="space-y-1 border-t border-outline-variant pt-3 text-[11px] text-outline"><div class="flex justify-between"><span>Due Date Alerts:</span><span class="font-semibold text-on-surface">Immediate</span></div><div class="flex justify-between"><span>General Advisories:</span><span class="font-semibold text-on-surface">Daily Digest (6:00 PM)</span></div></div>
                        </section>

                        <section class="flex items-start gap-3 rounded-xl border border-outline-variant bg-surface-container-low p-4 text-[11px] leading-relaxed text-outline">
                            <span class="material-symbols-outlined mt-0.5 shrink-0 text-lg text-primary">verified_user</span>
                            <p>Official communication from Central Luzon College of Science and Technology Library System. In compliance with the Philippine Data Privacy Act of 2012 (RA 10173).</p>
                        </section>
                    </aside>
                </section>
            </div>
        </main>
    </div>
</body>
</html>
