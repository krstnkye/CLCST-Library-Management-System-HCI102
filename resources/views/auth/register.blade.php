<!DOCTYPE html>
<html class="h-full bg-surface" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>CLCST Library Portal - Library User Registration</title>
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
                        'surface-container-low': '#f2f3ff',
                        'on-secondary-container': '#6d5200',
                        'on-tertiary': '#ffffff',
                        outline: '#707a6f',
                        surface: '#faf8ff',
                        'on-error-container': '#93000a',
                        'surface-container-high': '#e2e7ff',
                        'on-primary-container': '#8cd499',
                        'secondary-fixed': '#ffdf9a',
                        'surface-container': '#eaedff',
                        'surface-bright': '#faf8ff',
                        'error-container': '#ffdad6',
                        'surface-dim': '#d2d9f4',
                        'tertiary-container': '#0041cc',
                        'tertiary-fixed-dim': '#b7c4ff',
                        'surface-container-highest': '#dae2fd',
                        'on-secondary-fixed': '#251a00',
                        'inverse-primary': '#8fd79b',
                        'inverse-on-surface': '#eef0ff',
                        background: '#faf8ff',
                        'inverse-surface': '#283044',
                        'on-secondary': '#ffffff',
                        'primary-container': '#135d2e',
                        'on-primary-fixed': '#00210b',
                        'on-tertiary-container': '#b3c1ff',
                        'on-primary-fixed-variant': '#015225',
                        'on-error': '#ffffff',
                        secondary: '#785a00',
                        'primary-fixed-dim': '#8fd79b',
                        'tertiary-fixed': '#dce1ff',
                        'on-tertiary-fixed': '#001551',
                        'on-surface': '#131b2e',
                        error: '#ba1a1a',
                        'on-background': '#131b2e',
                        'outline-variant': '#c0c9bd',
                        'on-surface-variant': '#404940',
                        'surface-tint': '#256b3a',
                        'on-tertiary-fixed-variant': '#0039b5',
                        'surface-container-lowest': '#ffffff',
                        'on-secondary-fixed-variant': '#5a4300',
                        'secondary-container': '#fdc425',
                        primary: '#00441d',
                        'primary-fixed': '#aaf4b5',
                        'secondary-fixed-dim': '#f7be1d',
                        tertiary: '#002e97',
                        'on-primary': '#ffffff',
                        'surface-variant': '#dae2fd'
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
                        'headline-md': ['Plus Jakarta Sans', 'sans-serif'],
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
<body class="flex min-h-screen flex-col bg-surface font-body-md text-on-surface antialiased selection:bg-secondary-fixed selection:text-on-secondary-fixed">
    <header class="sticky top-0 z-40 border-b border-outline-variant/70 bg-surface-container-lowest/95 shadow-sm backdrop-blur">
        <div class="mx-auto flex min-h-20 max-w-7xl items-center justify-between gap-6 px-4 py-3 lg:px-8">
            <a class="flex items-center gap-3.5 group" href="{{ route('login') }}">
                @include('partials.celtech-logo', ['class' => 'flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-full bg-primary text-lg font-extrabold text-white shadow-sm ring-2 ring-secondary-container/80 object-contain bg-white p-0.5'])
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-headline-sm text-base font-bold tracking-tight text-primary">CLCST Library Portal</span>
                        <span class="rounded-full border border-primary-container/20 bg-primary-container/10 px-2 py-0.5 text-[10px] font-bold text-primary">LMS v4.2</span>
                    </div>
                    <p class="text-xs font-medium text-on-surface-variant sm:text-sm">Central Luzon College of Science and Technology - Learning Commons Registration</p>
                </div>
            </a>

            <div class="flex items-center gap-5">
                <div class="hidden items-center gap-2 text-[10px] font-bold text-on-surface-variant xl:flex">
                    <span class="flex items-center gap-1.5 rounded-lg border border-outline-variant/70 bg-surface-container-low/70 px-3 py-2 shadow-[inset_0_1px_0_rgba(255,255,255,0.55)]">
                        <span class="material-symbols-outlined text-[16px] text-primary">verified_user</span>
                        RA 10173 Data Privacy
                    </span>
                    <span class="flex items-center gap-1.5 rounded-lg border border-outline-variant/70 bg-surface-container-low/70 px-3 py-2 shadow-[inset_0_1px_0_rgba(255,255,255,0.55)]">
                        <span class="material-symbols-outlined text-[16px] text-secondary">school</span>
                        CHED CMO No. 25
                    </span>
                    <span class="flex items-center gap-1.5 rounded-lg border border-outline-variant/70 bg-surface-container-low/70 px-3 py-2 shadow-[inset_0_1px_0_rgba(255,255,255,0.55)]">
                        <span class="material-symbols-outlined text-[16px] text-primary">lock</span>
                        TLS 1.3 Secure
                    </span>
                </div>
                <div class="flex items-center gap-3 border-l border-outline-variant/70 pl-5">
                    <span class="hidden max-w-[7rem] text-right text-xs leading-4 text-on-surface-variant sm:inline">Already activated?</span>
                    <a class="inline-flex whitespace-nowrap items-center gap-2 rounded-full bg-primary px-4 py-2.5 text-xs font-bold text-on-primary shadow-sm transition-all hover:-translate-y-0.5 hover:bg-primary-container hover:shadow-md focus:outline-none focus:ring-2 focus:ring-primary-fixed focus:ring-offset-2 active:translate-y-0" href="{{ route('login') }}">
                        <span>Sign In</span>
                        <span class="material-symbols-outlined text-[17px]">login</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <section class="relative overflow-hidden bg-primary-container px-4 py-8 text-on-primary shadow-inner lg:px-8">
        <div class="absolute -right-16 -top-24 h-80 w-80 rounded-full bg-primary/20 blur-2xl"></div>
        <div class="mx-auto max-w-7xl">
            <div class="relative z-10 flex flex-col justify-between gap-6 md:flex-row md:items-center">
                <div class="max-w-3xl">
                    <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-secondary-container/30 bg-secondary-container/20 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-secondary-fixed">
                        <span class="material-symbols-outlined text-[14px]">id_card</span>
                        Official Library Registration - A.Y. 2024-2025
                    </div>
                    <h1 class="font-headline-xl text-3xl font-bold tracking-tight text-white md:text-4xl">Library Account Registration</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-on-primary-container md:text-base">
                        Create and connect your official library account with the registrar to obtain your digital library entry barcode, self-checkout authorization, and learning commons access.
                    </p>
                </div>
                <div class="flex items-center gap-3.5 self-start rounded-xl border border-on-primary-container/20 bg-primary/60 p-4 backdrop-blur-sm md:self-auto">
                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-secondary-container text-on-secondary-container shadow-sm">
                        <span class="material-symbols-outlined text-[22px]">contact_support</span>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-white">Need Assistance?</p>
                        <p class="text-xs text-on-primary-container">Library Desk Local 108 - Room 102</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <main class="mx-auto w-full max-w-7xl flex-1 px-4 py-8 lg:px-8">
        @if (session('status'))
            <div class="mb-6 rounded-lg border border-primary-fixed-dim bg-primary-fixed px-4 py-3 text-sm font-semibold text-on-primary-fixed">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-lg border border-error-container bg-error-container px-4 py-3 text-sm text-on-error-container">
                Please review the highlighted registration details and try again.
            </div>
        @endif

        <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
            <div class="space-y-8 lg:col-span-8">
                <section class="rounded-xl border border-outline-variant bg-surface-container-lowest p-6 shadow-sm">
                    <div class="mb-4 flex items-center justify-between border-b border-outline-variant pb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-primary-container text-xs font-bold text-white">1</span>
                            <h2 class="font-headline-sm text-base font-bold text-primary">Select Account Type</h2>
                        </div>
                        <span class="hidden text-[10px] font-bold text-on-surface-variant sm:inline">Used to set borrowing rules and book limits</span>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4" id="roleSelectorGrid">
                        @foreach ([
                            'student' => ['title' => 'Student', 'icon' => 'school', 'desc' => 'Undergraduate, Senior High & Tech-Voc', 'items' => ['5 Books Quota', '14-Day Loan', 'Digital Library Entry Pass']],
                            'faculty' => ['title' => 'Professor / Faculty', 'icon' => 'psychology', 'desc' => 'Faculty, Instructors & Researchers', 'items' => ['15 Books Quota', '60-Day Extended Loan', 'Research Resources Access']],
                            'staff' => ['title' => 'Library Staff', 'icon' => 'local_library', 'desc' => 'Borrowing Officers & Catalogers', 'items' => ['Library Desk Access', 'Barcode Scanner Station', 'Fine Assessment Authority']],
                            'superadmin' => ['title' => 'Super Admin', 'icon' => 'shield_person', 'desc' => 'Approved System Administrators', 'items' => ['User & Role Management', 'System Configuration', 'Audit Log Access']],
                        ] as $role => $details)
                            <label class="role-option relative flex cursor-pointer flex-col rounded-xl border {{ old('patron_role', 'student') === $role ? 'border-primary bg-surface-container-low/50' : 'border-outline-variant bg-surface-container-lowest' }} p-4 transition-all hover:border-primary/60" data-role="{{ $role }}">
                                <input class="sr-only" form="registrationForm" name="patron_role" type="radio" value="{{ $role }}" {{ old('patron_role', 'student') === $role ? 'checked' : '' }}>
                                <div class="mb-2.5 flex items-center justify-between">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg {{ old('patron_role', 'student') === $role ? 'bg-primary-container text-white' : 'bg-surface-container text-primary' }}">
                                        <span class="material-symbols-outlined text-[24px]">{{ $details['icon'] }}</span>
                                    </div>
                                    <span class="role-badge-check {{ old('patron_role', 'student') === $role ? 'flex' : 'hidden' }} h-5 w-5 items-center justify-center rounded-full bg-primary text-white">
                                        <span class="material-symbols-outlined text-[14px]">check</span>
                                    </span>
                                </div>
                                <h3 class="font-headline-sm mb-0.5 text-base font-bold {{ old('patron_role', 'student') === $role ? 'text-primary' : 'text-on-surface' }}">{{ $details['title'] }}</h3>
                                <p class="mb-3 flex-1 text-xs text-on-surface-variant">{{ $details['desc'] }}</p>
                                <div class="space-y-1.5 border-t border-outline-variant/60 pt-2 text-xs">
                                    @foreach ($details['items'] as $item)
                                        <div class="flex items-center gap-1.5 text-on-surface-variant">
                                            <span class="material-symbols-outlined text-[14px]">check_circle</span>
                                            <span>{{ $item }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('patron_role')
                        <p class="mt-3 text-sm text-error">{{ $message }}</p>
                    @enderror
                </section>

                <section class="rounded-xl border border-outline-variant bg-surface-container-lowest p-6 shadow-sm md:p-8">
                    <div class="mb-6 flex items-center justify-between border-b border-outline-variant pb-4">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-primary-container text-xs font-bold text-white">2</span>
                            <div>
                                <h2 class="font-headline-sm text-base font-bold text-primary">Account Registration & School ID Check</h2>
                                <p class="text-xs text-on-surface-variant">Ensure details match your official registrar Certificate of Registration.</p>
                            </div>
                        </div>
                        <span class="hidden items-center gap-1 rounded border border-secondary-container/30 bg-secondary-container/20 px-2.5 py-1 text-[10px] font-semibold text-on-secondary-container md:inline-flex">
                            <span class="material-symbols-outlined text-[14px]">sync</span>
                            School Records Check Active
                        </span>
                    </div>

                    <form class="space-y-6" id="registrationForm" method="POST" action="{{ route('register.store') }}" enctype="multipart/form-data">
                        @csrf
                        <input id="selectedPatronId" name="patron_id" type="hidden" value="{{ old('patron_id') }}">
                        <input id="selectedProgramOrDepartment" name="program_or_department" type="hidden" value="{{ old('program_or_department') }}">

                        <div>
                            <label class="mb-2 block text-xs font-semibold text-on-surface">Full Legal Name <span class="text-error">*</span></label>
                            <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                                <div>
                                    <input class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3.5 py-2.5 text-sm text-on-surface transition-all focus:border-primary focus:ring-2 focus:ring-primary" id="lastNameInput" name="last_name" placeholder="Last Name (e.g., Dela Cruz)" required type="text" value="{{ old('last_name') }}">
                                    <span class="mt-1 block text-[10px] text-on-surface-variant">Surname</span>
                                    @error('last_name')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <input class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3.5 py-2.5 text-sm text-on-surface transition-all focus:border-primary focus:ring-2 focus:ring-primary" id="firstNameInput" name="first_name" placeholder="First Name (e.g., Juan)" required type="text" value="{{ old('first_name') }}">
                                    <span class="mt-1 block text-[10px] text-on-surface-variant">Given name</span>
                                    @error('first_name')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <input class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3.5 py-2.5 text-sm text-on-surface transition-all focus:border-primary focus:ring-2 focus:ring-primary" maxlength="5" name="middle_initial" placeholder="Middle Initial (e.g., M.)" type="text" value="{{ old('middle_initial') }}">
                                    <span class="mt-1 block text-[10px] text-on-surface-variant">Optional</span>
                                    @error('middle_initial')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4 rounded-xl border border-outline-variant bg-surface-container-low/60 p-4" id="dynamicIdentificationFields">
                            <div class="space-y-4" id="studentFieldsBlock">
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div>
                                        <label class="mb-1.5 block text-xs font-semibold text-on-surface">Student ID Number <span class="text-error">*</span></label>
                                        <div class="relative">
                                            <span class="material-symbols-outlined pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-[18px] text-on-surface-variant">badge</span>
                                            <input class="role-id-input w-full rounded-lg border border-outline-variant bg-surface-container-lowest py-2.5 pl-10 pr-3.5 font-mono text-sm text-on-surface transition-all focus:border-primary focus:ring-2 focus:ring-primary" id="studentIdInput" placeholder="2024-10492-OL" required type="text" value="{{ old('patron_id') }}">
                                        </div>
                                        @error('patron_id')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                                    </div>
                                    <div>
                                        <label class="mb-1.5 block text-xs font-semibold text-on-surface">Program / Strand <span class="text-error">*</span></label>
                                        <select class="role-program-input w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3.5 py-2.5 text-sm text-on-surface transition-all focus:border-primary focus:ring-2 focus:ring-primary" id="programSelect">
                                            @foreach (['BS in Computer Science (BSCS)', 'BS in Information Technology (BSIT)', 'BS in Business Administration', 'Senior High School - STEM', 'Senior High School - ABM', 'Technical-Vocational Program'] as $program)
                                                <option value="{{ $program }}" {{ old('program_or_department') === $program ? 'selected' : '' }}>{{ $program }}</option>
                                            @endforeach
                                        </select>
                                        @error('program_or_department')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                                    </div>
                                </div>
                            </div>

                            <div class="hidden space-y-4" id="facultyFieldsBlock">
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div>
                                        <label class="mb-1.5 block text-xs font-semibold text-on-surface">Faculty Employee ID <span class="text-error">*</span></label>
                                        <input class="role-id-input w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3.5 py-2.5 font-mono text-sm text-on-surface transition-all focus:border-primary focus:ring-2 focus:ring-primary" id="facultyIdInput" placeholder="FAC-2024-819" type="text" value="{{ old('patron_id') }}">
                                    </div>
                                    <div>
                                        <label class="mb-1.5 block text-xs font-semibold text-on-surface">Department <span class="text-error">*</span></label>
                                        <select class="role-program-input w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3.5 py-2.5 text-sm text-on-surface transition-all focus:border-primary focus:ring-2 focus:ring-primary" id="facultyDepartmentSelect">
                                            @foreach (['College of Computer Studies', 'College of Business', 'General Education Department', 'Senior High School Department'] as $department)
                                                <option value="{{ $department }}" {{ old('program_or_department') === $department ? 'selected' : '' }}>{{ $department }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="hidden space-y-4" id="staffFieldsBlock">
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div>
                                        <label class="mb-1.5 block text-xs font-semibold text-on-surface">Library Staff ID <span class="text-error">*</span></label>
                                        <input class="role-id-input w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3.5 py-2.5 font-mono text-sm text-on-surface transition-all focus:border-primary focus:ring-2 focus:ring-primary" id="staffIdInput" placeholder="LIB-STAFF-042" type="text" value="{{ old('patron_id') }}">
                                    </div>
                                    <div>
                                        <label class="mb-1.5 block text-xs font-semibold text-on-surface">Access Level <span class="text-error">*</span></label>
                                        <select class="role-program-input w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3.5 py-2.5 text-sm text-on-surface transition-all focus:border-primary focus:ring-2 focus:ring-primary" id="staffAccessSelect">
                                            @foreach (['Library Desk - Level 1', 'Cataloging Office - Level 2', 'Library Administration - Level 3'] as $accessLevel)
                                                <option value="{{ $accessLevel }}" {{ old('program_or_department') === $accessLevel ? 'selected' : '' }}>{{ $accessLevel }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="hidden space-y-4" id="superadminFieldsBlock">
                                <div class="mb-3 flex items-start gap-2 rounded-lg border border-amber-200 bg-amber-50 p-3 text-xs leading-5 text-amber-900">
                                    <span class="material-symbols-outlined text-lg">security</span>
                                    <p>Super Admin accounts require approval from the school’s authorized system owner before access is activated.</p>
                                </div>
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div>
                                        <label class="mb-1.5 block text-xs font-semibold text-on-surface">Administrator ID <span class="text-error">*</span></label>
                                        <input class="role-id-input w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3.5 py-2.5 font-mono text-sm text-on-surface transition-all focus:border-primary focus:ring-2 focus:ring-primary" id="superadminIdInput" placeholder="SYS-ADMIN-001" type="text" value="{{ old('patron_id') }}">
                                    </div>
                                    <div>
                                        <label class="mb-1.5 block text-xs font-semibold text-on-surface">Administrative Assignment <span class="text-error">*</span></label>
                                        <select class="role-program-input w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3.5 py-2.5 text-sm text-on-surface transition-all focus:border-primary focus:ring-2 focus:ring-primary" id="superadminAccessSelect">
                                            @foreach (['System Owner', 'Information Systems Administrator', 'Library System Administrator'] as $assignment)
                                                <option value="{{ $assignment }}" {{ old('program_or_department') === $assignment ? 'selected' : '' }}>{{ $assignment }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold text-on-surface">School Email <span class="text-error">*</span></label>
                                <div class="relative">
                                    <span class="material-symbols-outlined pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-[18px] text-on-surface-variant">mail</span>
                                    <input class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest py-2.5 pl-10 pr-3.5 text-sm text-on-surface transition-all focus:border-primary focus:ring-2 focus:ring-primary" name="email" placeholder="juan.delacruz@clcst.edu.ph" required type="email" value="{{ old('email') }}">
                                </div>
                                @error('email')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold text-on-surface">Mobile Number <span class="text-error">*</span></label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-on-surface-variant">+63</span>
                                    <input class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest py-2.5 pl-12 pr-3.5 font-mono text-sm text-on-surface transition-all focus:border-primary focus:ring-2 focus:ring-primary" name="mobile_number" placeholder="917 123 4567" required type="tel" value="{{ old('mobile_number') }}">
                                </div>
                                @error('mobile_number')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold text-on-surface">Library PIN / Password <span class="text-error">*</span></label>
                                <div class="relative">
                                    <span class="material-symbols-outlined pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-[18px] text-on-surface-variant">key</span>
                                    <input class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest py-2.5 pl-10 pr-10 text-sm text-on-surface transition-all focus:border-primary focus:ring-2 focus:ring-primary" id="pinInput" name="password" placeholder="Minimum 6 characters" required type="password">
                                    <button class="toggle-password absolute inset-y-0 right-0 flex items-center pr-3 text-on-surface-variant hover:text-on-surface" data-target="pinInput" type="button">
                                        <span class="material-symbols-outlined text-[18px]">visibility</span>
                                    </button>
                                </div>
                                @error('password')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold text-on-surface">Confirm Library PIN / Password <span class="text-error">*</span></label>
                                <div class="relative">
                                    <span class="material-symbols-outlined pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-[18px] text-on-surface-variant">verified</span>
                                    <input class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest py-2.5 pl-10 pr-10 text-sm text-on-surface transition-all focus:border-primary focus:ring-2 focus:ring-primary" id="confirmPinInput" name="password_confirmation" placeholder="Re-enter to confirm PIN" required type="password">
                                    <button class="toggle-password absolute inset-y-0 right-0 flex items-center pr-3 text-on-surface-variant hover:text-on-surface" data-target="confirmPinInput" type="button">
                                        <span class="material-symbols-outlined text-[18px]">visibility</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-on-surface">School Document Upload (COR or School ID)</label>
                            <div class="rounded-xl border-2 border-dashed border-outline-variant bg-surface-container-low/40 p-6 text-center transition-colors hover:border-primary/80">
                                <input accept=".pdf,.jpg,.jpeg,.png" class="sr-only" id="fileUpload" name="credential_file" type="file">
                                <label class="flex cursor-pointer flex-col items-center" for="fileUpload">
                                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-surface-container-high text-primary transition-colors">
                                        <span class="material-symbols-outlined text-[28px]">cloud_upload</span>
                                    </div>
                                    <p class="mb-1 font-headline-sm text-base font-semibold text-on-surface">Drag and drop your Certificate of Registration or <span class="text-primary underline">browse files</span></p>
                                    <p class="mb-2 max-w-md text-xs text-on-surface-variant">Official PDF copy or high-resolution photo of validated COR stamp. Accepted formats: PDF, JPG, PNG. Max size: 10MB.</p>
                                    <img alt="Selected credential preview" class="mb-2 hidden max-h-40 rounded-lg border border-outline-variant object-contain shadow-sm" id="selectedFilePreview">
                                    <p class="text-[10px] font-semibold text-on-surface-variant" id="selectedFileName">No file selected</p>
                                </label>
                            </div>
                            @error('credential_file')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="rounded-xl border border-outline-variant bg-surface-container-low p-4">
                            <label class="flex cursor-pointer items-start gap-3">
                                <input class="mt-1 h-4 w-4 rounded border-outline text-primary focus:ring-primary focus:ring-offset-0" name="privacy_consent" required type="checkbox" value="1" {{ old('privacy_consent') ? 'checked' : '' }}>
                                <span class="text-xs leading-relaxed text-on-surface-variant">
                                    I declare that all information entered is true and correct according to official records. I agree to CLCST Library Terms of Use and consent to library account data processing under Republic Act No. 10173.
                                </span>
                            </label>
                            @error('privacy_consent')<p class="mt-2 text-xs text-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="flex flex-col items-center gap-4 pt-2 sm:flex-row">
                            <button class="flex w-full flex-1 items-center justify-center gap-2.5 rounded-lg bg-primary px-8 py-3.5 text-sm font-bold text-on-primary shadow-md transition-all hover:bg-[#0E4723] hover:shadow-lg active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-70 sm:w-auto" id="submitRegistrationButton" type="submit">
                                <span class="material-symbols-outlined text-[20px]">qr_code_2</span>
                                <span id="submitRegistrationText">Submit Registration & Issue Digital Library Pass</span>
                            </button>
                            <button class="w-full rounded-lg border border-outline-variant px-5 py-3.5 text-sm font-semibold text-on-surface transition-all hover:bg-surface-container active:scale-[0.98] sm:w-auto" type="reset">
                                Clear Form
                            </button>
                        </div>
                    </form>
                </section>
            </div>

            <aside class="space-y-6 lg:col-span-4">
                <section class="relative overflow-hidden rounded-2xl border border-outline-variant bg-surface-container-lowest p-5 shadow-md">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[20px] text-primary">badge</span>
                            <span class="font-headline-sm text-base font-bold text-primary">Digital Pass Preview</span>
                        </div>
                        <span class="rounded bg-secondary-container px-2 py-0.5 text-[10px] font-bold uppercase text-on-secondary-container">Preview</span>
                    </div>

                    <div class="relative overflow-hidden rounded-xl border border-[#0E4723]/30 bg-gradient-to-br from-[#135d2e] via-[#0E4723] to-[#00210b] p-5 text-white shadow-lg">
                        <div class="absolute -bottom-8 -right-6 text-8xl font-extrabold text-white/10">CL</div>
                        <div class="relative z-10 flex items-center justify-between border-b border-white/15 pb-3">
                            <div class="flex items-center gap-2.5">
                                @include('partials.celtech-logo', ['class' => 'flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-white text-xs font-extrabold text-primary shadow-sm object-contain bg-white p-0.5'])
                                <div>
                                    <p class="text-[13px] font-bold leading-tight tracking-tight text-white">Central Luzon College</p>
                                    <p class="text-[9px] font-semibold uppercase tracking-wider text-secondary-fixed">Learning Commons Digital ID</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-mono text-[9px] text-white/70">VALID THRU</p>
                                <p class="text-[11px] font-bold text-secondary-fixed">JUL 2025</p>
                            </div>
                        </div>

                        <div class="relative z-10 pb-2 pt-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[10px] font-medium uppercase tracking-wider text-white/70">Name</p>
                                    <p class="font-headline-md text-xl font-bold tracking-tight text-white" id="previewCardName">JUAN DELA CRUZ</p>
                                    <p class="mt-0.5 text-xs text-on-primary-container" id="previewCardProgram">BS in Computer Science (BSCS)</p>
                                </div>
                                <span class="inline-block rounded border border-secondary bg-secondary-container px-2 py-0.5 text-[10px] font-bold text-on-secondary-container" id="previewCardRoleBadge">STUDENT</span>
                            </div>

                            <div class="mt-3 flex items-center justify-between">
                                <div>
                                    <p class="text-[9px] uppercase tracking-wider text-white/70">ID Barcode Serial</p>
                                    <p class="font-mono text-sm font-bold tracking-wider text-white" id="previewCardId">2024-10492-OL</p>
                                </div>
                                <div class="flex items-center gap-1 rounded bg-white/10 px-2 py-1 text-[11px] text-primary-fixed">
                                    <span class="material-symbols-outlined text-[14px]">check_circle</span>
                                    Entry Ready
                                </div>
                            </div>
                        </div>

                        <div class="relative z-10 mt-3 rounded-lg bg-white p-3 text-center shadow-inner">
                            <div class="flex h-10 items-center justify-center gap-[2px] overflow-hidden">
                                @foreach ([2,1,3,1,4,1,2,3,1,2,1,4,2,1,3,2,1,4,2,1,3,2,1,4,1,2,3,1,4,2,1,3] as $bar)
                                    <span class="inline-block h-10 bg-black" style="width: {{ $bar }}px"></span>
                                @endforeach
                            </div>
                            <p class="mt-1 font-mono text-[11px] font-bold tracking-[0.25em] text-gray-800" id="barcodeText">*2024-10492-OL*</p>
                            <p class="mt-0.5 text-[9px] text-gray-500">Hold phone under optical library entry scanner at CLCST Library Gate</p>
                        </div>
                    </div>
                </section>

                <section class="space-y-3.5 rounded-xl border border-outline-variant bg-surface-container-lowest p-5 shadow-sm">
                    <div class="flex items-center gap-2 border-b border-outline-variant pb-2.5">
                        <span class="material-symbols-outlined text-[20px] text-primary">checklist</span>
                        <h3 class="font-headline-sm text-base font-bold text-primary">Registration Verification Checklist</h3>
                    </div>
                    @foreach ([
                        ['School Information', 'Fill in legal name, official ID, program, and institutional email address.'],
                        ['School Record Check', 'Your uploaded COR or ID is queued for matching against official records.'],
                        ['Digital Pass Activation', 'After approval, use your digital barcode for library entry, Book Catalog, and self-checkout.'],
                    ] as $index => $item)
                        <div class="flex items-start gap-3 text-sm">
                            <div class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full {{ $index === 2 ? 'bg-secondary-container text-on-secondary-container' : 'bg-primary-container text-white' }} text-xs font-bold">{{ $index + 1 }}</div>
                            <div>
                                <strong class="block font-semibold text-on-surface">{{ $item[0] }}</strong>
                                <p class="text-xs text-on-surface-variant">{{ $item[1] }}</p>
                            </div>
                        </div>
                    @endforeach
                </section>

                <section class="rounded-xl border border-outline-variant bg-surface-container-low p-5">
                    <div class="mb-2 flex items-center gap-2 font-bold text-primary">
                        <span class="material-symbols-outlined text-[20px]">support_agent</span>
                        <h4 class="font-headline-sm text-base">Library Desk Questions</h4>
                    </div>
                    <p class="mb-4 text-xs text-on-surface-variant">If your ID or COR is not validating automatically, contact technical support.</p>
                    <div class="space-y-2 text-xs text-on-surface">
                        <p class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px] text-primary">call</span>(047) 222-2432 local 108</p>
                        <p class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px] text-primary">mail</span><a class="font-medium text-primary hover:underline" href="mailto:library@clcst.edu.ph">library@clcst.edu.ph</a></p>
                        <p class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px] text-primary">location_on</span>Room 102, Main Library, CLCST Campus</p>
                        <p class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px] text-primary">schedule</span>Mon - Fri: 7:30 AM - 6:30 PM</p>
                    </div>
                </section>
            </aside>
        </div>
    </main>

    <footer class="mt-12 border-t border-outline-variant bg-surface-container-lowest px-4 py-8 lg:px-8">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-6 md:flex-row">
            <div class="flex items-center gap-3">
                @include('partials.celtech-logo', ['class' => 'flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-primary text-xs font-extrabold text-white ring-1 ring-outline-variant object-contain bg-white p-0.5'])
                <div>
                    <p class="font-headline-sm text-sm font-bold text-primary">Central Luzon College of Science and Technology</p>
                    <p class="text-xs text-on-surface-variant">Learning Resource Center & Library Management System 2024-2025.</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-4 text-xs font-medium text-on-surface-variant">
                <a class="transition-colors hover:text-primary" href="#">Privacy Policy</a>
                <span class="text-outline-variant">|</span>
                <a class="transition-colors hover:text-primary" href="#">Terms of Service</a>
                <span class="text-outline-variant">|</span>
                <a class="transition-colors hover:text-primary" href="#">Book Search</a>
            </div>
        </div>
    </footer>

    <script src="{{ asset('js/register.js') }}?v={{ filemtime(public_path('js/register.js')) }}"></script>
</body>
</html>
