@extends('layouts.app')

@section('title', $siteSettings['bkk.title'])
@section('meta_description', $siteSettings['bkk.description'])

@section('content')
    <section class="section">
        <div class="container">
            <header class="news-section-heading">
                <span class="section-label">BURSA KERJA KHUSUS</span>
                <h1 class="editorial-heading">{{ $siteSettings['bkk.title'] }}</h1>
                <p>{{ $siteSettings['bkk.description'] }}</p>
            </header>
            @if ($jobs->isNotEmpty())
                <div class="grid gap-5 md:grid-cols-2">
                    @foreach ($jobs as $job)
                        <article class="rounded-xl border border-[#E2E8E1] bg-white p-6">
                            @if ($job->category)
                                <span class="section-label">{{ $job->category }}</span>
                            @endif
                            <h2 class="editorial-heading">{{ $job->title }}</h2>
                            @if (data_get($job->metadata, 'company'))
                                <p class="font-semibold">{{ data_get($job->metadata, 'company') }}</p>
                            @endif
                            @if ($job->summary)
                                <p class="leading-relaxed text-[#53665B]">{{ $job->summary }}</p>
                            @endif
                            @if (data_get($job->metadata, 'closing_date'))
                                <p class="text-sm text-[#53665B]">Batas pendaftaran: {{ data_get($job->metadata, 'closing_date') }}</p>
                            @endif
                            @if ($job->body)
                                <p class="mt-4 whitespace-pre-line leading-relaxed text-[#53665B]">{{ $job->body }}</p>
                            @endif
                            @if (data_get($job->metadata, 'application_url'))
                                <a class="principal-about-link" href="{{ data_get($job->metadata, 'application_url') }}" target="_blank" rel="noopener noreferrer">Informasi dan lamaran <span aria-hidden="true">→</span></a>
                            @endif
                        </article>
                    @endforeach
                </div>
                {{ $jobs->links() }}
            @else
                <p>Belum ada informasi lowongan yang tersedia.</p>
            @endif
        </div>
    </section>
@endsection