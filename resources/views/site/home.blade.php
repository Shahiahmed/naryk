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
                <button class="load-more" type="button" id="load-more">Тағы да</button>
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
