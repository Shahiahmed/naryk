@php
    /** @var \App\Models\Post $post */
    $withImage ??= false;
    $category = $post->categories->first();
    $image = $withImage && $post->hasImage() ? $post->imageUrl() : null;
@endphp

<article class="aside-card">
    @if ($image)
        <a href="{{ $post->url() }}" class="aside-card__media">
            <img src="{{ $image }}" alt="" loading="lazy">
        </a>
    @endif

    <div class="aside-card__body">
        @includeWhen($post->pr_news, 'site.partials.pr-badge')
        <h3 class="aside-card__title">
            <a href="{{ $post->url() }}">{{ $post->post_title }}</a>
        </h3>
        @include('site.partials.meta', ['post' => $post, 'category' => $category, 'inverse' => false])
    </div>
</article>
