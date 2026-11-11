@extends('layouts.app')

@section('title', $siteSettings['about.title'])
@section('meta_description', $siteSettings['about.description'])

@section('content')
    <section class="section">
        <div class="container">
            <header class="news-section-heading">
                <span class="section-label">INFORMASI SEKOLAH</span>
                <h1 class="editorial-heading">{{ $siteSettings['about.title'] }}</h1>
                @if ($siteSettings['about.description'])
                    <p>{{ $siteSettings['about.description'] }}</p>
                @endif
            </header>

            @if ($siteSettings['about.image'])
                <figure class="mx-auto my-8 max-w-5xl overflow-hidden rounded-xl">
                    <img class="max-h-[520px] w-full object-cover" src="{{ $siteConfiguration->imageUrl($siteSettings['about.image']) }}" alt="{{ $siteSettings['about.title'] }}" loading="lazy">
                </figure>
            @endif

            <div class="mx-auto grid max-w-5xl gap-8 md:grid-cols-2">
                @if ($siteSettings['about.history'])
                    <section>
                        <h2 class="editorial-heading">Sejarah</h2>
                        <p class="whitespace-pre-line leading-relaxed text-[#53665B]">{{ $siteSettings['about.history'] }}</p>
                    </section>
                @endif
                @if ($siteSettings['about.vision'])
                    <section>
                        <h2 class="editorial-heading">Visi</h2>
                        <p class="whitespace-pre-line leading-relaxed text-[#53665B]">{{ $siteSettings['about.vision'] }}</p>
                    </section>
                @endif
                @php($missions = collect(preg_split('/\r\n|\r|\n/', $siteSettings['about.mission']))->filter(fn (string $mission): bool => trim($mission) !== ''))
                @if ($missions->isNotEmpty())
                    <section class="md:col-span-2">
                        <h2 class="editorial-heading">Misi</h2>
                        <ul class="list-disc space-y-2 pl-5 text-[#53665B]">
                            @foreach ($missions as $mission)
                                <li>{{ trim($mission) }}</li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            </div>
        </div>
    </section>
@endsection