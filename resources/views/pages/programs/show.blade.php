@extends('layouts.app')

@section('title', $program->meta_title ?: $program->title.' | '.$siteSettings['identity.school_name'])
@section('meta_description', $program->meta_description ?: \Illuminate\Support\Str::limit(strip_tags($program->body ?? ''), 160))
@section('meta_image', $program->image_path ? $siteConfiguration->imageUrl($program->image_path) : '')

@php
    $programBodyText = is_string($program->body) ? trim(strip_tags($program->body)) : '';
    $programImagePath = $program->image_path ?: $programPhotos->first()?->image_path;
    $programImage = $programImagePath ? $siteConfiguration->imageUrl($programImagePath) : null;
    $aboutImagePath = $programPhotos->first()?->image_path ?: $program->image_path;
    $aboutImage = $aboutImagePath ? $siteConfiguration->imageUrl($aboutImagePath) : null;
    $competencyText = data_get($program->metadata, 'advantages');
    $competencies = collect(preg_split('/\r\n|\r|\n/', is_string($competencyText) ? $competencyText : ''))
        ->map(fn (string $item): string => trim($item))
        ->filter();
    $facilityText = data_get($program->metadata, 'facilities');
    $facilities = collect(preg_split('/\r\n|\r|\n/', is_string($facilityText) ? $facilityText : ''))
        ->map(fn (string $item): string => trim($item))
        ->filter();
    $programHighlights = collect([
        ['label' => 'Kompetensi Utama', 'value' => data_get($program->metadata, 'primary_competence'), 'icon' => 'award'],
        ['label' => 'Fasilitas Pendukung', 'value' => $facilities->take(2)->implode(', '), 'icon' => 'building-2'],
        ['label' => 'Kompetensi yang Dipelajari', 'value' => $competencies->first(), 'icon' => 'book-open'],
        ['label' => 'Peluang Karier', 'value' => data_get($program->metadata, 'career_opportunities'), 'icon' => 'briefcase-business'],
    ])
        ->filter(fn (array $highlight): bool => is_string($highlight['value']) && filled($highlight['value']))
        ->values();
    $programIntroduction = $programBodyText !== ''
        ? \Illuminate\Support\Str::limit($programBodyText, 170)
        : null;
    $hasAboutContent = $programBodyText !== '' || $competencies->isNotEmpty() || $facilities->isNotEmpty();
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

            <section class="program-hero program-hero--with-image" aria-labelledby="program-title">
                <div class="program-hero__copy">
                    <span class="program-detail__eyebrow">
                        <i data-lucide="graduation-cap" aria-hidden="true"></i>
                        Program Keahlian
                    </span>
                    <h1 id="program-title">{{ $program->title }}</h1>
                    <span class="program-hero__rule" aria-hidden="true"></span>
                    @if ($programIntroduction)
                        <p>{{ $programIntroduction }}</p>
                    @endif
                    @if ($hasAboutContent)
                        <div class="program-hero__actions">
                            <a class="program-hero__button program-hero__button--secondary" href="#program-about">
                                <i data-lucide="info" aria-hidden="true"></i>
                                Profil Lengkap
                            </a>
                        </div>
                    @endif
                </div>
                <figure class="program-hero__image">
                    @if ($programImage)
                        <img src="{{ $programImage }}" alt="Program keahlian {{ $program->title }}" fetchpriority="high">
                    @else
                        <div class="program-hero__placeholder" role="img" aria-label="Foto Program Keahlian {{ $program->title }}">
                            <i data-lucide="graduation-cap" aria-hidden="true"></i>
                            <span>{{ data_get($program->metadata, 'abbreviation', $program->title) }}</span>
                        </div>
                    @endif
                </figure>
            </section>

            @if ($programHighlights->isNotEmpty())
                <section class="program-highlights" aria-label="Informasi program">
                    @foreach ($programHighlights as $highlight)
                        <article class="program-highlight">
                            <span class="program-highlight__icon" aria-hidden="true">
                                <i data-lucide="{{ $highlight['icon'] }}"></i>
                            </span>
                            <div>
                                <h2>{{ $highlight['label'] }}</h2>
                                <p>{{ \Illuminate\Support\Str::limit($highlight['value'], 90) }}</p>
                            </div>
                        </article>
                    @endforeach
                </section>
            @endif

            @if ($hasAboutContent)
                <section class="program-about" id="program-about" aria-label="Informasi lengkap {{ $program->title }}">
                    @if ($aboutImage)
                        <figure class="program-about__image">
                            <img src="{{ $aboutImage }}" alt="{{ $programPhotos->first()?->title ?: 'Kegiatan Program '.$program->title }}" loading="lazy">
                        </figure>
                    @endif

                    @if ($programBodyText !== '' || $competencies->isNotEmpty())
                        <div class="program-about__copy">
                            <h2>Tentang Program {{ $program->title }}</h2>
                            @if ($programBodyText !== '')
                                <p class="program-about__description">{{ $programBodyText }}</p>
                            @endif
                            @if ($competencies->isNotEmpty())
                                <div class="program-competencies">
                                    <h3><i data-lucide="book-open" aria-hidden="true"></i> Kompetensi yang Dipelajari</h3>
                                    <ul>
                                        @foreach ($competencies as $competency)
                                            <li><i data-lucide="check" aria-hidden="true"></i>{{ $competency }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if ($facilities->isNotEmpty())
                        <aside class="program-facilities" aria-labelledby="program-facilities-title">
                            <h2 id="program-facilities-title">
                                <span><i data-lucide="settings" aria-hidden="true"></i></span>
                                Fasilitas Pendukung
                            </h2>
                            <ul>
                                @foreach ($facilities as $facility)
                                    <li><i data-lucide="check" aria-hidden="true"></i>{{ $facility }}</li>
                                @endforeach
                            </ul>
                            <i class="program-facilities__decoration" data-lucide="settings-2" aria-hidden="true"></i>
                        </aside>
                    @endif
                </section>
            @endif

            <section class="program-cta" aria-label="Pendaftaran SPMB">
                <span class="program-cta__icon" aria-hidden="true">
                    <i data-lucide="graduation-cap"></i>
                </span>
                <div>
                    <h2>Tertarik Bergabung di Program {{ $program->title }}?</h2>
                    <p>Kembangkan minat dan keterampilanmu bersama SMK Islamiyah Ciputat.</p>
                </div>
                <a href="{{ route('spmb') }}">Daftar SPMB Sekarang <i data-lucide="arrow-right" aria-hidden="true"></i></a>
            </section>

        </div>
    </section>
@endsection
