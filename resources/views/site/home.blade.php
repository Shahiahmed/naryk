@extends('site.layout')

@section('ticker')
    @include('site.partials.ticker', ['quotes' => $quotes])
@endsection

@section('content')
    <div class="columns">

        {{-- Left: paid special projects. Smaller images than the centre. --}}
        <aside class="columns__side columns__side--left">
            @if ($specialProjects->isNotEmpty())
                @include('site.partials.column-title', [
                    'title' => 'Арнайы жобалар',
                    'slug' => config('naryk.columns.special_projects'),
                ])
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
                @include('site.partials.feed-slice', [
                    'expertOpinions' => $expertOpinions,
                    'specialProjects' => $specialProjects,
                ])
            </div>

            @if ($feed->hasMorePages())
                <button class="load-more" type="button" id="load-more">Тағы да</button>
            @endif
        </div>

        {{-- Right: expert opinions, as today. --}}
        <aside class="columns__side columns__side--right">
            @if ($expertOpinions->isNotEmpty())
                @include('site.partials.column-title', [
                    'title' => 'Мамандар пікірі',
                    'slug' => config('naryk.columns.expert_opinions'),
                ])
                <div class="aside-list">
                    @foreach ($expertOpinions as $post)
                        @include('site.partials.aside-card', ['post' => $post, 'withImage' => false])
                    @endforeach
                </div>
            @endif
        </aside>

    </div>
@endsection
