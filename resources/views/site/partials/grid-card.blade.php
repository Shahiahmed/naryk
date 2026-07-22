@php
    /** @var \App\Models\Post $post */
    $category = $post->categories->first();
@endphp

<article class="grid-card">
    {{--
        A rubric page lays its cards out on a grid, so a post without a picture
        left a hole where every neighbour had one. It gets the wordmark on green
        instead. The home feed is not this partial and keeps its text-only cards.
    --}}
    <a class="grid-card__media {{ $post->hasImage() ? '' : 'grid-card__media--fallback' }}" href="{{ $post->url() }}">
        <img src="{{ $post->coverUrl() }}" alt="" loading="lazy">
    </a>

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
