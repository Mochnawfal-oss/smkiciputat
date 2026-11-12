<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\NewsArticle;
use App\Models\PrincipalWelcome;
use App\Models\Program;
use App\Models\Teacher;
use App\Support\TeacherPhotoFocus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(TeacherPhotoFocus $photoFocus): View
    {
        $programs = Program::query()
            ->published()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
        $teachers = Teacher::query()
            ->published()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
        $photoFocus->decorate($teachers);
        $principalWelcomes = PrincipalWelcome::query()
            ->published()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
        $positionedNews = NewsArticle::query()
            ->published()
            ->whereIn('metadata->homepage_group', ['featured', 'carousel', 'below_carousel', 'sidebar'])
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();
        $recentNews = NewsArticle::query()
            ->published()
            ->where(function (Builder $query): void {
                $query->whereNull('metadata->homepage_group')
                    ->orWhereNotIn('metadata->homepage_group', [
                        'featured',
                        'carousel',
                        'below_carousel',
                        'sidebar',
                        'news_only',
                    ]);
            })
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(max(0, 14 - $positionedNews->count()))
            ->get();
        $news = $positionedNews->concat($recentNews)->unique('id')->values();
        $mainArticle = $news->first(fn (NewsArticle $article): bool => data_get($article->metadata, 'homepage_group') === 'featured');
        $newsWithoutMain = $news
            ->reject(fn (NewsArticle $article): bool => $mainArticle !== null && $article->is($mainArticle))
            ->values();
        $carouselNews = $newsWithoutMain
            ->filter(fn (NewsArticle $article): bool => data_get($article->metadata, 'homepage_group') === 'carousel')
            ->values();
        $belowCarouselNews = $newsWithoutMain
            ->filter(fn (NewsArticle $article): bool => data_get($article->metadata, 'homepage_group') === 'below_carousel')
            ->values();
        $sidebarNews = $newsWithoutMain
            ->filter(fn (NewsArticle $article): bool => data_get($article->metadata, 'homepage_group') === 'sidebar')
            ->values();
        $automaticNews = $newsWithoutMain
            ->reject(fn (NewsArticle $article): bool => in_array(
                data_get($article->metadata, 'homepage_group'),
                ['carousel', 'below_carousel', 'sidebar', 'news_only'],
                true,
            ))
            ->values();

        $carouselNews = $this->fillNewsSection($carouselNews, $automaticNews, 3);
        $belowCarouselNews = $this->fillNewsSection($belowCarouselNews, $automaticNews, 3);
        $sidebarNews = $this->fillNewsSection($sidebarNews, $automaticNews, 5);
        $carouselNews = $carouselNews->concat($automaticNews)->values();
        $featuredNews = $mainArticle === null ? collect() : collect([$mainArticle]);

        return view('pages.home', [
            'programs' => $programs,
            'teachers' => $teachers,
            'principalWelcomes' => $principalWelcomes,
            'featuredNews' => $featuredNews,
            'carouselNews' => $carouselNews,
            'belowCarouselNews' => $belowCarouselNews,
            'sidebarNews' => $sidebarNews,
        ]);
    }

    /**
     * @param  Collection<int, NewsArticle>  $section
     * @param  Collection<int, NewsArticle>  $automaticNews
     * @return Collection<int, NewsArticle>
     */
    private function fillNewsSection(Collection $section, Collection &$automaticNews, int $minimumCount): Collection
    {
        $additionalCount = max(0, $minimumCount - $section->count());
        $section = $section->concat($automaticNews->take($additionalCount))->values();
        $automaticNews = $automaticNews->slice($additionalCount)->values();

        return $section;
    }
}
