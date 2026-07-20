<!DOCTYPE html>
<html lang="kk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', $settings['site_information']['sitename'] ?? 'Naryk.kz')</title>
    <meta name="description" content="@yield('description', $settings['site_information']['sitedescription'] ?? '')">
    <meta name="keywords" content="{{ $settings['site_information']['metakeyword'] ?? '' }}">

    @if ($favicon)
        <link rel="icon" href="{{ Storage::disk('public')->url($favicon) }}">
    @endif

    @if ($verification = $settings['google']['googlesiteverification'] ?? null)
        <meta name="google-site-verification" content="{{ $verification }}">
    @endif

    <link rel="stylesheet" href="{{ asset('assets/site.css') }}?v={{ filemtime(public_path('assets/site.css')) }}">

    @if ($ga = $settings['google']['googleanalyticsid'] ?? null)
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ $ga }}');
        </script>
    @endif
</head>
<body>

{{--
    Every logo the client ships is dark green, so the masthead is light and the
    dark green lives in the ticker, the buttons and the footer.
--}}
@php
    $siteName = $settings['site_information']['company_name'] ?? 'Naryk.kz';
    $tagline = $settings['site_information']['sitedescription'] ?? null;
    $logoDesktop = file_exists(public_path('img/logo-desktop.png')) ? asset('img/logo-desktop.png') : null;
@endphp

{{--
    Desktop masthead, left to right: Freedom, search, НАРЫҚ, socials, БІЗ ТУРАЛЫ.
    On a phone it collapses to the old shape — Freedom, НАРЫҚ, burger — and the
    burger opens БІЗ ТУРАЛЫ rather than the rubrics.
--}}
<header class="site-header">
    <div class="shell site-header__inner">
        <div class="site-header__left">
            {{-- The wide lockup on the desktop, the bare shield on a phone. --}}
            @include('site.partials.sponsor', ['sponsor' => $sponsor, 'wide' => true])
            @include('site.partials.sponsor', ['sponsor' => $sponsor, 'wide' => false])

            <form class="site-search" method="GET" action="{{ route('search') }}" role="search">
                <input class="site-search__input" type="search" name="q"
                       value="{{ request()->routeIs('search') ? request('q') : '' }}"
                       placeholder="Іздеу…" aria-label="Іздеу">
            </form>
        </div>

        <a class="site-header__logo" href="/">
            @if ($logoDesktop)
                {{--
                    Point 14: one wordmark everywhere — НАРЫҚ ЖАҢАЛЫҚТАРЫ, as on
                    the site. The two-line phone variant is no longer swapped in.
                --}}
                <img src="{{ $logoDesktop }}" alt="{{ $siteName }}">
            @elseif ($logo)
                <img src="{{ Storage::disk('public')->url($logo) }}" alt="{{ $siteName }}">
            @else
                {{ $siteName }}
            @endif
        </a>

        <div class="site-header__right">
            @include('site.partials.socials', ['socials' => $socials, 'modifier' => 'socials--header'])

            <a class="site-nav__link site-header__about" href="/about">БІЗ ТУРАЛЫ</a>
        </div>

        <button class="burger" type="button" id="burger"
                aria-label="Мәзір" aria-expanded="false" aria-controls="site-menu">
            <span class="burger__bar"></span>
            <span class="burger__bar"></span>
            <span class="burger__bar"></span>
        </button>
    </div>

    {{-- Phone only: the burger opens БІЗ ТУРАЛЫ and the socials, not the rubrics. --}}
    <div class="site-menu" id="site-menu">
        <div class="shell site-menu__inner">
            <a class="site-nav__link" href="/about">БІЗ ТУРАЛЫ</a>
            @include('site.partials.socials', ['socials' => $socials, 'modifier' => 'socials--menu'])
        </div>
    </div>

    {{-- The rubric strip: same height and rhythm as the ticker. --}}
    @if ($headerMenu)
        <nav class="site-nav">
            <div class="shell site-nav__inner">
                @foreach ($headerMenu->items as $item)
                    @continue($item->link === '/about')
                    <a class="site-nav__link {{ $item->class }}" href="{{ $item->link }}">{{ $item->label }}</a>
                @endforeach
            </div>
        </nav>
    @endif
</header>

@yield('ticker')

<main class="shell">
    @yield('content')
</main>

{{--
    Points 23-27: the new wordmark, the tagline beside it rather than under it,
    the five socials, press.naryk@gmail.com — no phone number, no Home/Contact.
--}}
<footer class="site-footer">
    <div class="shell site-footer__inner">
        {{-- Point 10: the wordmark is gone from the foot; the tagline stays. --}}
        <div class="site-footer__brand">
            @if ($tagline)
                <p class="site-footer__desc">{{ $tagline }}</p>
            @endif
        </div>

        <div class="site-footer__right">
            @include('site.partials.socials', ['socials' => $socials, 'modifier' => 'socials--footer'])

            <a href="mailto:{{ $email = $settings['site_information']['siteemail'] ?? 'press.naryk@gmail.com' }}">{{ $email }}</a>
        </div>
    </div>

    <div class="shell site-footer__legal">
        © 2016–{{ date('Y') }} {{ $settings['site_information']['company_name'] ?? 'Naryk.kz' }}
        авторлық және жанама құқықтар сақталған.
    </div>
</footer>

<script src="{{ asset('assets/site.js') }}?v={{ filemtime(public_path('assets/site.js')) }}" defer></script>
</body>
</html>
