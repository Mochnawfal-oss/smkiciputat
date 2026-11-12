<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\GalleryPhoto;
use App\Models\Program;
use Illuminate\View\View;

class ProgramController extends Controller
{
    public function legacy(string $programKey): View
    {
        $program = Program::query()
            ->published()
            ->where('route_key', $programKey)
            ->firstOrFail();

        return $this->renderProgram($program);
    }

    public function show(string $slug): View
    {
        $program = Program::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return $this->renderProgram($program);
    }

    private function renderProgram(Program $program): View
    {
        $programPhotos = GalleryPhoto::query()
            ->published()
            ->where('metadata->program_route_key', $program->route_key)
            ->whereNotNull('image_path')
            ->where('image_path', '<>', '')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('pages.programs.show', [
            'program' => $program,
            'programPhotos' => $programPhotos,
        ]);
    }
}
