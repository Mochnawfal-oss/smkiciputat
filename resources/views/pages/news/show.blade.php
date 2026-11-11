@extends('layouts.app')

@section('title', $article->meta_title ?: $article->title.' | '.$siteSettings['identity.school_name'])
@section('meta_description', $article->meta_description ?: $article->summary)

@section('content')
    <article class="section">
        <div class="container max-w-4xl">
            <header class="news-section-heading">
                @if ($article->category)
                    <span class="section-label">{{ $article->category }}</span>
                @endif
                <h1 class="editorial-heading">{{ $article->title }}</h1>
                @if ($article->published_at)
                    <time class="news-item-date" datetime="{{ $article->published_at->toDateString() }}">{{ $article->published_at->format('d/m/Y') }}</time>
                @endif
                @if ($article->summary)
                    <p>{{ $article->summary }}</p>
                @endif
            </header>
            @if ($article->image_path)
                <figure class="my-8 overflow-hidden rounded-xl">
                    <img class="max-h-[560px] w-full object-cover" src="{{ $siteConfiguration->imageUrl($article->image_path) }}" alt="Foto {{ $article->title }}" fetchpriority="high">
                </figure>
            @endif
            @if ($article->body)
                <div class="whitespace-pre-line leading-relaxed text-[#53665B]">{{ $article->body }}</div>
            @endif
            <a class="principal-about-link" href="{{ route('news.index') }}">Kembali ke berita <span aria-hidden="true">→</span></a>
        </div>
    </article>
@endsection
