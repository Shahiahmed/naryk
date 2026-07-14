@php
    /** @var array<string, string> $socials */
    $modifier ??= '';
@endphp

@if ($socials)
    <div class="socials {{ $modifier }}">
        @foreach ($socials as $network => $url)
            <a class="social-link" href="{{ $url }}" target="_blank" rel="noopener noreferrer"
               title="{{ ucfirst($network) }}" aria-label="{{ ucfirst($network) }}">
                @include('site.partials.icon', ['name' => $network])
            </a>
        @endforeach
    </div>
@endif
