<!DOCTYPE html>
<html class="h-full bg-background" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>CLCST Library Portal - Login</title>
    @include('partials.favicon')

    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'secondary-fixed-dim': '#f7be1d',
                        'on-secondary-fixed': '#251a00',
                        'primary-fixed-dim': '#8fd79b',
                        error: '#ba1a1a',
                        'primary-fixed': '#aaf4b5',
                        'surface-dim': '#d2d9f4',
                        'error-container': '#ffdad6',
                        'surface-container-low': '#f2f3ff',
                        'on-background': '#131b2e',
                        'tertiary-fixed-dim': '#b7c4ff',
                        'inverse-surface': '#283044',
                        'outline-variant': '#c0c9bd',
                        tertiary: '#002e97',
                        'on-tertiary': '#ffffff',
                        'tertiary-container': '#0041cc',
                        'surface-bright': '#faf8ff',
                        'on-secondary': '#ffffff',
                        'secondary-fixed': '#ffdf9a',
                        'inverse-primary': '#8fd79b',
                        'primary-container': '#135d2e',
                        'surface-container': '#eaedff',
                        'surface-container-high': '#e2e7ff',
                        'on-primary-fixed-variant': '#015225',
                        'on-secondary-fixed-variant': '#5a4300',
                        outline: '#707a6f',
                        surface: '#faf8ff',
                        'on-secondary-container': '#6d5200',
                        'on-primary-fixed': '#00210b',
                        'secondary-container': '#fdc425',
                        'surface-variant': '#dae2fd',
                        'surface-tint': '#256b3a',
                        'on-tertiary-fixed': '#001551',
                        'on-primary-container': '#8cd499',
                        background: '#faf8ff',
                        primary: '#00441d',
                        'on-error': '#ffffff',
                        'on-surface': '#131b2e',
                        'on-primary': '#ffffff',
                        'surface-container-lowest': '#ffffff',
                        'on-tertiary-fixed-variant': '#0039b5',
                        secondary: '#785a00',
                        'on-surface-variant': '#404940',
                        'on-error-container': '#93000a',
                        'on-tertiary-container': '#b3c1ff',
                        'surface-container-highest': '#dae2fd',
                        'inverse-on-surface': '#eef0ff',
                        'tertiary-fixed': '#dce1ff'
                    },
                    borderRadius: {
                        DEFAULT: '0.25rem',
                        lg: '0.5rem',
                        xl: '0.75rem',
                        full: '9999px'
                    },
                    fontFamily: {
                        'body-md': ['Inter', 'sans-serif'],
                        'headline-xl': ['Plus Jakarta Sans', 'sans-serif'],
                        'headline-lg': ['Plus Jakarta Sans', 'sans-serif'],
                        'headline-sm': ['Plus Jakarta Sans', 'sans-serif'],
                        'label-lg': ['Inter', 'sans-serif'],
                        'label-md': ['Inter', 'sans-serif'],
                        'label-sm': ['Inter', 'sans-serif']
                    }
                }
            }
        };
    </script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="min-h-screen bg-background font-body-md text-on-surface antialiased selection:bg-primary-fixed selection:text-on-primary-fixed">
    <header class="sticky top-0 z-40 w-full border-b border-outline-variant/60 bg-surface-container-lowest/95 shadow-sm backdrop-blur-md">
        <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-4">
                <div class="relative">
                    @include('partials.celtech-logo', ['class' => 'h-14 w-14 rounded-full object-contain bg-white p-0.5 shadow-sm ring-2 ring-primary-container/20'])
                    <span class="material-symbols-outlined absolute -bottom-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-primary-fixed text-[12px] font-bold text-primary">check</span>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-headline-sm text-base font-bold tracking-tight text-primary">CLCST Library Portal</span>
                        <span class="rounded-full bg-secondary-fixed px-2 py-0.5 text-[10px] font-bold text-on-secondary-fixed">EST. 1959</span>
                    </div>
                    <p class="text-xs font-medium text-on-surface-variant sm:text-sm">Central Luzon College of Science & Technology - Library Access</p>
                </div>
            </div>

            <div class="hidden items-center gap-4 lg:flex">
                <div class="flex items-center gap-2 rounded-lg border border-outline-variant/40 bg-surface-container-low px-3 py-1.5">
                    <span class="material-symbols-outlined text-base text-primary">lock</span>
                    <div>
                        <p class="text-[10px] font-bold leading-none">TLS 1.3 RSA-256</p>
                        <p class="text-[10px] text-on-surface-variant">Encrypted Channel</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 rounded-lg border border-outline-variant/40 bg-surface-container-low px-3 py-1.5">
                    <span class="material-symbols-outlined text-base text-secondary">verified_user</span>
                    <div>
                        <p class="text-[10px] font-bold leading-none">RA 10173 Compliant</p>
                        <p class="text-[10px] text-on-surface-variant">PH Data Privacy Act</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 rounded-lg border border-outline-variant/40 bg-surface-container-low px-3 py-1.5">
                    <span class="material-symbols-outlined text-base text-primary-container">school</span>
                    <div>
                        <p class="text-[10px] font-bold leading-none">CHED Standard</p>
                        <p class="text-[10px] text-on-surface-variant">Academic Library Access</p>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="mx-auto flex w-full max-w-7xl flex-1 flex-col px-4 py-8 sm:px-6 lg:px-8">
        <section class="relative mb-8 overflow-hidden rounded-xl border border-outline-variant/20 bg-gradient-to-r from-primary to-primary-container p-6 text-on-primary shadow-sm md:p-8">
            <div class="absolute -right-16 -top-16 flex h-80 w-80 items-center justify-center rounded-full border border-white/20 text-7xl font-extrabold text-white/10">CLCST</div>
            <div class="relative z-10 max-w-2xl">
                <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-on-primary/20 bg-on-primary/10 px-3 py-1 text-[10px] font-bold text-secondary-fixed backdrop-blur-sm">
                    <span class="material-symbols-outlined text-sm">vpn_key</span>
                    SINGLE SIGN-ON & TURNSTILE AUTHORIZATION
                </div>
                <h1 class="font-headline-xl text-3xl font-bold tracking-tight text-white md:text-4xl">Library Sign In</h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-primary-fixed md:text-base">
                    Select your institutional credential domain to access circulation, digital repository indexes, journal archives, and contactless library entry entry access.
                </p>
            </div>
        </section>

        @if (session('status'))
            <div class="mb-6 rounded-lg border border-primary-fixed-dim bg-primary-fixed px-4 py-3 text-sm font-semibold text-on-primary-fixed">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
            <section class="flex flex-col gap-6 lg:col-span-7">
                <div>
                    <div class="mb-3 flex items-center justify-between">
                        <label class="flex items-center gap-2 text-sm font-bold text-on-surface">
                            <span class="material-symbols-outlined text-primary">switch_account</span>
                            Select Institutional Role
                        </label>
                        <span class="text-[10px] font-bold text-on-surface-variant sm:text-xs">4 Account Types Available</span>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4" id="role-selector-container">
                        <button class="role-btn flex h-full flex-col justify-between rounded-xl border-2 border-primary bg-surface-container-lowest p-3.5 text-left shadow-sm ring-1 ring-primary transition-all" id="btn-role-student" type="button" data-role="student">
                            <div>
                                <div class="mb-2 flex items-center justify-between">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary-fixed/30 text-primary">
                                        <span class="material-symbols-outlined text-lg">school</span>
                                    </div>
                                    <span class="role-check material-symbols-outlined inline-flex h-5 w-5 items-center justify-center rounded-full bg-primary text-sm text-white" id="badge-student">check</span>
                                </div>
                                <h2 class="text-sm font-bold text-on-surface">Student</h2>
                                <p class="mt-1 text-[11px] leading-tight text-on-surface-variant">Undergraduate, SHS & Tech-Voc students</p>
                            </div>
                            <div class="mt-3 flex items-center gap-1 border-t border-outline-variant/30 pt-2 text-[10px] font-semibold text-primary">
                                <span class="material-symbols-outlined text-xs">book</span>
                                5 Books - 14-Day Loan
                            </div>
                        </button>

                        <button class="role-btn flex h-full flex-col justify-between rounded-xl border-2 border-outline-variant/60 bg-surface-container-lowest p-3.5 text-left shadow-sm transition-all hover:border-outline" id="btn-role-faculty" type="button" data-role="faculty">
                            <div>
                                <div class="mb-2 flex items-center justify-between">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-surface-container-high text-on-surface-variant">
                                        <span class="material-symbols-outlined text-lg">assignment_ind</span>
                                    </div>
                                    <span class="role-check material-symbols-outlined hidden h-5 w-5 items-center justify-center rounded-full bg-primary text-sm text-white" id="badge-faculty">check</span>
                                </div>
                                <h2 class="text-sm font-bold text-on-surface">Faculty / Staff</h2>
                                <p class="mt-1 text-[11px] leading-tight text-on-surface-variant">Professors, Researchers & Deans</p>
                            </div>
                            <div class="mt-3 flex items-center gap-1 border-t border-outline-variant/30 pt-2 text-[10px] font-semibold text-on-surface-variant">
                                <span class="material-symbols-outlined text-xs">menu_book</span>
                                15 Books - 60-Day Loan
                            </div>
                        </button>

                        <button class="role-btn flex h-full flex-col justify-between rounded-xl border-2 border-outline-variant/60 bg-surface-container-lowest p-3.5 text-left shadow-sm transition-all hover:border-outline" id="btn-role-staff" type="button" data-role="staff">
                            <div>
                                <div class="mb-2 flex items-center justify-between">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-surface-container-high text-on-surface-variant">
                                        <span class="material-symbols-outlined text-lg">badge</span>
                                    </div>
                                    <span class="role-check material-symbols-outlined hidden h-5 w-5 items-center justify-center rounded-full bg-primary text-sm text-white" id="badge-staff">check</span>
                                </div>
                                <h2 class="text-sm font-bold text-on-surface">Librarian</h2>
                                <p class="mt-1 text-[11px] leading-tight text-on-surface-variant">Library Desk & Catalogers</p>
                            </div>
                            <div class="mt-3 flex items-center gap-1 border-t border-outline-variant/30 pt-2 text-[10px] font-semibold text-on-surface-variant">
                                <span class="material-symbols-outlined text-xs">barcode_scanner</span>
                                Admin Clearance & ILS
                            </div>
                        </button>

                        <button class="role-btn flex h-full flex-col justify-between rounded-xl border-2 border-outline-variant/60 bg-surface-container-lowest p-3.5 text-left shadow-sm transition-all hover:border-outline" id="btn-role-superadmin" type="button" data-role="superadmin">
                            <div>
                                <div class="mb-2 flex items-center justify-between">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-surface-container-high text-on-surface-variant">
                                        <span class="material-symbols-outlined text-lg">shield_person</span>
                                    </div>
                                    <span class="role-check material-symbols-outlined hidden h-5 w-5 items-center justify-center rounded-full bg-primary text-sm text-white" id="badge-superadmin">check</span>
                                </div>
                                <h2 class="text-sm font-bold text-on-surface">Super Admin</h2>
                                <p class="mt-1 text-[11px] leading-tight text-on-surface-variant">Authorized system administrators</p>
                            </div>
                            <div class="mt-3 flex items-center gap-1 border-t border-outline-variant/30 pt-2 text-[10px] font-semibold text-on-surface-variant">
                                <span class="material-symbols-outlined text-xs">security</span>
                                Full System Control
                            </div>
                        </button>
                    </div>
                </div>

                <div class="rounded-xl border border-outline-variant/70 bg-surface-container-lowest p-6 shadow-sm sm:p-7">
                    <div class="mb-6 flex items-center justify-between border-b border-outline-variant/40 pb-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center rounded-md bg-primary-fixed px-2.5 py-0.5 text-[10px] font-bold text-on-primary-fixed-variant" id="active-role-tag">STUDENT PATRON</span>
                                <span class="text-sm text-on-surface-variant">- Campus Network Active</span>
                            </div>
                            <p class="mt-1 text-sm text-on-surface" id="active-role-desc">
                                Authenticate using your Registered Student ID Number as printed on your COR or Smart Barcode ID.
                            </p>
                        </div>
                        <span class="material-symbols-outlined hidden text-2xl text-primary sm:block" id="role-hero-icon">school</span>
                    </div>

                    <form class="space-y-5" method="POST" action="{{ route('login.authenticate') }}">
                        @csrf
                        <input type="hidden" name="role" id="role-input" value="{{ old('role', 'student') }}">

                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-on-surface" for="identifier-input" id="identifier-label">
                                Student ID Number (e.g., 2023-10492-OL)
                            </label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-on-surface-variant">
                                    <span class="material-symbols-outlined text-lg" id="identifier-icon">badge</span>
                                </div>
                                <input class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest py-2.5 pl-10 pr-24 text-sm text-on-surface transition-all focus:border-primary focus:ring-2 focus:ring-primary/20" id="identifier-input" name="identifier" placeholder="Format: YYYY-XXXXX-XX" required type="text" value="{{ old('identifier') }}">
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                    <span class="font-mono text-xs text-on-surface-variant/80" id="identifier-hint">COR Verified</span>
                                </div>
                            </div>
                            @error('identifier')
                                <p class="mt-2 text-sm text-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <div class="mb-1.5 flex items-center justify-between">
                                <label class="text-sm font-semibold text-on-surface" for="password-input" id="password-label">Library PIN / Password</label>
                                <button class="text-xs font-semibold text-primary hover:underline" data-alert="Please visit the Library Help Desk Ground Floor or email library@clcst.edu.ph with your Certificate of Registration." type="button">
                                    Forgot PIN / Password?
                                </button>
                            </div>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-on-surface-variant">
                                    <span class="material-symbols-outlined text-lg">key</span>
                                </div>
                                <input class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest py-2.5 pl-10 pr-10 text-sm text-on-surface transition-all focus:border-primary focus:ring-2 focus:ring-primary/20" id="password-input" name="password" placeholder="Enter your security access PIN" required type="password">
                                <button class="absolute inset-y-0 right-0 flex items-center pr-3 text-on-surface-variant transition-colors hover:text-on-surface" id="toggle-password" title="Toggle visibility" type="button">
                                    <span class="material-symbols-outlined text-lg" id="pw-visibility-icon">visibility</span>
                                </button>
                            </div>
                            @error('password')
                                <p class="mt-2 text-sm text-error">{{ $message }}</p>
                            @enderror
                            @error('role')
                                <p class="mt-2 text-sm text-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex flex-col justify-between gap-3 pt-1 sm:flex-row sm:items-center" id="student-context-options">
                            <label class="flex cursor-pointer select-none items-center gap-2">
                                <input checked class="h-4 w-4 rounded border-outline-variant text-primary focus:ring-primary/30" type="checkbox">
                                <span class="text-sm text-on-surface-variant">Remember on this campus kiosk</span>
                            </label>
                            <div class="inline-flex items-center gap-1.5 text-[10px] font-bold text-secondary">
                                <span class="material-symbols-outlined text-base">qr_code_scanner</span>
                                Library Entry Pass Ready
                            </div>
                        </div>

                        <div class="hidden flex-col justify-between gap-3 pt-1 sm:flex-row sm:items-center" id="faculty-context-options">
                            <label class="flex cursor-pointer select-none items-center gap-2">
                                <input checked class="h-4 w-4 rounded border-outline-variant text-primary focus:ring-primary/30" type="checkbox">
                                <span class="text-sm text-on-surface-variant">Keep session active for 30 days</span>
                            </label>
                            <span class="flex items-center gap-1 text-[10px] font-semibold text-primary">
                                <span class="material-symbols-outlined text-xs">verified</span>
                                Online Journal Access
                            </span>
                        </div>

                        <div class="hidden flex-col gap-3 pt-1" id="staff-context-options">
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-[10px] font-semibold text-on-surface-variant">Borrowing Terminal ID</label>
                                    <input class="w-full rounded border border-outline-variant/60 bg-surface-container-low px-3 py-1.5 font-mono text-xs font-medium text-on-surface" readonly type="text" value="TERM-DESK-04-OL">
                                </div>
                                <div>
                                    <label class="mb-1 block text-[10px] font-semibold text-on-surface-variant">2FA TOTP Token</label>
                                    <input class="w-full rounded border border-outline-variant px-3 py-1.5 font-mono text-xs text-on-surface" maxlength="6" placeholder="6-digit code" type="text">
                                </div>
                            </div>
                        </div>

                        <div class="hidden flex-col gap-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-xs text-amber-900" id="superadmin-context-options">
                            <div class="flex items-start gap-2">
                                <span class="material-symbols-outlined text-lg">verified_user</span>
                                <p><strong>Restricted access:</strong> Super Admin sign-in is intended only for institution-approved system administrators. All administrative actions are recorded.</p>
                            </div>
                        </div>

                        <button class="flex w-full items-center justify-center gap-2 rounded-lg bg-primary px-5 py-3 text-sm font-bold text-on-primary shadow-sm transition-all hover:bg-primary-container active:scale-[0.99]" id="submit-button" type="submit">
                            <span class="material-symbols-outlined text-xl">login</span>
                            <span id="submit-button-text">Authenticate as Student</span>
                        </button>

                        <div class="hidden border-t border-outline-variant/30 pt-4 text-center" id="google-sso-container">
                            <p class="mb-2 text-xs text-on-surface-variant">Or use your official institutional email</p>
                            <button class="flex w-full items-center justify-center gap-3 rounded-lg border border-outline-variant bg-surface-container-lowest px-4 py-2.5 text-sm font-semibold text-on-surface transition-colors hover:bg-surface-container-low" type="button">
                                <span class="font-bold text-[#4285F4]">G</span>
                                Sign In with @clcst.edu.ph Google Account
                            </button>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-2 border-t border-outline-variant/40 pt-4 text-xs">
                            <span class="text-on-surface-variant">First-time library user?</span>
                            <div class="flex items-center gap-3">
                                <a class="flex items-center gap-1 font-bold text-primary hover:underline" href="{{ route('register') }}">
                                    <span class="material-symbols-outlined text-sm">how_to_reg</span>
                                    Activate Card / Web Pass
                                </a>
                                <span class="text-outline">|</span>
                                <button class="text-on-surface-variant transition-colors hover:text-primary" data-alert="Library Desk Support: Local Ext. 108 / 109 | library@clcst.edu.ph" type="button">Help Desk</button>
                            </div>
                        </div>
                    </form>
                </div>
            </section>

            <section class="flex flex-col gap-6 lg:col-span-5">
                <div class="rounded-xl border border-outline-variant/70 bg-surface-container-lowest p-5 shadow-sm">
                    <div class="mb-3 flex items-center justify-between">
                        <span class="flex items-center gap-2 text-sm font-bold text-on-surface">
                            <span class="material-symbols-outlined text-primary">contactless</span>
                            CLCST Digital Library Pass
                        </span>
                        <span class="rounded-full bg-primary-fixed px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-on-primary-fixed-variant">ID / Barcode</span>
                    </div>

                    <div class="relative overflow-hidden rounded-xl border border-primary-fixed/30 bg-gradient-to-br from-primary-container via-primary to-[#082813] p-5 text-on-primary shadow-md">
                        <div class="absolute -bottom-10 -right-8 text-8xl font-extrabold text-white/10">CL</div>
                        <div class="relative z-10 mb-4 flex items-start justify-between">
                            <div class="flex items-center gap-2.5">
                                @include('partials.celtech-logo', ['class' => 'flex h-10 w-10 items-center justify-center rounded-full bg-white text-xs font-extrabold text-primary shadow-sm object-contain bg-white p-0.5'])
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wide text-white">Central Luzon College</p>
                                    <p class="text-[10px] text-secondary-fixed">Science & Technology - Learning Commons</p>
                                </div>
                            </div>
                            <span class="rounded border border-white/20 bg-black/30 px-2 py-0.5 font-mono text-[10px] text-primary-fixed">PASS: 2024-SEM2</span>
                        </div>

                        <div class="relative z-10 my-3 flex items-center justify-between">
                            <div>
                                <p class="text-[10px] uppercase tracking-wider text-primary-fixed/80">Authorized Cardholder</p>
                                <p class="text-sm font-bold text-white" id="card-holder-name">DE GUZMAN, MATEO S.</p>
                                <p class="font-mono text-xs text-secondary-fixed" id="card-holder-id">ID: 2023-01984-OL</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] uppercase tracking-wider text-primary-fixed/80">Affiliation</p>
                                <p class="text-xs font-semibold text-white" id="card-holder-dept">BS Computer Science</p>
                                <span class="mt-0.5 inline-block rounded bg-white/20 px-1.5 py-0.5 text-[9px] font-bold text-white">VALIDATED</span>
                            </div>
                        </div>

                        <div class="relative z-10 mt-4 flex flex-col items-center justify-center rounded-lg bg-white p-2.5 shadow-inner">
                            <div class="flex h-9 w-full items-center justify-between gap-0.5 bg-white px-2">
                                @foreach ([6,2,4,8,2,6,2,10,4,2,8,4,2,8,6,2,10,4,2,8,6,2,8,4,2,6] as $bar)
                                    <span class="h-full bg-black" style="width: {{ $bar }}px"></span>
                                @endforeach
                            </div>
                            <p class="mt-1 font-mono text-[10px] font-bold tracking-widest text-slate-800" id="card-barcode-text">*202301984OL*</p>
                        </div>
                        <p class="mt-2 text-center text-[9px] text-primary-fixed/80">Entrance scanners accept mobile screens at 100% brightness.</p>
                    </div>
                </div>

                <div class="space-y-4 rounded-xl border border-outline-variant/70 bg-surface-container-lowest p-5 shadow-sm">
                    <div class="flex items-center justify-between border-b border-outline-variant/40 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">schedule</span>
                            <h3 class="text-sm font-bold text-on-surface">Library Operating Hours</h3>
                        </div>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#DCFCE7] px-2 py-0.5 text-[11px] font-bold text-[#166534]">
                            <span class="h-2 w-2 rounded-full bg-[#166534]"></span>
                            Open Now
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div class="rounded-lg border border-outline-variant/30 bg-surface-container-low p-2.5">
                            <p class="font-semibold text-on-surface">Monday - Friday</p>
                            <p class="mt-0.5 font-bold text-primary">8:00 AM - 8:00 PM</p>
                            <p class="text-[10px] text-on-surface-variant">Full Borrowing & Shelves</p>
                        </div>
                        <div class="rounded-lg border border-outline-variant/30 bg-surface-container-low p-2.5">
                            <p class="font-semibold text-on-surface">Saturday</p>
                            <p class="mt-0.5 font-bold text-primary">8:00 AM - 5:00 PM</p>
                            <p class="text-[10px] text-on-surface-variant">General Reference & Study</p>
                        </div>
                    </div>

                    <div class="rounded-lg border border-[#FEF08A] bg-[#FEFCE8] p-3 text-xs">
                        <div class="flex items-start gap-2">
                            <span class="material-symbols-outlined flex-shrink-0 text-lg text-secondary">notifications_active</span>
                            <div>
                                <p class="font-bold text-[#785A00]">Midterm Renewal Window Active</p>
                                <p class="mt-0.5 text-[#713F12]">Overdue fines for 1st trimester reference texts are waived until Friday, 5:00 PM.</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2 pt-2 text-xs text-on-surface-variant">
                        <div class="flex items-center justify-between gap-3">
                            <span class="flex items-center gap-1.5"><span class="material-symbols-outlined text-sm text-primary">call</span>Library Desk:</span>
                            <span class="font-semibold text-on-surface">(047) 222-2432 local 108</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="flex items-center gap-1.5"><span class="material-symbols-outlined text-sm text-primary">mail</span>Direct Email:</span>
                            <span class="font-semibold text-primary">circulation@clcst.edu.ph</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="flex items-center gap-1.5"><span class="material-symbols-outlined text-sm text-primary">meeting_room</span>Main Campus:</span>
                            <span class="font-semibold text-on-surface">Rizal Extension, Olongapo City</span>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <footer class="mt-auto border-t border-outline-variant/60 bg-surface-container-lowest py-6 text-xs text-on-surface-variant">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-4 text-center md:flex-row md:text-left lg:px-8">
            <div class="flex items-center gap-3">
                @include('partials.celtech-logo', ['class' => 'flex h-8 w-8 items-center justify-center rounded-full bg-primary text-[10px] font-extrabold text-white object-contain bg-white p-0.5'])
                <div>
                    <p class="font-semibold text-on-surface">Central Luzon College of Science and Technology (CLCST)</p>
                    <p>In compliance with Republic Act No. 10173 and CHED academic library standards.</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center justify-center gap-4 font-medium">
                <a class="transition-colors hover:text-primary" href="#">Privacy Policy</a>
                <span class="text-outline-variant">|</span>
                <a class="transition-colors hover:text-primary" href="#">Library ID Help</a>
                <span class="text-outline-variant">|</span>
                <a class="transition-colors hover:text-primary" href="#">Public Book Search</a>
            </div>
        </div>
    </footer>

    <script src="{{ asset('js/login.js') }}"></script>
</body>
</html>
