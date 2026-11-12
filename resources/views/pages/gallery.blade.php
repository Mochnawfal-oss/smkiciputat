@extends('layouts.app')

@section('title', 'Galeri | '.$siteSettings['identity.school_name'])
@section('meta_description', 'Galeri kegiatan dan dokumentasi '.$siteSettings['identity.school_name'].'.')

@section('content')
    <section class="section public-page public-page--gallery">
        <div class="container public-page__container">
            <x-public-page-heading eyebrow="GALERI SEKOLAH" title="Galeri" />
            @if ($photos->isNotEmpty())
                <div class="public-gallery-grid grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($photos as $photo)
                        <figure class="public-gallery-card overflow-hidden rounded-xl bg-white">
                            @if ($photo->image_path)
                                <img class="aspect-[4/3] w-full object-cover" src="{{ $siteConfiguration->imageUrl($photo->image_path) }}" alt="{{ $photo->title }}" loading="lazy">
                            @endif
                            <figcaption class="p-4">
                                <h2 class="font-semibold text-[#103526]">{{ $photo->title }}</h2>
                                @if ($photo->summary)
                                    <p class="mt-2 text-sm text-[#53665B]">{{ $photo->summary }}</p>
                                @endif
                            </figcaption>
                        </figure>
                    @endforeach
                </div>
                {{ $photos->links() }}
            @else
                <p>Galeri sekolah akan segera diperbarui.</p>
            @endif
        </div>
    </section>
@endsection
