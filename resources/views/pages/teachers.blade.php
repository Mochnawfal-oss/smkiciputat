@extends('layouts.app')

@section('title', $siteSettings['teachers.title'])
@section('meta_description', $siteSettings['teachers.description'])

@section('content')

    <section class="teacher-section section educators-page" aria-labelledby="educators-page-title">
        <div class="container educators-page-container">
            <div class="educators-page-intro">
                <header class="educators-page-intro-copy">
                    <span class="section-label">TENAGA KEPENDIDIKAN</span>
                    <h1 class="editorial-heading" id="educators-page-title">{{ $siteSettings['teachers.title'] }}</h1>
                </header>
                <p class="educators-page-intro-description">{{ $siteSettings['teachers.description'] }}</p>
            </div>

            <div class="educators-page-grid" aria-label="Daftar tenaga kependidikan">
                @forelse ($teachers as $teacher)
                    <x-teacher-id-card
                        :teacher="$teacher"
                        :school-name="$siteSettings['identity.school_name']"
                        :photo-url="$siteConfiguration->imageUrl($teacher->image_path)"
                    />
                @empty
                    <p class="educators-page-empty">Informasi tenaga kependidikan akan segera diperbarui.</p>
                @endforelse
            </div>

            {{ $teachers->links() }}
        </div>
    </section>
@endsection
