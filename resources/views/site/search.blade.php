@extends('site.layout')

@section('title', $query !== '' ? "Іздеу: {$query} — Naryk.kz" : 'Іздеу — Naryk.kz')

@section('content')
    <nav class="breadcrumbs">
        <a href="/">Басты бет</a>
        <span>/</span>
        <span class="breadcrumbs__current">Іздеу</span>
    </nav>

    <form class="search-form" method="GET" action="{{ route('search') }}">
        <input class="field__input" type="search" name="q" value="{{ $query }}"
               placeholder="Іздеу…" autofocus>
        <button class="load-more search-form__button" type="submit">Іздеу</button>
    </form>

    @if ($posts === null)
        <p class="archive__empty">Іздеу сөзін енгізіңіз.</p>
    @elseif ($posts->isEmpty())
        <p class="archive__empty">«{{ $query }}» бойынша ештеңе табылмады.</p>
    @else
        <h1 class="archive__title">{{ $posts->total() }} материал табылды</h1>

        <div class="grid">
            @foreach ($posts as $post)
                @include('site.partials.grid-card', ['post' => $post])
            @endforeach
        </div>

        {{ $posts->onEachSide(1)->links('site.partials.pagination') }}
    @endif
@endsection
