<?php

namespace Tests\Feature;

use App\Models\NewsArticle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PublicNewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_news_index_filters_by_published_categories(): void
    {
        $this->createArticle('Berita Akademik', 'berita-akademik', 'Akademik', now());
        $this->createArticle('Berita Kegiatan', 'berita-kegiatan', 'Kegiatan Sekolah', now()->subDay());
        $this->createArticle('Berita Draft', 'berita-draft', 'Pengumuman', now(), 'draft');

        $response = $this->get(route('news.index', ['category' => 'Akademik']));

        $response->assertOk()
            ->assertSee('Berita Akademik')
            ->assertSee(route('news.index', ['category' => 'Akademik']), false)
            ->assertDontSee('Berita Kegiatan')
            ->assertDontSee('Pengumuman');

        $this->get(route('news.index', ['category' => 'Tidak Ada']))
            ->assertNotFound();
    }

    public function test_news_index_features_the_latest_article_and_paginates_the_remaining_news(): void
    {
        for ($index = 1; $index <= 14; $index++) {
            $this->createArticle(
                'Berita '.$index,
                'berita-'.$index,
                'Kegiatan Sekolah',
                now()->subDays($index),
            );
        }

        $this->get(route('news.index'))
            ->assertOk()
            ->assertSee('class="news-featured"', false)
            ->assertSee('Berita 1')
            ->assertDontSee('Berita 14')
            ->assertSee('page=2', false);

        $this->get(route('news.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('Berita 14');
    }

    public function test_article_detail_shows_available_author_gallery_and_adjacent_articles(): void
    {
        $this->createArticle('Berita Sebelumnya', 'berita-sebelumnya', 'Kegiatan Sekolah', now()->subDays(2));
        $article = $this->createArticle(
            'Berita Utama',
            'berita-utama',
            'Prestasi',
            now()->subDay(),
            'published',
            [
                'author' => 'Redaksi Sekolah',
                'images' => ['/images/berita-satu.jpg', '/images/berita-dua.jpg'],
            ],
            "Paragraf pertama.\n\nParagraf kedua.",
            '/images/berita-utama.jpg',
        );
        $this->createArticle('Berita Berikutnya', 'berita-berikutnya', 'Kegiatan Sekolah', now());

        $this->get(route('news.show', $article->slug))
            ->assertOk()
            ->assertSee('Beranda')
            ->assertSee('Berita')
            ->assertSee('Berita Utama')
            ->assertSee('Redaksi Sekolah')
            ->assertSee('Paragraf pertama.')
            ->assertSee('Paragraf kedua.')
            ->assertSee('/images/berita-utama.jpg', false)
            ->assertSee('/images/berita-satu.jpg', false)
            ->assertSee('/images/berita-dua.jpg', false)
            ->assertSee('Berita Sebelumnya')
            ->assertSee('Berita Berikutnya')
            ->assertSee('https://wa.me/', false)
            ->assertSee('https://www.facebook.com/sharer/sharer.php', false)
            ->assertDontSee('dibaca');
    }

    public function test_article_detail_hides_unavailable_author_gallery_and_navigation(): void
    {
        $article = $this->createArticle(
            'Berita Tunggal',
            'berita-tunggal',
            'Kegiatan Sekolah',
            now(),
        );

        $this->get(route('news.show', $article->slug))
            ->assertOk()
            ->assertDontSee('Berita Sebelumnya')
            ->assertDontSee('Berita Berikutnya')
            ->assertDontSee('news-article-gallery')
            ->assertDontSee('Redaksi Sekolah');
    }

    private function createArticle(
        string $title,
        string $slug,
        string $category,
        ?Carbon $publishedAt,
        string $status = 'published',
        array $metadata = [],
        ?string $body = null,
        ?string $imagePath = null,
    ): NewsArticle {
        return NewsArticle::query()->create([
            'title' => $title,
            'slug' => $slug,
            'category' => $category,
            'summary' => 'Ringkasan '.$title,
            'body' => $body,
            'image_path' => $imagePath,
            'status' => $status,
            'published_at' => $publishedAt,
            'metadata' => $metadata,
        ]);
    }
}
