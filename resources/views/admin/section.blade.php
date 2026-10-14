@extends('admin.layout')
@section('title', $page['title'])
@section('content')
    <section class="flex flex-col justify-between gap-4 border-b border-[#D5DED8] pb-5 lg:flex-row lg:items-end">
        <div><a class="mb-3 inline-flex items-center gap-1 text-xs font-bold text-[#0F5C2E] hover:underline" href="{{ route('superadmin.dashboard') }}"><span class="material-symbols-outlined text-base">arrow_back</span>Dashboard</a><div class="flex items-center gap-3"><span class="material-symbols-outlined flex h-11 w-11 items-center justify-center rounded-lg bg-[#E8EEE9] text-[#17251D]">{{ $page['icon'] }}</span><div><h1 class="font-display text-2xl font-extrabold md:text-3xl">{{ $page['title'] }}</h1><p class="mt-1 text-sm text-[#5E6D65]">{{ $page['description'] }}</p></div></div></div>
        <button class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#17251D] px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-[#0F5C2E]" type="button"><span class="material-symbols-outlined text-lg">add</span>{{ $page['action'] }}</button>
    </section>

    <section class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([['Total Users', $systemStats['users'], 'group'], ['Staff Accounts', $systemStats['staff'], 'badge'], ['Pending Approvals', $systemStats['pendingApprovals'], 'approval'], ['Transactions', $systemStats['transactions'], 'sync_alt']] as [$label,$value,$icon])
            <article class="rounded-lg border border-[#D5DED8] bg-white p-4 shadow-sm"><div class="flex items-center justify-between"><div><p class="text-xs font-semibold text-[#68766E]">{{ $label }}</p><p class="mt-2 font-display text-2xl font-extrabold">{{ $value }}</p></div><span class="material-symbols-outlined rounded-lg bg-[#EEF2EF] p-2 text-[#0F5C2E]">{{ $icon }}</span></div></article>
        @endforeach
    </section>

    <section class="mt-6 rounded-lg border border-[#D5DED8] bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-[#E2E8E4] p-4 sm:flex-row sm:items-center sm:justify-between"><label class="relative block max-w-xl flex-1"><span class="material-symbols-outlined pointer-events-none absolute left-3 top-2.5 text-xl text-[#718078]">search</span><input class="w-full rounded-lg border-[#CBD6CF] py-2 pl-10 pr-3 text-sm focus:border-[#0F5C2E] focus:ring-[#0F5C2E]" placeholder="Search records" type="search"></label><button class="inline-flex items-center justify-center gap-2 rounded-lg border border-[#CBD6CF] bg-white px-3.5 py-2 text-sm font-bold text-[#34443B] hover:bg-[#F4F7F5]" type="button"><span class="material-symbols-outlined text-lg">filter_list</span>Filters</button></div>
        <div class="p-5"><div class="rounded-lg border border-dashed border-[#B7C6BC] bg-[#F8FAF9] px-6 py-12 text-center"><span class="material-symbols-outlined text-4xl text-[#0F5C2E]">{{ $page['icon'] }}</span><h2 class="mt-3 font-display text-lg font-bold">{{ $page['title'] }}</h2><p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-[#5E6D65]">This administration workspace is ready for its approved records, forms, and controls.</p></div></div>
    </section>
@endsection
