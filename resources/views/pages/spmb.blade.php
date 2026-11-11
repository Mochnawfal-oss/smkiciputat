@extends('layouts.app')

@section('title', $siteSettings['spmb.title'].' '.$siteSettings['spmb.year'])
@section('meta_description', $siteSettings['spmb.description'])

@section('content')
    @php
        $requirements = collect(preg_split('/\r\n|\r|\n/', $siteSettings['spmb.requirements']))
            ->filter(fn (string $item): bool => trim($item) !== '');
        $steps = collect(preg_split('/\r\n|\r|\n/', $siteSettings['spmb.steps']))
            ->filter(fn (string $item): bool => trim($item) !== '');
    @endphp
    <section class="admission-section">
        <div class="container admission-inner">
            <div class="admission-copy">
                <span class="section-label">SPMB TAHUN AJARAN {{ $siteSettings['spmb.year'] }}</span>
                <h1>{{ $siteSettings['spmb.title'] }}</h1>
                <p>{{ $siteSettings['spmb.description'] }}</p>
                <p>{{ $siteSettings['spmb.open'] ? 'Pendaftaran sedang dibuka.' : 'Pendaftaran saat ini belum dibuka.' }}</p>
                @if ($siteSettings['spmb.form_url'] && $siteSettings['spmb.open'])
                    <a class="button-light" href="{{ $siteSettings['spmb.form_url'] }}" target="_blank" rel="noopener noreferrer">Buka formulir pendaftaran</a>
                @endif
                @if ($siteSettings['spmb.contact'])
                    <p>Kontak panitia: {{ $siteSettings['spmb.contact'] }}</p>
                @endif
            </div>
            @if ($steps->isNotEmpty())
                <ol class="admission-steps" aria-label="Alur pendaftaran">
                    @foreach ($steps as $step)
                        <li class="admission-step">
                            <span class="admission-step__number">{{ $loop->iteration }}</span>
                            <span class="admission-step__content"><span class="admission-step__label">{{ $step }}</span></span>
                        </li>
                    @endforeach
                </ol>
            @endif
        </div>
    </section>

    @if ($siteSettings['spmb.schedule'] || $requirements->isNotEmpty() || $siteSettings['spmb.fee'])
        <section class="section">
            <div class="container grid gap-8 md:grid-cols-2">
                @if ($siteSettings['spmb.schedule'])
                    <div>
                        <h2 class="editorial-heading">Jadwal pendaftaran</h2>
                        <p class="whitespace-pre-line leading-relaxed text-[#53665B]">{{ $siteSettings['spmb.schedule'] }}</p>
                    </div>
                @endif
                @if ($requirements->isNotEmpty())
                    <div>
                        <h2 class="editorial-heading">Persyaratan</h2>
                        <ul class="list-disc space-y-2 pl-5 text-[#53665B]">
                            @foreach ($requirements as $requirement)
                                <li>{{ trim($requirement) }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if ($siteSettings['spmb.fee'])
                    <div>
                        <h2 class="editorial-heading">Informasi biaya</h2>
                        <p class="whitespace-pre-line leading-relaxed text-[#53665B]">{{ $siteSettings['spmb.fee'] }}</p>
                    </div>
                @endif
            </div>
        </section>
    @endif
@endsection