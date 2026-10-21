@php
    /** @var \App\Models\Post $post */
    /** @var \App\Models\Category|null $category */
    $inverse ??= false;
    // Off by default: the cards in the narrow columns have no room for it.
    $hits ??= false;
    $showHits = $hits && auth()->user()?->isStaff();
    $metaClasses = ['meta'];

    if ($inverse) {
        $metaClasses[] = 'meta--inverse';
    }

    if ($showHits) {
        $metaClasses[] = 'meta--hits';
    }
@endphp

<div class="{{ implode(' ', $metaClasses) }}">
    @if ($category)
        <a class="meta__rubric" href="/category/{{ $category->term->slug }}">{{ $category->term->name }}</a>
    @endif
    {{-- Point 28: the time of day is gone; the date stays. --}}
    <time class="meta__date" datetime="{{ $post->created_at->toIso8601String() }}">
        {{ $post->created_at->format('d.m.Y') }}
    </time>
    {{--
        The view count is for the newsroom, not the reader. A guest or a
        signed-in reader gets no trace of it in the markup.
    --}}
    @if ($showHits)
        <span class="meta__hits" title="Қаралым саны">
            {{ number_format($post->post_hits, 0, ',', ' ') }}
        </span>
    @endif
</div>
