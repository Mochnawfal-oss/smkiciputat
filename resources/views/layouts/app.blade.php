<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @php
        $pageTitle = trim($__env->yieldContent('title', $siteSettings['seo.title'])) ?: $siteSettings['seo.title'];
        $pageDescription = trim($__env->yieldContent('meta_description', $siteSettings['seo.description'])) ?: $siteSettings['seo.description'];
        $pageImage = trim($__env->yieldContent('meta_image'));
        $pageImage = $pageImage ?: $siteConfiguration->imageUrl($siteSettings['seo.og_image']);
        $pageImage = $pageImage ? url($pageImage) : null;
        $pageType = trim($__env->yieldContent('meta_type')) ?: 'website';
        $pageCanonical = trim($__env->yieldContent('canonical_url'));

        if ($pageCanonical === '') {
            $pageCanonical = request()->routeIs('home') && filled($siteSettings['seo.canonical_url'])
                ? $siteSettings['seo.canonical_url']
                : url()->current();
        }

        $schoolLogo = $siteConfiguration->imageUrl($siteSettings['identity.logo']);
        $schoolProfiles = array_values(array_filter([
            $siteSettings['identity.instagram'],
            $siteSettings['identity.facebook'],
            $siteSettings['identity.youtube'],
        ]));
        $schoolSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'EducationalOrganization',
            'name' => $siteSettings['identity.school_name'],
            'url' => route('home'),
            'description' => $siteSettings['seo.description'],
        ];

        if ($schoolLogo) {
            $schoolSchema['logo'] = url($schoolLogo);
        }

        if (filled($siteSettings['identity.address'])) {
            $schoolSchema['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => $siteSettings['identity.address'],
                'addressLocality' => $siteSettings['identity.location'],
                'addressCountry' => 'ID',
            ];
        }

        if (filled($siteSettings['identity.whatsapp'])) {
            $schoolSchema['telephone'] = $siteSettings['identity.whatsapp'];
        }

        if (filled($siteSettings['identity.email'])) {
            $schoolSchema['email'] = $siteSettings['identity.email'];
        }

        if ($schoolProfiles !== []) {
            $schoolSchema['sameAs'] = $schoolProfiles;
        }
    @endphp

    <meta name="description" content="{{ $pageDescription }}">
    <meta name="keywords" content="{{ $siteSettings['seo.keywords'] }}">
    <meta name="robots" content="{{ $siteSettings['seo.robots'] }}">
    <link rel="canonical" href="{{ $pageCanonical }}">
    @if ($siteSettings['identity.favicon'])
        <link rel="icon" href="{{ $siteConfiguration->imageUrl($siteSettings['identity.favicon']) }}">
    @endif

    <meta property="og:locale" content="id_ID">
    <meta property="og:site_name" content="{{ $siteSettings['identity.school_name'] }}">
    <meta property="og:type" content="{{ $pageType }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ $pageCanonical }}">
    @if ($pageImage)
        <meta property="og:image" content="{{ $pageImage }}">
        <meta property="og:image:alt" content="{{ $pageTitle }}">
    @endif
    <meta name="twitter:card" content="{{ $pageImage ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    @if ($pageImage)
        <meta name="twitter:image" content="{{ $pageImage }}">
    @endif

    <title>{{ $pageTitle }}</title>

    <script type="application/ld+json">@json($schoolSchema)</script>
    @stack('head')

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
