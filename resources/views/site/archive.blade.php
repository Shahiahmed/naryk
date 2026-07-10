@extends('site.layout')

@section('title', $term->name.' — Naryk.kz')

@section('content')
    <nav class="breadcrumbs">
        <a href="/">Басты бет</a>
        <span>/</span>
        <span>{{ $breadcrumb }}</span>
        <span>/</span>
        <span class="breadcrumbs__current">{{ $term->name }}</span>
    </nav>

    <h1 class="archive__title">{{ $term->name }}</h1>

    @if ($posts->isEmpty())
        <p class="archive__empty">Бұл бөлімде әзірге материал жоқ.</p>
    @else
        <div class="grid">
            @foreach ($posts as $post)
                @include('site.partials.grid-card', ['post' => $post])
            @endforeach
        </div>

        {{ $posts->onEachSide(1)->links('site.partials.pagination') }}
    @endif
@endsection
