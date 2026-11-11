<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ContentEntry;
use Illuminate\View\View;

class ProgramController extends Controller
{
    public function legacy(string $programKey): View
    {
        $program = ContentEntry::query()
            ->published()
            ->where('type', 'program')
            ->where('route_key', $programKey)
            ->firstOrFail();

        return view('pages.programs.show', compact('program'));
    }

    public function show(string $slug): View
    {
        $program = ContentEntry::query()
            ->published()
            ->where('type', 'program')
            ->where('slug', $slug)
            ->firstOrFail();

        return view('pages.programs.show', compact('program'));
    }
}
