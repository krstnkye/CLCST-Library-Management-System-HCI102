<!DOCTYPE html>
<html class="h-full bg-[#F7F6F3]" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Super Admin') - CLCST Library Management System</title>
    @include('partials.favicon')
    <link href="https://fonts.googleapis.com" rel="preconnect"><link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['Inter', 'sans-serif'], display: ['Plus Jakarta Sans', 'sans-serif'] } } } };</script>
    <style>.material-symbols-outlined{font-variation-settings:'FILL' 0,'wght' 450,'GRAD' 0,'opsz' 24;line-height:1}.custom-scrollbar::-webkit-scrollbar{width:6px;height:6px}.custom-scrollbar::-webkit-scrollbar-thumb{background:#b9c7be;border-radius:999px}</style>
    @include('partials.ui-motion')
</head>
<body class="clcst-portal clcst-role-superadmin min-h-full bg-[#F7F6F3] font-sans text-[#211F1C] antialiased">
    <header class="clcst-topbar fixed inset-x-0 top-0 z-40 flex h-16 items-center justify-between border-b border-[#E0DCD3] bg-white px-4 shadow-sm md:px-6">
        <div class="flex min-w-0 items-center gap-3">
            <button aria-label="Open navigation" class="rounded-md p-2 text-[#201F1C] hover:bg-[#F3F1ED] md:hidden" id="admin-menu-button" type="button"><span class="material-symbols-outlined">menu</span></button>
            <a class="flex min-w-0 items-center gap-3" href="{{ route('superadmin.dashboard') }}">@include('partials.celtech-logo', ['class' => 'h-10 w-10 shrink-0 rounded-full object-contain'])<div class="min-w-0"><p class="truncate font-display text-sm font-extrabold text-[#284A30] sm:text-base">CLCST Library Management System</p><p class="truncate text-[11px] font-medium text-[#787266]">Super Admin Control Center</p></div></a>
        </div>
        <form class="mx-6 hidden max-w-xl flex-1 lg:block" method="GET" action="{{ route('management.accounts.index') }}" role="search"><label class="relative block"><span class="sr-only">Search accounts</span><span class="material-symbols-outlined pointer-events-none absolute left-3 top-2.5 text-xl text-[#7E786B]">search</span><input class="w-full rounded-lg border-[#D6D2CB] bg-[#F9F8F7] py-2 pl-10 pr-3 text-sm focus:border-[#284A30] focus:ring-[#284A30]" name="q" placeholder="Search accounts by name, email, or ID" type="search"></label></form>
        <div class="flex items-center gap-2"><a aria-label="Security alerts" class="relative rounded-lg p-2 text-[#524E45] hover:bg-[#F3F1ED]" href="{{ route('superadmin.section', 'security') }}"><span class="material-symbols-outlined">notifications</span></a><div class="hidden text-right sm:block"><p class="text-xs font-bold">{{ auth()->user()->name }}</p><p class="text-[11px] text-[#787266]">Super Administrator</p></div><a aria-label="Open profile" class="overflow-hidden flex h-9 w-9 items-center justify-center rounded-full bg-[#201F1C] text-xs font-bold text-white" href="{{ route('superadmin.section', 'profile') }}">@include('partials.avatar', ['user' => auth()->user()])</a></div>
    </header>

    @include('partials.superadmin-sidebar', ['activePage' => $activePage])
    <div class="fixed inset-0 z-20 hidden bg-black/30 md:hidden" id="admin-menu-overlay"></div>
    <main class="clcst-main min-h-screen pt-16 md:ml-72"><div class="mx-auto max-w-[1600px] p-4 md:p-6 lg:p-8">@if (session('status') || $errors->any())<div class="mb-5 space-y-3">@include('partials.flash')</div>@endif @yield('content')</div></main>
    <script>(()=>{const button=document.getElementById('admin-menu-button');const sidebar=document.getElementById('admin-sidebar');const overlay=document.getElementById('admin-menu-overlay');if(!button||!sidebar||!overlay)return;const toggle=()=>{sidebar.classList.toggle('hidden');sidebar.classList.toggle('flex');overlay.classList.toggle('hidden')};button.addEventListener('click',toggle);overlay.addEventListener('click',toggle)})();</script>
</body>
</html>
