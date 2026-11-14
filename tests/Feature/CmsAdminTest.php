<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\ContentEntry;
use App\Models\MediaAsset;
use App\Models\NewsArticle;
use App\Models\PrincipalWelcome;
use App\Models\SiteSetting;
use App\Models\Teacher;
use Database\Seeders\CmsContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CmsAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_routes_require_authentication(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));

        $this->get(route('admin.register.success'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_sign_in_and_sign_out(): void
    {
        $admin = $this->createAdmin();

        $this->post(route('admin.login.submit'), [
            'username' => $admin->username,
            'password' => 'Secure-password-1234',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin, 'admin');

        $this->post(route('admin.logout'))
            ->assertRedirect(route('admin.login'));

        $this->assertGuest('admin');
    }

    public function test_first_superadmin_can_register_once_in_testing_and_is_signed_in(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertViewIs('admin.setup-welcome')
            ->assertSee(route('admin.register.form'));

        $this->get(route('admin.register.form'))
            ->assertOk()
            ->assertViewIs('admin.register')
            ->assertSee('Buat Administrator Utama');

        $this->post(route('admin.register.store'), [
            'name' => 'Superadmin Testing',
            'username' => 'superadmintesting',
            'email' => 'superadmin@example.test',
            'password' => 'Secure-password-1234',
            'password_confirmation' => 'Secure-password-1234',
        ])->assertRedirect(route('admin.register.success'))
            ->assertSessionHas('success', 'Akun Superadmin pertama berhasil dibuat.');

        $this->assertDatabaseHas('admins', [
            'username' => 'superadmintesting',
            'role' => 'superadmin',
        ]);
        $this->assertAuthenticated('admin');

        $this->get(route('admin.register.success'))
            ->assertOk()
            ->assertViewIs('admin.register-success')
            ->assertSee('Akun Superadmin')
            ->assertSee(route('admin.dashboard'));

        $this->get(route('admin.register'))
            ->assertNotFound();
        $this->get(route('admin.register.form'))
            ->assertNotFound();

        $this->post(route('admin.logout'))
            ->assertRedirect(route('admin.login'));

        $this->get(route('admin.login'))
            ->assertOk()
            ->assertDontSee(route('admin.register'));
    }

    public function test_first_superadmin_registration_is_disabled_outside_local_and_testing(): void
    {
        config(['app.env' => 'production']);

        $this->get(route('admin.register'))
            ->assertNotFound();
        $this->get(route('admin.register.form'))
            ->assertNotFound();

        $this->post(route('admin.register.store'), [
            'name' => 'Superadmin Production',
            'username' => 'superadminproduction',
            'password' => 'Secure-password-1234',
            'password_confirmation' => 'Secure-password-1234',
        ])->assertNotFound();

        $this->assertDatabaseMissing('admins', [
            'username' => 'superadminproduction',
        ]);
    }

    public function test_first_superadmin_registration_is_disabled_when_any_admin_account_already_exists(): void
    {
        $this->createAdmin('admin');

        $this->get(route('admin.register'))
            ->assertNotFound();

        $this->post(route('admin.register.store'), [
            'name' => 'Superadmin Kedua',
            'username' => 'superadminkedua',
            'password' => 'Secure-password-1234',
            'password_confirmation' => 'Secure-password-1234',
        ])->assertNotFound();

        $this->assertDatabaseMissing('admins', [
            'username' => 'superadminkedua',
        ]);
    }

    public function test_admin_can_publish_a_program_that_appears_on_public_pages(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.content.store', 'programs'), [
                'title' => 'Program Uji',
                'slug' => 'program-uji',
                'summary' => 'Deskripsi program uji.',
                'body' => 'Deskripsi detail program uji.',
                'status' => 'published',
                'metadata' => [
                    'abbreviation' => 'PU',
                    'route_key' => 'program-uji',
                    'icon' => 'graduation-cap',
                ],
            ])
            ->assertRedirect(route('admin.content.index', 'programs'));

        $this->assertDatabaseHas('programs', [
            'slug' => 'program-uji',
            'status' => 'published',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Program Uji');

        $this->get(route('program.show', 'program-uji'))
            ->assertOk()
            ->assertSee('Deskripsi detail program uji.')
            ->assertDontSee('Deskripsi program uji.');
    }

    public function test_admin_can_publish_a_job_vacancy_to_the_dedicated_table(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.content.store', 'jobs'), [
                'title' => 'Staf Administrasi Uji',
                'slug' => 'staf-administrasi-uji',
                'summary' => 'Lowongan uji untuk memastikan penyimpanan BKK.',
                'status' => 'published',
                'metadata' => [
                    'company' => 'Perusahaan Uji',
                    'position' => 'Staf Administrasi',
                ],
            ])
            ->assertRedirect(route('admin.content.index', 'jobs'));

        $this->assertDatabaseHas('job_vacancies', [
            'slug' => 'staf-administrasi-uji',
            'status' => 'published',
        ]);

        $this->get(route('bkk.jobs'))
            ->assertOk()
            ->assertSee('Staf Administrasi')
            ->assertSee('Perusahaan Uji');
    }

    public function test_admin_news_form_explains_homepage_placement_and_uses_automatic_by_default(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.content.create', 'news'))
            ->assertOk()
            ->assertSee('Posisi tampil di beranda')
            ->assertSee('Berita utama (1 slot)')
            ->assertSee('Carousel berita (bisa digeser)')
            ->assertSee('Kartu di bawah carousel')
            ->assertSee('Daftar berita di sisi kanan')
            ->assertSee('Otomatis (mengikuti urutan tanggal)')
            ->assertSee('Hanya tampil di halaman berita')
            ->assertSee('Slot berita utama hanya satu.')
            ->assertSee('value="auto" selected', false)
            ->assertDontSee('Tandai sebagai unggulan');
    }

    public function test_admin_selecting_a_main_news_article_replaces_the_existing_main_slot(): void
    {
        $admin = $this->createAdmin();
        $previousMain = NewsArticle::query()->create([
            'title' => 'Berita Utama Sebelumnya',
            'slug' => 'berita-utama-sebelumnya',
            'status' => 'published',
            'metadata' => ['homepage_group' => 'featured'],
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.content.store', 'news'), [
                'title' => 'Berita Utama Baru',
                'slug' => 'berita-utama-baru',
                'status' => 'published',
                'metadata' => ['homepage_group' => 'featured'],
            ])
            ->assertRedirect(route('admin.content.index', 'news'));

        $newMain = NewsArticle::query()->where('slug', 'berita-utama-baru')->firstOrFail();
        $this->assertSame('featured', $newMain->metadata['homepage_group']);
        $this->assertSame('auto', $previousMain->fresh()->metadata['homepage_group']);
        $this->assertDatabaseHas('content_activities', [
            'content_type' => 'news',
            'content_id' => $previousMain->id,
            'action' => 'updated',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.content.index', 'news'))
            ->assertOk()
            ->assertSee('Berita utama (1 slot)')
            ->assertSee('Otomatis (mengikuti urutan tanggal)');

        $homepageContent = $this->get(route('home'))
            ->assertOk()
            ->assertSee('Berita Utama Baru')
            ->assertSee('Berita Utama Sebelumnya')
            ->getContent() ?? '';

        $this->assertSame(1, substr_count($homepageContent, 'data-news-featured-slide'));
    }

    public function test_admin_promoting_an_existing_article_replaces_the_current_main(): void
    {
        $admin = $this->createAdmin();
        $previousMain = NewsArticle::query()->create([
            'title' => 'Berita Utama Lama',
            'slug' => 'berita-utama-lama',
            'status' => 'published',
            'metadata' => ['homepage_group' => 'featured'],
        ]);
        $article = NewsArticle::query()->create([
            'title' => 'Artikel yang Dipromosikan',
            'slug' => 'artikel-yang-dipromosikan',
            'status' => 'published',
            'metadata' => ['homepage_group' => 'auto'],
        ]);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.content.update', ['type' => 'news', 'entry' => $article]), [
                'title' => $article->title,
                'slug' => $article->slug,
                'status' => 'published',
                'metadata' => ['homepage_group' => 'featured'],
            ])
            ->assertRedirect(route('admin.content.index', 'news'));

        $this->assertSame('featured', $article->fresh()->metadata['homepage_group']);
        $this->assertSame('auto', $previousMain->fresh()->metadata['homepage_group']);
    }

    public function test_admin_saving_a_draft_as_main_does_not_replace_the_published_main(): void
    {
        $admin = $this->createAdmin();
        $publishedMain = NewsArticle::query()->create([
            'title' => 'Berita Utama Aktif',
            'slug' => 'berita-utama-aktif',
            'status' => 'published',
            'metadata' => ['homepage_group' => 'featured'],
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.content.store', 'news'), [
                'title' => 'Berita Utama Draft',
                'slug' => 'berita-utama-draft',
                'status' => 'draft',
                'metadata' => ['homepage_group' => 'featured'],
            ])
            ->assertRedirect(route('admin.content.index', 'news'));

        $this->assertSame('featured', $publishedMain->fresh()->metadata['homepage_group']);
        $this->assertDatabaseHas('news_articles', [
            'slug' => 'berita-utama-draft',
            'status' => 'draft',
        ]);

        $this->get(route('admin.content.index', 'news'))
            ->assertOk()
            ->assertSee('Berita utama (1 slot) (Draft)');
    }

    public function test_admin_scheduling_a_main_news_article_keeps_the_current_main_until_its_date(): void
    {
        $this->freezeTime();
        $admin = $this->createAdmin();
        $publishedMain = NewsArticle::query()->create([
            'title' => 'Berita Utama Saat Ini',
            'slug' => 'berita-utama-saat-ini',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'metadata' => ['homepage_group' => 'featured'],
        ]);
        $scheduledAt = now()->addDay();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.content.store', 'news'), [
                'title' => 'Berita Utama Terjadwal',
                'slug' => 'berita-utama-terjadwal',
                'status' => 'published',
                'published_at' => $scheduledAt->format('Y-m-d\TH:i'),
                'metadata' => ['homepage_group' => 'featured'],
            ])
            ->assertRedirect(route('admin.content.index', 'news'));

        $this->assertSame('featured', $publishedMain->fresh()->metadata['homepage_group']);
        $this->assertDatabaseHas('news_articles', [
            'slug' => 'berita-utama-terjadwal',
            'status' => 'published',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Berita Utama Saat Ini')
            ->assertDontSee('Berita Utama Terjadwal');

        $this->get(route('admin.content.index', 'news'))
            ->assertOk()
            ->assertSee('Berita utama (1 slot) (Terjadwal)');
    }

    public function test_admin_rejects_an_unknown_homepage_placement(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.content.store', 'news'), [
                'title' => 'Berita Tanpa Posisi Valid',
                'slug' => 'berita-tanpa-posisi-valid',
                'status' => 'published',
                'metadata' => ['homepage_group' => 'unknown'],
            ])
            ->assertSessionHasErrors('metadata.homepage_group');

        $this->assertDatabaseMissing('news_articles', [
            'slug' => 'berita-tanpa-posisi-valid',
        ]);
    }

    public function test_admin_cannot_save_content_with_published_at_outside_mysql_timestamp_range(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.content.store', 'principal-welcomes'), [
                'title' => 'Sambutan Tahun 2026',
                'status' => 'published',
                'published_at' => '0026-12-12T12:11',
            ])
            ->assertSessionHasErrors('published_at');

        $this->assertDatabaseMissing('principal_welcomes', [
            'slug' => 'sambutan-tahun-2026',
        ]);
    }

    public function test_admin_can_manage_principal_welcomes_separately_from_teachers(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $type = 'principal-welcomes';

        $this->actingAs($admin, 'admin')
            ->get(route('admin.content.index', $type))
            ->assertOk()
            ->assertSee(route('admin.content.index', $type));

        $this->actingAs($admin, 'admin')
            ->get(route('admin.content.create', $type))
            ->assertOk()
            ->assertSee('Judul sambutan')
            ->assertSee('Jabatan')
            ->assertSee('Salam pembuka')
            ->assertSee('Status publikasi')
            ->assertDontSee('Slug URL')
            ->assertDontSee('Urutan tampil')
            ->assertDontSee('Tanggal publikasi')
            ->assertDontSee('Tandai sebagai unggulan')
            ->assertDontSee('Pengaturan SEO');

        PrincipalWelcome::query()->create([
            'title' => 'Sambutan Sebelumnya',
            'slug' => 'sambutan-sebelumnya-untuk-urutan',
            'status' => 'draft',
            'sort_order' => 7,
            'metadata' => [],
        ]);
        $publishedAt = now()->startOfMinute();
        $this->travelTo($publishedAt);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.content.store', $type), [
                'title' => 'Sambutan Kepala Sekolah 2026',
                'category' => 'Kepala SMK Islamiyah Ciputat',
                'summary' => 'Assalamu’alaikum warahmatullahi wabarakatuh.',
                'body' => 'Kami mendampingi siswa untuk berkembang.',
                'status' => 'published',
                'metadata' => [
                    'name' => 'Kepala Sekolah Uji',
                    'description' => 'Sambutan untuk peserta didik dan orang tua.',
                ],
                'image' => UploadedFile::fake()->image('kepala-sekolah.jpg', 600, 400),
            ])
            ->assertRedirect(route('admin.content.index', $type));

        $entry = PrincipalWelcome::query()
            ->where('slug', 'sambutan-kepala-sekolah-2026')
            ->firstOrFail();
        $imagePath = $entry->image_path;

        $this->assertSame(8, $entry->sort_order);
        $this->assertSame($publishedAt->format('Y-m-d H:i:s'), $entry->published_at->format('Y-m-d H:i:s'));
        $this->assertDatabaseHas('content_activities', [
            'content_type' => 'principal_welcome',
            'content_id' => $entry->id,
            'action' => 'created',
        ]);
        Storage::disk('public')->assertExists($imagePath);
        $this->assertDatabaseMissing('teachers', ['slug' => 'sambutan-kepala-sekolah-2026'], 'teachers');

        $this->travelTo($publishedAt->copy()->addMinute());
        $this->actingAs($admin, 'admin')
            ->put(route('admin.content.update', ['type' => $type, 'entry' => $entry]), [
                'title' => 'Sambutan Kepala Sekolah 2027',
                'category' => 'Kepala SMK Islamiyah Ciputat',
                'summary' => 'Salam pembuka yang diperbarui.',
                'body' => 'Isi sambutan yang diperbarui.',
                'status' => 'published',
                'metadata' => [
                    'name' => 'Kepala Sekolah Uji',
                    'description' => 'Deskripsi yang diperbarui.',
                ],
            ])
            ->assertRedirect(route('admin.content.index', $type));

        $entry->refresh();
        $this->assertSame('sambutan-kepala-sekolah-2027', $entry->slug);
        $this->assertSame(8, $entry->sort_order);
        $this->assertSame($publishedAt->format('Y-m-d H:i:s'), $entry->published_at->format('Y-m-d H:i:s'));
        $this->assertSame($imagePath, $entry->image_path);
        $this->assertDatabaseHas('content_activities', [
            'content_type' => 'principal_welcome',
            'content_id' => $entry->id,
            'action' => 'updated',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Kepala Sekolah Uji')
            ->assertSee('Isi sambutan yang diperbarui.');

        $this->actingAs($admin, 'admin')
            ->delete(route('admin.content.destroy', ['type' => $type, 'entry' => $entry]))
            ->assertRedirect(route('admin.content.index', $type));

        $this->assertDatabaseMissing('principal_welcomes', ['id' => $entry->id]);
        $this->assertDatabaseHas('content_activities', [
            'content_type' => 'principal_welcome',
            'content_id' => $entry->id,
            'action' => 'deleted',
        ]);
        Storage::disk('public')->assertMissing($imagePath);
    }

    public function test_principal_welcome_generates_unique_slugs_and_automatic_order(): void
    {
        $admin = $this->createAdmin();
        $type = 'principal-welcomes';

        PrincipalWelcome::query()->create([
            'title' => 'Sambutan Sebelumnya',
            'slug' => 'sambutan-sebelumnya-untuk-urutan',
            'status' => 'draft',
            'sort_order' => 7,
            'metadata' => [],
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.content.store', $type), [
                'title' => 'Sambutan Otomatis Uji',
                'status' => 'draft',
            ])
            ->assertRedirect(route('admin.content.index', $type));

        $this->actingAs($admin, 'admin')
            ->post(route('admin.content.store', $type), [
                'title' => 'Sambutan Otomatis Uji',
                'status' => 'draft',
            ])
            ->assertRedirect(route('admin.content.index', $type));

        $entries = PrincipalWelcome::query()
            ->where('title', 'Sambutan Otomatis Uji')
            ->orderBy('id')
            ->get();

        $this->assertSame(
            ['sambutan-otomatis-uji', 'sambutan-otomatis-uji-2'],
            $entries->pluck('slug')->all(),
        );
        $this->assertSame([8, 9], $entries->pluck('sort_order')->all());
        $this->assertNull($entries[0]->published_at);
        $this->assertNull($entries[1]->published_at);
    }

    public function test_principal_welcome_migration_preserves_existing_settings(): void
    {
        ContentEntry::query()->where('type', 'principal_welcome')->delete();

        $legacySettings = [
            'home.principal.visible' => true,
            'home.principal.name' => 'Kepala Sekolah Lama',
            'home.principal.position' => 'Kepala SMK Islamiyah Ciputat',
            'home.principal.greeting' => 'Salam lama.',
            'home.principal.message' => 'Isi sambutan lama.',
            'home.principal.description' => 'Deskripsi lama.',
            'home.principal.image' => 'cms/settings/kepala.jpg',
        ];

        foreach ($legacySettings as $key => $value) {
            SiteSetting::query()->updateOrCreate(
                ['key' => $key],
                ['group' => 'home', 'value' => json_encode($value, JSON_THROW_ON_ERROR)],
            );
        }

        $migration = require database_path('migrations/2026_11_12_031213_migrate_principal_welcome_settings_to_content_entries.php');
        $migration->up();

        $entry = ContentEntry::query()->where('type', 'principal_welcome')->firstOrFail();

        $this->assertSame('Kepala Sekolah Lama', $entry->metadata['name']);
        $this->assertSame('Deskripsi lama.', $entry->metadata['description']);
        $this->assertSame('Isi sambutan lama.', $entry->body);
        $this->assertSame('cms/settings/kepala.jpg', $entry->image_path);
        $this->assertDatabaseMissing('site_settings', ['key' => 'home.principal.message']);
    }

    public function test_settings_reject_unsafe_link_schemes(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin, 'admin')
            ->put(route('admin.settings.update'), [
                'section' => 'navbar',
                'settings' => [
                    'navbar' => [
                        'home' => ['url' => 'javascript:alert(1)'],
                    ],
                ],
            ])
            ->assertSessionHasErrors(['settings.navbar.home.url']);

        $this->assertDatabaseMissing('site_settings', ['key' => 'navbar.home.url']);
    }

    public function test_settings_accept_internal_links(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin, 'admin')
            ->put(route('admin.settings.update'), [
                'section' => 'navbar',
                'settings' => [
                    'navbar' => [
                        'home' => ['url' => '/custom-page'],
                    ],
                ],
            ])
            ->assertRedirect(route('admin.settings.index', ['section' => 'navbar']));

        $this->assertSame(
            '/custom-page',
            json_decode(SiteSetting::query()->findOrFail('navbar.home.url')->value, true, flags: JSON_THROW_ON_ERROR),
        );
    }

    #[DataProvider('settingsSections')]
    public function test_admin_can_save_settings_in_each_section(
        string $section,
        array $settings,
        string $key,
        string $expectedValue,
    ): void {
        $admin = $this->createAdmin();

        $this->actingAs($admin, 'admin')
            ->put(route('admin.settings.update'), [
                'section' => $section,
                'settings' => [$section => $settings],
            ])
            ->assertRedirect(route('admin.settings.index', ['section' => $section]));

        $this->assertSame(
            $expectedValue,
            json_decode(SiteSetting::query()->findOrFail($key)->value, true, flags: JSON_THROW_ON_ERROR),
        );
    }

    public static function settingsSections(): array
    {
        return [
            'identity' => ['identity', ['school_name' => 'SMK Uji'], 'identity.school_name', 'SMK Uji'],
            'navbar' => ['navbar', ['home' => ['label' => 'Awal']], 'navbar.home.label', 'Awal'],
            'homepage' => ['home', ['hero' => ['slogan' => 'Belajar bersama']], 'home.hero.slogan', 'Belajar bersama'],
            'about' => ['about', ['title' => 'Profil Sekolah'], 'about.title', 'Profil Sekolah'],
            'teachers' => ['teachers', ['title' => 'Pendidik'], 'teachers.title', 'Pendidik'],
            'bkk' => ['bkk', ['title' => 'Peluang Kerja'], 'bkk.title', 'Peluang Kerja'],
            'spmb' => ['spmb', ['title' => 'Penerimaan Siswa'], 'spmb.title', 'Penerimaan Siswa'],
            'footer' => ['footer', ['description' => 'Informasi sekolah'], 'footer.description', 'Informasi sekolah'],
            'seo' => ['seo', ['title' => 'SMK Islamiyah Ciputat'], 'seo.title', 'SMK Islamiyah Ciputat'],
        ];
    }

    public function test_homepage_renders_admin_managed_teacher_section_copy(): void
    {
        SiteSetting::query()->create([
            'key' => 'home.teachers.heading',
            'group' => 'home',
            'value' => json_encode('Pendidik Unggul', JSON_THROW_ON_ERROR),
        ]);
        SiteSetting::query()->create([
            'key' => 'home.teachers.description',
            'group' => 'home',
            'value' => json_encode('Mendampingi masa depan siswa.', JSON_THROW_ON_ERROR),
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Pendidik Unggul')
            ->assertSee('Mendampingi masa depan siswa.');
    }

    public function test_teacher_page_renders_admin_managed_title_and_description(): void
    {
        SiteSetting::query()->create([
            'key' => 'teachers.title',
            'group' => 'teachers',
            'value' => json_encode('Tenaga Pendidik Kami', JSON_THROW_ON_ERROR),
        ]);
        SiteSetting::query()->create([
            'key' => 'teachers.description',
            'group' => 'teachers',
            'value' => json_encode('Kenali para pendidik sekolah.', JSON_THROW_ON_ERROR),
        ]);

        $this->get(route('teachers'))
            ->assertOk()
            ->assertSee('Tenaga Pendidik Kami')
            ->assertSee('Kenali para pendidik sekolah.');
    }

    public function test_public_news_escapes_admin_provided_html(): void
    {
        $article = NewsArticle::query()->create([
            'title' => '<script>alert(1)</script>',
            'slug' => 'safe-rendering-test',
            'summary' => '<img src=x onerror=alert(1)>',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $this->get(route('news.show', $article->slug))
            ->assertOk()
            ->assertSee('&lt;script&gt;', false)
            ->assertSee('&lt;img', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('<img src=x onerror=alert(1)>', false);
    }

    public function test_superadmin_can_manage_users(): void
    {
        $superadmin = $this->createAdmin('superadmin');

        $this->actingAs($superadmin, 'admin')
            ->post(route('admin.admins.store'), [
                'name' => 'Staf Baru',
                'username' => 'stafbaru',
                'role' => 'admin',
                'password' => 'password1234',
                'password_confirmation' => 'password1234',
            ])
            ->assertRedirect(route('admin.admins.index'));

        $this->assertDatabaseHas('admins', [
            'username' => 'stafbaru',
            'role' => 'admin',
        ]);

        $created = Admin::query()->where('username', 'stafbaru')->firstOrFail();

        $this->actingAs($superadmin, 'admin')
            ->put(route('admin.admins.update', $created), [
                'name' => 'Staf Baru Edit',
                'username' => 'stafbaruedit',
                'role' => 'admin',
                'password' => 'newpassword1234',
                'password_confirmation' => 'newpassword1234',
            ])
            ->assertRedirect(route('admin.admins.index'));

        $this->assertDatabaseHas('admins', [
            'username' => 'stafbaruedit',
        ]);

        $this->actingAs($superadmin, 'admin')
            ->delete(route('admin.admins.destroy', $created))
            ->assertRedirect(route('admin.admins.index'));

        $this->assertDatabaseMissing('admins', [
            'id' => $created->id,
        ]);
    }

    public function test_superadmin_can_create_editor_but_cannot_create_another_superadmin(): void
    {
        $superadmin = $this->createAdmin('superadmin');

        $this->actingAs($superadmin, 'admin')
            ->get(route('admin.admins.create'))
            ->assertOk()
            ->assertSee('Editor — konten dan unggah foto konten')
            ->assertDontSee('value="superadmin"', false);

        $this->actingAs($superadmin, 'admin')
            ->post(route('admin.admins.store'), [
                'name' => 'Editor Konten',
                'username' => 'editorkonten',
                'role' => 'editor',
                'password' => 'password1234',
                'password_confirmation' => 'password1234',
            ])
            ->assertRedirect(route('admin.admins.index'));

        $this->assertDatabaseHas('admins', [
            'username' => 'editorkonten',
            'role' => 'editor',
        ]);

        $this->actingAs($superadmin, 'admin')
            ->from(route('admin.admins.create'))
            ->post(route('admin.admins.store'), [
                'name' => 'Superadmin Kedua',
                'username' => 'superadminkedua',
                'role' => 'superadmin',
                'password' => 'password1234',
                'password_confirmation' => 'password1234',
            ])
            ->assertRedirect(route('admin.admins.create'))
            ->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('admins', [
            'username' => 'superadminkedua',
        ]);
        $this->assertSame(1, Admin::query()->where('role', 'superadmin')->count());
    }

    public function test_admin_create_command_allows_only_the_first_superadmin(): void
    {
        $this->artisan('admin:create')
            ->expectsQuestion('Nama admin', 'Super Administrator')
            ->expectsQuestion('Username admin', 'superadminpertama')
            ->expectsQuestion('Role / Peran', 'superadmin')
            ->expectsQuestion('Email admin (opsional, tekan Enter untuk lewati)', '')
            ->expectsQuestion('Kata sandi (minimal 8 karakter)', 'Secure-password-1234')
            ->expectsQuestion('Ulangi kata sandi', 'Secure-password-1234')
            ->assertExitCode(0);

        $this->assertDatabaseHas('admins', [
            'username' => 'superadminpertama',
            'role' => 'superadmin',
        ]);

        $this->artisan('admin:create')
            ->expectsQuestion('Nama admin', 'Editor Konten')
            ->expectsQuestion('Username admin', 'editorpertama')
            ->expectsQuestion('Role / Peran', 'editor')
            ->expectsQuestion('Email admin (opsional, tekan Enter untuk lewati)', '')
            ->expectsQuestion('Kata sandi (minimal 8 karakter)', 'Secure-password-1234')
            ->expectsQuestion('Ulangi kata sandi', 'Secure-password-1234')
            ->assertExitCode(0);

        $this->assertDatabaseHas('admins', [
            'username' => 'editorpertama',
            'role' => 'editor',
        ]);
        $this->assertSame(1, Admin::query()->where('role', 'superadmin')->count());
    }

    public function test_cms_content_seeder_does_not_create_a_predictable_admin_account(): void
    {
        $this->seed(CmsContentSeeder::class);

        $this->assertDatabaseMissing('admins', [
            'username' => 'superadmin',
        ]);
    }

    public function test_editor_can_manage_content_but_cannot_access_admin_features(): void
    {
        $editor = $this->createAdmin('editor');

        $this->actingAs($editor, 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Peran Anda: <strong>Editor</strong>', false)
            ->assertSee('Editor dapat mengunggah foto untuk konten')
            ->assertDontSee('href="'.route('admin.media.index').'"', false)
            ->assertDontSee('href="'.route('admin.settings.index').'"', false)
            ->assertDontSee('Manajemen Admin');

        $this->actingAs($editor, 'admin')
            ->get(route('admin.content.index', 'programs'))
            ->assertOk();

        $this->actingAs($editor, 'admin')
            ->get(route('admin.settings.index'))
            ->assertForbidden();

        $this->actingAs($editor, 'admin')
            ->get(route('admin.media.index'))
            ->assertForbidden();

        $this->actingAs($editor, 'admin')
            ->get(route('admin.admins.index'))
            ->assertForbidden();
    }

    public function test_regular_admin_cannot_access_user_management(): void
    {
        $admin = $this->createAdmin('admin');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.admins.index'))
            ->assertForbidden();
    }

    public function test_admin_can_manage_teacher_in_separate_database(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.teachers.store'), [
                'title' => 'Guru Uji',
                'slug' => 'guru-uji',
                'summary' => 'Profil singkat guru uji.',
                'status' => 'published',
                'metadata' => [
                    'position' => 'Guru Matematika',
                    'education' => 'S1 Pendidikan',
                ],
            ])
            ->assertRedirect(route('admin.teachers.index'));

        $this->assertDatabaseHas('teachers', [
            'name' => 'Guru Uji',
            'slug' => 'guru-uji',
            'status' => 'published',
        ], 'teachers');

        $this->assertDatabaseMissing('content_entries', [
            'type' => 'teacher',
            'slug' => 'guru-uji',
        ]);

        $teacher = Teacher::query()->where('slug', 'guru-uji')->firstOrFail();

        $this->actingAs($admin, 'admin')
            ->put(route('admin.teachers.update', $teacher), [
                'title' => 'Guru Uji Edit',
                'slug' => 'guru-uji-edit',
                'summary' => 'Profil diperbarui.',
                'status' => 'published',
                'metadata' => [
                    'position' => 'Guru IPA',
                    'education' => 'S2 Pendidikan',
                ],
            ])
            ->assertRedirect(route('admin.teachers.index'));

        $this->assertDatabaseHas('teachers', [
            'id' => $teacher->id,
            'name' => 'Guru Uji Edit',
            'slug' => 'guru-uji-edit',
        ], 'teachers');

        $this->get(route('teachers'))
            ->assertOk()
            ->assertSee('Guru Uji Edit');

        $this->actingAs($admin, 'admin')
            ->delete(route('admin.teachers.destroy', $teacher))
            ->assertRedirect(route('admin.teachers.index'));

        $this->assertDatabaseMissing('teachers', [
            'id' => $teacher->id,
        ], 'teachers');
        $this->assertNull(Teacher::query()->find($teacher->id));

        $this->assertDatabaseHas('content_activities', [
            'content_type' => 'teacher',
            'content_id' => $teacher->id,
            'action' => 'deleted',
            'title' => 'Guru Uji Edit',
        ]);

        $this->get(route('teachers'))
            ->assertOk()
            ->assertDontSee('Guru Uji Edit');
    }

    public function test_public_teacher_directory_filters_by_teacher_category(): void
    {
        $guru = $this->createTeacher([
            'name' => 'Siti Guru',
            'slug' => 'siti-guru',
            'category' => 'Tenaga Pendidik',
        ]);
        $staff = $this->createTeacher([
            'name' => 'Budi Tenaga Kependidikan',
            'slug' => 'budi-tenaga-kependidikan',
            'category' => 'Tenaga Kependidikan',
        ]);

        $this->get(route('teachers', ['category' => 'guru']))
            ->assertOk()
            ->assertSee($guru->name)
            ->assertDontSee($staff->name);
    }

    public function test_public_teacher_directory_filters_by_support_staff_category(): void
    {
        $guru = $this->createTeacher([
            'name' => 'Siti Guru',
            'slug' => 'siti-guru',
            'category' => 'Tenaga Pendidik',
        ]);
        $staff = $this->createTeacher([
            'name' => 'Budi Tenaga Kependidikan',
            'slug' => 'budi-tenaga-kependidikan',
            'category' => 'Tenaga Kependidikan',
        ]);

        $this->get(route('teachers', ['category' => 'kependidikan']))
            ->assertOk()
            ->assertSee($staff->name)
            ->assertDontSee($guru->name);
    }

    public function test_public_teacher_directory_search_and_pagination_keep_the_selected_filters(): void
    {
        foreach (range(1, 9) as $number) {
            $this->createTeacher([
                'name' => sprintf('Guru Pendidik %02d', $number),
                'slug' => sprintf('guru-pendidik-%02d', $number),
                'category' => 'Tenaga Pendidik',
            ]);
        }

        $this->get(route('teachers', [
            'category' => 'guru',
            'search' => 'Pendidik',
        ]))
            ->assertOk()
            ->assertSee('Guru Pendidik 01')
            ->assertDontSee('Guru Pendidik 09')
            ->assertSee('category=guru', false)
            ->assertSee('search=Pendidik', false)
            ->assertSee('page=2', false);

        $this->get(route('teachers', [
            'category' => 'guru',
            'search' => 'Pendidik',
            'page' => 2,
        ]))
            ->assertOk()
            ->assertSee('Guru Pendidik 09');
    }

    public function test_teacher_edit_does_not_create_duplicate_record(): void
    {
        $admin = $this->createAdmin();
        $teacher = $this->createTeacher(['name' => 'Guru A', 'slug' => 'guru-a', 'position' => 'Guru']);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.teachers.update', $teacher), $this->teacherPayload([
                'title' => 'Guru A Edit',
                'slug' => 'guru-a-edit',
                'metadata' => ['position' => 'Guru IPA', 'education' => 'S1'],
            ]))
            ->assertRedirect(route('admin.teachers.index'));

        $this->assertSame(1, Teacher::query()->count());
        $this->assertDatabaseHas('teachers', ['id' => $teacher->id, 'name' => 'Guru A Edit'], 'teachers');
    }

    public function test_teacher_photo_replaced_and_old_file_cleaned_when_unused(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $teacher = $this->createTeacher(['image_path' => UploadedFile::fake()->image('old.jpg')->storePublicly('cms/teacher', 'public')]);
        $oldPath = $teacher->image_path;
        Storage::disk('public')->assertExists($oldPath);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.teachers.update', $teacher), $this->teacherPayload([
                'title' => $teacher->name,
                'slug' => $teacher->slug,
                'metadata' => ['position' => $teacher->position, 'education' => $teacher->education],
                'image' => UploadedFile::fake()->image('new.jpg'),
            ]))
            ->assertRedirect(route('admin.teachers.index'));

        $teacher->refresh();
        $this->assertNotSame($oldPath, $teacher->image_path);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($teacher->image_path);
    }

    public function test_teacher_photo_focus_is_saved_and_rendered_on_public_id_cards(): void
    {
        $admin = $this->createAdmin();
        $asset = MediaAsset::query()->create([
            'disk' => 'public',
            'filename' => 'guru.webp',
            'original_path' => 'cms/media/originals/guru-focus.png',
            'optimized_path' => 'cms/media/optimized/guru-focus.webp',
            'media_type' => 'image',
            'mime_type' => 'image/png',
            'file_size' => 1000,
            'optimized_size' => 800,
            'width' => 600,
            'height' => 900,
            'quality' => 'optimized',
            'category' => 'teacher',
            'processing' => ['output_mime_type' => 'image/webp'],
        ]);
        $teacher = $this->createTeacher(['image_path' => $asset->publicPath()]);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.teachers.update', $teacher), $this->teacherPayload([
                'title' => $teacher->name,
                'slug' => $teacher->slug,
                'metadata' => ['position' => $teacher->position, 'education' => null],
                'focal_point_x' => 63,
                'focal_point_y' => 34,
            ]))
            ->assertRedirect(route('admin.teachers.index'));

        $asset->refresh();
        $this->assertSame('image/webp', $asset->processing['output_mime_type']);
        $this->assertSame(63.0, (float) data_get($asset->processing, 'focal_point.x'));
        $this->assertSame(34.0, (float) data_get($asset->processing, 'focal_point.y'));

        $this->get(route('teachers'))
            ->assertOk()
            ->assertSee('--staff-focal-x: 63%; --staff-focal-y: 34%', false);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('--staff-focal-x: 63%; --staff-focal-y: 34%', false);
    }

    public function test_teacher_photo_focus_rejects_values_outside_the_image_bounds(): void
    {
        $admin = $this->createAdmin();
        $teacher = $this->createTeacher();

        $this->actingAs($admin, 'admin')
            ->put(route('admin.teachers.update', $teacher), $this->teacherPayload([
                'title' => $teacher->name,
                'slug' => $teacher->slug,
                'metadata' => ['position' => $teacher->position, 'education' => null],
                'focal_point_x' => 101,
                'focal_point_y' => 24,
            ]))
            ->assertSessionHasErrors(['focal_point_x']);
    }

    public function test_teacher_edit_without_photo_keeps_existing_photo(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $path = UploadedFile::fake()->image('keep.jpg')->storePublicly('cms/teacher', 'public');
        $teacher = $this->createTeacher(['image_path' => $path]);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.teachers.update', $teacher), $this->teacherPayload([
                'title' => 'Nama Baru',
                'slug' => $teacher->slug,
                'metadata' => ['position' => $teacher->position, 'education' => $teacher->education],
            ]))
            ->assertRedirect(route('admin.teachers.index'));

        $teacher->refresh();
        $this->assertSame($path, $teacher->image_path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_teacher_delete_removes_record_and_unused_photo(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $path = UploadedFile::fake()->image('gone.jpg')->storePublicly('cms/teacher', 'public');
        $teacher = $this->createTeacher(['image_path' => $path]);

        $this->actingAs($admin, 'admin')
            ->delete(route('admin.teachers.destroy', $teacher))
            ->assertRedirect(route('admin.teachers.index'));

        $this->assertDatabaseMissing('teachers', ['id' => $teacher->id], 'teachers');
        Storage::disk('public')->assertMissing($path);
    }

    public function test_teacher_delete_keeps_shared_photo_still_in_use(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $path = UploadedFile::fake()->image('shared.jpg')->storePublicly('cms/teacher', 'public');
        $teacher = $this->createTeacher(['name' => 'Guru 1', 'slug' => 'guru-1', 'image_path' => $path]);
        $this->createTeacher(['name' => 'Guru 2', 'slug' => 'guru-2', 'position' => 'Guru B', 'image_path' => $path]);

        $this->actingAs($admin, 'admin')
            ->delete(route('admin.teachers.destroy', $teacher))
            ->assertRedirect(route('admin.teachers.index'));

        $this->assertDatabaseMissing('teachers', ['id' => $teacher->id], 'teachers');
        Storage::disk('public')->assertExists($path);
    }

    public function test_teacher_duplicate_name_and_position_is_rejected(): void
    {
        $admin = $this->createAdmin();
        $this->createTeacher(['name' => 'Guru Sama', 'slug' => 'guru-sama', 'position' => 'Guru Matematika']);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.teachers.store'), $this->teacherPayload([
                'title' => 'Guru Sama',
                'slug' => 'guru-sama-beda',
                'metadata' => ['position' => 'Guru Matematika', 'education' => null],
            ]))
            ->assertSessionHasErrors('title');

        $this->assertSame(1, Teacher::query()->count());
    }

    public function test_teacher_bulk_delete_and_empty_category(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $t1 = $this->createTeacher(['name' => 'Guru 1', 'slug' => 'guru-1', 'image_path' => UploadedFile::fake()->image('a.jpg')->storePublicly('cms/teacher', 'public')]);
        $t2 = $this->createTeacher(['name' => 'Guru 2', 'slug' => 'guru-2', 'position' => 'Guru B']);
        $t3 = $this->createTeacher(['name' => 'Guru 3', 'slug' => 'guru-3', 'position' => 'Guru C']);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.teachers.bulk-destroy'), ['ids' => [$t1->id, $t2->id]])
            ->assertRedirect(route('admin.teachers.index'));

        $this->assertDatabaseMissing('teachers', ['id' => $t1->id], 'teachers');
        $this->assertDatabaseMissing('teachers', ['id' => $t2->id], 'teachers');
        $this->assertDatabaseHas('teachers', ['id' => $t3->id], 'teachers');
        Storage::disk('public')->assertMissing($t1->image_path);
        $this->assertDatabaseHas('content_activities', ['content_type' => 'teacher', 'content_id' => $t1->id, 'action' => 'deleted']);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.teachers.empty'), ['confirm_name' => 'TENAGA KEPENDIDIKAN'])
            ->assertRedirect(route('admin.teachers.index'));

        $this->assertSame(0, Teacher::query()->count());
        $this->get(route('teachers'))
            ->assertOk()
            ->assertDontSee('Guru 1')
            ->assertDontSee('Guru 2')
            ->assertDontSee('Guru 3');
    }

    private function createAdmin(string $role = 'superadmin'): Admin
    {
        return Admin::query()->create([
            'name' => 'CMS Editor',
            'username' => 'editor',
            'role' => $role,
            'email' => 'editor@example.test',
            'password' => Hash::make('Secure-password-1234'),
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createTeacher(array $attributes = []): Teacher
    {
        $defaults = [
            'name' => 'Guru Default',
            'slug' => 'guru-default-'.Str::random(4),
            'position' => 'Guru Umum',
            'education' => null,
            'category' => 'Tenaga Pendidik',
            'summary' => null,
            'image_path' => null,
            'status' => 'published',
            'sort_order' => 0,
            'published_at' => now(),
        ];

        return Teacher::query()->create(array_merge($defaults, $attributes));
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function teacherPayload(array $overrides = []): array
    {
        $payload = [
            'title' => 'Guru Payload',
            'slug' => 'guru-payload',
            'summary' => 'Ringkasan',
            'status' => 'published',
            'metadata' => [
                'position' => 'Guru Payload',
                'education' => 'S1',
            ],
        ];

        return array_replace_recursive($payload, $overrides);
    }
}
