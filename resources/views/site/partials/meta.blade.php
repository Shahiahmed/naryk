@php
    /** @var \App\Models\Post $post */
    /** @var \App\Models\Category|null $category */
    $inverse ??= false;
    // Off by default: the cards in the narrow columns have no room for it.
    $hits ??= false;
@endphp

<div class="meta {{ $inverse ? 'meta--inverse' : '' }}">
    @if ($category)
        <a class="meta__rubric" href="/category/{{ $category->term->slug }}">{{ $category->term->name }}</a>
    @endif
    {{-- Point 28: the time of day is gone; the date stays. --}}
    <time class="meta__date" datetime="{{ $post->created_at->toIso8601String() }}">
        {{ $post->created_at->format('d.m.Y') }}
    </time>
    {{--
        The view count is for the newsroom, not the reader: it shows only to a
        signed-in member of staff. A logged-out visitor gets no trace of it in
        the markup at all, so there is nothing to read out of the page source.
    --}}
    @if ($hits && auth()->user()?->isStaff())
        <span class="meta__hits" title="Қаралым саны">
            {{ number_format($post->post_hits, 0, ',', ' ') }}
        </span>
    @endif
</div>
