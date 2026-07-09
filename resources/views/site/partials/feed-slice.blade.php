@foreach ($feed as $index => $post)
    @include('site.partials.card', ['post' => $post])

    @if ($feedBanner && $bannerAfter && $index + 1 === $bannerAfter)
        @include('site.partials.banner', ['banner' => $feedBanner])
    @endif
@endforeach
