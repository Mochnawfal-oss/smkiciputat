<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\NewsArticle;
use App\Models\Program;
use App\Support\SiteConfiguration;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(SiteConfiguration $configuration): Response
    {
        $settings = $configuration->all();
        $urls = collect([
            ['loc' => route('home'), 'lastmod' => null],
            ['loc' => route('teachers'), 'lastmod' => null],
            ['loc' => route('news.index'), 'lastmod' => null],
            ['loc' => route('gallery'), 'lastmod' => null],
            ['loc' => route('bkk.jobs'), 'lastmod' => null],
            ['loc' => route('bkk.tracer-study'), 'lastmod' => null],
            ['loc' => route('spmb'), 'lastmod' => null],
        ]);

        if ($settings['about.visible']) {
            $urls->push(['loc' => route('about'), 'lastmod' => null]);
        }

        $contentUrls = Program::query()
            ->published()
            ->orderBy('slug')
            ->get(['slug', 'updated_at'])
            ->map(fn (Program $program): array => [
                'loc' => route('program.show', $program->slug),
                'lastmod' => $program->updated_at?->toAtomString(),
            ])
            ->concat(
                NewsArticle::query()
                    ->published()
                    ->orderBy('slug')
                    ->get(['slug', 'updated_at'])
                    ->map(fn (NewsArticle $article): array => [
                        'loc' => route('news.show', $article->slug),
                        'lastmod' => $article->updated_at?->toAtomString(),
                    ]),
            );

        return response()
            ->view('sitemap', ['urls' => $urls->concat($contentUrls)])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
