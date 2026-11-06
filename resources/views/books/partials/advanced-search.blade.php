<template data-modal="advanced-search" data-modal-title="Advanced search" data-modal-icon="tune" data-modal-size="wide">
    <form class="ui-form-grid" method="GET" action="{{ route('books.catalog') }}">
        <p class="ui-modal-lead sm:col-span-2">Fill in any of the fields. Books must match all of them.</p>
        <label>Title<input name="title" value="{{ request('title') }}" maxlength="150" placeholder="e.g. Clean Code"></label>
        <label>Author<input name="author" value="{{ request('author') }}" maxlength="150" placeholder="e.g. Robert Martin"></label>
        <label>ISBN or copy barcode<input name="isbn" value="{{ request('isbn') }}" maxlength="40" placeholder="978-0132350884"></label>
        <label>Subject or keyword<input name="q" value="{{ $query }}" maxlength="150" placeholder="e.g. databases"></label>
        <label>Category
            <select name="category">
                <option value="all">All categories</option>
                @foreach ($categories as $filterCategory)
                    <option value="{{ $filterCategory }}" @selected($category === $filterCategory)>{{ $filterCategory }}</option>
                @endforeach
            </select>
        </label>
        <label>Availability
            <select name="availability">
                <option value="all" @selected($availability === 'all')>Any availability</option>
                <option value="available" @selected($availability === 'available')>Available now</option>
                <option value="reference" @selected($availability === 'reference')>Use in library only</option>
            </select>
        </label>
        <label>Published from<input name="year_from" type="number" min="1900" max="{{ now()->year }}" value="{{ request('year_from') }}" placeholder="e.g. 2010"></label>
        <label>Published until<input name="year_to" type="number" min="1900" max="{{ now()->year }}" value="{{ request('year_to') }}" placeholder="{{ now()->year }}"></label>
        <label class="sm:col-span-2">Sort results
            <select name="sort">
                <option value="relevance" @selected($sort === 'relevance')>Relevance</option>
                <option value="title" @selected($sort === 'title')>Title (A to Z)</option>
                <option value="newest" @selected($sort === 'newest')>Newest first</option>
                <option value="available" @selected($sort === 'available')>Most copies available</option>
            </select>
        </label>
        <div class="ui-form-actions sm:col-span-2">
            <a class="ui-btn ui-btn-ghost" href="{{ route('books.catalog') }}">Clear all</a>
            <button class="ui-btn ui-btn-primary" type="submit"><span class="material-symbols-outlined" aria-hidden="true">manage_search</span>Search books</button>
        </div>
    </form>
</template>
