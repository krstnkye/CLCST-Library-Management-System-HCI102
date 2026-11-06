{{-- Import and export opens inside the signed-in staff member's own portal, so the sidebar stays in place. --}}
@php
    [$portalLayout, $activePage] = match (auth()->user()->role) {
        'superadmin' => ['superadmin.layout', 'data-export'],
        'admin' => ['admin.layout', 'bulk-data'],
        default => ['librarian.layout', 'bulk-data'],
    };
@endphp
@extends($portalLayout)
@section('title', 'Import and Export')
@section('content')
<div>
    <div class="max-w-3xl"><h1 class="text-2xl font-bold">Import and Export Data</h1><p class="mt-2 text-sm leading-6 text-slate-600">Use CSV files to add or update many records at once. Download the template first so headings and values are checked correctly.</p></div>
    <div class="mt-6 grid gap-5 {{ $canManageUsers ? 'lg:grid-cols-2' : '' }}">
        @if($canManageUsers)
        <section class="rounded-md border border-slate-200 bg-white p-5 shadow-sm"><div class="flex items-center justify-between"><div><h2 class="font-bold">User Accounts</h2><p class="text-sm text-slate-500">{{ $userCount }} records</p></div><span class="material-symbols-outlined rounded-md bg-blue-50 p-2 text-blue-700">group</span></div><p class="mt-4 text-sm leading-6 text-slate-600">Administrators can import approved account lists or export records for authorized school reporting.</p><div class="mt-4 flex flex-wrap gap-2"><a class="rounded-md border border-slate-300 px-3 py-2 text-sm font-semibold hover:bg-slate-50" href="{{ route('management.data.template','users') }}">Download Template</a><a class="rounded-md border border-slate-300 px-3 py-2 text-sm font-semibold hover:bg-slate-50" href="{{ route('management.data.export','users') }}">Export CSV</a></div><form class="mt-4 border-t border-slate-200 pt-4" method="POST" enctype="multipart/form-data" action="{{ route('management.data.import','users') }}">@csrf<label class="block text-sm font-semibold" for="users-file">Import user CSV</label><input id="users-file" name="csv_file" required type="file" accept=".csv,text/csv" class="mt-2 block w-full rounded-md border-slate-300 text-sm"><button class="mt-3 inline-flex items-center gap-2 rounded-md bg-emerald-900 px-4 py-2.5 text-sm font-bold text-white hover:bg-emerald-800" type="submit"><span class="material-symbols-outlined text-lg">upload</span>Import Users</button></form></section>
        @endif
        <section class="rounded-md border border-slate-200 bg-white p-5 shadow-sm"><div class="flex items-center justify-between"><div><h2 class="font-bold">Book Records</h2><p class="text-sm text-slate-500">{{ $bookCount }} records</p></div><span class="material-symbols-outlined rounded-md bg-emerald-50 p-2 text-emerald-800">menu_book</span></div><p class="mt-4 text-sm leading-6 text-slate-600">Import catalog records and copy counts, or export a clean inventory file for checking and reporting.</p><div class="mt-4 flex flex-wrap gap-2"><a class="rounded-md border border-slate-300 px-3 py-2 text-sm font-semibold hover:bg-slate-50" href="{{ route('management.data.template','books') }}">Download Template</a><a class="rounded-md border border-slate-300 px-3 py-2 text-sm font-semibold hover:bg-slate-50" href="{{ route('management.data.export','books') }}">Export CSV</a></div><form class="mt-4 border-t border-slate-200 pt-4" method="POST" enctype="multipart/form-data" action="{{ route('management.data.import','books') }}">@csrf<label class="block text-sm font-semibold" for="books-file">Import book CSV</label><input id="books-file" name="csv_file" required type="file" accept=".csv,text/csv" class="mt-2 block w-full rounded-md border-slate-300 text-sm"><button class="mt-3 inline-flex items-center gap-2 rounded-md bg-emerald-900 px-4 py-2.5 text-sm font-bold text-white hover:bg-emerald-800" type="submit"><span class="material-symbols-outlined text-lg">upload</span>Import Books</button></form></section>
    </div>
    <aside class="mt-6 rounded-md border border-amber-200 bg-amber-50 p-4 text-sm text-amber-950"><p class="font-bold">Data safety</p><p class="mt-1 leading-6">Imports are limited to 2,000 rows, checked before saving, and rolled back completely if a row is invalid. Passwords are never included in exported files.</p></aside>
</div>
@endsection
