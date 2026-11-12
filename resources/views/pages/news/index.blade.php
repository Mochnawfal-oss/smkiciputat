@extends('layouts.app')

@section('title', 'Berita | '.$siteSettings['identity.school_name'])
@section('meta_description', $siteSettings['home.news.description'])

@section('content')
    <section class="news-index-page">
        <div class="container news-editorial__container">
            <x-public-page-heading
                eyebrow="BERITA SEKOLAH"
                :title="$siteSettings['home.news.heading']"
                :description="$siteSettings['home.news.description']"
            />

            <nav class="news-category-filter" aria-label="Filter kategori berita">
                <a
                    class="{{ $selectedCategory === null ? 'is-active' : '' }}"
                    href="{{ route('news.index') }}"
                    @if ($selectedCategory === null) aria-current="page" @endif
                >Semua</a>
                @foreach ($categories as $category)
                    <a
                        class="{{ $selectedCategory === $category ? 'is-active' : '' }}"
                        href="{{ route('news.index', ['category' => $category]) }}"
                        @if ($selectedCategory === $category) aria-current="page" @endif
                    >{{ $category }}</a>
                @endforeach
            </nav>

            @if ($featuredArticle)
                <article class="news-featured">
                    <a
                        class="news-featured__link {{ $featuredArticle->image_path ? '' : 'news-featured__link--without-image' }}"
                        href="{{ route('news.show', $featuredArticle->slug) }}"
                    >
                        @if ($featuredArticle->image_path)
                            <figure class="news-featured__image">
                                <img src="{{ $siteConfiguration->imageUrl($featuredArticle->image_path) }}" alt="Foto {{ $featuredArticle->title }}" fetchpriority="high">
                            </figure>
                        @endif
                        <div class="news-featured__copy">
                            <div class="news-card-meta">
                                @if ($featuredArticle->category)
                                    <span class="news-card-category">{{ $featuredArticle->category }}</span>
                                @endif
                                @if ($featuredArticle->published_at)
                                    <time datetime="{{ $featuredArticle->published_at->toDateString() }}">{{ $featuredArticle->published_at->format('d/m/Y') }}</time>
                                @endif
                            </div>
                            <h2>{{ $featuredArticle->title }}</h2>
                            @if ($featuredArticle->summary)
                                <p>{{ $featuredArticle->summary }}</p>
                            @endif
                            <span class="news-featured__action">Baca Selengkapnya <span aria-hidden="true">→</span></span>
                        </div>
                    </a>
                </article>
            @endif

            @if ($articles->isNotEmpty())
                <div class="news-card-grid">
                    @foreach ($articles as $article)
                        <article class="news-card">
                            <a class="news-card__link" href="{{ route('news.show', $article->slug) }}">
                                @if ($article->image_path)
                                    <figure class="news-card__image">
                                        <img src="{{ $siteConfiguration->imageUrl($article->image_path) }}" alt="Foto {{ $article->title }}" loading="lazy">
                                    </figure>
                                @endif
                                <div class="news-card__copy">
                                    <div class="news-card-meta">
                                        @if ($article->category)
                                            <span class="news-card-category">{{ $article->category }}</span>
                                        @endif
                                        @if ($article->published_at)
                                            <time datetime="{{ $article->published_at->toDateString() }}">{{ $article->published_at->format('d/m/Y') }}</time>
                                        @endif
                                    </div>
                                    <h2>{{ $article->title }}</h2>
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>
                {{ $articles->links() }}
            @elseif (! $featuredArticle)
                <p class="news-empty-state">Belum ada berita yang dipublikasikan.</p>
            @endif
        </div>
    </section>
@endsection
