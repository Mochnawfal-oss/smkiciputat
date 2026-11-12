<?php

namespace Tests\Feature;

use App\Models\NewsArticle;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_page_emits_route_specific_social_metadata_and_school_schema(): void
    {
        $response = $this->get(route('about'));

        $response->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('about').'">', false)
            ->assertSee('<meta property="og:url" content="'.route('about').'">', false)
            ->assertSee('"@type":"EducationalOrganization"', false);
    }

    public function test_about_page_shows_vision_mission_and_gallery_empty_states_when_content_is_missing(): void
    {
        $response = $this->get(route('about'));

        $response->assertOk()
            ->assertSee('Visi Sekolah')
            ->assertSee('Informasi visi sekolah belum tersedia.')
            ->assertSee('Misi Sekolah')
            ->assertSee('Informasi misi sekolah belum tersedia.')
            ->assertDontSee('Profil Lengkap')
            ->assertDontSee('aria-label="Breadcrumb"', false)
            ->assertSee('Galeri Sekolah')
            ->assertSee('Belum ada foto galeri sekolah yang dipublikasikan.')
            ->assertDontSee('Perjalanan Sekolah');
    }

    public function test_news_page_emits_article_metadata_and_structured_data(): void
    {
        $article = NewsArticle::query()->create([
            'title' => 'Berita Sekolah',
            'slug' => 'berita-sekolah-seo',
            'summary' => 'Ringkasan berita untuk hasil pencarian.',
            'body' => 'Isi berita.',
            'status' => 'published',
            'published_at' => now(),
            'meta_title' => 'Judul SEO Berita Sekolah',
            'meta_description' => 'Deskripsi SEO berita sekolah.',
            'image_path' => 'news/berita-sekolah.webp',
        ]);

        $response = $this->get(route('news.show', $article->slug));

        $response->assertOk()
            ->assertSee('<meta property="og:type" content="article">', false)
            ->assertSee('<meta property="og:title" content="Judul SEO Berita Sekolah">', false)
            ->assertSee('"@type":"NewsArticle"', false)
            ->assertSee('berita-sekolah.webp', false);
    }

    public function test_sitemap_includes_public_routes_and_published_content_only(): void
    {
        $publishedProgram = Program::query()->create([
            'title' => 'Program Aktif',
            'slug' => 'program-aktif-seo',
            'status' => 'published',
        ]);
        $publishedNews = NewsArticle::query()->create([
            'title' => 'Berita Aktif',
            'slug' => 'berita-aktif-seo',
            'status' => 'published',
        ]);
        NewsArticle::query()->create([
            'title' => 'Berita Draft',
            'slug' => 'berita-draft-seo',
            'status' => 'draft',
        ]);

        $response = $this->get(route('sitemap'));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('home'), false)
            ->assertSee(route('program.show', $publishedProgram->slug), false)
            ->assertSee(route('news.show', $publishedNews->slug), false)
            ->assertDontSee(route('news.show', 'berita-draft-seo'), false);
    }
}
