@extends('layouts.app')

@section('title', $siteSettings['seo.title'])
@section('meta_description', $siteSettings['seo.description'])

@section('content')
    @if ($siteSettings['home.hero.visible'])
        <section class="school-hero" aria-labelledby="school-hero-title">
            <div class="school-hero__inner">
                <div class="school-hero__copy">
                    <h1 id="school-hero-title">{{ $siteSettings['home.hero.title'] }}</h1>
                    <p class="school-hero__description">{{ $siteSettings['home.hero.description'] }}</p>
                    @if ($siteSettings['home.hero.slogan'])
                        <p class="school-hero__description">{{ $siteSettings['home.hero.slogan'] }}</p>
                    @endif
                    <div class="school-hero__actions">
                        <a class="school-hero__primary" href="{{ $siteSettings['home.hero.primary_url'] }}">{{ $siteSettings['home.hero.primary_label'] }}</a>
                        <a class="school-hero__secondary" href="{{ $siteSettings['home.hero.secondary_url'] }}">{{ $siteSettings['home.hero.secondary_label'] }}</a>
                    </div>
                </div>
                @if ($siteSettings['home.hero.image'])
                    <div class="school-hero__visual">
                        <span class="school-hero__circle" aria-hidden="true"></span>
                        <span class="school-hero__accent" aria-hidden="true"></span>
                        <span class="school-hero__outline" aria-hidden="true"></span>
                        <figure class="school-hero__photo">
                            <img class="school-hero__background" src="{{ $siteConfiguration->imageUrl($siteSettings['home.hero.image']) }}" alt="{{ $siteSettings['home.hero.image_alt'] }}" fetchpriority="high">
                            <span class="school-hero__shade" aria-hidden="true"></span>
                        </figure>
                        @if ($siteSettings['home.hero.badge'])
                            <div class="school-hero__badge school-hero__badge--faith">{{ $siteSettings['home.hero.badge'] }}</div>
                        @endif
                        @if ($siteSettings['home.hero.student_count'])
                            <div class="school-hero__badge school-hero__badge--spmb">
                                <span class="school-hero__calendar" aria-hidden="true"><i data-lucide="users"></i></span>
                                <span><strong>{{ $siteSettings['home.hero.student_count'] }}</strong><small>Jumlah peserta didik</small></span>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </section>
    @endif

    @if ($siteSettings['home.stats.visible'])
        <section class="hero-statistics-reveal__stats" aria-label="Informasi sekolah">
            <div class="hero-statistics-reveal__stats-inner">
                <div class="hero-statistics-reveal__stat">
                    <strong>{{ str_pad((string) $programs->count(), 2, '0', STR_PAD_LEFT) }}</strong>
                    <span>Program Keahlian</span>
                </div>
                <div class="hero-statistics-reveal__stat">
                    <strong>{{ $siteSettings['home.stats.student_count'] }}</strong>
                    <span>Jumlah Siswa</span>
                </div>
                <div class="hero-statistics-reveal__stat">
                    <strong>{{ $siteSettings['home.stats.teacher_count'] }}</strong>
                    <span>Jumlah Guru</span>
                </div>
                <div class="hero-statistics-reveal__stat hero-statistics-reveal__stat--location">
                    <i data-lucide="map-pin" aria-hidden="true"></i>
                    <strong>{{ $siteSettings['home.stats.city'] }}</strong>
                    <span>{{ $siteSettings['home.stats.region'] }}</span>
                </div>
            </div>
        </section>
    @endif

    @if ($siteSettings['home.about.visible'])
        <section class="hero-statistics-reveal__about overflow-hidden bg-[#E4E8DE] py-10 md:py-14 lg:py-16" id="profil">
            <div class="mx-auto max-w-[1180px] px-5 sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-[14px] bg-[#F7F8F2] shadow-[0_16px_36px_rgba(52,72,59,0.12)]">
                    <div class="grid grid-cols-1 gap-5 px-6 pt-7 sm:px-8 sm:pt-8 md:grid-cols-2 md:gap-8 md:px-10 lg:px-12 lg:pt-10">
                        <h2 class="max-w-[470px] font-sans text-[28px] font-extrabold leading-[1.05] tracking-[-0.045em] text-[#103526] sm:text-[32px] lg:text-[38px]">{{ $siteSettings['home.about.heading'] }}</h2>
                        <div class="flex flex-col items-start">
                            <div class="space-y-3 text-[12px] leading-[1.6] text-[#53665B] sm:text-[13px]">
                                <p>{{ $siteSettings['home.about.paragraph_1'] }}</p>
                                <p>{{ $siteSettings['home.about.paragraph_2'] }}</p>
                            </div>
                        </div>
                    </div>
                    @if ($siteSettings['home.about.image'])
                        <figure class="m-0 mt-6 aspect-[1.55/1] overflow-hidden sm:mt-8 sm:aspect-[2.2/1] md:mt-6 md:aspect-[2.8/1] lg:mt-5 lg:aspect-[3.2/1]">
                            <img class="h-full w-full object-cover object-[center_62%]" src="{{ $siteConfiguration->imageUrl($siteSettings['home.about.image']) }}" alt="{{ $siteSettings['home.about.image_alt'] }}" loading="lazy">
                        </figure>
                    @endif
                </div>
            </div>
        </section>
    @endif

    <section class="programs-section section" id="program-keahlian">
        <div class="container">
            <div class="section-heading">
                <span class="section-label">PROGRAM PENDIDIKAN</span>
                <span class="section-caption">{{ str_pad((string) $programs->count(), 2, '0', STR_PAD_LEFT) }} BIDANG KEAHLIAN</span>
            </div>
            <div class="program-header">
                <h2 class="editorial-heading">Pilih bidang yang <span>cocok untuk masa depanmu.</span></h2>
                <p>Kenali program keahlian yang tersedia dan pilih jalur belajar yang sesuai dengan minat dan kemampuanmu.</p>
            </div>
            @if ($programs->isNotEmpty())
                @php
                    $programColors = config('site-content.program_colors');
                @endphp
                <div class="program-grid" data-program-accordion>
                    @foreach ($programs as $program)
                        @php
                            $programColor = $programColors[$loop->index % count($programColors)];
                            $programId = 'program-'.$program->slug.'-detail';
                        @endphp
                        <article class="program-card {{ $loop->first ? 'is-active' : '' }}" data-active="{{ $loop->first ? 'true' : 'false' }}" style="--program-color: {{ $programColor[0] }}; --program-surface: {{ $programColor[1] }};">
                            <button class="program-card-trigger" type="button" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="{{ $programId }}" aria-label="Pilih program {{ $program->title }}">
                                <span class="program-card-rail-label">{{ $program->title }}</span>
                                <span class="program-card-trigger-icon" aria-hidden="true">+</span>
                            </button>
                            <div class="program-card-content" id="{{ $programId }}" role="region" aria-label="Informasi program {{ $program->title }}" @if (! $loop->first) hidden @endif>
                                @if ($program->image_path)
                                    <figure class="program-card-visual">
                                        <img src="{{ $siteConfiguration->imageUrl($program->image_path) }}" alt="Foto {{ $program->title }}" loading="lazy">
                                    </figure>
                                @else
                                    <figure class="program-card-visual program-card-visual-placeholder" role="img" aria-label="Visual program {{ $program->title }}">
                                        <span>{{ data_get($program->metadata, 'abbreviation', $program->title) }}</span>
                                    </figure>
                                @endif
                                <div class="program-card-details">
                                    <span class="program-code">{{ data_get($program->metadata, 'abbreviation', $program->title) }}</span>
                                    <h3>{{ $program->title }}</h3>
                                    <p>{{ $program->summary }}</p>
                                    <a href="{{ route('program.show', $program->slug) }}">Pelajari program <span aria-hidden="true">↗</span></a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
                <p class="program-hint">Arahkan kursor ke kartu atau pilih dengan sentuhan untuk melihat detail program.</p>
            @else
                <p class="program-hint">Informasi program keahlian akan segera diperbarui.</p>
            @endif
        </div>
    </section>

    @if ($siteSettings['home.principal.visible'])
        <section class="principal-section section" aria-labelledby="principal-section-title">
            <div class="container">
                <article class="principal-card">
                    <div class="principal-portrait">
                        <span class="principal-portrait-frame" aria-hidden="true">
                            @if ($siteSettings['home.principal.image'])
                                <img class="principal-portrait-image" src="{{ $siteConfiguration->imageUrl($siteSettings['home.principal.image']) }}" alt="">
                            @else
                                <span class="teacher-placeholder-photo"><span class="teacher-placeholder-person"></span></span>
                            @endif
                        </span>
                        @if ($siteSettings['home.principal.name'])
                            <span class="principal-name-badge">
                                <strong>{{ $siteSettings['home.principal.name'] }}</strong>
                                <small>{{ $siteSettings['home.principal.position'] }}</small>
                            </span>
                        @endif
                    </div>
                    <div class="principal-welcome">
                        <span class="teacher-card-label">SAMBUTAN KEPALA SEKOLAH</span>
                        <h2 class="editorial-heading" id="principal-section-title">Selamat datang di <span>{{ $siteSettings['identity.school_name'] }}.</span></h2>
                        <p class="principal-greeting">{{ $siteSettings['home.principal.greeting'] }}</p>
                        <blockquote><p>{{ $siteSettings['home.principal.message'] }}</p></blockquote>
                        <p class="principal-supporting-copy">{{ $siteSettings['home.principal.description'] }}</p>
                        <a class="principal-about-link" href="{{ route('about') }}">Kenali sekolah kami <span aria-hidden="true">→</span></a>
                    </div>
                </article>
            </div>
        </section>
    @endif

    @if ($siteSettings['home.teachers.visible'])
        <section class="teacher-section educator-lanyard-section" aria-labelledby="teacher-section-title">
            <div class="container educator-lanyard-inner">
                <div class="educator-lanyard-copy">
                    <h2 class="educator-lanyard-headline" id="teacher-section-title">Para pendidik yang mendampingi perjalanan belajar siswa.</h2>
                    <div class="educator-lanyard-details">
                        <p class="educator-lanyard-description">Kenali guru dan tenaga kependidikan yang membimbing siswa dari kelas sampai dunia kerja.</p>
                        <a class="educator-lanyard-cta" href="{{ route('teachers') }}">Lihat semua tenaga pendidik</a>
                    </div>
                </div>
                <div class="educator-lanyard-grid" aria-label="Kartu identitas tenaga pendidik">
                    @forelse ($teachers as $teacher)
                        <x-teacher-id-card
                            :teacher="$teacher"
                            :school-name="$siteSettings['identity.school_name']"
                            :photo-url="$siteConfiguration->imageUrl($teacher->image_path)"
                            :featured="$loop->first"
                            context="home"
                        />
                    @empty
                        <div class="educator-lanyard-empty" role="status">
                            <p>Informasi tenaga pendidik akan segera diperbarui.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    @endif

    @if ($siteSettings['home.news.visible'])
        <section class="news-section section">
            <div class="container">
                <div class="news-section-heading">
                    <span class="news-section-label">BERITA SEKOLAH</span>
                    <h2 id="latest-news-heading">{{ $siteSettings['home.news.heading'] }}</h2>
                    <p>{{ $siteSettings['home.news.description'] }}</p>
                    <a href="{{ route('news.index') }}">Semua berita <span aria-hidden="true">→</span></a>
                </div>
                @if ($featuredNews || $carouselNews->isNotEmpty() || $archiveNews->isNotEmpty())
                    <div class="news-grid">
                        <section class="news-latest" aria-labelledby="latest-news-heading">
                            @if ($featuredNews)
                                <article class="news-featured-item">
                                    @if ($featuredNews->image_path)
                                        <img class="news-featured-image" src="{{ $siteConfiguration->imageUrl($featuredNews->image_path) }}" alt="Foto {{ $featuredNews->title }}" loading="lazy">
                                    @endif
                                    <div class="news-featured-content">
                                        @if ($featuredNews->published_at)
                                            <time class="news-item-date" datetime="{{ $featuredNews->published_at->toDateString() }}">{{ $featuredNews->published_at->format('d/m/Y') }}</time>
                                        @endif
                                        <h4><a href="{{ route('news.show', $featuredNews->slug) }}">{{ $featuredNews->title }}</a></h4>
                                        @if ($featuredNews->summary)
                                            <p>{{ $featuredNews->summary }}</p>
                                        @endif
                                    </div>
                                </article>
                            @endif
                            @if ($carouselNews->isNotEmpty())
                                <div class="news-carousel">
                                    <div class="news-latest-list" data-news-track tabindex="0" aria-label="Daftar berita terbaru">
                                        @foreach ($carouselNews as $article)
                                            <article class="news-item news-carousel-item">
                                                @if ($article->image_path)
                                                    <img class="news-item-image" src="{{ $siteConfiguration->imageUrl($article->image_path) }}" alt="Foto {{ $article->title }}" loading="lazy">
                                                @endif
                                                <div class="news-item-content">
                                                    @if ($article->published_at)
                                                        <time class="news-item-date" datetime="{{ $article->published_at->toDateString() }}">{{ $article->published_at->format('d/m/Y') }}</time>
                                                    @endif
                                                    <h4><a href="{{ route('news.show', $article->slug) }}">{{ $article->title }}</a></h4>
                                                    @if ($article->summary)
                                                        <p>{{ $article->summary }}</p>
                                                    @endif
                                                </div>
                                            </article>
                                        @endforeach
                                    </div>
                                    <div class="news-carousel-controls" aria-label="Navigasi carousel berita">
                                        <div class="news-carousel-pages" data-news-pages aria-label="Halaman berita"></div>
                                        <div class="news-carousel-arrows">
                                            <button type="button" class="news-carousel-button" data-news-scroll="left" aria-label="Geser berita ke kiri" disabled><span aria-hidden="true">←</span></button>
                                            <button type="button" class="news-carousel-button" data-news-scroll="right" aria-label="Geser berita ke kanan"><span aria-hidden="true">→</span></button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </section>
                        @if ($archiveNews->isNotEmpty() || $siteSettings['home.news.video_1'] || $siteSettings['home.news.video_2'])
                            <aside class="news-sidebar">
                                @if ($archiveNews->isNotEmpty())
                                    <div class="news-list">
                                        <h3 class="news-group-heading">Berita Lama</h3>
                                        @foreach ($archiveNews as $article)
                                            <article class="{{ $loop->first ? 'news-archive-featured' : 'news-item news-archive-item' }}">
                                                @if ($loop->first && $article->image_path)
                                                    <img class="news-item-image" src="{{ $siteConfiguration->imageUrl($article->image_path) }}" alt="Foto {{ $article->title }}" loading="lazy">
                                                @endif
                                                <div class="news-item-content">
                                                    @if ($article->published_at)
                                                        <time class="news-item-date" datetime="{{ $article->published_at->toDateString() }}">{{ $article->published_at->format('d/m/Y') }}</time>
                                                    @endif
                                                    <h4><a href="{{ route('news.show', $article->slug) }}">{{ $article->title }}</a></h4>
                                                </div>
                                            </article>
                                        @endforeach
                                    </div>
                                @endif
                                @php
                                    $videoIds = array_filter([$siteSettings['home.news.video_1'], $siteSettings['home.news.video_2']]);
                                @endphp
                                @if ($videoIds)
                                    <h4 class="news-video-heading">Video Sekolah</h4>
                                    <div class="news-video-player">
                                        <div class="news-video-track" data-video-track tabindex="0" aria-label="Video sekolah">
                                            @foreach ($videoIds as $videoId)
                                                <article class="news-video-slide">
                                                    <iframe src="https://www.youtube-nocookie.com/embed/{{ $videoId }}?playsinline=1&amp;controls=1&amp;rel=0" title="Video sekolah {{ $loop->iteration }}" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                                                </article>
                                            @endforeach
                                        </div>
                                        @if (count($videoIds) > 1)
                                            <div class="news-video-controls" aria-label="Navigasi video sekolah">
                                                <button type="button" class="news-carousel-button" data-video-scroll="left" aria-label="Video sebelumnya" disabled><span aria-hidden="true">←</span></button>
                                                <button type="button" class="news-carousel-button" data-video-scroll="right" aria-label="Video berikutnya"><span aria-hidden="true">→</span></button>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </aside>
                        @endif
                    </div>
                @else
                    <p class="program-hint">Belum ada berita yang dipublikasikan.</p>
                @endif
            </div>
        </section>
    @endif

    @if ($siteSettings['home.bkk.visible'])
        <section class="bkk-section section">
            <div class="container bkk-inner">
                <div class="bkk-copy">
                    <span class="section-label">Bursa Kerja Khusus</span>
                    <h2 class="editorial-heading">{{ $siteSettings['home.bkk.heading'] }}</h2>
                    <p>{{ $siteSettings['home.bkk.description'] }}</p>
                    <div class="bkk-links">
                        <a href="{{ route('bkk.jobs') }}">
                            <span class="bkk-link-icon" aria-hidden="true"><i data-lucide="briefcase-business"></i></span>
                            <span class="bkk-link-copy"><strong>Lowongan pekerjaan</strong><small>Informasi lowongan pekerjaan</small></span>
                            <span class="bkk-link-arrow" aria-hidden="true">›</span>
                        </a>
                        <a href="{{ route('bkk.tracer-study') }}">
                            <span class="bkk-link-icon" aria-hidden="true"><i data-lucide="chart-no-axes-column-increasing"></i></span>
                            <span class="bkk-link-copy"><strong>Tracer study</strong><small>Informasi alumni setelah lulus</small></span>
                            <span class="bkk-link-arrow" aria-hidden="true">›</span>
                        </a>
                    </div>
                </div>
                @if ($siteSettings['home.bkk.image'])
                    <div class="bkk-visual">
                        <span class="bkk-image-accent" aria-hidden="true"></span>
                        <span class="bkk-image-outline" aria-hidden="true"></span>
                        <div class="bkk-image-wrap">
                            <img src="{{ $siteConfiguration->imageUrl($siteSettings['home.bkk.image']) }}" alt="Kegiatan belajar di sekolah" loading="lazy">
                        </div>
                        <div class="bkk-image-badge"><strong>2 layanan</strong><span>Informasi BKK untuk alumni</span></div>
                    </div>
                @endif
            </div>
        </section>
    @endif

    @if ($siteSettings['home.admissions.visible'])
        @php
            $admissionSteps = collect(preg_split('/\r\n|\r|\n/', $siteSettings['home.admissions.steps']))
                ->filter(fn (string $step): bool => trim($step) !== '')
                ->values();
        @endphp
        <section class="admission-section" aria-labelledby="admission-title">
            <div class="container admission-inner">
                <div class="admission-copy">
                    <span class="section-label">SPMB tahun ajaran {{ $siteSettings['home.admissions.year'] }}</span>
                    <h2 id="admission-title">{{ $siteSettings['home.admissions.heading'] }}</h2>
                    <p>{{ $siteSettings['home.admissions.description'] }}</p>
                    <a href="{{ route('spmb') }}" class="button-light">Lihat informasi SPMB</a>
                </div>
                @if ($admissionSteps->isNotEmpty())
                    <ol class="admission-steps" aria-label="Langkah pendaftaran SPMB">
                        @foreach ($admissionSteps as $step)
                            @php
                                $stepParts = array_pad(explode('|', $step, 2), 2, '');
                            @endphp
                            <li class="admission-step">
                                <span class="admission-step__number">{{ $loop->iteration }}</span>
                                <span class="admission-step__content">
                                    <span class="admission-step__label">{{ trim($stepParts[0]) }}</span>
                                    @if (trim($stepParts[1]) !== '')
                                        <span class="admission-step__description">{{ trim($stepParts[1]) }}</span>
                                    @endif
                                </span>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>
        </section>
    @endif
@endsection
