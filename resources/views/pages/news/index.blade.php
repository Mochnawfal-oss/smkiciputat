@extends('layouts.app')

@section('title', 'Berita | '.$siteSettings['identity.school_name'])
@section('meta_description', $siteSettings['home.news.description'])

@section('content')
    <section class="section">
        <div class="container">
            <header class="news-section-heading">
                <span class="section-label">BERITA SEKOLAH</span>
                <h1 class="editorial-heading">{{ $siteSettings['home.news.heading'] }}</h1>
                <p>{{ $siteSettings['home.news.description'] }}</p>
            </header>
            @if ($articles->isNotEmpty())
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($articles as $article)
                        <article class="overflow-hidden rounded-xl border border-[#E2E8E1] bg-white">
                            @if ($article->image_path)
                                <img class="aspect-[16/10] w-full object-cover" src="{{ $siteConfiguration->imageUrl($article->image_path) }}" alt="Foto {{ $article->title }}" loading="lazy">
                            @endif
                            <div class="space-y-3 p-5">
                                @if ($article->published_at)
                                    <time class="news-item-date" datetime="{{ $article->published_at->toDateString() }}">{{ $article->published_at->format('d/m/Y') }}</time>
                                @endif
                                @if ($article->category)
                                    <span class="section-label">{{ $article->category }}</span>
                                @endif
                                <h2 class="editorial-heading"><a href="{{ route('news.show', $article->slug) }}">{{ $article->title }}</a></h2>
                                @if ($article->summary)
                                    <p class="leading-relaxed text-[#53665B]">{{ $article->summary }}</p>
                                @endif
                                <a class="principal-about-link" href="{{ route('news.show', $article->slug) }}">Baca berita <span aria-hidden="true">→</span></a>
                            </div>
                        </article>
                    @endforeach
                </div>
                {{ $articles->links() }}
            @else
                <p>Belum ada berita yang dipublikasikan.</p>
            @endif
        </div>
    </section>
@endsection
