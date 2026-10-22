<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In - CLCST Library Management System</title>
    @include('partials.favicon')
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Material+Symbols+Outlined&display=swap" rel="stylesheet">
    <style>
        body{font-family:Inter,sans-serif}.material-symbols-outlined{font-variation-settings:'FILL' 0,'wght' 450,'GRAD' 0,'opsz' 24}
        .role-card[aria-pressed="true"]{border-color:#065f46;background:#ecfdf5;box-shadow:0 0 0 1px #065f46}
        .role-card[aria-pressed="true"] .role-icon{background:#065f46;color:#fff}
        .role-card[aria-pressed="true"] .role-check{display:inline-flex}
    </style>
    @include('partials.ui-motion')
</head>
<body class="min-h-full text-slate-950">
<div class="flex min-h-screen flex-col">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
            <div class="flex min-w-0 items-center gap-3">
                @include('partials.celtech-logo', ['class' => 'h-12 w-12 shrink-0 rounded-full object-contain'])
                <div class="min-w-0"><p class="truncate font-extrabold text-emerald-950">CLCST Library Portal</p><p class="truncate text-xs text-slate-600">Central Luzon College of Science and Technology</p></div>
            </div>
            <div class="hidden items-center gap-3 md:flex">
                <div class="flex items-center gap-2 rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700"><span class="material-symbols-outlined text-base text-emerald-800">lock</span><span><strong class="block">Secure connection</strong>Encrypted sign-in</span></div>
                <div class="flex items-center gap-2 rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700"><span class="material-symbols-outlined text-base text-emerald-800">verified_user</span><span><strong class="block">Data protected</strong>RA 10173 compliant</span></div>
            </div>
        </div>
    </header>

    <main class="flex-1">
        <section class="relative overflow-hidden bg-emerald-950 text-white">
            <div class="pointer-events-none absolute right-8 top-1/2 -translate-y-1/2 text-8xl font-extrabold text-white/5 sm:text-9xl">CLCST</div>
            <div class="relative mx-auto max-w-7xl px-4 py-7 sm:px-6 sm:py-9 lg:px-8">
                <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-emerald-300/30 bg-white/10 px-3 py-1 text-xs font-bold text-emerald-100"><span class="material-symbols-outlined text-sm">vpn_key</span>SECURE LIBRARY ACCESS</div>
                <h1 class="max-w-3xl text-3xl font-extrabold leading-tight sm:text-4xl">Unified Academic Access Gateway</h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-emerald-100 sm:text-base">Sign in with your school ID or your @clcst.edu.ph email. You will be taken to the portal that matches your account.</p>
            </div>
        </section>

        <div class="mx-auto max-w-7xl px-4 py-7 sm:px-6 lg:px-8">
            @if(session('status'))
                <div class="mb-6 flex items-start gap-3 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-950" role="status"><span class="material-symbols-outlined text-xl">check_circle</span><span>{{ session('status') }}</span></div>
            @endif
            @if($errors->any())
                <div class="mb-6 flex items-start gap-3 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-900" role="alert"><span class="material-symbols-outlined text-xl">error</span><span>{{ $errors->first() }}</span></div>
            @endif

            <div class="grid items-start gap-7 lg:grid-cols-[minmax(0,1fr)_360px]">
                <section aria-labelledby="signin-heading">
                    <div class="mb-3 flex flex-wrap items-end justify-between gap-2">
                        <div><h2 id="signin-heading" class="flex items-center gap-2 text-lg font-bold"><span class="material-symbols-outlined text-emerald-800">switch_account</span>Choose your account type</h2><p class="mt-1 text-sm text-slate-600">Optional. It only changes the hints below; we open the portal saved on your account.</p></div>
                    </div>
                    @php
                        $roles = [
                            'student' => ['school', 'Student', 'Borrow and reserve books'],
                            'faculty' => ['co_present', 'Faculty', 'Teaching and research access'],
                            'librarian' => ['local_library', 'Librarian', 'Daily library operations'],
                            'admin' => ['admin_panel_settings', 'Administrator', 'Records and reports'],
                            'superadmin' => ['security', 'Super Administrator', 'Roles and system security'],
                        ];
                        $selectedRole = old('role', 'student');
                        $chosenRole = old('role', '');
                    @endphp
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 sm:gap-3 xl:grid-cols-5" aria-label="Account type" role="group">
                        @foreach($roles as $value => [$icon, $label, $description])
                            <button id="role-{{ $value }}" class="role-card relative flex flex-col rounded-md border-2 sm:min-h-28 border-slate-200 bg-white p-3 text-left transition hover:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:ring-offset-2" type="button" data-role="{{ $value }}" aria-label="{{ $label }}: {{ $description }}" aria-pressed="{{ $selectedRole === $value ? 'true' : 'false' }}">
                                <span class="role-check absolute right-3 top-3 hidden h-5 w-5 items-center justify-center rounded-full bg-emerald-800 text-xs font-bold text-white" aria-hidden="true">&#10003;</span>
                                <span class="role-icon flex h-9 w-9 items-center justify-center rounded-md bg-emerald-50 text-emerald-800"><span class="material-symbols-outlined text-xl">{{ $icon }}</span></span>
                                <span class="mt-2 text-sm font-bold sm:mt-3">{{ $label }}</span><span class="mt-1 hidden text-xs leading-4 text-slate-600 sm:block">{{ $description }}</span>
                            </button>
                        @endforeach
                    </div>

                    <div class="mt-6 rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
                        <div class="flex items-start justify-between gap-4 border-b border-slate-200 pb-4">
                            <div><span id="active-role-tag" class="inline-flex rounded-md bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-900">STUDENT PORTAL</span><p id="active-role-description" class="mt-2 text-sm leading-6 text-slate-600">Access your loans, reservations, notifications, and library account.</p></div>
                            <span id="active-role-icon" class="material-symbols-outlined text-3xl text-emerald-800" aria-hidden="true">school</span>
                        </div>
                        <form class="mt-6 space-y-5" method="POST" action="{{ route('login.authenticate') }}">
                            @csrf
                            <input id="role-input" type="hidden" name="role" value="{{ $chosenRole }}">
                            <div>
                                <label id="identifier-label" for="identifier" class="mb-1.5 block text-sm font-semibold">Student ID or school email</label>
                                <div class="relative"><span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xl text-slate-500">badge</span><input id="identifier" name="identifier" value="{{ old('identifier') }}" required autocomplete="username" maxlength="150" class="w-full rounded-md border-slate-300 py-3 pl-11 pr-4 text-sm focus:border-emerald-700 focus:ring-emerald-700" placeholder="Example: STU-2026-001"></div>
                            </div>
                            <div>
                                <div class="mb-1.5 flex items-center justify-between gap-3"><label for="password" class="block text-sm font-semibold">Password</label><a class="text-xs font-bold text-emerald-800 hover:underline" href="mailto:library@clcst.edu.ph?subject=Library%20password%20assistance">Forgot your password?</a></div>
                                <div class="relative">
                                    <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xl text-slate-500">key</span>
                                    <input id="password" name="password" required type="password" autocomplete="current-password" class="w-full rounded-md border-slate-300 py-3 pl-11 pr-12 text-sm focus:border-emerald-700 focus:ring-emerald-700" placeholder="Enter your password">
                                    <button id="password-toggle" class="absolute right-1 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-md text-slate-500 hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-700" type="button" aria-label="Show password" title="Show password"><span id="password-toggle-icon" class="material-symbols-outlined text-xl">visibility</span></button>
                                </div>
                            </div>
                            <label class="flex cursor-pointer items-start gap-2.5 text-sm leading-5 text-slate-700"><input class="mt-0.5 rounded border-slate-300 text-emerald-800 focus:ring-emerald-700" type="checkbox" name="remember" value="1" @checked(old('remember'))><span>Keep me signed in on this private device</span></label>
                            <button class="flex w-full items-center justify-center gap-2 rounded-md bg-emerald-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:ring-offset-2" type="submit"><span class="material-symbols-outlined text-xl">login</span><span id="submit-label">Sign In as Student</span></button>
                            <div class="flex flex-col items-center justify-between gap-2 border-t border-slate-200 pt-5 text-sm sm:flex-row"><span class="text-slate-600">First time using the library portal?</span><a class="flex items-center gap-1 font-bold text-emerald-800 hover:underline" href="{{ route('register') }}"><span class="material-symbols-outlined text-lg">how_to_reg</span>Create an account</a></div>
                        </form>
                    </div>
                </section>

                <aside class="space-y-5" aria-label="Library information">
                    <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 class="flex items-center gap-2 font-bold text-emerald-950"><span class="material-symbols-outlined text-emerald-800">verified_user</span>Access is based on your role</h2>
                        <div class="mt-4 space-y-4 text-sm">
                            <div class="flex gap-3"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-800 text-xs font-bold text-white">1</span><p><strong class="block">Use your school account</strong><span class="text-slate-600">Only @clcst.edu.ph emails or school IDs are accepted.</span></p></div>
                            <div class="flex gap-3"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-800 text-xs font-bold text-white">2</span><p><strong class="block">Enter your credentials</strong><span class="text-slate-600">Enter your school ID or email and password.</span></p></div>
                            <div class="flex gap-3"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-amber-400 text-xs font-bold">3</span><p><strong class="block">Open your portal</strong><span class="text-slate-600">We open the dashboard for the role saved on your account.</span></p></div>
                        </div>
                    </section>
                    <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between border-b border-slate-200 pb-3"><h2 class="flex items-center gap-2 font-bold text-emerald-950"><span class="material-symbols-outlined text-emerald-800">schedule</span>Library hours</h2><span class="rounded-full bg-emerald-100 px-2 py-1 text-xs font-bold text-emerald-800">Main campus</span></div>
                        <dl class="mt-4 space-y-3 text-sm"><div class="flex justify-between gap-4"><dt class="text-slate-600">Monday to Friday</dt><dd class="font-semibold">8:00 AM - 8:00 PM</dd></div><div class="flex justify-between gap-4"><dt class="text-slate-600">Saturday</dt><dd class="font-semibold">8:00 AM - 5:00 PM</dd></div><div class="flex justify-between gap-4"><dt class="text-slate-600">Sunday</dt><dd class="font-semibold">Closed</dd></div></dl>
                    </section>
                    <section class="rounded-lg border border-emerald-200 bg-emerald-50 p-5">
                        <h2 class="flex items-center gap-2 font-bold text-emerald-950"><span class="material-symbols-outlined">support_agent</span>Need help signing in?</h2>
                        <p class="mt-2 text-sm leading-6 text-emerald-950">Contact the library if your account is pending, your role is incorrect, or you cannot remember your password.</p>
                        <div class="mt-3 space-y-2 text-sm font-medium text-emerald-950"><a class="flex items-center gap-2 hover:underline" href="mailto:library@clcst.edu.ph"><span class="material-symbols-outlined text-lg">mail</span>library@clcst.edu.ph</a><p class="flex items-center gap-2"><span class="material-symbols-outlined text-lg">call</span>(047) 222-2432 local 108</p></div>
                    </section>
                </aside>
            </div>
        </div>
    </main>
    <footer class="border-t border-slate-200 bg-white"><div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-2 px-4 py-5 text-center text-xs text-slate-600 sm:px-6 md:flex-row md:text-left lg:px-8"><p>CLCST Library Management System - HCI102 Project</p><p>Account verification and role-based access portal</p></div></footer>
</div>

<script>
const roleDetails={
student:{tag:'STUDENT PORTAL',icon:'school',description:'Access your loans, reservations, notifications, and library account.',identifier:'Student ID or school email',placeholder:'Example: STU-2026-001',submit:'Sign In as Student'},
faculty:{tag:'FACULTY PORTAL',icon:'co_present',description:'Access teaching resources, borrowing services, course reserves, and academic requests.',identifier:'Employee ID or school email',placeholder:'Example: FAC-2026-001',submit:'Sign In as Faculty'},
librarian:{tag:'LIBRARIAN PORTAL',icon:'local_library',description:'Manage books, circulation, reservations, user verification, and library services.',identifier:'Librarian ID or school email',placeholder:'Example: LIB-2026-001',submit:'Sign In as Librarian'},
admin:{tag:'ADMINISTRATOR PORTAL',icon:'admin_panel_settings',description:'Manage library records, staff workflows, reports, and approved administrative settings.',identifier:'Administrator ID or school email',placeholder:'Example: ADM-2026-001',submit:'Sign In as Administrator'},
superadmin:{tag:'SUPER ADMINISTRATOR PORTAL',icon:'security',description:'Manage roles, permissions, security settings, audits, and system-wide configuration.',identifier:'System administrator ID or school email',placeholder:'Example: SYS-2026-001',submit:'Sign In as Super Administrator'}};
const roleInput=document.getElementById('role-input');const roleCards=document.querySelectorAll('[data-role]');
function selectRole(role,picked){const d=roleDetails[role]||roleDetails.student;if(picked){roleInput.value=role}roleCards.forEach(c=>c.setAttribute('aria-pressed',String(c.dataset.role===role)));document.getElementById('active-role-tag').textContent=d.tag;document.getElementById('active-role-icon').textContent=d.icon;document.getElementById('active-role-description').textContent=d.description;document.getElementById('identifier-label').textContent=d.identifier;document.getElementById('identifier').placeholder=d.placeholder;document.getElementById('submit-label').textContent=d.submit}
roleCards.forEach(c=>c.addEventListener('click',()=>selectRole(c.dataset.role,true)));selectRole(roleInput.value||'student',false);
document.getElementById('password-toggle').addEventListener('click',function(){const p=document.getElementById('password');const show=p.type==='password';p.type=show?'text':'password';document.getElementById('password-toggle-icon').textContent=show?'visibility_off':'visibility';this.setAttribute('aria-label',show?'Hide password':'Show password');this.setAttribute('title',show?'Hide password':'Show password')});
</script>
</body>
</html>
