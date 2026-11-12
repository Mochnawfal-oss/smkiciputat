@extends('layouts.app')

@section('title', $siteSettings['bkk.title'])
@section('meta_description', $siteSettings['bkk.description'])

@section('content')
    <section class="section public-page public-page--bkk-jobs bkk-jobs-page">
        <div class="container public-page__container">
            <header class="bkk-jobs-hero">
                <div class="bkk-jobs-hero__copy">
                    <span class="bkk-jobs-eyebrow">BURSA KERJA KHUSUS (BKK)</span>
                    <h1>{{ $siteSettings['bkk.title'] }}</h1>
                    <p>{{ $siteSettings['bkk.description'] }}</p>
                </div>
                <div class="bkk-jobs-hero__media">
                    <img
                        src="{{ asset('images/10.jpeg') }}"
                        alt="Siswa SMK Islamiyah Ciputat di lingkungan sekolah"
                        fetchpriority="high"
                    >
                </div>
            </header>

            <nav class="bkk-jobs-tabs" aria-label="Navigasi Bursa Kerja Khusus">
                <a class="is-active" href="{{ route('bkk.jobs') }}" aria-current="page">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <rect x="3" y="7" width="18" height="13" rx="2" stroke="currentColor" stroke-width="1.8"/>
                        <path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 12h18m-11 0v2h4v-2" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/>
                    </svg>
                    Lowongan Tersedia
                </a>
                <a href="{{ route('bkk.tracer-study') }}">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/>
                        <path d="M8 7h8M8 11h7" stroke="currentColor" stroke-linecap="round" stroke-width="1.8"/>
                    </svg>
                    Tracer Study
                </a>
            </nav>

            @if ($jobs->isNotEmpty())
                <div class="public-jobs-grid bkk-jobs-grid">
                    @foreach ($jobs as $job)
                        @php
                            $company = data_get($job->metadata, 'company');
                            $position = data_get($job->metadata, 'position') ?: $job->title;
                            $closingDate = data_get($job->metadata, 'closing_date');
                            $applicationUrl = data_get($job->metadata, 'application_url');
                            $companyImage = $siteConfiguration->imageUrl($job->image_path);
                        @endphp
                        <article class="public-job-card bkk-job-card">
                            <header class="bkk-job-card__company">
                                <span class="bkk-job-card__logo">
                                    @if ($companyImage)
                                        <img src="{{ $companyImage }}" alt="" loading="lazy">
                                    @else
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <rect x="3" y="7" width="18" height="13" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                            <path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 12h18m-11 0v2h4v-2" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/>
                                        </svg>
                                    @endif
                                </span>
                                @if (filled($company))
                                    <span>{{ $company }}</span>
                                @endif
                            </header>

                            <h2>{{ $position }}</h2>

                            <div class="bkk-job-card__meta">
                                @if (filled($job->category))
                                    <span class="bkk-job-card__meta-item">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <rect x="3" y="7" width="18" height="13" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                            <path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" stroke="currentColor" stroke-linecap="round" stroke-width="1.8"/>
                                        </svg>
                                        {{ $job->category }}
                                    </span>
                                @endif
                                @if (filled($closingDate))
                                    <span class="bkk-job-card__meta-item bkk-job-card__meta-item--date">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                            <path d="M16 3v4M8 3v4M3 10h18" stroke="currentColor" stroke-linecap="round" stroke-width="1.8"/>
                                        </svg>
                                        Batas lamaran: {{ $closingDate }}
                                    </span>
                                @endif
                            </div>

                            @if (filled($job->summary))
                                <p class="bkk-job-card__summary">{{ $job->summary }}</p>
                            @endif

                            @if (filled($applicationUrl))
                                <a class="bkk-job-card__link" href="{{ $applicationUrl }}" target="_blank" rel="noopener noreferrer">
                                    Lihat Detail
                                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/>
                                    </svg>
                                </a>
                            @endif
                        </article>
                    @endforeach
                </div>
                @if ($jobs->hasPages())
                    <div class="bkk-jobs-pagination">{{ $jobs->links() }}</div>
                @endif
            @else
                <section class="bkk-jobs-empty" aria-labelledby="bkk-jobs-empty-title">
                    <span class="bkk-jobs-empty__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <rect x="3" y="7" width="18" height="13" rx="2" stroke="currentColor" stroke-width="1.6"/>
                            <path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 12h18m-11 0v2h4v-2" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"/>
                            <circle cx="18" cy="18" r="4" fill="currentColor"/>
                            <path d="m17 18 1 1 2-2" stroke="#fff" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2"/>
                        </svg>
                    </span>
                    <div>
                        <h2 id="bkk-jobs-empty-title">Belum Ada Lowongan Pekerjaan</h2>
                        <p>Informasi lowongan akan diperbarui ketika tersedia. Silakan kembali lagi untuk melihat peluang kerja terbaru.</p>
                    </div>
                </section>
            @endif
        </div>
    </section>
@endsection