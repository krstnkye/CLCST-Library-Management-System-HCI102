@php
    $book = $book ?? null;
    $field = 'w-full rounded-lg border-[#CBD6CF] text-sm focus:border-[#0F5C2E] focus:ring-[#0F5C2E]';
@endphp
<div class="grid gap-4 md:grid-cols-2">
    <div class="md:col-span-2"><label class="mb-1.5 block text-sm font-bold" for="title">Title <span class="text-red-600">*</span></label><input id="title" name="title" value="{{ old('title', $book?->title) }}" required maxlength="255" class="{{ $field }}"></div>
    <div><label class="mb-1.5 block text-sm font-bold" for="author">Author(s) <span class="text-red-600">*</span></label><input id="author" name="author" value="{{ old('author', $book?->author) }}" required maxlength="255" class="{{ $field }}"></div>
    <div><label class="mb-1.5 block text-sm font-bold" for="publisher">Publisher</label><input id="publisher" name="publisher" value="{{ old('publisher', $book?->publisher) }}" maxlength="150" class="{{ $field }}"></div>
    <div><label class="mb-1.5 block text-sm font-bold" for="isbn">ISBN <span class="text-red-600">*</span></label><input id="isbn" name="isbn" value="{{ old('isbn', $book?->isbn) }}" required maxlength="17" pattern="[0-9Xx\- ]{10,17}" placeholder="978-0132350884" class="{{ $field }}"></div>
    <div><label class="mb-1.5 block text-sm font-bold" for="call_number">Call number</label><input id="call_number" name="call_number" value="{{ old('call_number', $book?->call_number) }}" maxlength="80" placeholder="QA76.76.C64 M37" class="{{ $field }}"></div>
    <div><label class="mb-1.5 block text-sm font-bold" for="category">Category <span class="text-red-600">*</span></label><input id="category" name="category" list="category-options" value="{{ old('category', $book?->category) }}" required maxlength="100" class="{{ $field }}"><datalist id="category-options">@foreach ($categories ?? [] as $option)<option value="{{ $option }}">@endforeach</datalist></div>
    <div><label class="mb-1.5 block text-sm font-bold" for="subject">Subject</label><input id="subject" name="subject" value="{{ old('subject', $book?->subject) }}" maxlength="100" class="{{ $field }}"></div>
    <div><label class="mb-1.5 block text-sm font-bold" for="publication_year">Publication year</label><input id="publication_year" name="publication_year" type="number" min="1800" max="{{ now()->year + 1 }}" value="{{ old('publication_year', $book?->publication_year) }}" class="{{ $field }}"></div>
    <div><label class="mb-1.5 block text-sm font-bold" for="shelf_location">Shelf location</label><input id="shelf_location" name="shelf_location" value="{{ old('shelf_location', $book?->shelf_location) }}" maxlength="100" placeholder="2nd Floor, Shelf 2B-06" class="{{ $field }}"></div>
    <fieldset class="md:col-span-2">
        <legend class="mb-1.5 text-sm font-bold">Loan policy</legend>
        <div class="flex flex-wrap gap-4 text-sm">
            <label class="flex items-center gap-2"><input type="radio" name="loan_policy" value="circulating" @checked(old('loan_policy', $book?->loan_policy ?? 'circulating') === 'circulating')> Can be borrowed</label>
            <label class="flex items-center gap-2"><input type="radio" name="loan_policy" value="reference" @checked(old('loan_policy', $book?->loan_policy) === 'reference')> Reference (in-library use only)</label>
        </div>
    </fieldset>
</div>
