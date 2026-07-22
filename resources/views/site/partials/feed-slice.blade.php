@php
    /*
     * Point 22: on a phone the side columns have nowhere to go, so they fold
     * into the feed — banner after the third card, expert opinions after the
     * sixth, special projects after the ninth. The blocks ship in the markup
     * and CSS hides them on the desktop, where the columns are back.
     */
    /*
     * Five apiece here, however many the desktop columns carry: nothing scrolls
     * within a block folded into the feed, so a longer list only makes the page
     * longer.
     */
    $expertOpinions = ($expertOpinions ?? collect())->take(5);
    $specialProjects = ($specialProjects ?? collect())->take(5);
@endphp

@foreach ($feed as $index => $post)
    @include('site.partials.card', ['post' => $post])

    @php $position = $index + 1; @endphp

    @if ($feedBanner && $bannerAfter && $position === $bannerAfter)
        @include('site.partials.banner', ['banner' => $feedBanner])
    @endif

    @if ($bannerAfter && $expertOpinions->isNotEmpty() && $position === $bannerAfter * 2)
        <div class="feed-block feed-block--phone">
            @include('site.partials.column-title', [
                'title' => 'Мамандар пікірі',
                'slug' => config('naryk.columns.expert_opinions'),
            ])
            <div class="aside-list">
                @foreach ($expertOpinions as $item)
                    @include('site.partials.aside-card', ['post' => $item, 'withImage' => false])
                @endforeach
            </div>
        </div>
    @endif

    @if ($bannerAfter && $specialProjects->isNotEmpty() && $position === $bannerAfter * 3)
        <div class="feed-block feed-block--phone">
            @include('site.partials.column-title', [
                'title' => 'Арнайы жобалар',
                'slug' => config('naryk.columns.special_projects'),
            ])
            <div class="aside-list">
                @foreach ($specialProjects as $item)
                    @include('site.partials.aside-card', ['post' => $item, 'withImage' => true])
                @endforeach
            </div>
        </div>
    @endif
@endforeach
