@extends('layouts.faculty')

@section('title', $title)

@section('content')
    @php
        // Guidance and form hints for each teaching-support request page.
        $requestPages = [
            'catalog' => ['Ask the library to buy a title for your courses or research.', 'Title, author and edition', 'Course it supports, number of copies, and why it is needed.'],
            'course-reserves' => ['Place books on the short-loan reserve shelf for a course.', 'Course code - title(s) to reserve', 'Course, section, semester, titles and number of copies, and the loan period students should get.'],
            'reading-lists' => ['Send a syllabus reading list so the library can check holdings and order missing titles.', 'Course code - reading list', 'List each title, author and edition. Mark required versus recommended readings.'],
            'instruction-sessions' => ['Book a library orientation or research-skills session for your class.', 'Course code - session topic', 'Preferred dates and times, number of students, and topics such as catalog searching or citation.'],
            'inter-library-loan' => ['Borrow a book or article the CLCST library does not own from a partner library.', 'Title of the book or article', 'Author, year, publisher or journal, ISBN/DOI, and the date you need it by.'],
            'academic' => ['Any other academic support request: thesis advising resources, accreditation documents, research data, and similar.', 'Short summary of the request', 'Describe what you need and any deadline.'],
            'feedback' => ['Tell the library what works and what should improve. Every message is read by the library staff.', 'Topic of your feedback', 'Your comments or suggestions.'],
            'support' => ['Report a problem with your account, a loan, or this portal.', 'Short description of the problem', 'What happened, when, and what you expected.'],
        ];
    @endphp

    <header>
        <h1 class="font-headline text-2xl font-bold">{{ $title }}</h1>
        @isset($requestPages[$section])<p class="mt-1 text-sm text-[#475569]">{{ $requestPages[$section][0] }}</p>@endisset
    </header>

    @switch($section)
        @case('circulation')
            <section class="space-y-4 rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
                <p class="text-sm text-[#475569]">{{ $activeLoans->count() }} of {{ $loanLimit }} books on loan. Loans last {{ $loanDays }} days and can be renewed {{ $policy['max_renewals'] }} time(s) online.</p>
                @forelse ($activeLoans as $loan)
                    @include('student.partials.loan-card', ['loan' => $loan])
                @empty
                    <p class="rounded-xl border border-dashed border-[#CBD5E1] p-6 text-center text-sm text-[#64748B]">You have no books on loan.</p>
                @endforelse
            </section>
            @break

        @case('reservations')
            @include('partials.patron-reservations')
            @break

        @case('history')
            <div class="flex justify-end"><a class="inline-flex items-center gap-2 rounded-lg border border-[#CBD5E1] bg-white px-4 py-2 text-sm font-bold hover:bg-[#F1F5F9]" href="{{ route('staff.section', ['history', 'export' => 'csv']) }}"><span class="material-symbols-outlined text-base">download</span>Download CSV</a></div>
            @include('partials.patron-history')
            @break

        @case('notifications')
            @include('partials.notification-center')
            @break

        @case('profile')
            @include('partials.profile-forms')
            @break

        @default
            @php([$intro, $subjectHint, $detailsHint] = $requestPages[$section])
            <div class="grid gap-6 xl:grid-cols-12">
                <form class="space-y-3 rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm xl:col-span-5" method="POST" action="{{ route('patron.requests.store') }}">
                    @csrf
                    <input type="hidden" name="type" value="{{ $requestType }}">
                    <h2 class="font-headline text-lg font-bold">New request</h2>
                    <div><label class="mb-1.5 block text-sm font-bold" for="subject">Subject</label><input id="subject" name="subject" value="{{ old('subject') }}" required maxlength="200" placeholder="{{ $subjectHint }}" class="w-full rounded-lg border-[#CBD5E1] text-sm focus:border-[#065F2A] focus:ring-[#065F2A]"></div>
                    <div><label class="mb-1.5 block text-sm font-bold" for="details">Details</label><textarea id="details" name="details" rows="6" maxlength="3000" placeholder="{{ $detailsHint }}" class="w-full rounded-lg border-[#CBD5E1] text-sm focus:border-[#065F2A] focus:ring-[#065F2A]">{{ old('details') }}</textarea></div>
                    <button class="rounded-lg bg-[#065F2A] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#064E3B]" type="submit">Send to the library</button>
                </form>

                <section class="rounded-2xl border border-[#E2E8F0] bg-white shadow-sm xl:col-span-7">
                    <h2 class="border-b border-[#E2E8F0] px-5 py-4 font-headline text-lg font-bold">My {{ strtolower(\App\Models\LibraryRequest::TYPES[$requestType]) }} requests</h2>
                    <ul class="divide-y divide-[#E2E8F0]">
                        @forelse ($requests as $libraryRequest)
                            <li class="px-5 py-4">
                                <div class="flex flex-wrap items-center justify-between gap-2"><p class="font-semibold">{{ $libraryRequest->subject }}</p><span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ match ($libraryRequest->status) { 'approved', 'completed' => 'bg-emerald-100 text-emerald-800', 'declined' => 'bg-red-100 text-red-800', 'in_review' => 'bg-blue-100 text-blue-800', default => 'bg-amber-100 text-amber-900' } }}">{{ $libraryRequest->statusLabel() }}</span></div>
                                <p class="mt-1 text-xs text-[#64748B]">REQ-{{ str_pad((string) $libraryRequest->id, 5, '0', STR_PAD_LEFT) }} - sent {{ $libraryRequest->created_at->format('M d, Y') }}</p>
                                @if ($libraryRequest->response)<p class="mt-2 rounded-lg bg-emerald-50 p-2 text-xs text-emerald-900">Librarian: {{ $libraryRequest->response }}</p>@endif
                            </li>
                        @empty
                            <li class="px-5 py-8 text-center text-sm text-[#64748B]">No requests of this type yet.</li>
                        @endforelse
                    </ul>
                </section>
            </div>
    @endswitch
@endsection
