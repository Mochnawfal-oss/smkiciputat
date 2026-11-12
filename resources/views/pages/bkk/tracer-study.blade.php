@extends('layouts.app')

@section('title', $siteSettings['bkk.tracer_title'])
@section('meta_description', $siteSettings['bkk.tracer_description'])

@section('content')
    <section class="section public-page public-page--tracer">
        <div class="container public-page__container max-w-4xl">
            <x-public-page-heading
                eyebrow="BURSA KERJA KHUSUS"
                :title="$siteSettings['bkk.tracer_title']"
                :description="$siteSettings['bkk.tracer_description']"
            />
            @if ($siteSettings['bkk.cooperation'])
                <div class="public-page__body whitespace-pre-line leading-relaxed text-[#53665B]">{{ $siteSettings['bkk.cooperation'] }}</div>
            @endif
            @if ($siteSettings['bkk.tracer_url'])
                <a class="principal-about-link" href="{{ $siteSettings['bkk.tracer_url'] }}" target="_blank" rel="noopener noreferrer">Isi formulir tracer study <span aria-hidden="true">→</span></a>
            @endif
        </div>
    </section>
@endsection