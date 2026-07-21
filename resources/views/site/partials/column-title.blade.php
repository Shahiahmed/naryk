@php
    /*
     * Point 12: the column headings were plain text, so the only way to reach
     * the rest of Арнайы жобалар or Мамандар пікірі was the rubric strip. They
     * are links now, and open in their own tab so the reader keeps the front
     * page they were half-way through.
     */
    $slug ??= null;
@endphp

@if ($slug)
    <h2 class="column-title">
        <a class="column-title__link" href="/category/{{ $slug }}" target="_blank" rel="noopener">
            {{ $title }}
        </a>
    </h2>
@else
    <h2 class="column-title">{{ $title }}</h2>
@endif
