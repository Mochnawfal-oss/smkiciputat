@extends('layouts.app')

@section('title', $siteSettings['teachers.title'])
@section('meta_description', $siteSettings['teachers.description'])

@section('content')

    <section
        class="teacher-section section educators-page public-page--teachers"
        aria-labelledby="educators-page-title"
        style="--educator-motif-primary: url('{{ asset('images/bagorundmotivtenagapendidik/tenagapendidik1.png') }}'); --educator-motif-secondary: url('{{ asset('images/bagorundmotivtenagapendidik/tenagapendidik2.png') }}')"
    >
        <div class="container educators-page-container">
            <div class="educators-page-hero">
                <div class="educators-page-hero-copy">
                    <span class="educators-page-label">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M3 9.5 12 5l9 4.5-9 4.5L3 9.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                            <path d="M6.5 11.25v4.1c2.95 2.45 8.05 2.45 11 0v-4.1M21 9.5v5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        PARA PENDIDIK
                    </span>
                    <h1 id="educators-page-title">{{ $siteSettings['teachers.title'] }}</h1>
                    <span class="educators-page-title-rule" aria-hidden="true"></span>
                    <p class="educators-page-intro-description">{{ $siteSettings['teachers.description'] }}</p>
                </div>
                <figure class="educators-page-hero-image">
                    <img
                        src="{{ asset('images/bagorundmotivtenagapendidik/bagorundtenagapendidik.png') }}"
                        alt="Gedung SMK Islamiyah Ciputat"
                        fetchpriority="high"
                    >
                </figure>
            </div>

            <div class="educators-page-toolbar">
                <nav class="educators-page-filters" aria-label="Filter kategori tenaga pendidik">
                    <a
                        class="{{ $activeCategory === 'all' ? 'is-active' : '' }}"
                        href="{{ route('teachers', ['category' => 'all', 'search' => $searchTerm]) }}"
                        @if ($activeCategory === 'all') aria-current="page" @endif
                    >
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M16 20v-1.6a3.4 3.4 0 0 0-3.4-3.4H6.4A3.4 3.4 0 0 0 3 18.4V20M9.5 11a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7ZM17 11a3.2 3.2 0 0 0 0-6.2M21 20v-1.6a3.4 3.4 0 0 0-2.6-3.3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Semua
                    </a>
                    <a
                        class="{{ $activeCategory === 'guru' ? 'is-active' : '' }}"
                        href="{{ route('teachers', ['category' => 'guru', 'search' => $searchTerm]) }}"
                        @if ($activeCategory === 'guru') aria-current="page" @endif
                    >
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M3 9.5 12 5l9 4.5-9 4.5L3 9.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                            <path d="M6.5 11.25v4.1c2.95 2.45 8.05 2.45 11 0v-4.1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Guru
                    </a>
                    <a
                        class="{{ $activeCategory === 'kependidikan' ? 'is-active' : '' }}"
                        href="{{ route('teachers', ['category' => 'kependidikan', 'search' => $searchTerm]) }}"
                        @if ($activeCategory === 'kependidikan') aria-current="page" @endif
                    >
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M16 20v-1.6a3.4 3.4 0 0 0-3.4-3.4H6.4A3.4 3.4 0 0 0 3 18.4V20M9.5 11a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7ZM17 11a3.2 3.2 0 0 0 0-6.2M21 20v-1.6a3.4 3.4 0 0 0-2.6-3.3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Tenaga Kependidikan
                    </a>
                </nav>

                <form class="educators-page-search" method="GET" action="{{ route('teachers') }}" role="search">
                    <input type="hidden" name="category" value="{{ $activeCategory }}">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="10.8" cy="10.8" r="6.8" stroke="currentColor" stroke-width="1.8"/>
                        <path d="m16 16 4.2 4.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                    <input
                        type="search"
                        name="search"
                        value="{{ $searchTerm }}"
                        maxlength="100"
                        placeholder="Cari nama pendidik..."
                        aria-label="Cari nama pendidik"
                    >
                    <button type="submit">Cari</button>
                </form>
            </div>

            @if ($errors->has('category') || $errors->has('search'))
                <p class="educators-page-filter-error" role="alert">
                    {{ $errors->first('category') ?: $errors->first('search') }}
                </p>
            @endif

            <div class="educators-page-grid" aria-label="Daftar tenaga kependidikan">
                @forelse ($teachers as $teacher)
                    <x-teacher-id-card
                        :teacher="$teacher"
                        :school-name="$siteSettings['identity.school_name']"
                        :photo-url="$siteConfiguration->imageUrl($teacher->image_path)"
                        :featured="$loop->first"
                        context="directory"
                    />
                @empty
                    <p class="educators-page-empty">Informasi tenaga kependidikan akan segera diperbarui.</p>
                @endforelse
            </div>

            @if ($teachers->hasPages())
                <nav class="educators-page-pagination" aria-label="Navigasi halaman tenaga pendidik">
                    @if ($teachers->onFirstPage())
                        <span class="is-disabled" aria-label="Halaman sebelumnya" aria-disabled="true">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="m15 18-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    @else
                        <a href="{{ $teachers->previousPageUrl() }}" rel="prev" aria-label="Halaman sebelumnya">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="m15 18-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    @endif

                    @foreach ($teachers->getUrlRange(1, $teachers->lastPage()) as $page => $url)
                        @if ($page === $teachers->currentPage())
                            <span class="is-active" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" aria-label="Halaman {{ $page }}">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if ($teachers->hasMorePages())
                        <a href="{{ $teachers->nextPageUrl() }}" rel="next" aria-label="Halaman berikutnya">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    @else
                        <span class="is-disabled" aria-label="Halaman berikutnya" aria-disabled="true">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    @endif
                </nav>
            @endif
        </div>
    </section>
@endsection
