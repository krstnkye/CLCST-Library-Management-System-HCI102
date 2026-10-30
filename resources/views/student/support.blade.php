@extends('layouts.student')

@section('title', $title)

@section('content')
    <header>
        <h1 class="font-headline text-2xl font-bold">Help & Support</h1>
        <p class="mt-1 text-sm text-on-surface-variant">Answers to common questions, and a form that sends your concern straight to the library staff.</p>
    </header>

    <div class="grid gap-6 xl:grid-cols-12">
        <section class="space-y-3 xl:col-span-7" aria-labelledby="faq-heading">
            <h2 id="faq-heading" class="font-headline text-lg font-bold">Frequently asked questions</h2>
            @foreach ([
                ['How many books can I borrow?', 'Students may borrow up to '.$policy['max_loans_student'].' books for '.$policy['loan_days_student'].' days each. Faculty may borrow up to '.$policy['max_loans_faculty'].' books for '.$policy['loan_days_faculty'].' days.'],
                ['How do I renew a book?', 'Open My Loans and press Renew. Each loan can be renewed '.$policy['max_renewals'].' time(s) for '.$policy['renewal_days'].' more days, as long as it is not overdue and no one else is waiting for the title.'],
                ['How much is the overdue fine?', 'PHP '.number_format($policy['fine_per_day'], 2).' per day for each late book, capped at PHP '.number_format($policy['max_fine_per_item'], 2).' per book. Fines are paid at the library cashier, who issues an official receipt.'],
                ['How do reservations work?', 'Reserve a title from the catalog. When a copy is set aside for you, it appears under Notifications and is held for '.$policy['pickup_days'].' days.'],
                ['Can I borrow reference books?', 'Reference books are for in-library use only. Ask at the reference desk to use them inside the library.'],
                ['I forgot my password.', 'Visit the library office with your school ID, or email library@clcst.edu.ph from your school email, and a librarian will reset it.'],
            ] as [$question, $answer])
                <details class="group rounded-xl border border-outline-variant bg-surface-container-lowest p-4 shadow-sm">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 text-sm font-bold">{{ $question }}<span class="material-symbols-outlined transition group-open:rotate-180">expand_more</span></summary>
                    <p class="mt-3 text-sm leading-6 text-on-surface-variant">{{ $answer }}</p>
                </details>
            @endforeach
        </section>

        <div class="space-y-6 xl:col-span-5">
            <section class="rounded-2xl border border-[#E2E8F0] bg-surface-container-lowest p-5 shadow-sm" aria-labelledby="ticket-heading">
                <h2 id="ticket-heading" class="font-headline text-lg font-bold">Send a concern to the library</h2>
                <form class="mt-4 space-y-3" method="POST" action="{{ route('patron.requests.store') }}">
                    @csrf
                    <div>
                        <label class="block text-sm font-semibold" for="type">Topic</label>
                        <select id="type" name="type" class="mt-1.5 w-full rounded-lg border-outline-variant text-sm focus:border-primary focus:ring-primary">
                            <option value="support" @selected(old('type') === 'support')>Account, fine or borrowing concern</option>
                            <option value="book_recommendation" @selected(old('type') === 'book_recommendation')>Suggest a book to buy</option>
                            <option value="feedback" @selected(old('type') === 'feedback')>Feedback about the library</option>
                        </select>
                    </div>
                    <div><label class="block text-sm font-semibold" for="subject">Subject</label><input id="subject" name="subject" value="{{ old('subject') }}" required maxlength="200" class="mt-1.5 w-full rounded-lg border-outline-variant text-sm focus:border-primary focus:ring-primary"></div>
                    <div><label class="block text-sm font-semibold" for="details">Details</label><textarea id="details" name="details" rows="4" maxlength="3000" class="mt-1.5 w-full rounded-lg border-outline-variant text-sm focus:border-primary focus:ring-primary">{{ old('details') }}</textarea></div>
                    <button class="rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-on-primary hover:bg-primary-container" type="submit">Send to library</button>
                </form>
            </section>

            <section class="rounded-2xl border border-[#E2E8F0] bg-surface-container-lowest p-5 shadow-sm" aria-labelledby="my-requests-heading">
                <h2 id="my-requests-heading" class="font-headline text-lg font-bold">My recent concerns</h2>
                <ul class="mt-3 divide-y divide-outline-variant/50 text-sm">
                    @forelse ($requests as $libraryRequest)
                        <li class="py-3">
                            <div class="flex items-center justify-between gap-2"><span class="font-semibold">{{ $libraryRequest->subject }}</span><span class="rounded-full bg-surface-container px-2 py-0.5 text-[11px] font-bold">{{ $libraryRequest->statusLabel() }}</span></div>
                            <p class="text-xs text-outline">REQ-{{ str_pad((string) $libraryRequest->id, 5, '0', STR_PAD_LEFT) }} - {{ $libraryRequest->typeLabel() }} - {{ $libraryRequest->created_at->format('M d, Y') }}</p>
                            @if ($libraryRequest->response)<p class="mt-1 rounded-lg bg-emerald-50 p-2 text-xs text-emerald-900">Librarian: {{ $libraryRequest->response }}</p>@endif
                        </li>
                    @empty
                        <li class="py-3 text-on-surface-variant">You have not sent any concerns yet.</li>
                    @endforelse
                </ul>
            </section>

            <section class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 text-sm text-emerald-950">
                <h2 class="font-bold">Visit or contact us</h2>
                <p class="mt-2">Main Library, Monday to Friday 8:00 AM - 8:00 PM, Saturday 8:00 AM - 5:00 PM.</p>
                <p class="mt-1"><a class="font-semibold hover:underline" href="mailto:library@clcst.edu.ph">library@clcst.edu.ph</a> - (047) 222-2432 local 108</p>
            </section>
        </div>
    </div>
@endsection
