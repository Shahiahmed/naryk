@php
    /** @var \App\Models\Advertisement $banner */
    $src = $banner->imageUrl();
@endphp

@if ($src)
    <div class="banner">
        @if ($banner->url)
            <a href="{{ $banner->url }}" rel="noopener sponsored" target="_blank">
                <img src="{{ $src }}" alt="{{ $banner->name }}" loading="lazy">
            </a>
        @else
            <img src="{{ $src }}" alt="{{ $banner->name }}" loading="lazy">
        @endif
    </div>
@endif
