@extends('site.layout')

@section('ticker')
    @include('site.partials.ticker', ['quotes' => $quotes])
@endsection

@section('content')
    <div class="columns">

        {{-- Left: paid special projects. Smaller images than the centre. --}}
        <aside class="columns__side columns__side--left">
            @if ($specialProjects->isNotEmpty())
                <h2 class="column-title">Арнайы жобалар</h2>
                <div class="aside-list">
                    @foreach ($specialProjects as $post)
                        @include('site.partials.aside-card', ['post' => $post, 'withImage' => true])
                    @endforeach
                </div>
            @endif
        </aside>

        {{-- Centre: the only column that scrolls. --}}
        <div class="columns__main">
            <div class="feed" id="feed">
                @include('site.partials.feed-slice')
            </div>

            @if ($feed->hasMorePages())
                {{--
                    The brief asks for a button. Scrolling loads the next page
                    on its own; the button stays as the fallback for browsers
                    without IntersectionObserver, and for when a fetch fails.
                --}}
                <div class="feed-status" id="feed-status" role="status" aria-live="polite" hidden>
                    Жүктелуде…
                </div>

                <button class="load-more" type="button" id="load-more">Тағы да</button>

                <div id="feed-sentinel" aria-hidden="true"></div>
            @endif
        </div>

        {{-- Right: expert opinions, as today. --}}
        <aside class="columns__side columns__side--right">
            @if ($expertOpinions->isNotEmpty())
                <h2 class="column-title">Мамандар пікірі</h2>
                <div class="aside-list">
                    @foreach ($expertOpinions as $post)
                        @include('site.partials.aside-card', ['post' => $post, 'withImage' => false])
                    @endforeach
                </div>
            @endif
        </aside>

    </div>
@endsection
