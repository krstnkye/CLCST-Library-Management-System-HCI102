<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Administrator') - CLCST Library</title>
    @include('partials.favicon')
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Material+Symbols+Outlined&display=swap" rel="stylesheet">
    <style>body{font-family:Inter,sans-serif}.material-symbols-outlined{font-variation-settings:'FILL' 0,'wght' 450,'GRAD' 0,'opsz' 24}</style>
    @include('partials.ui-motion')
</head>
<body class="clcst-portal clcst-role-admin min-h-full text-slate-900">
    <header class="clcst-topbar fixed inset-x-0 top-0 z-40 flex h-16 items-center justify-between border-b border-slate-200 bg-white px-4 md:px-6">
        <div class="flex items-center gap-3">
            <button id="administrator-menu-button" class="rounded-md p-2 hover:bg-slate-100 md:hidden" aria-label="Open navigation" type="button"><span class="material-symbols-outlined">menu</span></button>
            @include('partials.celtech-logo', ['class' => 'h-10 w-10 rounded-full object-contain'])
            <div><p class="font-bold text-emerald-900">CLCST Library</p><p class="text-xs text-slate-500">Administrator workspace</p></div>
        </div>
        <div class="flex items-center gap-3"><div class="hidden text-right sm:block"><p class="text-sm font-semibold">{{ auth()->user()->name }}</p><p class="text-xs text-slate-500">Administrator</p></div><span class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-900 text-xs font-bold text-white">{{ auth()->user()->initials() }}</span></div>
    </header>
    @include('partials.admin-sidebar', ['activePage' => $activePage])
    <div id="administrator-menu-overlay" class="fixed inset-0 z-20 hidden bg-black/30 md:hidden"></div>
    <main id="administrator-main" class="clcst-main min-h-screen pt-16 md:ml-64"><div class="mx-auto max-w-7xl p-4 md:p-7">
        @if (session('status') || $errors->any())<div class="mb-5 space-y-3">@include('partials.flash')</div>@endif
        @yield('content')
    </div></main>
    <script>
        (() => { const button=document.getElementById('administrator-menu-button'), sidebar=document.getElementById('administrator-sidebar'), overlay=document.getElementById('administrator-menu-overlay'); if(!button||!sidebar||!overlay)return; const toggle=()=>{sidebar.classList.toggle('hidden');sidebar.classList.toggle('flex');overlay.classList.toggle('hidden')}; button.addEventListener('click',toggle);overlay.addEventListener('click',toggle); })();
    </script>
</body>
</html>
