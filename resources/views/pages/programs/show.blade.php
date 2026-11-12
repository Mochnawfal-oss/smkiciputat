@extends('layouts.app')

@section('title', $program->meta_title ?: $program->title.' | '.$siteSettings['identity.school_name'])
@section('meta_description', $program->meta_description ?: $program->summary)
@section('meta_image', $program->image_path ? $siteConfiguration->imageUrl($program->image_path) : '')

@php
    $programImage = $program->image_path ? $siteConfiguration->imageUrl($program->image_path) : null;
    $competencies = collect(preg_split('/\r\n|\r|\n/', data_get($program->metadata, 'advantages', '') ?? ''))
        ->map(fn (string $item): string => trim($item))
        ->filter();
    $programHighlights = [
        ['label' => 'Kompetensi Utama', 'value' => data_get($program->metadata, 'primary_competence'), 'icon' => 'award'],
        ['label' => 'Fasilitas', 'value' => data_get($program->metadata, 'facilities'), 'icon' => 'building-2'],
        ['label' => 'Peluang Karier', 'value' => data_get($program->metadata, 'career_opportunities'), 'icon' => 'briefcase-business'],
    ];
@endphp

@section('content')
    <section class="program-detail">
        <div class="container program-detail__container">
            <nav class="program-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('home') }}">Beranda</a>
                <span aria-hidden="true">›</span>
                <a href="{{ route('home') }}#program-keahlian">Program Keahlian</a>
                <span aria-hidden="true">›</span>
                <span aria-current="page">{{ $program->title }}</span>
            </nav>

            <section class="program-hero {{ $programImage ? 'program-hero--with-image' : '' }}" aria-labelledby="program-title">
                <div class="program-hero__copy">
                    <span class="program-detail__eyebrow">Program Keahlian</span>
                    <h1 id="program-title">{{ $program->title }}</h1>
                    <span class="program-hero__rule" aria-hidden="true"></span>
                    @if ($program->summary)
                        <p>{{ $program->summary }}</p>
                    @endif
                </div>
                @if ($programImage)
                    <figure class="program-hero__image">
                        <img src="{{ $programImage }}" alt="Program keahlian {{ $program->title }}" fetchpriority="high">
                    </figure>
                @endif
            </section>

            @if (collect($programHighlights)->contains(fn (array $highlight): bool => filled($highlight['value'])))
                <section class="program-highlights" aria-label="Informasi program">
                    @foreach ($programHighlights as $highlight)
                        @if (filled($highlight['value']))
                            <article class="program-highlight">
                                <span class="program-highlight__icon" aria-hidden="true">
                                    <i data-lucide="{{ $highlight['icon'] }}"></i>
                                </span>
                                <div>
                                    <h2>{{ $highlight['label'] }}</h2>
                                    <p>{{ $highlight['value'] }}</p>
                                </div>
                            </article>
                        @endif
                    @endforeach
                </section>
            @endif

            <section class="program-about {{ $programPhotos->isNotEmpty() ? 'program-about--with-image' : '' }}" aria-labelledby="program-about-title">
                @if ($programPhotos->isNotEmpty())
                    <figure class="program-about__image">
                        <img src="{{ $siteConfiguration->imageUrl($programPhotos->first()->image_path) }}" alt="{{ $programPhotos->first()->title }}" loading="lazy">
                    </figure>
                @endif
                <div class="program-about__copy">
                    <h2 id="program-about-title">Tentang Program {{ $program->title }}</h2>
                    @if ($program->body ?: $program->summary)
                        <p class="program-about__description">{{ $program->body ?: $program->summary }}</p>
                    @endif
                    @if ($competencies->isNotEmpty())
                        <div class="program-competencies">
                            <h3><i data-lucide="book-open" aria-hidden="true"></i> Kompetensi yang Dipelajari</h3>
                            <ul>
                                @foreach ($competencies as $competency)
                                    <li><i data-lucide="circle-check" aria-hidden="true"></i>{{ $competency }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </section>

            @if ($programPhotos->isNotEmpty())
                <section class="program-gallery" aria-labelledby="program-gallery-title">
                    <h2 id="program-gallery-title">Galeri Kegiatan {{ $program->title }}</h2>
                    <div class="program-gallery__grid">
                        @foreach ($programPhotos->take(5) as $photo)
                            <figure>
                                <img src="{{ $siteConfiguration->imageUrl($photo->image_path) }}" alt="{{ $photo->title }}" loading="lazy">
                                @if ($photo->title)
                                    <figcaption>{{ $photo->title }}</figcaption>
                                @endif
                            </figure>
                        @endforeach
                    </div>
                </section>
            @endif

            <section class="program-cta" aria-label="Pendaftaran SPMB">
                <div>
                    <h2>Tertarik dengan Program {{ $program->title }}?</h2>
                    <p>Jadilah bagian dari SMK Islamiyah Ciputat dan kembangkan bakatmu bersama kami.</p>
                </div>
                <a href="{{ route('spmb') }}">Daftar SPMB Sekarang <span aria-hidden="true">→</span></a>
            </section>
        </div>
    </section>
@endsection
