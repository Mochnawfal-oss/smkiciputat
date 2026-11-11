<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentActivity;
use App\Models\ContentEntry;
use App\Models\Teacher;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'counts' => [
                'programs' => ContentEntry::query()->where('type', 'program')->count(),
                'teachers' => Teacher::query()->count(),
                'news' => ContentEntry::query()->where('type', 'news')->count(),
                'jobs' => ContentEntry::query()->where('type', 'job')->count(),
                'gallery' => ContentEntry::query()->where('type', 'gallery')->count(),
            ],
            'latestNews' => ContentEntry::query()
                ->where('type', 'news')
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
