@extends('layouts.app')

@section('title', $siteSettings['bkk.tracer_title'])
@section('meta_description', $siteSettings['bkk.tracer_description'])

@section('content')
    <section class="section">
        <div class="container max-w-4xl">
            <header class="news-section-heading">
                <span class="section-label">BURSA KERJA KHUSUS</span>
                <h1 class="editorial-heading">{{ $siteSettings['bkk.tracer_title'] }}</h1>
                <p>{{ $siteSettings['bkk.tracer_description'] }}</p>
            </header>
            @if ($siteSettings['bkk.cooperation'])
                <div class="whitespace-pre-line leading-relaxed text-[#53665B]">{{ $siteSettings['bkk.cooperation'] }}</div>
            @endif
            @if ($siteSettings['bkk.tracer_url'])
                <a class="principal-about-link" href="{{ $siteSettings['bkk.tracer_url'] }}" target="_blank" rel="noopener noreferrer">Isi formulir tracer study <span aria-hidden="true">→</span></a>
            @endif
        </div>
    </section>
@endsection