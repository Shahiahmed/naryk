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

<header class="site-header">
    <div class="shell site-header__inner">
        <a class="site-header__logo" href="/">
            @if ($logo)
                <img src="{{ Storage::disk('public')->url($logo) }}"
                     alt="{{ $settings['site_information']['company_name'] ?? 'Naryk.kz' }}">
            @else
                {{ $settings['site_information']['company_name'] ?? 'Naryk.kz' }}
            @endif
        </a>

        @if ($headerMenu)
            <nav class="site-nav">
                @foreach ($headerMenu->items as $item)
                    <a class="site-nav__link {{ $item->class }}" href="{{ $item->link }}">{{ $item->label }}</a>
                @endforeach
            </nav>
        @endif

        <form class="site-search" method="GET" action="{{ route('search') }}" role="search">
            <input class="site-search__input" type="search" name="q"
                   value="{{ request()->routeIs('search') ? request('q') : '' }}"
                   placeholder="Іздеу…" aria-label="Іздеу">
        </form>
    </div>
</header>

@yield('ticker')

<main class="shell">
    @yield('content')
</main>

<footer class="site-footer">
    <div class="shell site-footer__inner">
        <div class="site-footer__brand">
            @if ($logoFooter)
                <img src="{{ Storage::disk('public')->url($logoFooter) }}" alt="" class="site-footer__logo">
            @endif
            <p class="site-footer__desc">{{ $settings['site_information']['sitedescription'] ?? '' }}</p>
        </div>

        @if ($footerMenu)
            <nav class="site-footer__nav">
                @foreach ($footerMenu->items as $item)
                    <a href="{{ $item->link }}">{{ $item->label }}</a>
                @endforeach
            </nav>
        @endif

        <div class="site-footer__contacts">
            @if ($email = $settings['site_information']['siteemail'] ?? null)
                <a href="mailto:{{ $email }}">{{ $email }}</a>
            @endif
            @if ($phone = $settings['site_information']['sitephone'] ?? null)
                <a href="tel:{{ preg_replace('/\D+/', '', $phone) }}">{{ $phone }}</a>
            @endif

            <div class="site-footer__socials">
                @foreach ($socials as $network => $url)
                    <a href="{{ $url }}" rel="noopener noreferrer" target="_blank">{{ ucfirst($network) }}</a>
                @endforeach
            </div>
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
