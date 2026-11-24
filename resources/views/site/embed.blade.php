<!DOCTYPE html>
<html lang="kk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Naryk.kz')</title>
    @stack('head')
    <style>
        html, body { margin: 0; padding: 0; background: transparent; overflow-x: hidden; }
        .uk-page { padding: 4px 0 8px !important; }
    </style>
</head>
<body>
    @yield('content')
</body>
</html>
