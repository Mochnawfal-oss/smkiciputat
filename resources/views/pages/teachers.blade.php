@extends('layouts.app')

@section('title', 'Tenaga Pendidik | SMK Islamiyah Ciputat')

@section('content')
    <section class="teacher-section section educators-page" aria-labelledby="educators-page-title">
        <div class="container educators-page-container">
            <header class="educator-section-header">
                <div>
                    <span class="section-label">TENAGA PENDIDIK</span>
                    <h1 class="editorial-heading" id="educators-page-title">
                        Para pendidik yang
                        <span>mendampingi perjalanan belajar siswa.</span>
                    </h1>
                </div>
                <p>Kenali para pendidik yang mendampingi perjalanan belajar siswa.</p>
            </header>

            <div class="educators-page-grid">
                @foreach (range(1, 6) as $profile)
                    <article class="educators-page-card">
                        <div class="educators-page-photo" role="img" aria-label="Placeholder foto tenaga pendidik">
                            <span class="teacher-placeholder-person" aria-hidden="true"></span>
                            <span class="educators-page-photo-note">FOTO PENDIDIK</span>
                        </div>

                        <div class="educators-page-caption">
                            <svg class="educators-page-person-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="1.7" />
                                <path d="M4.5 21v-2a7.5 7.5 0 0 1 15 0v2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
                            </svg>
                            <div class="educators-page-copy">
                                <h2>Nama tenaga pendidik</h2>
                                <p>Mata pelajaran / jabatan</p>
                            </div>
                            <span class="educators-page-arrow" aria-hidden="true">→</span>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endsection