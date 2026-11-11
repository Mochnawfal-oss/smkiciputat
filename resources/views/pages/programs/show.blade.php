@extends('layouts.app')

@section('title', $program->meta_title ?: $program->title.' | '.$siteSettings['identity.school_name'])
@section('meta_description', $program->meta_description ?: $program->summary)

@section('content')
    <section class="section">
        <div class="container">
            <header class="news-section-heading">
                <span class="section-label">PROGRAM KEAHLIAN</span>
                <h1 class="editorial-heading">{{ $program->title }}</h1>
                @if ($program->summary)
                    <p>{{ $program->summary }}</p>
                @endif
            </header>
            @if ($program->image_path)
                <figure class="mx-auto my-8 max-w-5xl overflow-hidden rounded-xl">
                    <img class="max-h-[560px] w-full object-cover" src="{{ $siteConfiguration->imageUrl($program->image_path) }}" alt="Foto program {{ $program->title }}" loading="lazy">
                </figure>
            @endif
            @if ($program->body)
                <div class="mx-auto max-w-4xl whitespace-pre-line leading-relaxed text-[#53665B]">{{ $program->body }}</div>
            @endif
            @if (data_get($program->metadata, 'advantages'))
                <section class="mx-auto mt-10 max-w-4xl">
                    <h2 class="editorial-heading">Kompetensi yang dipelajari</h2>
                    <ul class="list-disc space-y-2 pl-5 text-[#53665B]">
                        @foreach (preg_split('/\r\n|\r|\n/', data_get($program->metadata, 'advantages')) as $advantage)
                            @if (trim($advantage) !== '')
                                <li>{{ trim($advantage) }}</li>
                            @endif
                        @endforeach
                    </ul>
                </section>
            @endif
            <a class="principal-about-link" href="{{ route('home') }}#program-keahlian">Kembali ke program keahlian <span aria-hidden="true">→</span></a>
        </div>
    </section>
@endsection
