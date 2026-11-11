
@php
    $logoUrl = $siteConfiguration->imageUrl($siteSettings['identity.logo']);
    $menuItems = collect(['home', 'programs', 'teachers', 'news', 'gallery', 'bkk', 'about'])
        ->filter(fn (string $key): bool => (bool) ($siteSettings['navbar.'.$key.'.visible'] ?? false))
        ->sortBy(fn (string $key): int => (int) ($siteSettings['navbar.'.$key.'.order'] ?? 0))
        ->values();
@endphp

<header class="site-header">
    <div class="container nav-wrapper">
        <a href="{{ route('home') }}" class="brand">
            @if ($logoUrl)
                <img class="brand-logo" src="{{ $logoUrl }}" alt="Logo {{ $siteSettings['identity.school_name'] }}">
            @else
                <span class="brand-symbol" aria-hidden="true">SMK</span>
            @endif

            <span class="brand-text">
                <strong>{{ $siteSettings['identity.school_name'] }}</strong>
                <small>{{ $siteSettings['identity.location'] }}</small>
            </span>
        </a>

        <nav class="nav-menu" id="navMenu">
            @foreach ($menuItems as $menuItem)
                @if ($menuItem === 'programs')
                    <div class="nav-dropdown {{ request()->routeIs('program.*') ? 'active' : '' }}">
                        <a class="nav-link dropdown-toggle" href="{{ $siteSettings['navbar.programs.url'] }}" id="programsDropdown" role="button" aria-haspopup="true" aria-expanded="false" aria-controls="programsMenu">
                            {{ $siteSettings['navbar.programs.label'] }}
                            <i data-lucide="chevron-down" class="dropdown-arrow"></i>
                        </a>
                        <div class="dropdown-menu" id="programsMenu" aria-labelledby="programsDropdown">
                            @foreach ($navigationPrograms as $program)
                                <a href="{{ route('program.show', $program->slug) }}">{{ $program->title }}</a>
                            @endforeach
                        </div>
                    </div>
                @elseif ($menuItem === 'bkk')
                    <div class="nav-dropdown {{ request()->routeIs('bkk.*') ? 'active' : '' }}">
                        <a class="nav-link dropdown-toggle" href="{{ $siteSettings['navbar.bkk.url'] }}" id="bkkDropdown" role="button" aria-haspopup="true" aria-expanded="false" aria-controls="bkkMenu">
                            {{ $siteSettings['navbar.bkk.label'] }}
                            <i data-lucide="chevron-down" class="dropdown-arrow"></i>
                        </a>
                        <div class="dropdown-menu" id="bkkMenu" aria-labelledby="bkkDropdown">
                            <a href="{{ route('bkk.jobs') }}">Lowongan Pekerjaan</a>
                            <a href="{{ route('bkk.tracer-study') }}">Tracer Study</a>
                        </div>
                    </div>
                @else
                    <a
                        href="{{ $siteSettings['navbar.'.$menuItem.'.url'] }}"
                        class="nav-link {{ ($menuItem === 'home' && request()->routeIs('home')) || ($menuItem === 'teachers' && request()->routeIs('teachers')) || ($menuItem === 'news' && request()->routeIs('news.*')) || ($menuItem === 'gallery' && request()->routeIs('gallery')) || ($menuItem === 'about' && request()->routeIs('about')) ? 'active' : '' }}"
                    >
                        {{ $siteSettings['navbar.'.$menuItem.'.label'] }}
                    </a>
                @endif
            @endforeach

            <a href="{{ $siteSettings['navbar.spmb.url'] }}" class="nav-cta">
                <span>{{ $siteSettings['navbar.spmb.label'] }}</span>
                <i data-lucide="arrow-up-right"></i>
            </a>
        </nav>

        <button class="menu-toggle" id="menuToggle"
                type="button" aria-label="Buka menu"
                aria-expanded="false">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</header>
