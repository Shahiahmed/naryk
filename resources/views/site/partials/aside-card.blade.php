@php
    /** @var \App\Models\Post $post */
    // Point 13: a thumbnail on the left, the headline beside it.
    $withImage ??= false;
    $category = $post->categories->first();
    $image = $withImage && $post->hasImage() ? $post->imageUrl() : null;
@endphp

<article class="aside-card {{ $image ? 'aside-card--thumb' : '' }}">
    @if ($image)
        <a href="{{ $post->url() }}" class="aside-card__media">
            <img src="{{ $image }}" alt="" loading="lazy">
        </a>
    @endif

    <div class="aside-card__body">
        <h3 class="aside-card__title">
            @includeWhen($post->pr_news, 'site.partials.pr-badge')
            <a href="{{ $post->url() }}">{{ $post->post_title }}</a>
        </h3>
        @include('site.partials.meta', ['post' => $post, 'category' => $category, 'inverse' => false])
    </div>
</article>
