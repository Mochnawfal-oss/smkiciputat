<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentActivity;
use App\Models\GalleryPhoto;
use App\Models\JobVacancy;
use App\Models\NewsArticle;
use App\Models\Program;
use App\Models\Teacher;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'counts' => [
                'programs' => Program::query()->count(),
                'teachers' => Teacher::query()->count(),
                'news' => NewsArticle::query()->count(),
                'jobs' => JobVacancy::query()->count(),
                'gallery' => GalleryPhoto::query()->count(),
            ],
            'latestNews' => NewsArticle::query()
                ->latest('published_at')
                ->latest('id')
                ->limit(5)
                ->get(),
            'activities' => ContentActivity::query()
                ->with('admin:id,name')
                ->latest()
                ->limit(8)
                ->get(),
        ]);
    }
}
