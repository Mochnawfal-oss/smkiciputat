<?php

namespace Tests\Feature;

use App\Models\ContentActivity;
use App\Models\ContentEntry;
use App\Models\NewsArticle;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SeparateContentTablesMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_copies_all_content_types_and_keeps_the_legacy_table(): void
    {
        $migration = $this->migration();
        $migration->down();
        $entries = $this->createLegacyEntries();

        $migration->up();

        foreach ([
            'news' => 'news_articles',
            'program' => 'programs',
            'principal_welcome' => 'principal_welcomes',
            'job' => 'job_vacancies',
            'gallery' => 'gallery_photos',
        ] as $type => $table) {
            $this->assertDatabaseHas($table, [
                'id' => $entries[$type]->id,
                'title' => $entries[$type]->title,
                'slug' => $entries[$type]->slug,
                'status' => 'published',
            ]);
        }

        $this->assertDatabaseHas('content_entries', [
            'type' => 'news',
            'slug' => 'news-legacy',
        ]);
        $this->assertTrue(Schema::hasTable('content_entries'));
    }

    public function test_migration_rollback_restores_split_content_and_activity_ids(): void
    {
        $migration = $this->migration();
        $migration->down();
        $this->createLegacyEntries();
        $migration->up();

        $article = NewsArticle::query()->create([
            'title' => 'Berita Baru',
            'slug' => 'berita-baru-rollback',
            'status' => 'published',
        ]);
        $program = Program::query()->create([
            'title' => 'Program Baru',
            'slug' => 'program-baru-rollback',
            'status' => 'published',
        ]);

        ContentActivity::query()->create([
            'content_type' => 'news',
            'content_id' => $article->id,
            'action' => 'created',
            'title' => $article->title,
        ]);
        ContentActivity::query()->create([
            'content_type' => 'program',
            'content_id' => $program->id,
            'action' => 'created',
            'title' => $program->title,
        ]);

        $migration->down();

        $restoredArticle = ContentEntry::query()->where('title', $article->title)->firstOrFail();
        $restoredProgram = ContentEntry::query()->where('title', $program->title)->firstOrFail();

        $this->assertSame('news', $restoredArticle->type);
        $this->assertSame('program', $restoredProgram->type);
        $this->assertDatabaseHas('content_activities', [
            'content_type' => 'news',
            'content_id' => $restoredArticle->id,
            'title' => $article->title,
        ]);
        $this->assertDatabaseHas('content_activities', [
            'content_type' => 'program',
            'content_id' => $restoredProgram->id,
            'title' => $program->title,
        ]);
        $this->assertFalse(Schema::hasTable('news_articles'));
        $this->assertFalse(Schema::hasTable('programs'));
    }

    /**
     * @return array<string, ContentEntry>
     */
    private function createLegacyEntries(): array
    {
        $entries = [];

        foreach ([
            'news' => 'news-legacy',
            'program' => 'program-legacy',
            'principal_welcome' => 'principal-legacy',
            'job' => 'job-legacy',
            'gallery' => 'gallery-legacy',
        ] as $type => $slug) {
            $entries[$type] = ContentEntry::query()->create([
                'type' => $type,
                'title' => ucfirst($type).' Legacy',
                'slug' => $slug,
                'status' => 'published',
                'metadata' => ['legacy_type' => $type],
            ]);
        }

        return $entries;
    }

    private function migration(): object
    {
        return require database_path('migrations/2026_11_12_224443_create_separate_content_tables.php');
    }
}
