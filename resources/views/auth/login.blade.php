<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In - CLCST Library Management System</title>
    @include('partials.favicon')
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Material+Symbols+Outlined&display=swap" rel="stylesheet">
    <style>body{font-family:Inter,sans-serif}.material-symbols-outlined{font-variation-settings:'FILL' 0,'wght' 450,'GRAD' 0,'opsz' 24}</style>
</head>
<body class="min-h-full text-slate-900">
<header class="border-b border-slate-200 bg-white"><div class="mx-auto flex max-w-6xl items-center gap-3 px-4 py-4">@include('partials.celtech-logo', ['class'=>'h-11 w-11 rounded-full object-contain'])<div><p class="font-bold text-emerald-900">CLCST Library Management System</p><p class="text-xs text-slate-500">Secure library access</p></div></div></header>
<main class="mx-auto grid max-w-6xl gap-8 px-4 py-10 lg:grid-cols-[1fr_440px] lg:items-start">
    <section class="pt-4"><p class="text-sm font-bold uppercase tracking-wide text-emerald-700">HCI102 Project</p><h1 class="mt-2 max-w-xl text-3xl font-extrabold leading-tight sm:text-4xl">One secure account for every library role.</h1><p class="mt-4 max-w-2xl text-base leading-7 text-slate-600">Students and faculty can manage borrowing. Librarians handle daily operations. Administrators manage records and reports. Super administrators control roles and system security.</p><div class="mt-8 grid gap-3 sm:grid-cols-3"><div class="rounded-md border border-slate-200 bg-white p-4"><span class="material-symbols-outlined text-emerald-800">verified_user</span><h2 class="mt-2 font-bold">Role protected</h2><p class="mt-1 text-sm text-slate-600">Your saved account role controls access.</p></div><div class="rounded-md border border-slate-200 bg-white p-4"><span class="material-symbols-outlined text-emerald-800">lock</span><h2 class="mt-2 font-bold">Secure sign-in</h2><p class="mt-1 text-sm text-slate-600">Passwords are hashed and attempts are limited.</p></div><div class="rounded-md border border-slate-200 bg-white p-4"><span class="material-symbols-outlined text-emerald-800">history</span><h2 class="mt-2 font-bold">Activity recorded</h2><p class="mt-1 text-sm text-slate-600">Important security actions are logged.</p></div></div></section>
    <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm sm:p-8"><h2 class="text-2xl font-bold">Sign In</h2><p class="mt-1 text-sm text-slate-600">Use your school ID or registered email.</p>
        @if(session('status'))<div class="mt-5 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900">{{ session('status') }}</div>@endif
        @if($errors->any())<div class="mt-5 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $errors->first() }}</div>@endif
        <form class="mt-6 space-y-5" method="POST" action="{{ route('login.authenticate') }}">@csrf
            <div><label for="role" class="mb-1.5 block text-sm font-semibold">Account type</label><select id="role" name="role" required class="w-full rounded-md border-slate-300 text-sm focus:border-emerald-700 focus:ring-emerald-700"><option value="student" @selected(old('role')==='student')>Student</option><option value="faculty" @selected(old('role')==='faculty')>Faculty or teaching staff</option><option value="librarian" @selected(old('role')==='librarian')>Librarian</option><option value="admin" @selected(old('role')==='admin')>Administrator</option><option value="superadmin" @selected(old('role')==='superadmin')>Super administrator</option></select></div>
            <div><label for="identifier" class="mb-1.5 block text-sm font-semibold">School ID or email</label><input id="identifier" name="identifier" value="{{ old('identifier') }}" required autocomplete="username" class="w-full rounded-md border-slate-300 text-sm focus:border-emerald-700 focus:ring-emerald-700" placeholder="Example: STU-2026-001" maxlength="150"></div>
            <div><label for="password" class="mb-1.5 block text-sm font-semibold">Password</label><input id="password" name="password" required type="password" autocomplete="current-password" class="w-full rounded-md border-slate-300 text-sm focus:border-emerald-700 focus:ring-emerald-700" placeholder="Enter your password"></div>
            <label class="flex items-center gap-2 text-sm text-slate-700"><input class="rounded border-slate-300 text-emerald-800 focus:ring-emerald-700" type="checkbox" name="remember" value="1">Keep me signed in on this private device</label>
            <button class="flex w-full items-center justify-center gap-2 rounded-md bg-emerald-900 px-4 py-3 text-sm font-bold text-white hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:ring-offset-2" type="submit"><span class="material-symbols-outlined text-lg">login</span>Sign In</button>
        </form>
        <div class="mt-6 border-t border-slate-200 pt-5 text-center text-sm"><span class="text-slate-600">Need a library account?</span> <a class="font-bold text-emerald-800 hover:underline" href="{{ route('register') }}">Register here</a></div>
    </section>
</main>
</body></html>
