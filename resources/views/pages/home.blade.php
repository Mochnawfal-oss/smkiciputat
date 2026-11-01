@extends('layouts.app')

@section('title', 'Beranda | SMK Islamiyah Ciputat')

@section('content')

<div class="hero-statistics-reveal">
{{-- HERO --}}
<section class="school-hero overflow-hidden bg-[#F7F8F2] py-4 md:py-14 lg:py-[72px]">
    <div class="mx-auto max-w-[1180px] px-5 sm:px-6 lg:px-8">
        <div class="grid items-center gap-5 lg:grid-cols-[48fr_52fr] lg:gap-8">
            <div class="order-2 max-w-[520px] lg:order-1">
                <div class="mb-3 flex items-center gap-3 text-[10px] font-medium tracking-[0.16em] text-[#68746B] uppercase sm:text-[11px] md:mb-5">
                    <span class="h-px w-7 bg-[#8FAE98]"></span>
                    <span>SMK ISLAMIYAH CIPUTAT</span>
                </div>

                <h1 class="font-serif text-[32px] font-medium leading-[1.1] tracking-[-0.035em] text-[#34483B] md:text-[38px] lg:text-[46px]">
                    <span class="block">Belajar Hari Ini,</span>
                    <span class="mt-0.5 block italic font-normal text-[#668875]">Siap</span>
                    <span class="mt-0.5 block italic font-normal text-[#668875]">Menghadapi</span>
                    <span class="mt-0.5 block italic font-normal text-[#668875]">Masa Depan.</span>
                </h1>

                <p class="mt-4 max-w-[480px] text-[13px] font-normal leading-[1.6] text-[#68746B] sm:text-[14px]">
                    Tempat terbaik untuk mengembangkan pengetahuan, keterampilan praktis, dan karakter islami melalui pembelajaran yang relevan dengan kebutuhan dunia kerja.
                </p>

                <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-3 md:mt-6">
                    <a href="{{ route('spmb') }}" class="inline-flex h-11 items-center gap-2 rounded-lg bg-[#668875] px-5 text-[13px] font-semibold text-white transition-colors hover:bg-[#587964]">
                        Informasi SPMB
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                            <path fill-rule="evenodd" d="M5.22 14.78a.75.75 0 0 0 1.06 0L13.5 6.56v3.19a.75.75 0 0 0 1.5 0V4.75a.75.75 0 0 0-.75-.75h-5a.75.75 0 0 0 0 1.5h3.19l-7.22 7.22a.75.75 0 0 0 0 1.06Z" clip-rule="evenodd"/>
                        </svg>
                    </a>

                    <a href="{{ route('about') }}" class="group inline-flex h-11 items-center gap-2 border-b border-[#E3E8DF] text-[13px] font-medium text-[#34483B] transition-colors hover:text-[#668875]">
                        Tentang Sekolah
                        <span class="transition-transform duration-200 group-hover:translate-x-1">→</span>
                    </a>
                </div>
            </div>

            <div class="relative order-1 mx-auto w-full max-w-[560px] lg:order-2">
                <div aria-hidden="true" class="absolute -right-3 -top-3 h-16 w-16 bg-[radial-gradient(circle,#8FAE98_1.2px,transparent_1.4px)] bg-[size:10px_10px] opacity-60 sm:-right-4 sm:-top-4 sm:h-20 sm:w-20"></div>
                <div aria-hidden="true" class="absolute -bottom-3 right-5 h-20 w-20 rounded-full bg-[#D8CBA9]/70 sm:-bottom-4 sm:right-8 sm:h-24 sm:w-24"></div>

                <div class="relative h-[230px] sm:h-[250px] md:h-[340px] lg:h-[360px]">
                    <div class="absolute inset-0 overflow-hidden rounded-2xl border border-[#E3E8DF] bg-white shadow-[0_16px_34px_rgba(52,72,59,0.10)] sm:rounded-[20px]">
                        <img
                            class="h-full w-full object-cover"
                            src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=1200&q=85"
                            alt="Siswa belajar dan berdiskusi bersama di kelas"
                        >
                    </div>

                    <div class="absolute -bottom-2 left-3 h-[42%] w-[30%] overflow-hidden rounded-xl border-4 border-[#F7F8F2] bg-white shadow-[0_10px_24px_rgba(52,72,59,0.14)] sm:-bottom-3 sm:left-5 sm:rounded-2xl">
                        <img
                            class="h-full w-full object-cover"
                            src="https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=600&q=80"
                            alt="Suasana kegiatan belajar di sekolah"
                            loading="lazy"
                        >
                    </div>

                    <div class="absolute -bottom-4 right-2 inline-flex items-center gap-2 rounded-xl border border-[#E3E8DF] bg-white px-3 py-2 shadow-[0_10px_24px_rgba(52,72,59,0.10)] sm:right-4">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#E4EDE4] text-[12px] font-semibold text-[#668875]">06</span>
                        <span class="text-[11px] font-medium text-[#34483B]">Program Keahlian</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="hero-statistics-reveal__stats bg-[#F7F8F2] px-5 py-3 sm:px-6 lg:px-8" aria-label="Statistik sekolah">
    <div class="mx-auto max-w-[1180px] py-2">
        <div class="grid grid-cols-2 md:grid-cols-4">
            <div class="flex flex-col items-center gap-1 px-3 py-3 text-center">
                <strong class="text-[22px] font-semibold leading-none text-[#34483B] md:text-[26px]">—</strong>
                <span class="text-[11px] font-medium text-[#68746B] md:text-[12px]">Jumlah Siswa</span>
            </div>
            <div class="flex flex-col items-center gap-1 px-3 py-3 text-center">
                <strong class="text-[22px] font-semibold leading-none text-[#34483B] md:text-[26px]">—</strong>
                <span class="text-[11px] font-medium text-[#68746B] md:text-[12px]">Jumlah Guru</span>
            </div>
            <div class="flex flex-col items-center gap-1 px-3 py-3 text-center">
                <strong class="text-[22px] font-semibold leading-none text-[#34483B] md:text-[26px]">—</strong>
                <span class="text-[11px] font-medium text-[#68746B] md:text-[12px]">Tahun Berdiri</span>
            </div>
            <div class="flex flex-col items-center gap-1 px-3 py-3 text-center">
                <strong class="text-[22px] font-semibold leading-none text-[#34483B] md:text-[26px]">—</strong>
                <span class="text-[11px] font-medium text-[#68746B] md:text-[12px]">Jumlah Rombel</span>
            </div>
        </div>
    </div>
</section>

{{-- PROFIL SINGKAT --}}
<section class="hero-statistics-reveal__about overflow-hidden bg-[#F7F8F2] py-12 md:py-16 lg:py-[72px]" id="profil">
    <div class="mx-auto max-w-[1180px] px-5 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 items-center gap-8 lg:grid-cols-12">
            <div class="relative order-1 mx-auto w-full max-w-[520px] lg:col-span-5 lg:mx-0">
                <div aria-hidden="true" class="absolute -bottom-3 -left-3 h-24 w-24 rounded-[20px] bg-[#E4EDE4] sm:-bottom-4 sm:-left-4"></div>
                <div aria-hidden="true" class="absolute -right-3 -top-3 h-16 w-16 bg-[radial-gradient(circle,#8FAE98_1px,transparent_1.2px)] bg-[size:9px_9px] opacity-60"></div>

                <figure class="relative m-0 h-[230px] overflow-hidden rounded-[18px] border border-[#E3E8DF] bg-white shadow-[0_14px_30px_rgba(52,72,59,0.10)] sm:h-[250px] lg:h-[400px] lg:rounded-[24px]">
                    <img
                        class="h-full w-full object-cover"
                        src="https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=1000&q=85"
                        alt="Kegiatan belajar siswa di dalam kelas"
                        loading="lazy"
                    >

                    <figcaption class="absolute inset-x-0 bottom-0 flex items-center justify-between bg-gradient-to-t from-[#34483B]/70 to-transparent px-4 pb-4 pt-10 text-[10px] font-medium tracking-[0.1em] text-white">
                        <span>PROSES PEMBELAJARAN</span>
                        <span>01 / 02</span>
                    </figcaption>
                </figure>
            </div>

            <div class="order-2 lg:col-span-7">
                <p class="mb-3 text-[11px] font-semibold tracking-[0.12em] text-[#668875]">
                    TENTANG SEKOLAH
                </p>

                <h2 class="mb-5 max-w-[560px] font-serif text-[30px] font-medium leading-[1.15] tracking-[-0.025em] text-[#34483B] md:text-[32px] lg:text-[38px]">
                    Pendidikan yang membekali
                    <span class="font-normal italic">lebih dari sekadar</span>
                    pengetahuan.
                </h2>

                <div class="space-y-4 text-[13px] font-normal leading-[1.65] text-[#68746B] md:text-[14px]">
                    <p>
                        SMK Islamiyah Ciputat membentuk siswa dengan pengetahuan, keterampilan, dan karakter yang siap menghadapi masa depan.
                    </p>

                    <p>
                        Beragam program keahlian membantu siswa menemukan minat, mengembangkan kemampuan, dan siap untuk melanjutkan pendidikan atau bekerja.
                    </p>
                </div>

                <a href="{{ route('about') }}" class="mt-5 inline-flex h-[42px] w-full items-center justify-center gap-2 rounded-[11px] bg-[#668875] px-5 text-[13px] font-medium text-white transition-colors hover:bg-[#587964] lg:w-auto">
                    Mengenal Sekolah
                    <i data-lucide="arrow-up-right" class="h-4 w-4"></i>
                </a>
            </div>
        </div>
    </div>
</section>
</div>

{{-- PROGRAM KEAHLIAN --}}
<section class="programs-section section">
    <div class="container">

        <div class="section-heading">
            <span class="section-label">PROGRAM PENDIDIKAN</span>
            <span class="section-caption">ENAM BIDANG KEAHLIAN</span>
        </div>

        <div class="program-header">
            <h2 class="editorial-heading">
                Pilih bidang yang
                <span>cocok untuk masa depanmu.</span>
            </h2>

            <p>
                Kenali program keahlian yang tersedia dan pilih jalur belajar yang sesuai dengan minat dan kemampuanmu.
            </p>
        </div>

        <div class="program-grid" data-program-accordion>
            <article class="program-card is-active" data-active="true" style="--program-color: #b86f4d; --program-surface: #f5e9e2;">
                <button class="program-card-trigger" type="button" aria-expanded="true" aria-controls="program-kuliner-detail" aria-label="Pilih program Kuliner">
                    <span class="program-card-rail-label">Kuliner</span>
                    <span class="program-card-trigger-icon" aria-hidden="true">+</span>
                </button>
                <div class="program-card-content" id="program-kuliner-detail" role="region" aria-labelledby="program-kuliner-title">
                    <figure class="program-card-visual">
                        <img src="{{ asset('images/2.jpg') }}" alt="Poster program Kuliner" loading="lazy">
                    </figure>
                    <div class="program-card-details">
                        <span class="program-code">KULINER</span>
                        <h3 id="program-kuliner-title">Kuliner</h3>
                        <p>Belajar pengolahan makanan, penyajian, dan keterampilan kuliner.</p>
                        <a href="{{ route('program.kuliner') }}">Pelajari program <span aria-hidden="true">↗</span></a>
                    </div>
                </div>
            </article>

            <article class="program-card" data-active="false" style="--program-color: #a75483; --program-surface: #f4e7ef;">
                <button class="program-card-trigger" type="button" aria-expanded="false" aria-controls="program-dkv-detail" aria-label="Pilih program DKV">
                    <span class="program-card-rail-label">DKV</span>
                    <span class="program-card-trigger-icon" aria-hidden="true">+</span>
                </button>
                <div class="program-card-content" id="program-dkv-detail" role="region" aria-labelledby="program-dkv-title" hidden>
                    <figure class="program-card-visual">
                        <img src="{{ asset('images/3.jpg') }}" alt="Poster program DKV" loading="lazy">
                    </figure>
                    <div class="program-card-details">
                        <span class="program-code">DKV</span>
                        <h3 id="program-dkv-title">DKV</h3>
                        <p>Mengasah kreativitas dalam desain grafis dan komunikasi visual.</p>
                        <a href="{{ route('program.dkv') }}">Pelajari program <span aria-hidden="true">↗</span></a>
                    </div>
                </div>
            </article>

            <article class="program-card" data-active="false" style="--program-color: #567cab; --program-surface: #e8eef5;">
                <button class="program-card-trigger" type="button" aria-expanded="false" aria-controls="program-tkj-detail" aria-label="Pilih program TKJ">
                    <span class="program-card-rail-label">TKJ</span>
                    <span class="program-card-trigger-icon" aria-hidden="true">+</span>
                </button>
                <div class="program-card-content" id="program-tkj-detail" role="region" aria-labelledby="program-tkj-title" hidden>
                    <figure class="program-card-visual">
                        <img src="{{ asset('images/4.jpg') }}" alt="Poster program TKJ" loading="lazy">
                    </figure>
                    <div class="program-card-details">
                        <span class="program-code">TKJ</span>
                        <h3 id="program-tkj-title">TKJ</h3>
                        <p>Memahami komputer, jaringan, dan teknologi informasi modern.</p>
                        <a href="{{ route('program.tkj') }}">Pelajari program <span aria-hidden="true">↗</span></a>
                    </div>
                </div>
            </article>

            <article class="program-card" data-active="false" style="--program-color: #43836c; --program-surface: #e8f0eb;">
                <button class="program-card-trigger" type="button" aria-expanded="false" aria-controls="program-ak-detail" aria-label="Pilih program Akuntansi">
                    <span class="program-card-rail-label">Akuntansi</span>
                    <span class="program-card-trigger-icon" aria-hidden="true">+</span>
                </button>
                <div class="program-card-content" id="program-ak-detail" role="region" aria-labelledby="program-ak-title" hidden>
                    <figure class="program-card-visual">
                        <img src="{{ asset('images/5.jpg') }}" alt="Poster program Akuntansi" loading="lazy">
                    </figure>
                    <div class="program-card-details">
                        <span class="program-code">AK</span>
                        <h3 id="program-ak-title">Akuntansi</h3>
                        <p>Belajar pencatatan transaksi dan pengelolaan keuangan.</p>
                        <a href="{{ route('program.ak') }}">Pelajari program <span aria-hidden="true">↗</span></a>
                    </div>
                </div>
            </article>

            <article class="program-card" data-active="false" style="--program-color: #70589b; --program-surface: #eeeaf4;">
                <button class="program-card-trigger" type="button" aria-expanded="false" aria-controls="program-mp-detail" aria-label="Pilih program Manajemen Perkantoran">
                    <span class="program-card-rail-label">Manajemen Perkantoran</span>
                    <span class="program-card-trigger-icon" aria-hidden="true">+</span>
                </button>
                <div class="program-card-content" id="program-mp-detail" role="region" aria-labelledby="program-mp-title" hidden>
                    <figure class="program-card-visual">
                        <img src="{{ asset('images/5.jpg') }}" alt="Poster Akuntansi sebagai visual pendukung program Manajemen Perkantoran" loading="lazy">
                    </figure>
                    <div class="program-card-details">
                        <span class="program-code">MP</span>
                        <h3 id="program-mp-title">Manajemen Perkantoran</h3>
                        <p>Mengembangkan kemampuan administrasi dan manajemen kantor.</p>
                        <a href="{{ route('program.mp') }}">Pelajari program <span aria-hidden="true">↗</span></a>
                    </div>
                </div>
            </article>

            <article class="program-card" data-active="false" style="--program-color: #46534c; --program-surface: #e9ede9;">
                <button class="program-card-trigger" type="button" aria-expanded="false" aria-controls="program-bd-detail" aria-label="Pilih program Bisnis Digital">
                    <span class="program-card-rail-label">Bisnis Digital</span>
                    <span class="program-card-trigger-icon" aria-hidden="true">+</span>
                </button>
                <div class="program-card-content" id="program-bd-detail" role="region" aria-labelledby="program-bd-title" hidden>
                    <figure class="program-card-visual">
                        <img src="{{ asset('images/1.jpg') }}" alt="Poster program Bisnis Digital" loading="lazy">
                    </figure>
                    <div class="program-card-details">
                        <span class="program-code">BD</span>
                        <h3 id="program-bd-title">Bisnis Digital</h3>
                        <p>Mengembangkan kemampuan bisnis dan pemanfaatan teknologi digital.</p>
                        <a href="{{ route('program.bd') }}">Pelajari program <span aria-hidden="true">↗</span></a>
                    </div>
                </div>
            </article>
        </div>
        <p class="program-hint">Arahkan kursor ke kartu atau pilih dengan sentuhan untuk melihat detail program.</p>
    </div>
</section>

{{-- SAMBUTAN KEPALA SEKOLAH --}}
<section class="principal-section section" aria-labelledby="principal-section-title">
    <div class="container">
        <article class="teacher-card principal-card">
            <div class="principal-portrait">
                <div class="principal-portrait-media">
                    <span class="teacher-placeholder-photo" role="img" aria-label="Placeholder foto Kepala Sekolah">
                        <span class="teacher-placeholder-person" aria-hidden="true"></span>
                        <span class="teacher-photo-note">FOTO KEPALA SEKOLAH · TAMPIL UTUH</span>
                    </span>
                    <span class="principal-portrait-caption">
                        <span class="teacher-card-label">PIMPINAN SEKOLAH</span>
                        <span class="teacher-card-title">Kepala Sekolah</span>
                    </span>
                    <div class="teacher-card-details principal-profile-details" id="teacher-profile-principal" aria-hidden="true" inert>
                        <div class="teacher-popup-copy">
                            <span class="teacher-card-label">PIMPINAN SEKOLAH</span>
                            <h3>Nama Kepala Sekolah akan ditambahkan</h3>
                            <p>Foto dan informasi profil resmi akan ditambahkan setelah tersedia.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="principal-welcome">
                <span class="principal-welcome-status">DRAF · MENUNGGU PENINJAUAN</span>
                <span class="teacher-card-label">SAMBUTAN KEPALA SEKOLAH</span>
                <h2 class="editorial-heading" id="principal-section-title">
                    Selamat datang di
                    <span>SMK Islamiyah Ciputat.</span>
                </h2>
                <blockquote>
                    <p>Assalamu’alaikum warahmatullahi wabarakatuh.</p>
                    <p>Selamat datang di SMK Islamiyah Ciputat. Kami berkomitmen mendampingi peserta didik untuk tumbuh, mengembangkan karakter, serta membekali diri dengan pengetahuan dan keterampilan.</p>
                    <p>Semoga website ini dapat menjadi sumber informasi yang bermanfaat bagi peserta didik, orang tua, dan masyarakat untuk mengenal sekolah kami lebih dekat.</p>
                    <p>Wassalamu’alaikum warahmatullahi wabarakatuh.</p>
                </blockquote>

                <div class="principal-signature">
                    <span class="principal-signature-icon" aria-hidden="true"></span>
                    <span>
                        <strong>Nama Kepala Sekolah</strong>
                        <small>SMK Islamiyah Ciputat</small>
                    </span>
                </div>

                <div class="principal-actions">
                    <button class="teacher-card-trigger principal-profile-trigger" type="button" aria-expanded="false" aria-controls="teacher-profile-principal">
                        Lihat profil <span aria-hidden="true">→</span>
                    </button>
                    <a href="{{ route('about') }}">Kenali sekolah kami</a>
                </div>
            </div>

        </article>
    </div>
</section>

{{-- TENAGA PENDIDIK --}}
<section class="teacher-section section" aria-labelledby="teacher-section-title">
    <div class="container">
        <div class="educator-section-header">
            <div>
                <span class="section-label">TENAGA PENDIDIK</span>
                <h2 class="editorial-heading" id="teacher-section-title">
                    Para pendidik yang
                    <span>mendampingi perjalanan belajar siswa.</span>
                </h2>
            </div>
            <p>Kenali para pendidik yang mendampingi perjalanan belajar siswa.</p>
        </div>

        <div class="teacher-grid educator-grid">
            @foreach (range(1, 6) as $profile)
                <article class="teacher-card educator-card">
                    <button class="teacher-card-trigger" type="button" aria-expanded="false" aria-controls="teacher-profile-educator-{{ $profile }}" aria-label="Lihat pratinjau profil tenaga pendidik">
                        <span class="teacher-placeholder-photo" aria-hidden="true">
                            <span class="teacher-placeholder-person"></span>
                            <span class="teacher-photo-note">FOTO GURU · TAMPIL UTUH</span>
                        </span>
                        <span class="teacher-card-caption">
                            <span class="teacher-card-label">GURU</span>
                            <span class="teacher-card-title">Nama tenaga pendidik</span>
                            <span class="educator-card-subtitle">Mata pelajaran / jabatan</span>
                        </span>
                    </button>

                    <div class="teacher-card-details educator-profile-details" id="teacher-profile-educator-{{ $profile }}" aria-hidden="true" inert>
                        <div class="teacher-popup-copy">
                            <span class="teacher-card-label">PROFIL TENAGA PENDIDIK</span>
                            <h3>Nama tenaga pendidik akan ditambahkan</h3>
                            <p>Foto, mata pelajaran atau jabatan, dan deskripsi singkat akan ditambahkan setelah data resmi tersedia.</p>
                            <span class="teacher-popup-hint">Pratinjau tampilan profil</span>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <a class="educator-all-link" href="{{ route('teachers') }}">
            Lihat semua tenaga pendidik <span aria-hidden="true">→</span>
        </a>
    </div>
</section>

{{-- BERITA SEKOLAH --}}
<section class="news-section section">
    <div class="container">

        <div class="news-section-heading">
            <span class="news-section-label">BERITA SEKOLAH</span>
            <h2 id="latest-news-heading">Berita Terbaru</h2>
            <p>Informasi terkini seputar kegiatan dan prestasi di SMK Islamiyah Ciputat.</p>
        </div>

        <div class="news-grid">
            <section class="news-latest" aria-labelledby="latest-news-heading">
                <article class="news-featured-item">
                    <img
                        class="news-featured-image"
                        src="https://images.unsplash.com/photo-1523580846011-d3a5bc25702b?auto=format&amp;fit=crop&amp;w=600&amp;q=80"
                        alt="Suasana kegiatan belajar siswa di sekolah"
                        loading="lazy"
                    >
                    <div class="news-featured-content">
                        <time class="news-item-date" datetime="2026-10-28">28 Oktober 2026</time>
                        <h4>Gelar kreativitas siswa menampilkan karya terbaik</h4>
                        <p>Siswa berbagi karya dan pengalaman dalam kegiatan kreatif di lingkungan sekolah.</p>
                    </div>
                </article>
                <div class="news-carousel">
                    <div class="news-latest-list" data-news-track tabindex="0" aria-label="Daftar berita terbaru">
                        <article class="news-item news-carousel-item">
                        <img
                            class="news-item-image"
                            src="https://images.unsplash.com/photo-1498243691581-b145c3f54a5a?auto=format&amp;fit=crop&amp;w=360&amp;q=80"
                            alt="Siswa belajar di ruang kelas"
                            loading="lazy"
                        >
                        <div class="news-item-content">
                            <time class="news-item-date" datetime="2026-10-18">18 Oktober 2026</time>
                            <h4>Siswa mengikuti kegiatan belajar dan diskusi bersama</h4>
                            <p>Kegiatan kelas mendorong siswa untuk aktif bertukar gagasan dan bekerja sama.</p>
                        </div>
                        </article>

                        <article class="news-item news-carousel-item">
                        <img
                            class="news-item-image"
                            src="https://images.unsplash.com/photo-1529390079861-591de354faf5?auto=format&amp;fit=crop&amp;w=360&amp;q=80"
                            alt="Siswa beraktivitas bersama di sekolah"
                            loading="lazy"
                        >
                        <div class="news-item-content">
                            <time class="news-item-date" datetime="2026-10-10">10 Oktober 2026</time>
                            <h4>Kegiatan bersama membangun semangat dan kerja tim</h4>
                            <p>Siswa berpartisipasi dalam aktivitas yang mengasah kekompakan dan kepedulian.</p>
                        </div>
                        </article>

                        <article class="news-item news-carousel-item">
                        <img
                            class="news-item-image"
                            src="https://images.unsplash.com/photo-1524178232363-1fb2b075b655?auto=format&amp;fit=crop&amp;w=360&amp;q=80"
                            alt="Guru menyampaikan materi di kelas"
                            loading="lazy"
                        >
                        <div class="news-item-content">
                            <time class="news-item-date" datetime="2026-09-21">21 September 2026</time>
                            <h4>Suasana pembelajaran aktif di kelas</h4>
                            <p>Guru dan siswa berkolaborasi dalam suasana belajar yang interaktif.</p>
                        </div>
                        </article>

                        <article class="news-item news-carousel-item">
                        <img
                            class="news-item-image"
                            src="https://images.unsplash.com/photo-1427504494785-3a9ca7044f45?auto=format&amp;fit=crop&amp;w=360&amp;q=80"
                            alt="Suasana kegiatan belajar bersama"
                            loading="lazy"
                        >
                        <div class="news-item-content">
                            <time class="news-item-date" datetime="2026-09-03">3 September 2026</time>
                            <h4>Belajar bersama untuk memperkuat pemahaman materi</h4>
                            <p>Diskusi kelompok membantu siswa saling berbagi cara dan pengalaman belajar.</p>
                        </div>
                        </article>

                        <article class="news-item news-carousel-item">
                        <img
                            class="news-item-image"
                            src="https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&amp;fit=crop&amp;w=360&amp;q=80"
                            alt="Buku dan perlengkapan untuk kegiatan literasi"
                            loading="lazy"
                        >
                        <div class="news-item-content">
                            <time class="news-item-date" datetime="2026-08-28">28 Agustus 2026</time>
                            <h4>Menghidupkan kebiasaan membaca di lingkungan sekolah</h4>
                            <p>Kegiatan literasi mengajak siswa menjadikan membaca bagian dari keseharian.</p>
                        </div>
                        </article>

                        <article class="news-item news-carousel-item">
                        <img
                            class="news-item-image"
                            src="https://images.unsplash.com/photo-1529390079861-591de354faf5?auto=format&amp;fit=crop&amp;w=360&amp;q=80"
                            alt="Siswa mengikuti kegiatan pendidikan"
                            loading="lazy"
                        >
                        <div class="news-item-content">
                            <time class="news-item-date" datetime="2026-08-10">10 Agustus 2026</time>
                            <h4>Mengenal lingkungan sekolah melalui kegiatan orientasi</h4>
                            <p>Siswa baru berkenalan dengan lingkungan belajar dan warga sekolah.</p>
                        </div>
                        </article>
                    </div>

                    <div class="news-carousel-controls" aria-label="Navigasi carousel berita">
                        <div class="news-carousel-pages" data-news-pages aria-label="Halaman berita"></div>
                        <div class="news-carousel-arrows">
                            <button type="button" class="news-carousel-button" data-news-scroll="left" aria-label="Geser berita ke kiri" disabled>
                                <span aria-hidden="true">←</span>
                            </button>
                            <button type="button" class="news-carousel-button" data-news-scroll="right" aria-label="Geser berita ke kanan">
                                <span aria-hidden="true">→</span>
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <div class="news-sidebar">
                <div class="news-list">
                    <h3 class="news-group-heading">Berita Lama</h3>

                    <article class="news-archive-featured">
                        <img
                            class="news-item-image"
                            src="https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&amp;fit=crop&amp;w=360&amp;q=80"
                            alt="Ruang belajar di sekolah"
                            loading="lazy"
                        >
                        <div class="news-item-content">
                            <time class="news-item-date" datetime="2026-08-17">17 Agustus 2026</time>
                            <h4>Kebersamaan warga sekolah dalam peringatan kemerdekaan</h4>
                        </div>
                    </article>

                    <article class="news-item news-archive-item">
                        <div class="news-item-content">
                            <time class="news-item-date" datetime="2026-06-15">15 Juni 2026</time>
                            <h4>Hasil praktik keahlian siswa hadir dalam pameran karya</h4>
                        </div>
                    </article>

                    <article class="news-item news-archive-item">
                        <div class="news-item-content">
                            <time class="news-item-date" datetime="2026-04-20">20 April 2026</time>
                            <h4>Menumbuhkan budaya membaca melalui kegiatan literasi</h4>
                        </div>
                    </article>
                </div>

                <h4 class="news-video-heading">Video Sekolah</h4>
                <div class="news-video-player">
                    <div class="news-video-track" data-video-track tabindex="0" aria-label="Video sekolah">
                        <article class="news-video-slide">
                            <iframe
                                src="https://www.youtube-nocookie.com/embed/sYGenhpU1RM?autoplay=1&amp;mute=1&amp;playsinline=1&amp;controls=1&amp;loop=1&amp;playlist=sYGenhpU1RM&amp;rel=0"
                                title="Video sekolah 1"
                                allow="autoplay; accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                referrerpolicy="strict-origin-when-cross-origin"
                                allowfullscreen
                            ></iframe>
                        </article>
                        <article class="news-video-slide">
                            <iframe
                                src="https://www.youtube-nocookie.com/embed/wr0fisFw0fc?autoplay=1&amp;mute=1&amp;playsinline=1&amp;controls=1&amp;loop=1&amp;playlist=wr0fisFw0fc&amp;rel=0"
                                title="Video sekolah 2"
                                allow="autoplay; accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                referrerpolicy="strict-origin-when-cross-origin"
                                allowfullscreen
                            ></iframe>
                        </article>
                    </div>
                    <div class="news-video-controls" aria-label="Navigasi video sekolah">
                        <button type="button" class="news-carousel-button" data-video-scroll="left" aria-label="Video sebelumnya" disabled>
                            <span aria-hidden="true">←</span>
                        </button>
                        <button type="button" class="news-carousel-button" data-video-scroll="right" aria-label="Video berikutnya">
                            <span aria-hidden="true">→</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


{{-- SPMB --}}
<section class="admission-section">
    <div class="container admission-inner">

        <div>
            <span class="section-label">PENERIMAAN PESERTA DIDIK BARU</span>

            <h2>
                Mulai langkah
                <span>masa depanmu.</span>
            </h2>

            <p>
                Dapatkan informasi penerimaan siswa baru dan pilih program yang sesuai dengan minatmu.
            </p>

            <a href="{{ route('spmb') }}" class="button-light">
                Lihat Informasi SPMB <span>↗</span>
            </a>
        </div>

        <span class="admission-mark" aria-hidden="true">I</span>

    </div>
</section>

@endsection
