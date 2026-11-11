<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ContentEntry;
use App\Models\Teacher;
use App\Support\SiteConfiguration;
use App\Support\TeacherPhotoFocus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class ContentController extends Controller
{
    public function about(SiteConfiguration $configuration): View
    {
        abort_unless($configuration->all()['about.visible'], 404);

        return view('pages.about');
    }

    public function teachers(TeacherPhotoFocus $photoFocus): View
    {
        $teachers = Teacher::query()
            ->published()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(12);
        $photoFocus->decorate($teachers->getCollection());

        return view('pages.teachers', [
            'teachers' => $teachers,
        ]);
    }

    public function news(): View
    {
        return view('pages.news.index', [
            'articles' => ContentEntry::query()
                ->published()
                ->where('type', 'news')
                ->orderByDesc('published_at')
                ->orderByDesc('id')
                ->paginate(12),
        ]);
    }

    public function article(string $slug): View
    {
        $article = ContentEntry::query()
            ->published()
            ->where('type', 'news')
            ->where('slug', $slug)
            ->firstOrFail();

        return view('pages.news.show', compact('article'));
    }

    public function gallery(): View
    {
        return view('pages.gallery', [
            'photos' => ContentEntry::query()
                ->published()
                ->where('type', 'gallery')
                ->orderBy('sort_order')
                ->orderByDesc('id')
                ->paginate(12),
        ]);
    }

    public function jobs(): View
    {
        return view('pages.bkk.jobs', [
            'jobs' => ContentEntry::query()
                ->published()
                ->where('type', 'job')
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
