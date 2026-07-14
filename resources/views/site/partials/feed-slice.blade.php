@php
    /*
     * Point 22: on a phone the side columns have nowhere to go, so they fold
     * into the feed — banner after the third card, expert opinions after the
     * sixth, special projects after the ninth. The blocks ship in the markup
     * and CSS hides them on the desktop, where the columns are back.
     */
    $expertOpinions ??= collect();
    $specialProjects ??= collect();
@endphp

@foreach ($feed as $index => $post)
    @include('site.partials.card', ['post' => $post])

    @php $position = $index + 1; @endphp

    @if ($feedBanner && $bannerAfter && $position === $bannerAfter)
        @include('site.partials.banner', ['banner' => $feedBanner])
    @endif

    @if ($bannerAfter && $expertOpinions->isNotEmpty() && $position === $bannerAfter * 2)
        <div class="feed-block feed-block--phone">
            <h2 class="column-title">Мамандар пікірі</h2>
            <div class="aside-list">
                @foreach ($expertOpinions as $item)
                    @include('site.partials.aside-card', ['post' => $item, 'withImage' => false])
                @endforeach
            </div>
        </div>
    @endif

    @if ($bannerAfter && $specialProjects->isNotEmpty() && $position === $bannerAfter * 3)
        <div class="feed-block feed-block--phone">
            <h2 class="column-title">Арнайы жобалар</h2>
            <div class="aside-list">
                @foreach ($specialProjects as $item)
                    @include('site.partials.aside-card', ['post' => $item, 'withImage' => true])
                @endforeach
            </div>
        </div>
    @endif
@endforeach
