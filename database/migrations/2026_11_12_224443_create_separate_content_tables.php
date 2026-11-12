<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const CONTENT_TABLES = [
        'news' => 'news_articles',
        'program' => 'programs',
        'principal_welcome' => 'principal_welcomes',
        'job' => 'job_vacancies',
        'gallery' => 'gallery_photos',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (self::CONTENT_TABLES as $tableName) {
            Schema::create($tableName, function (Blueprint $table): void {
                $table->id();
                $table->string('title');
                $table->string('slug')->nullable()->unique();
                $table->string('route_key')->nullable()->unique();
                $table->text('summary')->nullable();
                $table->longText('body')->nullable();
                $table->string('category')->nullable();
                $table->string('image_path')->nullable();
                $table->string('status', 20)->default('draft');
                $table->boolean('featured')->default(false);
                $table->integer('sort_order')->default(0);
                $table->timestamp('published_at')->nullable();
                $table->string('meta_title')->nullable();
                $table->string('meta_description', 320)->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['status', 'published_at']);
            });
        }

        foreach (self::CONTENT_TABLES as $type => $tableName) {
            DB::table('content_entries')
                ->where('type', $type)
                ->orderBy('id')
                ->chunkById(100, function ($entries) use ($tableName): void {
                    foreach ($entries as $entry) {
                        $attributes = (array) $entry;
                        unset($attributes['type']);

                        DB::table($tableName)->insert($attributes);
                    }
                });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::transaction(function (): void {
            foreach (self::CONTENT_TABLES as $type => $tableName) {
                DB::table('content_entries')->where('type', $type)->delete();

                DB::table($tableName)
                    ->orderBy('id')
                    ->chunkById(100, function ($entries) use ($type): void {
                        foreach ($entries as $entry) {
                            $attributes = (array) $entry;
                            $attributes['type'] = $type;
                            $existing = DB::table('content_entries')
                                ->where('id', $attributes['id'])
                                ->first();

                            if ($existing !== null && $existing->type !== $type) {
                                $oldId = $attributes['id'];
                                unset($attributes['id']);
                                $newId = DB::table('content_entries')->insertGetId($attributes);

                                DB::table('content_activities')
                                    ->where('content_type', $type)
                                    ->where('content_id', $oldId)
                                    ->update(['content_id' => $newId]);

                                continue;
                            }

                            DB::table('content_entries')->updateOrInsert(
                                ['id' => $attributes['id']],
                                $attributes,
                            );
                        }
                    });
            }
        });

        foreach (array_reverse(self::CONTENT_TABLES) as $tableName) {
            Schema::dropIfExists($tableName);
        }
    }
};
