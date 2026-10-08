<!DOCTYPE html>
<html class="h-full bg-background text-on-surface" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} - CLCST Library Portal</title>
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
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
                        'secondary-fixed': '#ffdf9a',
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
        .material-symbols-outlined { display: inline-block; font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; line-height: 1; vertical-align: middle; }
        .fill-icon { font-variation-settings: 'FILL' 1, 'wght' 500, 'GRAD' 0, 'opsz' 24; }
        details > summary { list-style: none; }
        details > summary::-webkit-details-marker { display: none; }
        ::-webkit-scrollbar { height: 6px; width: 6px; }
        ::-webkit-scrollbar-track { background: #faf8ff; }
        ::-webkit-scrollbar-thumb { background: #c0c9bd; border-radius: 9999px; }
    </style>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="flex h-full flex-col overflow-x-hidden bg-background font-body text-on-surface antialiased">
    @php
        $activeLoanCount = count($activeLoans);
        $reservationCount = count($reservations);
        $outstanding = collect($fineRows)->reject(fn ($fine) => isset($paidFines[$fine['id']]))->sum('amount');
        $ticketReference = 'CL-2024-' . str_pad((string) (842 + $activeLoanCount + $reservationCount), 4, '0', STR_PAD_LEFT);
        $supportTopics = [
            ['label' => 'RFID Turnstile Issues', 'icon' => 'contactless', 'href' => '#digital-id'],
            ['label' => 'Book Return Box', 'icon' => 'inventory_2', 'href' => '#borrowing'],
            ['label' => 'Fine Appeals', 'icon' => 'gavel', 'href' => '#fines'],
            ['label' => 'OPAC Search', 'icon' => 'menu_book', 'href' => route('books.catalog')],
            ['label' => 'Reservations', 'icon' => 'bookmark_added', 'href' => route('student.section', 'reservations')],
            ['label' => 'Borrowing History', 'icon' => 'history_edu', 'href' => route('student.section', 'history')],
        ];
    @endphp

    <header class="sticky top-0 z-40 flex h-16 w-full items-center justify-between border-b border-outline-variant bg-surface-container-lowest px-4 shadow-sm md:px-6">
        <div class="flex min-w-0 items-center gap-5">
            <a class="flex shrink-0 items-center gap-3" href="{{ route('student.dashboard') }}">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary text-sm font-extrabold text-white shadow-sm">CL</div>
                <div>
                    <span class="block font-headline text-base font-bold leading-tight tracking-wide text-primary">CLCST SCHOOL LIBRARY</span>
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-outline">Student Help & Support Desk</span>
                </div>
            </a>
            <form class="hidden w-80 items-center lg:flex" method="GET" action="{{ route('books.catalog') }}">
                <div class="relative w-full">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-lg text-outline">search</span>
                    <input class="h-9 w-full rounded-lg border border-outline-variant bg-surface-container-low pl-9 pr-3 text-sm placeholder:text-outline focus:border-primary focus:ring-1 focus:ring-primary" name="q" placeholder="Search catalog, ISBN, call numbers...">
                </div>
            </form>
        </div>

        <div class="flex items-center gap-3">
            <a class="relative rounded-lg p-2 text-on-surface-variant hover:bg-surface-container-low" href="{{ route('student.section', 'notifications') }}" aria-label="Notifications">
                <span class="material-symbols-outlined">notifications</span>
                <span class="absolute right-1.5 top-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-error text-[10px] font-bold text-white">3</span>
            </a>
            <a class="rounded-lg bg-surface-container p-2 text-primary" href="{{ route('student.section', 'support') }}" aria-label="Help"><span class="material-symbols-outlined fill-icon">help</span></a>
            <div class="hidden h-7 w-px bg-outline-variant sm:block"></div>
            <a class="flex items-center gap-3" href="{{ route('student.section', 'account') }}">
                <div class="hidden text-right sm:block">
                    <div class="text-xs font-bold">Juan Dela Cruz</div>
                    <div class="text-[11px] text-outline">BSCS 3A - ID: 2024-10492-OL</div>
                </div>
                <div class="relative flex h-9 w-9 items-center justify-center rounded-full bg-primary-container text-sm font-bold text-on-primary shadow-sm">JD<span class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full bg-primary-fixed ring-2 ring-white"></span></div>
            </a>
        </div>
    </header>

    <div class="relative flex flex-1 overflow-hidden">
        <aside class="z-30 hidden w-64 shrink-0 select-none flex-col border-r border-outline-variant bg-surface-container-lowest md:flex">
            <div class="border-b border-outline-variant p-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary text-white"><span class="material-symbols-outlined">badge</span></div>
                    <div>
                        <div class="text-sm font-extrabold text-primary">CLCST e-Pass</div>
                        <div class="text-[11px] text-on-surface-variant">ID: 2024-10492-OL</div>
                    </div>
                </div>
                <a class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg bg-primary px-3 py-2.5 text-sm font-bold text-on-primary shadow-sm hover:bg-primary-container" href="{{ route('student.section', 'scan') }}"><span class="material-symbols-outlined text-lg">qr_code_scanner</span>Scan Barcode</a>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                <div class="px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-outline">Student Navigation</div>
                <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-xs font-semibold text-on-surface-variant hover:bg-surface-container hover:text-on-surface" href="{{ route('student.dashboard') }}"><span class="material-symbols-outlined text-xl">dashboard</span>Dashboard</a>
                <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-xs font-semibold text-on-surface-variant hover:bg-surface-container hover:text-on-surface" href="{{ route('books.catalog') }}"><span class="material-symbols-outlined text-xl">menu_book</span>Books (Catalog)</a>
                <a class="flex items-center justify-between rounded-lg px-3 py-2.5 text-xs font-semibold text-on-surface-variant hover:bg-surface-container hover:text-on-surface" href="{{ route('student.section', 'borrow-return') }}"><span class="flex items-center gap-3"><span class="material-symbols-outlined text-xl">swap_horiz</span>Borrow/Return</span><span class="rounded-full bg-surface-container px-2 py-0.5 text-[10px]">{{ $activeLoanCount }}</span></a>
                <a class="flex items-center justify-between rounded-lg px-3 py-2.5 text-xs font-semibold text-on-surface-variant hover:bg-surface-container hover:text-on-surface" href="{{ route('student.section', 'reservations') }}"><span class="flex items-center gap-3"><span class="material-symbols-outlined text-xl">book_online</span>Reservations</span><span class="rounded-full bg-surface-container px-2 py-0.5 text-[10px]">{{ $reservationCount }}</span></a>
                <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-xs font-semibold text-on-surface-variant hover:bg-surface-container hover:text-on-surface" href="{{ route('student.section', 'history') }}"><span class="material-symbols-outlined text-xl">history_edu</span>Borrowing History</a>
                <a class="flex items-center justify-between rounded-lg px-3 py-2.5 text-xs font-semibold text-on-surface-variant hover:bg-surface-container hover:text-on-surface" href="{{ route('student.section', 'fines') }}"><span class="flex items-center gap-3"><span class="material-symbols-outlined text-xl">payments</span>Fines & Penalties</span><span class="rounded-full bg-rose-100 px-2 py-0.5 text-[10px] text-error">PHP {{ number_format($outstanding, 0) }}</span></a>
                <a class="flex items-center justify-between rounded-lg px-3 py-2.5 text-xs font-semibold text-on-surface-variant hover:bg-surface-container hover:text-on-surface" href="{{ route('student.section', 'notifications') }}"><span class="flex items-center gap-3"><span class="material-symbols-outlined text-xl">notifications</span>Notifications</span><span class="rounded-full bg-error-container px-2 py-0.5 text-[10px] text-error">3</span></a>
                <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-xs font-semibold text-on-surface-variant hover:bg-surface-container hover:text-on-surface" href="{{ route('student.section', 'account') }}"><span class="material-symbols-outlined text-xl">account_circle</span>My Profile</a>
            </nav>

            <div class="space-y-1 border-t border-outline-variant p-3">
                <a class="flex items-center gap-3 rounded-lg bg-primary-container px-3 py-2.5 text-xs font-bold text-on-primary shadow-sm" href="{{ route('student.section', 'support') }}"><span class="material-symbols-outlined fill-icon text-lg">help_outline</span>Help & Support</a>
                <a class="flex items-center gap-3 rounded-lg px-3 py-2 text-xs font-semibold text-outline hover:bg-error-container/30 hover:text-error" href="{{ route('login') }}"><span class="material-symbols-outlined text-lg">logout</span>Log Out</a>
            </div>
        </aside>

        <main class="flex-1 overflow-y-auto bg-background px-4 py-6 lg:px-8">
            <div class="mx-auto max-w-7xl space-y-6">
                <section class="flex flex-col justify-between gap-3 rounded-xl border border-outline-variant bg-surface-container-lowest px-4 py-3 shadow-sm lg:flex-row lg:items-center">
                    <div class="flex flex-wrap items-center gap-2 text-xs font-semibold text-outline">
                        <a class="hover:text-primary" href="{{ route('student.dashboard') }}">CLCST Portal</a>
                        <span>/</span>
                        <span>Library Hub</span>
                        <span>/</span>
                        <span class="text-on-surface">Help & Support</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 text-xs font-semibold">
                        <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-primary"><span class="h-2 w-2 rounded-full bg-primary"></span>OPAC Gateway Operational</span>
                        <span class="inline-flex items-center gap-2 rounded-full bg-surface-container px-3 py-1 text-on-surface-variant"><span class="material-symbols-outlined text-sm text-secondary">schedule</span>Stacks: 7:30 AM - 6:30 PM</span>
                        <span class="inline-flex items-center gap-2 rounded-full bg-surface-container px-3 py-1 text-on-surface-variant"><span class="material-symbols-outlined text-sm text-primary">support_agent</span>Ms. Santos on duty</span>
                    </div>
                </section>

                <section class="relative overflow-hidden rounded-xl border border-primary bg-gradient-to-br from-primary via-primary-container to-[#08351a] p-6 text-white shadow-sm lg:p-8">
                    <div class="relative z-10 max-w-3xl space-y-4">
                        <div class="inline-flex items-center gap-2 rounded-full bg-secondary-container px-3 py-1 text-[10px] font-extrabold uppercase tracking-wider text-on-secondary-container"><span class="material-symbols-outlined text-sm">school</span>Academic Patron Knowledge Hub</div>
                        <div>
                            <h1 class="font-headline text-3xl font-extrabold tracking-tight lg:text-4xl">How can the CLCST Library help you today?</h1>
                            <p class="mt-2 max-w-2xl text-sm leading-6 text-primary-fixed">Search patron documentation, turnstile ID guides, borrowing regulations, account notifications, and ticket support.</p>
                        </div>
                        <form class="relative flex items-center rounded-xl border-2 border-primary-fixed/40 bg-white shadow-lg" method="GET" action="{{ route('student.section', 'support') }}">
                            <span class="material-symbols-outlined absolute left-4 text-primary">search</span>
                            <input class="h-14 w-full rounded-xl border-0 bg-transparent pl-12 pr-32 text-sm text-on-surface placeholder:text-outline focus:ring-0" name="q" value="{{ request('q') }}" placeholder="Ask a question, like renew loan, lost book fine, or turnstile red light...">
                            <button class="absolute right-2 rounded-lg bg-primary px-4 py-2 text-xs font-bold text-on-primary hover:bg-primary-container" type="submit">Search Hub</button>
                        </form>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-primary-fixed">Popular Topics:</span>
                            @foreach ($supportTopics as $topic)
                                <a class="inline-flex items-center gap-1 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold text-white backdrop-blur-sm hover:bg-white/20" href="{{ $topic['href'] }}"><span class="material-symbols-outlined text-sm">{{ $topic['icon'] }}</span>{{ $topic['label'] }}</a>
                            @endforeach
                        </div>
                    </div>
                </section>

                <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <article class="rounded-xl border border-outline-variant bg-surface-container-lowest p-5 shadow-sm">
                        <div class="mb-4 flex items-center justify-between"><div class="flex h-10 w-10 items-center justify-center rounded-lg bg-surface-container text-primary"><span class="material-symbols-outlined">desk</span></div><span class="rounded-full bg-primary-fixed px-2 py-0.5 text-[10px] font-bold uppercase text-primary">Active</span></div>
                        <h2 class="font-headline text-base font-bold">Circulation Helpdesk</h2>
                        <p class="mt-1 text-sm text-on-surface-variant">Live Desk Counter 01 and 02, Stacks Level 1.</p>
                        <a class="mt-4 inline-flex items-center gap-1 text-xs font-bold text-primary hover:underline" href="#directory">Directions<span class="material-symbols-outlined text-sm">chevron_right</span></a>
                    </article>
                    <article class="rounded-xl border border-outline-variant bg-surface-container-lowest p-5 shadow-sm">
                        <div class="mb-4 flex items-center justify-between"><div class="flex h-10 w-10 items-center justify-center rounded-lg bg-secondary-fixed text-secondary"><span class="material-symbols-outlined">confirmation_number</span></div><span class="rounded-full bg-secondary-container px-2 py-0.5 text-[10px] font-bold uppercase text-on-secondary-container">2-4h SLA</span></div>
                        <h2 class="font-headline text-base font-bold">Submit Support Ticket</h2>
                        <p class="mt-1 text-sm text-on-surface-variant">For lost books, unreadable barcodes, and fine disputes.</p>
                        <a class="mt-4 flex items-center justify-center gap-1.5 rounded-lg bg-primary px-3 py-2 text-xs font-bold text-on-primary hover:bg-primary-container" href="#ticket-form"><span class="material-symbols-outlined text-lg">add_circle</span>Create Ticket</a>
                    </article>
                    <article class="rounded-xl border border-outline-variant bg-surface-container-lowest p-5 shadow-sm">
                        <div class="mb-4 flex items-center justify-between"><div class="flex h-10 w-10 items-center justify-center rounded-lg bg-tertiary-fixed text-tertiary"><span class="material-symbols-outlined">auto_stories</span></div><span class="rounded-full bg-surface-container px-2 py-0.5 text-[10px] font-bold uppercase text-tertiary">Guides</span></div>
                        <h2 class="font-headline text-base font-bold">Digital Library FAQ</h2>
                        <p class="mt-1 text-sm text-on-surface-variant">Step-by-step guides for OPAC, reservations, and e-pass use.</p>
                        <a class="mt-4 inline-flex items-center gap-1 text-xs font-bold text-primary hover:underline" href="#faq-section">Browse Manuals<span class="material-symbols-outlined text-sm">chevron_right</span></a>
                    </article>
                    <article class="rounded-xl border border-outline-variant bg-surface-container-lowest p-5 shadow-sm">
                        <div class="mb-4 flex items-center justify-between"><div class="flex h-10 w-10 items-center justify-center rounded-lg bg-surface-container-high text-primary"><span class="material-symbols-outlined">support_agent</span></div><span class="rounded-full bg-primary-fixed px-2 py-0.5 text-[10px] font-bold uppercase text-primary">On Duty</span></div>
                        <h2 class="font-headline text-base font-bold">Librarian Assistance</h2>
                        <p class="mt-1 text-sm text-on-surface-variant">Direct inquiries with Ms. Maria Santos, RL.</p>
                        <a class="mt-4 flex items-center justify-center gap-1.5 rounded-lg border border-primary px-3 py-2 text-xs font-bold text-primary hover:bg-surface-container-low" href="mailto:maria.santos@clcst.edu.ph"><span class="material-symbols-outlined text-lg">mail</span>Email Librarian</a>
                    </article>
                </section>

                <section class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                    <div class="space-y-5 lg:col-span-7" id="faq-section">
                        <div class="flex items-end justify-between border-b border-outline-variant pb-3">
                            <div>
                                <h2 class="flex items-center gap-2 font-headline text-xl font-bold text-primary"><span class="material-symbols-outlined">quiz</span>Knowledge Base & Regulations</h2>
                                <p class="text-sm text-on-surface-variant">Verified answers for common student library concerns.</p>
                            </div>
                            <span class="rounded bg-surface-container px-2.5 py-1 text-[10px] font-bold uppercase text-on-surface-variant">7 Articles</span>
                        </div>

                        <div class="space-y-3" id="borrowing">
                            <h3 class="flex items-center gap-2 font-headline text-base font-bold text-primary"><span class="material-symbols-outlined text-xl">menu_book</span>Circulation & Book Borrowing</h3>
                            <details class="group overflow-hidden rounded-lg border border-outline-variant bg-surface-container-lowest" open>
                                <summary class="flex cursor-pointer items-center justify-between gap-3 p-4 text-sm font-bold hover:bg-surface-container-low"><span>How many books can undergraduate students borrow?</span><span class="material-symbols-outlined text-outline transition group-open:rotate-180">expand_more</span></summary>
                                <div class="border-t border-outline-variant/60 bg-background/50 px-5 py-4 text-sm leading-6 text-on-surface-variant">Undergraduate patrons may borrow <strong class="text-on-surface">up to 5 books</strong> for <strong class="text-on-surface">14 calendar days</strong>. Each loan can be renewed up to two times when no active reservation exists.</div>
                            </details>
                            <details class="group overflow-hidden rounded-lg border border-outline-variant bg-surface-container-lowest">
                                <summary class="flex cursor-pointer items-center justify-between gap-3 p-4 text-sm font-bold hover:bg-surface-container-low"><span>Where is the 24/7 book return drop box?</span><span class="material-symbols-outlined text-outline transition group-open:rotate-180">expand_more</span></summary>
                                <div class="border-t border-outline-variant/60 bg-background/50 px-5 py-4 text-sm leading-6 text-on-surface-variant">The drop box is beside Main Campus Turnstile Gate 2. Returns are scanned by RFID and checked into the system during the next morning circulation run.</div>
                            </details>
                        </div>

                        <div class="space-y-3" id="digital-id">
                            <h3 class="flex items-center gap-2 font-headline text-base font-bold text-primary"><span class="material-symbols-outlined text-xl">badge</span>Digital Patron ID & Turnstiles</h3>
                            <details class="group overflow-hidden rounded-lg border border-outline-variant bg-surface-container-lowest">
                                <summary class="flex cursor-pointer items-center justify-between gap-3 p-4 text-sm font-bold hover:bg-surface-container-low"><span>My digital barcode is not scanning. What should I do?</span><span class="material-symbols-outlined text-outline transition group-open:rotate-180">expand_more</span></summary>
                                <div class="border-t border-outline-variant/60 bg-background/50 px-5 py-4 text-sm leading-6 text-on-surface-variant">Increase screen brightness, hold the barcode flat over the scanner, and confirm your current semester status. If the gate flashes amber or red, bring your ID to the Circulation Desk for credential re-syncing.</div>
                            </details>
                            <details class="group overflow-hidden rounded-lg border border-outline-variant bg-surface-container-lowest">
                                <summary class="flex cursor-pointer items-center justify-between gap-3 p-4 text-sm font-bold hover:bg-surface-container-low"><span>How do I sync my COR with the library database?</span><span class="material-symbols-outlined text-outline transition group-open:rotate-180">expand_more</span></summary>
                                <div class="border-t border-outline-variant/60 bg-background/50 px-5 py-4 text-sm leading-6 text-on-surface-variant">Updated semester registration is mirrored within 24 hours after registrar clearance. For immediate review, attach a stamped COR in the ticket form.</div>
                            </details>
                        </div>

                        <div class="space-y-3" id="fines">
                            <h3 class="flex items-center gap-2 font-headline text-base font-bold text-primary"><span class="material-symbols-outlined text-xl">payments</span>Fines, Payments & Clearance</h3>
                            <details class="group overflow-hidden rounded-lg border border-outline-variant bg-surface-container-lowest">
                                <summary class="flex cursor-pointer items-center justify-between gap-3 p-4 text-sm font-bold hover:bg-surface-container-low"><span>How long does payment verification take?</span><span class="material-symbols-outlined text-outline transition group-open:rotate-180">expand_more</span></summary>
                                <div class="border-t border-outline-variant/60 bg-background/50 px-5 py-4 text-sm leading-6 text-on-surface-variant">Portal payments are usually verified within 15 minutes. Manual GCash or Maya references may need cashier review before library holds are cleared.</div>
                            </details>
                            <details class="group overflow-hidden rounded-lg border border-outline-variant bg-surface-container-lowest">
                                <summary class="flex cursor-pointer items-center justify-between gap-3 p-4 text-sm font-bold hover:bg-surface-container-low"><span>How do I request a fine waiver?</span><span class="material-symbols-outlined text-outline transition group-open:rotate-180">expand_more</span></summary>
                                <div class="border-t border-outline-variant/60 bg-background/50 px-5 py-4 text-sm leading-6 text-on-surface-variant">Submit a ticket under Fine or Payment Dispute and include supporting proof such as a medical slip, official school activity note, or local suspension advisory.</div>
                            </details>
                        </div>
                    </div>

                    <div class="space-y-5 lg:col-span-5" id="ticket-form">
                        <div class="rounded-xl border border-outline-variant bg-surface-container-lowest p-5 shadow-sm">
                            <div class="border-b border-outline-variant pb-4">
                                <div class="flex items-center justify-between gap-3">
                                    <h2 class="flex items-center gap-2 font-headline text-lg font-bold text-primary"><span class="material-symbols-outlined">support</span>Submit an Inquiry or Report</h2>
                                    <span class="rounded-full bg-secondary-container px-2.5 py-1 text-[10px] font-bold text-on-secondary-container">Direct SLA</span>
                                </div>
                                <p class="mt-1 text-sm text-on-surface-variant">Dispatch a verified ticket to the library technical and circulation desk.</p>
                            </div>

                            <div class="my-4 flex items-center justify-between rounded-lg border border-outline-variant bg-surface-container-low p-3">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-primary">verified_user</span>
                                    <div>
                                        <div class="text-xs font-bold">Patron: Juan Dela Cruz (BSCS 3A)</div>
                                        <div class="text-[11px] text-outline">Auto-linked ID: 2024-10492-OL</div>
                                    </div>
                                </div>
                                <span class="rounded bg-primary-fixed px-2 py-0.5 text-[10px] font-bold text-primary">Active</span>
                            </div>

                            <form class="space-y-4" method="GET" action="{{ route('student.section', 'support') }}">
                                <input type="hidden" name="ticket" value="{{ $ticketReference }}">
                                <div>
                                    <label class="mb-1 block text-xs font-bold" for="issue-category">Issue Category <span class="text-error">*</span></label>
                                    <select class="h-11 w-full rounded-lg border border-outline-variant bg-white text-sm focus:border-primary focus:ring-1 focus:ring-primary" id="issue-category" name="category" required>
                                        <option value="">Select issue category...</option>
                                        <option value="turnstile">Turnstile / RFID Pass Problem</option>
                                        <option value="barcode">Barcode Reading Error</option>
                                        <option value="fine">Fine / Payment Dispute</option>
                                        <option value="reservation">Book Reservation Inquiry</option>
                                        <option value="eresource">E-Resource Access</option>
                                        <option value="lost_book">Lost or Damaged Book Report</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-bold" for="ticket-subject">Subject Line <span class="text-error">*</span></label>
                                    <input class="h-11 w-full rounded-lg border border-outline-variant bg-white text-sm placeholder:text-outline focus:border-primary focus:ring-1 focus:ring-primary" id="ticket-subject" name="subject" placeholder="Example: Turnstile rejects my digital barcode" required>
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-bold">Priority Level</label>
                                    <div class="grid grid-cols-3 gap-2">
                                        <label class="flex cursor-pointer items-center justify-center rounded-lg border border-outline-variant bg-surface-container-low p-2 text-center text-xs font-bold text-on-surface-variant has-[:checked]:border-primary has-[:checked]:bg-primary-container has-[:checked]:text-on-primary"><input class="sr-only" name="priority" type="radio" value="standard" checked>Standard</label>
                                        <label class="flex cursor-pointer items-center justify-center rounded-lg border border-outline-variant bg-surface-container-low p-2 text-center text-xs font-bold text-on-surface-variant has-[:checked]:border-primary has-[:checked]:bg-primary-container has-[:checked]:text-on-primary"><input class="sr-only" name="priority" type="radio" value="urgent">Urgent</label>
                                        <label class="flex cursor-pointer items-center justify-center rounded-lg border border-outline-variant bg-surface-container-low p-2 text-center text-xs font-bold text-on-surface-variant has-[:checked]:border-primary has-[:checked]:bg-primary-container has-[:checked]:text-on-primary"><input class="sr-only" name="priority" type="radio" value="clearance">Clearance</label>
                                    </div>
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-bold" for="ticket-message">Detailed Message <span class="text-error">*</span></label>
                                    <textarea class="w-full rounded-lg border border-outline-variant bg-white text-sm placeholder:text-outline focus:border-primary focus:ring-1 focus:ring-primary" id="ticket-message" name="message" rows="4" placeholder="Include dates, book titles, call numbers, barcode numbers, or screenshots you can show at the desk." required></textarea>
                                </div>
                                <div class="rounded-lg border-2 border-dashed border-outline-variant bg-surface-container-low p-4 text-center">
                                    <span class="material-symbols-outlined text-3xl text-outline">cloud_upload</span>
                                    <p class="text-xs font-bold">Proof attachment placeholder</p>
                                    <p class="text-[11px] text-outline">File upload can be connected when ticket storage is added.</p>
                                </div>
                                <div class="rounded-lg border border-secondary-container bg-amber-50 p-3 text-xs leading-5 text-on-surface-variant"><strong class="text-secondary">Reference preview:</strong> {{ $ticketReference }} - response expected within 2 to 4 academic hours.</div>
                                <button class="flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-primary text-sm font-bold text-on-primary shadow-sm hover:bg-primary-container" type="submit"><span class="material-symbols-outlined">send</span>Dispatch Support Ticket</button>
                            </form>
                        </div>

                        <div class="rounded-xl border border-outline-variant bg-surface-container-lowest p-5 shadow-sm">
                            <h2 class="flex items-center gap-2 font-headline text-lg font-bold text-primary"><span class="material-symbols-outlined">fact_check</span>Your Current Library Snapshot</h2>
                            <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                                <a class="rounded-lg bg-surface-container-low p-3 hover:bg-surface-container" href="{{ route('student.section', 'borrow-return') }}"><div class="font-headline text-xl font-bold">{{ $activeLoanCount }}</div><div class="text-[10px] font-bold uppercase text-outline">Loans</div></a>
                                <a class="rounded-lg bg-surface-container-low p-3 hover:bg-surface-container" href="{{ route('student.section', 'reservations') }}"><div class="font-headline text-xl font-bold">{{ $reservationCount }}</div><div class="text-[10px] font-bold uppercase text-outline">Holds</div></a>
                                <a class="rounded-lg bg-surface-container-low p-3 hover:bg-surface-container" href="{{ route('student.section', 'fines') }}"><div class="font-headline text-xl font-bold">PHP {{ number_format($outstanding, 0) }}</div><div class="text-[10px] font-bold uppercase text-outline">Fines</div></a>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="rounded-xl border border-outline-variant bg-surface-container-lowest p-5 shadow-sm lg:p-6" id="directory">
                    <div class="flex flex-col justify-between gap-3 border-b border-outline-variant pb-4 md:flex-row md:items-center">
                        <div>
                            <h2 class="flex items-center gap-2 font-headline text-xl font-bold text-primary"><span class="material-symbols-outlined">corporate_fare</span>Circulation Desk Contact & Campus Service Directory</h2>
                            <p class="text-sm text-on-surface-variant">Direct channels across CLCST central college departments and physical stacks.</p>
                        </div>
                        <span class="inline-flex items-center gap-2 rounded-full border border-outline-variant bg-surface-container px-3 py-1 text-xs font-bold"><span class="h-2 w-2 rounded-full bg-primary"></span>Stacks Level 1 & 2</span>
                    </div>
                    <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div class="rounded-lg border border-outline-variant bg-surface-container-low p-4">
                            <h3 class="mb-3 flex items-center gap-2 font-headline font-bold text-primary"><span class="material-symbols-outlined">location_on</span>Campus Locations</h3>
                            <p class="text-sm"><strong>Main College Library:</strong><br>Don Bonifacio Blvd, Olongapo City, Zambales 2200</p>
                            <p class="mt-3 border-t border-outline-variant pt-3 text-sm"><strong>Extension Desk:</strong><br>San Fernando, Pampanga Campus - Branch Annex, Room 204</p>
                        </div>
                        <div class="rounded-lg border border-outline-variant bg-surface-container-low p-4">
                            <h3 class="mb-3 flex items-center gap-2 font-headline font-bold text-primary"><span class="material-symbols-outlined">call</span>Telephone & Digital</h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between gap-3"><span class="text-outline">Trunkline</span><strong>+63 (047) 224-1234</strong></div>
                                <div class="flex justify-between gap-3"><span class="text-outline">Circulation</span><strong class="text-primary">Local 108</strong></div>
                                <div class="flex justify-between gap-3"><span class="text-outline">Technical Services</span><strong class="text-primary">Local 109</strong></div>
                                <a class="inline-flex items-center gap-1 pt-2 text-sm font-bold text-primary hover:underline" href="mailto:library.support@clcst.edu.ph"><span class="material-symbols-outlined text-base">mail</span>library.support@clcst.edu.ph</a>
                            </div>
                        </div>
                        <div class="rounded-lg border border-outline-variant bg-surface-container-low p-4">
                            <h3 class="mb-3 flex items-center gap-2 font-headline font-bold text-primary"><span class="material-symbols-outlined">calendar_today</span>Operating Schedules</h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between gap-3 border-b border-outline-variant pb-2"><span>Monday to Friday</span><strong class="text-primary">7:30 AM - 6:30 PM</strong></div>
                                <div class="flex justify-between gap-3 border-b border-outline-variant pb-2"><span>Saturday Stacks</span><strong class="text-primary">8:00 AM - 5:00 PM</strong></div>
                                <div class="flex justify-between gap-3 text-outline"><span>Sundays & Holidays</span><strong class="text-error">Closed</strong></div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>
</body>
</html>
