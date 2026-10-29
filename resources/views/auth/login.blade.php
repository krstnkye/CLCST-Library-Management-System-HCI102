<!DOCTYPE html>
<html class="h-full" lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CLCST Library Account Sign In</title>
    @include('partials.favicon')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Material+Symbols+Outlined&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <style>body { font-family: Inter, sans-serif; } .material-symbols-outlined { line-height: 1; vertical-align: middle; }</style>
    @include('partials.ui-motion')
</head>
<body class="clcst-portal library-login">
<div class="login-shell">
    <aside class="login-story" aria-label="CLCST School Library">
        <a class="login-brand" href="{{ route('login') }}">
            @include('partials.celtech-logo', ['class' => 'rounded-full'])
            <div><strong>CLCST School Library</strong><span>Central Luzon College of Science and Technology</span></div>
        </a>
        <div class="login-story-copy">
            <p class="login-eyebrow">Read. Discover. Belong.</p>
            <h2>Find your<br>next <em>chapter.</em></h2>
            <p>From your first reference to your next big idea. Your campus library is here for every part of the journey.</p>
        </div>
        <div class="library-shelf" aria-hidden="true">
            <span>Literature</span><span>Science &amp; Discovery</span><span>The Arts</span><span>Technology</span><span>History</span><span>Ideas</span>
        </div>
        <div class="login-story-footer"><span>A space for curious minds.</span><span>CLCST · Main Library</span></div>
    </aside>
    <main class="login-content">
        <div class="login-content-top">New to the library?<a href="{{ route('register') }}">Create an account <span class="material-symbols-outlined text-base" aria-hidden="true">arrow_outward</span></a></div>
        <section class="login-panel" aria-labelledby="signin-heading">
            <p class="login-eyebrow">Your campus library, connected</p>
            <h1 id="signin-heading">Welcome back.</h1>
            <p class="login-intro">Sign in to find books, manage your loans, and pick up where you left off.</p>
            @if (session('status'))
                <div class="mt-5 rounded-md border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-900" role="status">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="mt-5 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-800" role="alert">
                    <p class="font-semibold">We couldn't sign you in.</p>
                    <ul class="mt-1 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            @php
                $roles = [
                    'student' => ['school', 'Student'],
                    'faculty' => ['co_present', 'Faculty'],
                    'librarian' => ['local_library', 'Librarian'],
                    'admin' => ['admin_panel_settings', 'Administrator'],
                    'superadmin' => ['security', 'Super Administrator'],
                ];
                $selectedRole = old('role', 'student');
            @endphp
            <p class="login-role-label" id="role-choice-label">Choose your account type</p>
            <div class="login-roles" role="group" aria-labelledby="role-choice-label">
                @foreach ($roles as $value => [$icon, $label])
                    <button type="button" id="role-{{ $value }}" data-role="{{ $value }}" class="role-card" aria-pressed="{{ $selectedRole === $value ? 'true' : 'false' }}"><span class="material-symbols-outlined" aria-hidden="true">{{ $icon }}</span>{{ $label }}</button>
                @endforeach
            </div>
            <div class="login-role-help" aria-live="polite">
                <span id="active-role-tag" class="sr-only">STUDENT PORTAL</span><span id="active-role-icon" class="sr-only" aria-hidden="true">school</span>
                <p id="active-role-description">Access your loans, reservations, notifications, and library account.</p>
            </div>
            <form method="POST" action="{{ route('login.authenticate') }}" class="login-form">
                @csrf
                <input type="hidden" id="role-input" name="role" value="{{ old('role', 'student') }}">
                <div>
                    <label id="identifier-label" for="identifier">Student ID or school email</label>
                    <div class="login-field"><span class="material-symbols-outlined" aria-hidden="true">badge</span><input id="identifier" name="identifier" value="{{ old('identifier') }}" required autocomplete="username" maxlength="150" placeholder="Example: STU-2026-001"></div>
                </div>
                <div>
                    <div class="flex items-center justify-between gap-3"><label for="password">Password</label><a class="text-[11px] font-semibold text-emerald-800 hover:underline" href="mailto:library@clcst.edu.ph?subject=Library%20password%20assistance">Forgot password?</a></div>
                    <div class="login-field">
                        <span class="material-symbols-outlined" aria-hidden="true">lock</span>
                        <input id="password" name="password" required type="password" autocomplete="current-password" placeholder="Enter your password">
                        <button id="password-toggle" class="login-password-toggle" type="button" aria-label="Show password" title="Show password"><span id="password-toggle-icon" class="material-symbols-outlined text-xl" aria-hidden="true">visibility</span></button>
                    </div>
                </div>
                <label class="flex cursor-pointer items-start gap-2.5 leading-5 text-slate-600"><input class="mt-0.5 rounded border-slate-300 text-emerald-800 focus:ring-emerald-700" type="checkbox" name="remember" value="1" @checked(old('remember'))><span>Keep me signed in on this private device</span></label>
                <button class="login-submit" type="submit"><span id="submit-label">Sign In as Student</span><span class="material-symbols-outlined text-xl" aria-hidden="true">arrow_forward</span></button>
            </form>
            <div class="login-assistance"><span class="material-symbols-outlined" aria-hidden="true">support_agent</span><p>Having trouble getting in?<br><a href="mailto:library@clcst.edu.ph">Contact the library</a> for account or approval assistance.</p></div>
        </section>
        <footer class="login-footer"><span>CLCST Library Management System</span><span>HCI102 Project</span></footer>
    </main>
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
