@extends('site.layout')

@section('title', $post->post_title.' — Naryk.kz')
@section('description', $post->lead() ?? $post->meta_description)

@section('content')
    <div class="article-layout">

        <div class="article-rail">
            @include('site.partials.share', ['post' => $post, 'modifier' => 'share--rail'])
        </div>

        <article class="article">
            @if ($post->hasImage())
                <img class="article__image" src="{{ $post->imageUrl() }}" alt="{{ $post->post_title }}">
            @endif

            <h1 class="article__title">{{ $post->post_title }}</h1>

            <div class="article__meta">
                @include('site.partials.meta', [
                    'post' => $post,
                    'category' => $post->categories->first(),
                    'inverse' => false,
                ])
            </div>

            @if ($banner)
                @include('site.partials.banner', ['banner' => $banner])
            @endif

            {{-- Editors write HTML in the admin's rich editor. --}}
            <div class="article__body">
                {!! $post->post_content !!}
            </div>

            @if ($post->tags->isNotEmpty())
                <div class="article__tags">
                    @foreach ($post->tags as $tag)
                        <a class="tag-pill" href="{{ $tag->url() }}">{{ $tag->name }}</a>
                    @endforeach
                </div>
            @endif

            @include('site.partials.share', ['post' => $post, 'modifier' => 'share--bottom'])
        </article>

        <aside class="columns__side columns__side--right">
            <h2 class="column-title">Соңғы жаңалықтар</h2>
            <ol class="latest">
                @foreach ($latest as $item)
                    <li class="latest__item">
                        <a class="latest__title" href="{{ $item->url() }}">{{ $item->post_title }}</a>
                        <time class="meta__date" datetime="{{ $item->created_at->toIso8601String() }}">
                            {{ $item->created_at->format('d.m.Y') }}
                        </time>
                    </li>
                @endforeach
            </ol>
        </aside>

    </div>
@endsection
