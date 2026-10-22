@extends('admin.layout')
@section('title', 'Admin Dashboard')
@section('content')
<header class="border-b border-slate-200 pb-5"><p class="text-sm font-semibold text-emerald-800">Library administration</p><h1 class="mt-1 text-2xl font-bold">Admin Dashboard</h1><p class="mt-1 text-sm text-slate-600">Review accounts, book records, transactions, and data updates.</p></header>
<section class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    @foreach([['User accounts',$stats['users'],'group','users'],['Waiting for approval',$stats['pending'],'how_to_reg','approvals'],['Book titles',$stats['books'],'menu_book','books'],['Books on loan',$stats['activeLoans'],'sync_alt','circulation']] as [$label,$value,$icon,$target])
    <a class="rounded-md border border-slate-200 bg-white p-5 shadow-sm hover:border-emerald-500" href="{{ route('admin.section', $target) }}"><div class="flex items-center justify-between"><div><p class="text-sm text-slate-600">{{ $label }}</p><p class="mt-2 text-3xl font-bold">{{ $value }}</p></div><span class="material-symbols-outlined rounded-md bg-emerald-50 p-2 text-emerald-800">{{ $icon }}</span></div></a>
    @endforeach
</section>
<section class="mt-6 grid gap-6 lg:grid-cols-2">
    <article class="rounded-md border border-slate-200 bg-white"><div class="border-b border-slate-200 px-5 py-4"><h2 class="font-bold">New Accounts</h2><p class="text-xs text-slate-500">Most recently submitted or created accounts.</p></div><div class="divide-y divide-slate-100">@forelse($recentUsers as $user)<div class="flex items-center justify-between gap-3 px-5 py-3"><div><p class="text-sm font-semibold">{{ $user->name }}</p><p class="text-xs text-slate-500">{{ $user->patron_id }} · {{ ucfirst($user->role) }}</p></div><span class="rounded-full px-2 py-1 text-xs font-medium {{ $user->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">{{ ucfirst($user->status) }}</span></div>@empty<div class="p-5 text-sm text-slate-500">No accounts yet.</div>@endforelse</div></article>
    <article class="rounded-md border border-slate-200 bg-white"><div class="border-b border-slate-200 px-5 py-4"><h2 class="font-bold">Recent Activity</h2><p class="text-xs text-slate-500">Security and data actions recorded by the system.</p></div><div class="divide-y divide-slate-100">@forelse($recentActivity as $log)<div class="px-5 py-3"><div class="flex justify-between gap-3"><p class="text-sm font-semibold">{{ $log->description }}</p><time class="whitespace-nowrap text-xs text-slate-500">{{ $log->created_at->diffForHumans() }}</time></div><p class="mt-1 text-xs text-slate-500">{{ $log->event }}</p></div>@empty<div class="p-5 text-sm text-slate-500">No activity recorded yet.</div>@endforelse</div></article>
</section>
@endsection
