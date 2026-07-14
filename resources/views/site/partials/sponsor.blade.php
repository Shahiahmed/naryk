@php
    /**
     * `wide` is the full lockup with the words, for the navigation strip. The
     * compact shield goes in the phone masthead, where the wide one will not
     * fit. A logo uploaded in the admin panel is used for both.
     */
    $wide ??= false;
@endphp

<a class="sponsor {{ $wide ? 'sponsor--wide' : 'sponsor--compact' }}"
   href="{{ $sponsor['url'] }}"
   target="_blank"
   rel="noopener sponsored"
   title="{{ $sponsor['title'] }}">
    <img src="{{ $wide ? $sponsor['wide'] : $sponsor['logo'] }}" alt="{{ $sponsor['title'] }}">
</a>
