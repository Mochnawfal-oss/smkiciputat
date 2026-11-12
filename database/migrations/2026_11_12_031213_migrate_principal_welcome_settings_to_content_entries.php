<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const LEGACY_DEFAULTS = [
        'home.principal.visible' => true,
        'home.principal.name' => '',
        'home.principal.position' => 'Kepala SMK Islamiyah Ciputat',
        'home.principal.greeting' => 'Assalamu’alaikum warahmatullahi wabarakatuh.',
        'home.principal.message' => 'Kami berkomitmen mendampingi peserta didik untuk tumbuh, berkarakter, dan menguasai pengetahuan serta keterampilan.',
        'home.principal.description' => 'Semoga website ini membantu peserta didik, orang tua, dan masyarakat mengenal SMK Islamiyah Ciputat lebih dekat.',
        'home.principal.image' => '',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::transaction(function (): void {
            $values = self::LEGACY_DEFAULTS;

            foreach (DB::table('site_settings')->whereIn('key', array_keys(self::LEGACY_DEFAULTS))->get(['key', 'value']) as $setting) {
                $values[$setting->key] = json_decode($setting->value, true, flags: JSON_THROW_ON_ERROR);
            }

            if (! DB::table('content_entries')->where('type', 'principal_welcome')->exists()) {
                DB::table('content_entries')->insert([
                    'type' => 'principal_welcome',
                    'title' => 'Sambutan Kepala Sekolah',
                    'slug' => 'sambutan-kepala-sekolah',
                    'summary' => $values['home.principal.greeting'] ?: null,
                    'body' => $values['home.principal.message'] ?: null,
                    'category' => $values['home.principal.position'] ?: null,
                    'image_path' => $values['home.principal.image'] ?: null,
                    'status' => (bool) $values['home.principal.visible'] ? 'published' : 'draft',
                    'featured' => false,
                    'sort_order' => 0,
                    'published_at' => null,
                    'metadata' => json_encode([
                        'name' => $values['home.principal.name'] ?: null,
                        'description' => $values['home.principal.description'] ?: null,
                    ], JSON_THROW_ON_ERROR),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('site_settings')->whereIn('key', array_keys(self::LEGACY_DEFAULTS))->delete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::transaction(function (): void {
            $values = self::LEGACY_DEFAULTS;
            $entry = DB::table('content_entries')
                ->where('type', 'principal_welcome')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->first();

            if ($entry !== null) {
                $metadata = $entry->metadata
                    ? json_decode($entry->metadata, true, flags: JSON_THROW_ON_ERROR)
                    : [];
                $values = [
                    'home.principal.visible' => $entry->status === 'published',
                    'home.principal.name' => $metadata['name'] ?? '',
                    'home.principal.position' => $entry->category ?? '',
                    'home.principal.greeting' => $entry->summary ?? '',
                    'home.principal.message' => $entry->body ?? '',
                    'home.principal.description' => $metadata['description'] ?? '',
                    'home.principal.image' => $entry->image_path ?? '',
                ];
            }

            foreach ($values as $key => $value) {
                DB::table('site_settings')->updateOrInsert(
                    ['key' => $key],
                    [
                        'group' => 'home',
                        'value' => json_encode($value, JSON_THROW_ON_ERROR),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                );
            }
        });
    }
};
