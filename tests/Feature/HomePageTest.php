<?php

namespace Tests\Feature;

use App\Models\NewsArticle;
use App\Models\PrincipalWelcome;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_renders_published_principal_welcomes_and_hides_drafts(): void
    {
        PrincipalWelcome::query()->create([
            'title' => 'Sambutan Terbit',
            'slug' => 'sambutan-terbit',
            'category' => 'Kepala Sekolah',
            'summary' => 'Salam yang tampil.',
            'body' => 'Isi sambutan yang tampil.',
            'status' => 'published',
            'sort_order' => 1,
            'metadata' => [
                'name' => 'Kepala Sekolah Terbit',
                'description' => 'Deskripsi yang tampil.',
            ],
        ]);
        PrincipalWelcome::query()->create([
            'title' => 'Sambutan Draft',
            'slug' => 'sambutan-draft',
            'body' => 'Isi draft yang tidak boleh tampil.',
            'status' => 'draft',
            'sort_order' => 2,
            'metadata' => ['name' => 'Kepala Sekolah Draft'],
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Kepala Sekolah Terbit')
            ->assertSee('Isi sambutan yang tampil.')
            ->assertDontSee('Kepala Sekolah Draft')
            ->assertDontSee('Isi draft yang tidak boleh tampil.');
    }

    public function test_homepage_renders_all_published_teachers_beyond_six_records(): void
    {
        $teacherNames = [];

        for ($index = 1; $index <= 10; $index++) {
            $teacherName = "Tenaga Pendidik {$index}";
            $teacherNames[] = $teacherName;

            Teacher::query()->create([
                'name' => $teacherName,
                'slug' => "tenaga-pendidik-{$index}",
                'position' => "Jabatan {$index}",
                'status' => 'published',
                'sort_order' => $index,
            ]);
        }

        Teacher::query()->create([
            'name' => 'Tenaga Nonaktif',
            'slug' => 'tenaga-nonaktif',
            'position' => 'Jabatan nonaktif',
            'status' => 'draft',
            'sort_order' => 11,
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();

        foreach ($teacherNames as $teacherName) {
            $response->assertSee($teacherName);
        }

        $response->assertDontSee('Tenaga Nonaktif');
    }

    public function test_homepage_renders_published_news_without_a_homepage_group(): void
    {
        $this->freezeTime();

        NewsArticle::query()->create([
            'title' => 'Berita Unggulan',
            'slug' => 'berita-unggulan',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'metadata' => ['homepage_group' => 'featured'],
        ]);
        NewsArticle::query()->create([
            'title' => 'Berita Terbaru Tanpa Grup',
            'slug' => 'berita-terbaru-tanpa-grup',
            'status' => 'published',
            'published_at' => now(),
            'metadata' => ['homepage_group' => 'null'],
        ]);
        NewsArticle::query()->create([
            'title' => 'Berita Lama Tanpa Metadata',
            'slug' => 'berita-lama-tanpa-metadata',
            'status' => 'published',
            'published_at' => now()->subHours(2),
        ]);
        NewsArticle::query()->create([
            'title' => 'Berita Arsip Lama',
            'slug' => 'berita-arsip-lama',
            'status' => 'published',
            'published_at' => now()->subDays(2),
            'metadata' => ['homepage_group' => 'archive'],
        ]);
        NewsArticle::query()->create([
            'title' => 'Berita Draft',
            'slug' => 'berita-draft',
            'status' => 'draft',
            'published_at' => now(),
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Berita Unggulan')
            ->assertSee('Berita Terbaru Tanpa Grup')
            ->assertSee('Berita Lama Tanpa Metadata')
            ->assertSee('Berita Arsip Lama')
            ->assertDontSee('Berita Draft');
    }

    public function test_homepage_shows_one_selected_main_news_and_distributes_fourteen_articles(): void
    {
        $this->freezeTime();

        for ($index = 1; $index <= 16; $index++) {
            $placement = match ($index) {
                1 => 'featured',
                2 => 'carousel',
                3 => 'below_carousel',
                4 => 'sidebar',
                default => 'auto',
            };

            NewsArticle::query()->create([
                'title' => "Berita Beranda {$index}",
                'slug' => "berita-beranda-{$index}",
                'status' => 'published',
                'published_at' => now()->subMinutes($index),
                'image_path' => "/images/berita-{$index}.jpg",
                'metadata' => ['homepage_group' => $placement],
            ]);
        }
        NewsArticle::query()->create([
            'title' => 'Berita Hanya di Halaman Berita',
            'slug' => 'berita-hanya-di-halaman-berita',
            'status' => 'published',
            'published_at' => now(),
            'metadata' => ['homepage_group' => 'news_only'],
        ]);

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('data-news-featured-rotator', false)
            ->assertSee('Berita Sekolah Lainnya')
            ->assertSee('Berita Lainnya')
            ->assertDontSee('Berita Hanya di Halaman Berita');

        for ($index = 1; $index <= 14; $index++) {
            $response->assertSee("Berita Beranda {$index}");
        }

        $response->assertDontSee('Berita Beranda 15')
            ->assertDontSee('Berita Beranda 16');

        $content = $response->getContent() ?? '';

        $this->assertSame(1, substr_count($content, 'data-news-featured-slide'));
        $this->assertSame(5, substr_count($content, 'data-news-carousel-item'));
        $this->assertSame(3, substr_count($content, 'data-news-below-carousel-item'));
        $this->assertSame(5, substr_count($content, 'data-news-sidebar-item'));
        $this->assertSame(3, substr_count($content, 'class="news-below-carousel-card__image"'));
        $this->assertSame(5, substr_count($content, 'class="news-sidebar-image"'));

        $this->get(route('news.index'))
            ->assertOk()
            ->assertSee('Berita Hanya di Halaman Berita');
    }

    public function test_homepage_keeps_an_explicitly_selected_older_main_article_visible(): void
    {
        $this->freezeTime();

        NewsArticle::query()->create([
            'title' => 'Berita Utama Pilihan Lama',
            'slug' => 'berita-utama-pilihan-lama',
            'status' => 'published',
            'published_at' => now()->subDays(30),
            'metadata' => ['homepage_group' => 'featured'],
        ]);

        for ($index = 1; $index <= 14; $index++) {
            NewsArticle::query()->create([
                'title' => "Berita Otomatis {$index}",
                'slug' => "berita-otomatis-{$index}",
                'status' => 'published',
                'published_at' => now()->subMinutes($index),
                'metadata' => ['homepage_group' => 'auto'],
            ]);
        }

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('Berita Utama Pilihan Lama');
        for ($index = 1; $index <= 13; $index++) {
            $response->assertSee("Berita Otomatis {$index}");
        }
        $response->assertDontSee('Berita Otomatis 14');

        $content = $response->getContent() ?? '';
        $this->assertSame(1, substr_count($content, 'data-news-featured-slide'));
        $this->assertSame(5, substr_count($content, 'data-news-carousel-item'));
        $this->assertSame(3, substr_count($content, 'data-news-below-carousel-item'));
        $this->assertSame(5, substr_count($content, 'data-news-sidebar-item'));
    }
}
