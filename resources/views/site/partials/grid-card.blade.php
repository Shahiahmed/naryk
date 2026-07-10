@php
    /** @var \App\Models\Post $post */
    $category = $post->categories->first();
@endphp

<article class="grid-card">
    @if ($post->hasImage())
        <a class="grid-card__media" href="{{ $post->url() }}">
            <img src="{{ $post->imageUrl() }}" alt="" loading="lazy">
        </a>
    @endif

    <div class="grid-card__body">
        @includeWhen($post->pr_news, 'site.partials.pr-badge')

        <h2 class="grid-card__title">
            <a href="{{ $post->url() }}">{{ $post->post_title }}</a>
        </h2>

        @if ($lead = $post->lead())
            <p class="grid-card__lead">{{ Str::limit($lead, 120) }}</p>
        @endif

        @include('site.partials.meta', ['post' => $post, 'category' => $category, 'inverse' => false])
    </div>
</article>
