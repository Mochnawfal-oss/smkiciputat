<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ContentEntry;
use App\Models\Teacher;
use App\Support\TeacherPhotoFocus;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(TeacherPhotoFocus $photoFocus): View
    {
        $programs = ContentEntry::query()
            ->published()
            ->where('type', 'program')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
        $teachers = Teacher::query()
            ->published()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
        $photoFocus->decorate($teachers);
        $news = ContentEntry::query()
            ->published()
            ->where('type', 'news')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(12)
            ->get();

        return view('pages.home', [
            'programs' => $programs,
            'teachers' => $teachers,
            'featuredNews' => $news->first(fn (ContentEntry $entry): bool => data_get($entry->metadata, 'homepage_group') === 'featured') ?? $news->first(),
            'carouselNews' => $news
                ->filter(fn (ContentEntry $entry): bool => data_get($entry->metadata, 'homepage_group') === 'carousel')
                ->values(),
            'archiveNews' => $news
                ->filter(fn (ContentEntry $entry): bool => data_get($entry->metadata, 'homepage_group') === 'archive')
                ->values(),
        ]);
    }
}
