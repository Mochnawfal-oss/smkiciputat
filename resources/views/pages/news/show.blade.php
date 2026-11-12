@extends('layouts.app')

@section('title', $article->meta_title ?: $article->title.' | '.$siteSettings['identity.school_name'])
@section('meta_description', $article->meta_description ?: $article->summary)
@section('meta_image', $article->image_path ? $siteConfiguration->imageUrl($article->image_path) : '')
@section('meta_type', 'article')

@php
    $articleImage = $article->image_path ? $siteConfiguration->imageUrl($article->image_path) : null;
    $articleAuthor = data_get($article->metadata, 'author');
    $storedImages = data_get($article->metadata, 'images', []);
    $articleImages = is_array($storedImages)
        ? collect($storedImages)
            ->filter(fn (mixed $image): bool => is_string($image) && trim($image) !== '')
            ->values()
        : collect();
    $articleSchema = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'NewsArticle',
        'headline' => $article->title,
        'description' => $article->meta_description ?: $article->summary,
        'mainEntityOfPage' => route('news.show', $article->slug),
        'datePublished' => $article->published_at?->toAtomString(),
        'dateModified' => $article->updated_at?->toAtomString(),
        'image' => $articleImage ? url($articleImage) : null,
        'publisher' => [
            '@type' => 'Organization',
            'name' => $siteSettings['identity.school_name'],
        ],
    ], fn (mixed $value): bool => $value !== null && $value !== '');
@endphp

@push('head')
    <script type="application/ld+json">@json($articleSchema)</script>
@endpush

@section('content')
    <article class="news-detail-page">
        <div class="container news-detail__container">
            <nav class="news-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('home') }}">Beranda</a>
                <span aria-hidden="true">›</span>
                <a href="{{ route('news.index') }}">Berita</a>
                <span aria-hidden="true">›</span>
                <span aria-current="page">{{ $article->title }}</span>
            </nav>

            <header class="news-article-header">
                @if ($article->category)
                    <span class="news-card-category">{{ $article->category }}</span>
                @endif
                <h1>{{ $article->title }}</h1>
                <div class="news-article-header__bottom">
                    <div class="news-article-byline">
                        @if ($article->published_at)
                            <time datetime="{{ $article->published_at->toDateString() }}">{{ $article->published_at->format('d/m/Y') }}</time>
                        @endif
                        @if (filled($articleAuthor))
                            <span>{{ $articleAuthor }}</span>
                        @endif
                    </div>
                    <div class="news-share" aria-label="Bagikan artikel">
                        <span class="news-share__label">Bagikan</span>
                        <a
                            class="news-share__button news-share__button--whatsapp"
                            href="https://wa.me/?text={{ urlencode($article->title.' '.route('news.show', $article->slug)) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            aria-label="Bagikan lewat WhatsApp"
                        >WA</a>
                        <a
                            class="news-share__button news-share__button--facebook"
                            href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('news.show', $article->slug)) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            aria-label="Bagikan lewat Facebook"
                        >f</a>
                        <button
                            class="news-share__button news-share__button--copy"
                            type="button"
                            data-copy-article-link="{{ route('news.show', $article->slug) }}"
                            aria-label="Salin tautan artikel"
                        >⧉</button>
                        <span class="news-share__status" data-copy-article-status role="status" aria-live="polite"></span>
                    </div>
                </div>
            </header>

            @if ($articleImage)
                <figure class="news-article-cover">
                    <img src="{{ $articleImage }}" alt="Foto {{ $article->title }}" fetchpriority="high">
                </figure>
            @endif

            <div class="news-article-body">
                @if ($article->summary)
                    <p class="news-article-lead">{{ $article->summary }}</p>
                @endif
                @if ($article->body)
                    <div class="news-article-content">{{ $article->body }}</div>
                @endif
            </div>

            @if ($articleImages->isNotEmpty())
                <section class="news-article-gallery" aria-label="Foto tambahan berita">
                    @foreach ($articleImages as $image)
                        <figure>
                            <img src="{{ $siteConfiguration->imageUrl($image) }}" alt="Foto {{ $article->title }} {{ $loop->iteration }}" loading="lazy">
                        </figure>
                    @endforeach
                </section>
            @endif

            @if ($previousArticle || $nextArticle)
                <nav class="news-article-navigation" aria-label="Navigasi artikel">
                    @if ($previousArticle)
                        <a class="news-article-navigation__item" href="{{ route('news.show', $previousArticle->slug) }}">
                            <span aria-hidden="true">←</span>
                            <span>
                                <small>Berita Sebelumnya</small>
                                <strong>{{ $previousArticle->title }}</strong>
                            </span>
                        </a>
                    @endif
                    @if ($nextArticle)
                        <a class="news-article-navigation__item news-article-navigation__item--next" href="{{ route('news.show', $nextArticle->slug) }}">
                            <span>
                                <small>Berita Berikutnya</small>
                                <strong>{{ $nextArticle->title }}</strong>
                            </span>
                            <span aria-hidden="true">→</span>
                        </a>
                    @endif
                </nav>
            @endif
        </div>
    </article>
@endsection
