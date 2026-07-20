@extends('site.layout')

@section('title', $page->post_title.' — Naryk.kz')
@section('description', $page->seoDescription())
@section('og_image', $page->hasImage() ? $page->imageUrl() : '')

@section('content')
    <nav class="breadcrumbs">
        <a href="/">Басты бет</a>
        <span>/</span>
        <span class="breadcrumbs__current">{{ $page->post_title }}</span>
    </nav>

    <article class="article article--wide">
        @if ($page->hasImage())
            <img class="article__image" src="{{ $page->imageUrl() }}" alt="{{ $page->post_title }}">
        @endif

        <h1 class="article__title">{{ $page->post_title }}</h1>

        {{-- Editors write HTML in the admin's rich editor. --}}
        <div class="article__body">
            {!! $page->post_content !!}
        </div>
    </article>
@endsection
