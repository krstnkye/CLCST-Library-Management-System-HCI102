@php
    // Build citations from the catalog record. Authors are stored as "First Last & First Last".
    $authors = collect(preg_split('/\s*(?:&|\band\b|;)\s*/', (string) $book->author))->map(fn ($name) => trim($name))->filter()->values();
    $split = fn (string $name) => [\Illuminate\Support\Str::beforeLast($name, ' '), \Illuminate\Support\Str::afterLast($name, ' ')];
    $apaName = function (string $name) use ($split) {
        [$first, $last] = $split($name);
        $initials = collect(preg_split('/[\s.-]+/', $first))->filter()->map(fn ($part) => mb_substr($part, 0, 1).'.')->implode(' ');

        return $first === $name ? $name : $last.', '.$initials;
    };
    $apaAuthors = $authors->map($apaName);
    $apa = ($apaAuthors->count() > 1 ? $apaAuthors->slice(0, -1)->implode(', ').', & '.$apaAuthors->last() : $apaAuthors->first())
        .' ('.($book->publication_year ?: 'n.d.').'). '.$book->title.'. '.$book->publisher.'.';
    $firstAuthor = $authors->first() ? (function ($name) use ($split) { [$first, $last] = $split($name); return $first === $name ? $name : $last.', '.$first; })($authors->first()) : '';
    $mlaAuthors = match (true) {
        $authors->count() === 0 => '',
        $authors->count() === 1 => $firstAuthor,
        $authors->count() === 2 => $firstAuthor.', and '.$authors[1],
        default => $firstAuthor.', et al',
    };
    $mla = rtrim($mlaAuthors, '.').'. '.$book->title.'. '.$book->publisher.', '.$book->publication_year.'.';
    $chicagoAuthors = $authors->count() > 1 ? $firstAuthor.', and '.$authors->slice(1)->implode(', ') : $firstAuthor;
    $chicago = rtrim($chicagoAuthors, '.').'. '.$book->title.'. '.$book->publisher.', '.$book->publication_year.'.';
@endphp

<template data-modal="cite-{{ $book->id }}" data-modal-title="Cite this book" data-modal-icon="format_quote">
    <p class="ui-modal-lead">{{ $book->title }}</p>
    <div class="ui-cite-list">
        @foreach (['APA 7th edition' => $apa, 'MLA 9th edition' => $mla, 'Chicago (author-date)' => $chicago] as $style => $citation)
            <div class="ui-cite">
                <div class="ui-cite-head"><span>{{ $style }}</span><button type="button" data-copy="{{ $citation }}"><span class="material-symbols-outlined" aria-hidden="true">content_copy</span>Copy</button></div>
                <p>{{ $citation }}</p>
            </div>
        @endforeach
    </div>
    <p class="ui-modal-note">Titles are usually written in italics. Check the format your instructor requires.</p>
</template>

@if ($book->isReference())
    <template data-modal="counter-{{ $book->id }}" data-modal-title="Use this book in the library" data-modal-icon="desk">
        <p class="ui-modal-lead">{{ $book->title }}</p>
        <p>Reference books stay inside the library, so they cannot be reserved or borrowed. Show this slip at the reference counter and a librarian will hand you a copy to use in the reading area.</p>
        <dl class="ui-slip">
            <div><dt>Call number</dt><dd class="font-mono">{{ $book->call_number }}</dd></div>
            <div><dt>Shelf location</dt><dd>{{ $book->shelf_location }}</dd></div>
            <div><dt>ISBN</dt><dd class="font-mono">{{ $book->isbn }}</dd></div>
            <div><dt>Copies in the library</dt><dd>{{ $book->copies->count() }}</dd></div>
        </dl>
        <p class="ui-modal-note">Counter hours: Monday to Friday 8:00 AM - 8:00 PM, Saturday 8:00 AM - 5:00 PM.</p>
    </template>
@endif
