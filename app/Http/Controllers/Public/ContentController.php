<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\GalleryPhoto;
use App\Models\JobVacancy;
use App\Models\NewsArticle;
use App\Models\Teacher;
use App\Support\SiteConfiguration;
use App\Support\TeacherPhotoFocus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContentController extends Controller
{
    public function about(SiteConfiguration $configuration): View
    {
        $settings = $configuration->all();
        abort_unless($settings['about.visible'], 404);

        $galleryPhotos = GalleryPhoto::query()
            ->published()
            ->whereNotNull('image_path')
            ->where('image_path', '<>', '')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->limit(4)
            ->get(['id', 'title', 'summary', 'image_path']);

        return view('pages.about', [
            'galleryPhotos' => $galleryPhotos,
        ]);
    }

    public function teachers(Request $request, TeacherPhotoFocus $photoFocus): View
    {
        $filters = $request->validate([
            'category' => ['nullable', 'string', 'in:all,guru,kependidikan'],
            'search' => ['nullable', 'string', 'max:100'],
        ], [
            'category.in' => 'Filter kategori tidak valid.',
            'search.max' => 'Pencarian maksimal 100 karakter.',
        ]);
        $category = $filters['category'] ?? 'all';
        $search = trim($filters['search'] ?? '');

        $query = Teacher::query()
            ->published()
            ->when($category === 'guru', function (Builder $query): void {
                $query->whereIn('category', ['Guru', 'Tenaga Pendidik']);
            })
            ->when($category === 'kependidikan', function (Builder $query): void {
                $query->where('category', 'Tenaga Kependidikan');
            })
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where('name', 'like', '%'.$search.'%');
            })
            ->orderBy('sort_order')
            ->orderBy('name');

        $teachers = $query->paginate(8)->withQueryString();
        $photoFocus->decorate($teachers->getCollection());

        return view('pages.teachers', [
            'teachers' => $teachers,
            'activeCategory' => $category,
            'searchTerm' => $search,
        ]);
    }

    public function news(Request $request): View
    {
        $publishedNews = NewsArticle::query()->published();
        $categories = (clone $publishedNews)
            ->whereNotNull('category')
            ->where('category', '<>', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');
        $category = $request->query('category');

        abort_if(
            $category !== null && (! is_string($category) || ! $categories->contains($category)),
            404,
        );

        $newsQuery = (clone $publishedNews)
            ->when($category !== null, fn (Builder $query): Builder => $query->where('category', $category));
        $featuredArticle = (clone $newsQuery)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->first();
        $articles = (clone $newsQuery)
            ->when(
                $featuredArticle !== null,
                fn (Builder $query): Builder => $query->where('id', '<>', $featuredArticle->id),
            )
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('pages.news.index', [
            'articles' => $articles,
            'categories' => $categories,
            'selectedCategory' => $category,
            'featuredArticle' => $featuredArticle,
        ]);
    }

    public function article(string $slug): View
    {
        $article = NewsArticle::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        [$previousArticle, $nextArticle] = $this->adjacentArticles($article);

        return view('pages.news.show', compact('article', 'previousArticle', 'nextArticle'));
    }

    /**
     * @return array{0: ?NewsArticle, 1: ?NewsArticle}
     */
    private function adjacentArticles(NewsArticle $article): array
    {
        if ($article->published_at === null) {
            return [null, null];
        }

        $publishedNews = NewsArticle::query()
            ->published()
            ->where('id', '<>', $article->getKey());

        $previousArticle = (clone $publishedNews)
            ->where(function (Builder $query) use ($article): void {
                $query->where('published_at', '<', $article->published_at)
                    ->orWhere(function (Builder $query) use ($article): void {
                        $query->where('published_at', $article->published_at)
                            ->where('id', '<', $article->id);
                    });
            })
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->first();

        $nextArticle = (clone $publishedNews)
            ->where(function (Builder $query) use ($article): void {
                $query->where('published_at', '>', $article->published_at)
                    ->orWhere(function (Builder $query) use ($article): void {
                        $query->where('published_at', $article->published_at)
                            ->where('id', '>', $article->id);
                    });
            })
            ->orderBy('published_at')
            ->orderByDesc('id')
            ->first();

        return [$previousArticle, $nextArticle];
    }

    public function gallery(): View
    {
        return view('pages.gallery', [
            'photos' => GalleryPhoto::query()
                ->published()
                ->orderBy('sort_order')
                ->orderByDesc('id')
                ->paginate(12),
        ]);
    }

    public function jobs(): View
    {
        return view('pages.bkk.jobs', [
            'jobs' => JobVacancy::query()
                ->published()
                ->where(function (Builder $query): void {
                    $query->whereNull('metadata->closing_date')
                        ->orWhere('metadata->closing_date', '')
                        ->orWhereDate('metadata->closing_date', '>=', today());
                })
                ->orderByDesc('published_at')
                ->orderByDesc('id')
                ->paginate(12),
        ]);
    }

    public function tracerStudy(): View
    {
        return view('pages.bkk.tracer-study');
    }

    public function spmb(SiteConfiguration $configuration): View
    {
        return view('pages.spmb', ['settings' => $configuration->all()]);
    }
}
