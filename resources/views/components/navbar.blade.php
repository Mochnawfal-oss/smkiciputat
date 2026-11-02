
<header class="site-header">
    <div class="container nav-wrapper">

        {{-- Logo sekolah --}}
        <a href="{{ route('home') }}" class="brand">
            <img class="brand-logo" src="{{ asset('images/logo/logo.PNG') }}" alt="Logo SMK Islamiyah Ciputat">

            <span class="brand-text">
                <strong>SMK Islamiyah</strong>
                <small>Ciputat, Tangerang Selatan</small>
            </span>
        </a>

        {{-- Navigasi --}}
        <nav class="nav-menu" id="navMenu">

            <a href="{{ route('home') }}"
               class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                Beranda
            </a>

            {{-- Program Keahlian --}}
            <div class="nav-dropdown {{ request()->routeIs('program.*') ? 'active' : '' }}">
                <button type="button"
                        class="nav-link dropdown-toggle"
                        aria-expanded="false">
                    Program Keahlian
                    <i data-lucide="chevron-down" class="dropdown-arrow"></i>
                </button>

                <div class="dropdown-menu">
                    <a href="{{ route('program.kuliner') }}">Kuliner</a>
                    <a href="{{ route('program.dkv') }}">Desain Komunikasi Visual</a>
                    <a href="{{ route('program.tkj') }}">Teknik Komputer dan Jaringan</a>
                    <a href="{{ route('program.ak') }}">Akuntansi</a>
                    <a href="{{ route('program.mp') }}">Manajemen Perkantoran</a>
                    <a href="{{ route('program.bd') }}">Bisnis Digital</a>
                </div>
            </div>

            <a href="{{ route('teachers') }}"
               class="nav-link {{ request()->routeIs('teachers') ? 'active' : '' }}">
                Tenaga Kependidikan
            </a>

            {{-- BKK --}}
            <div class="nav-dropdown {{ request()->routeIs('bkk.*') ? 'active' : '' }}">
                <button type="button"
                        class="nav-link dropdown-toggle"
                        aria-expanded="false">
                    BKK
                    <i data-lucide="chevron-down" class="dropdown-arrow"></i>
                </button>

                <div class="dropdown-menu">
                    <a href="{{ route('bkk.jobs') }}">Lowongan Pekerjaan</a>
                    <a href="{{ route('bkk.tracer-study') }}">Tracer Study</a>
                </div>
            </div>

            <a href="{{ route('about') }}"
               class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}">
                Tentang Kami
            </a>

            {{-- Tombol SPMB --}}
            <a href="{{ route('spmb') }}" class="nav-cta">
                <span>SPMB</span>
                <i data-lucide="arrow-up-right"></i>
            </a>

        </nav>

        {{-- Tombol menu mobile --}}
        <button class="menu-toggle" id="menuToggle"
                type="button" aria-label="Buka menu"
                aria-expanded="false">
            <span></span>
            <span></span>
            <span></span>
        </button>

    </div>
</header>
