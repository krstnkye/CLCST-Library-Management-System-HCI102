@extends('admin.layout')
@section('title', $page['title'])
@section('content')
<header class="border-b border-slate-200 pb-5"><a class="text-sm font-semibold text-emerald-800 hover:underline" href="{{ route('admin.dashboard') }}">Back to dashboard</a><div class="mt-3 flex items-start gap-3"><span class="material-symbols-outlined rounded-md bg-emerald-50 p-2 text-emerald-800">{{ $page['icon'] }}</span><div><h1 class="text-2xl font-bold">{{ $page['title'] }}</h1><p class="mt-1 text-sm text-slate-600">{{ $page['description'] }}</p></div></div></header>

<div class="mt-6">
@if (in_array($section, ['users', 'approvals'], true))
    @include('partials.account-table', ['users' => $users, 'searchRoute' => route('admin.section', $section)])
@elseif ($section === 'books')
    <section class="overflow-hidden rounded-md border border-slate-200 bg-white">
        <form class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between" method="GET" action="{{ route('admin.section', 'books') }}" role="search">
            <div><h2 class="font-bold">Book records</h2><p class="text-xs text-slate-500">Edit records and copies from the librarian workspace.</p></div>
            <div class="flex gap-2"><label class="sr-only" for="book-search">Search</label><input id="book-search" class="rounded-md border-slate-300 py-1.5 text-sm" name="q" value="{{ request('q') }}" placeholder="Title or ISBN" type="search"><button class="rounded-md border border-slate-300 px-3 text-xs font-bold" type="submit">Search</button><a class="rounded-md bg-emerald-900 px-3 py-2 text-xs font-bold text-white" href="{{ route('librarian.section', 'add-book') }}">Add book</a></div>
        </form>
        <div class="overflow-x-auto"><table class="w-full min-w-[760px] text-left text-sm"><thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">Title</th><th class="px-5 py-3">ISBN</th><th class="px-5 py-3">Category</th><th class="px-5 py-3">Copies</th><th class="px-5 py-3">Status</th><th class="px-5 py-3 text-right">Action</th></tr></thead><tbody class="divide-y divide-slate-100">
            @forelse ($storedBooks as $book)
                <tr><td class="px-5 py-4"><p class="font-semibold">{{ $book->title }}</p><p class="text-xs text-slate-500">{{ $book->author }}</p></td><td class="px-5 py-4 font-mono text-xs">{{ $book->isbn }}</td><td class="px-5 py-4">{{ $book->category ?: 'Uncategorized' }}</td><td class="px-5 py-4">{{ $book->available_copies }} of {{ $book->total_copies }} on shelf</td><td class="px-5 py-4">{{ ucfirst($book->status) }}</td><td class="px-5 py-4 text-right"><a class="font-bold text-emerald-800 hover:underline" href="{{ route('librarian.books.edit', $book) }}">Edit</a></td></tr>
            @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-slate-500">No book records match.</td></tr>
            @endforelse
        </tbody></table></div>
        @if ($storedBooks->hasPages())<div class="border-t border-slate-200 p-4">{{ $storedBooks->links() }}</div>@endif
    </section>
@elseif ($section === 'circulation')
    <section class="overflow-hidden rounded-md border border-slate-200 bg-white">
        <div class="overflow-x-auto"><table class="w-full min-w-[860px] text-left text-sm"><thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">Receipt</th><th class="px-5 py-3">Patron</th><th class="px-5 py-3">Book</th><th class="px-5 py-3">Borrowed</th><th class="px-5 py-3">Due</th><th class="px-5 py-3">Returned</th><th class="px-5 py-3">Issued by</th></tr></thead><tbody class="divide-y divide-slate-100">
            @forelse ($loans as $loan)
                <tr class="{{ $loan->isOverdue() ? 'bg-red-50/50' : '' }}"><td class="px-5 py-3 font-mono text-xs">{{ $loan->receipt_no }}</td><td class="px-5 py-3">{{ $loan->user->name }}</td><td class="px-5 py-3">{{ $loan->copy->book->title }}</td><td class="px-5 py-3 text-xs">{{ $loan->borrowed_at->format('M d, Y') }}</td><td class="px-5 py-3 text-xs">{{ $loan->due_at->format('M d, Y') }}</td><td class="px-5 py-3 text-xs">{{ $loan->returned_at?->format('M d, Y') ?? ($loan->isOverdue() ? 'Overdue' : 'On loan') }}</td><td class="px-5 py-3 text-xs">{{ $loan->issuer?->name }}</td></tr>
            @empty
                <tr><td colspan="7" class="px-5 py-10 text-center text-slate-500">No transactions yet.</td></tr>
            @endforelse
        </tbody></table></div>
        @if ($loans->hasPages())<div class="border-t border-slate-200 p-4">{{ $loans->links() }}</div>@endif
    </section>
@elseif ($section === 'reports')
    @include('partials.reports')
@elseif ($section === 'audit-logs')
    @include('partials.audit-log', ['searchRoute' => route('admin.section', 'audit-logs')])
@elseif ($section === 'profile')
    @include('partials.profile-forms')
@endif
</div>
@endsection
