@php
    /** @var \App\Models\Post $post */
    $layout = $post->layout();
    $category = $post->categories->first();
    $image = $post->hasImage() ? $post->imageUrl() : null;
@endphp

@if ($layout === \App\Models\Post::LAYOUT_TALL && $image)
    {{-- Tall image: title, date and rubric sit low on the image, no lead. --}}
    <article class="card card--tall">
        <a href="{{ $post->url() }}" class="card__link">
            <img class="card--tall__image" src="{{ $image }}" alt="" loading="lazy">
            <div class="card--tall__overlay">
                @includeWhen($post->pr_news, 'site.partials.pr-badge')
                <h2 class="card__title card__title--inverse">{{ $post->post_title }}</h2>
                @include('site.partials.meta', ['post' => $post, 'category' => $category, 'inverse' => true])
            </div>
        </a>
    </article>
@else
    {{--
        The brief's text orders the title, then the date and rubric, then the
        lead — but both reference mockups put the rubric and date last. The
        mockups agree with each other, so they win.
    --}}
    <article class="card">
        @if ($image)
            <a href="{{ $post->url() }}" class="card__media">
                <img class="card__image" src="{{ $image }}" alt="" loading="lazy">
            </a>
        @endif

        @includeWhen($post->pr_news, 'site.partials.pr-badge')

        <h2 class="card__title">
            <a href="{{ $post->url() }}">{{ $post->post_title }}</a>
        </h2>

        @if ($lead = $post->lead())
            <p class="card__lead">{{ $lead }}</p>
        @endif

        @include('site.partials.meta', ['post' => $post, 'category' => $category, 'inverse' => false])
    </article>
@endif
