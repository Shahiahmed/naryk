@php
    /** @var \App\Models\Post $post */
    /** @var \App\Models\Category|null $category */
    $inverse ??= false;
@endphp

<div class="meta {{ $inverse ? 'meta--inverse' : '' }}">
    @if ($category)
        <a class="meta__rubric" href="/category/{{ $category->term->slug }}">{{ $category->term->name }}</a>
    @endif
    <time class="meta__date" datetime="{{ $post->created_at->toIso8601String() }}">
        {{ $post->created_at->format('d.m.Y') }} · {{ $post->created_at->format('H:i') }}
    </time>
</div>
