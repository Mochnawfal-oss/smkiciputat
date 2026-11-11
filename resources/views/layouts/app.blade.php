<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description" content="@yield('meta_description', $siteSettings['seo.description'])">
    <meta name="keywords" content="{{ $siteSettings['seo.keywords'] }}">
    <meta name="robots" content="{{ $siteSettings['seo.robots'] }}">
    @if ($siteSettings['seo.canonical_url'])
        <link rel="canonical" href="{{ $siteSettings['seo.canonical_url'] }}">
    @endif
    @if ($siteSettings['identity.favicon'])
        <link rel="icon" href="{{ $siteConfiguration->imageUrl($siteSettings['identity.favicon']) }}">
    @endif

    <title>@yield('title', $siteSettings['seo.title'])</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @include('components.navbar')

    <main>
        @yield('content')
    </main>

    @include('components.footer')
</body>
</html>
