<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\GalleryPhoto;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_program_details_and_new_programs_use_the_shared_template(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.content.store', 'programs'), [
                'title' => 'Program Keahlian Baru',
                'slug' => 'program-keahlian-baru',
                'summary' => 'Ringkasan program baru.',
                'body' => 'Penjelasan lengkap program baru.',
                'status' => 'published',
                'metadata' => [
                    'abbreviation' => 'PKB',
                    'route_key' => 'program-keahlian-baru',
                    'primary_competence' => 'Kompetensi program baru',
                    'facilities' => 'Fasilitas program baru',
                    'career_opportunities' => 'Peluang karier program baru',
                    'advantages' => "Kompetensi pertama\nKompetensi kedua",
                ],
            ])
            ->assertRedirect(route('admin.content.index', 'programs'));

        GalleryPhoto::query()->create([
            'title' => 'Praktik program baru',
            'slug' => 'praktik-program-baru',
            'image_path' => '/images/program-kegiatan.jpg',
            'status' => 'published',
            'metadata' => ['program_route_key' => 'program-keahlian-baru'],
        ]);

        $this->get(route('program.show', 'program-keahlian-baru'))
            ->assertOk()
            ->assertSee('Program Keahlian Baru')
            ->assertDontSee('Galeri Kegiatan Program Keahlian Baru')
            ->assertDontSee('#program-gallery', false)
            ->assertSee('Profil Lengkap')
            ->assertSee('Kompetensi program baru')
            ->assertSee('Fasilitas Pendukung')
            ->assertSee('Fasilitas program baru')
            ->assertSee('Peluang karier program baru')
            ->assertSee('Kompetensi yang Dipelajari')
            ->assertSee('Penjelasan lengkap program baru.')
            ->assertDontSee('Ringkasan program baru.')
            ->assertSee('Kompetensi kedua')
            ->assertSee('Praktik program baru')
            ->assertSee('/images/program-kegiatan.jpg', false)
            ->assertSee(route('spmb'), false);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Ringkasan program baru.');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.content.create', 'gallery'))
            ->assertOk()
            ->assertSee('metadata[program_route_key]', false)
            ->assertSee('value="program-keahlian-baru"', false);
    }

    public function test_program_detail_hides_sections_without_available_data(): void
    {
        $program = Program::query()->create([
            'title' => 'Akuntansi',
            'slug' => 'akuntansi',
            'route_key' => 'ak',
            'summary' => 'Ringkasan yang hanya untuk beranda.',
            'status' => 'published',
        ]);

        $this->get(route('program.show', $program->slug))
            ->assertOk()
            ->assertSee('Akuntansi')
            ->assertSee('Tertarik Bergabung di Program Akuntansi?')
            ->assertDontSee('Ringkasan yang hanya untuk beranda.')
            ->assertDontSee('Tentang Program Akuntansi')
            ->assertDontSee('Fasilitas Pendukung')
            ->assertDontSee('Lihat Galeri Kegiatan')
            ->assertDontSee('Profil Lengkap');
    }

    public function test_program_form_exposes_only_the_fields_used_by_the_public_pages(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.content.create', 'programs'))
            ->assertOk()
            ->assertSee('Nama Bidang')
            ->assertSee('Ringkasan Beranda')
            ->assertSee('maxlength="80"', false)
            ->assertSee('Deskripsi Detail Program')
            ->assertSee('Konten Halaman Detail')
            ->assertSee('Isi deskripsi panjang untuk bagian Tentang Program di halaman detail.')
            ->assertSee('Foto Program Keahlian')
            ->assertSee('Kompetensi Utama')
            ->assertSee('Fasilitas Pendukung')
            ->assertSee('Kompetensi yang Dipelajari')
            ->assertSee('Peluang Karier')
            ->assertSee('name="metadata[primary_competence]"', false)
            ->assertSee('name="metadata[facilities]"', false)
            ->assertSee('name="metadata[advantages]"', false)
            ->assertSee('name="metadata[career_opportunities]"', false)
            ->assertDontSee('Slug URL')
            ->assertDontSee('metadata[abbreviation]', false)
            ->assertDontSee('Kunci URL lama')
            ->assertDontSee('Status publikasi')
            ->assertDontSee('Pengaturan SEO');
    }

    public function test_program_creation_generates_hidden_values_and_separates_home_and_detail_copy(): void
    {
        $admin = $this->createAdmin();
        $summary = 'Ringkasan khusus yang tampil pada beranda.';
        $body = 'Deskripsi panjang khusus untuk halaman detail program.';

        $this->actingAs($admin, 'admin')
            ->post(route('admin.content.store', 'programs'), [
                'title' => 'Desain Komunikasi Visual',
                'summary' => $summary,
                'body' => $body,
            ])
            ->assertRedirect(route('admin.content.index', 'programs'));

        $program = Program::query()->where('title', 'Desain Komunikasi Visual')->firstOrFail();

        $this->assertModelExists($program);
        $this->assertSame('desain-komunikasi-visual', $program->slug);
        $this->assertSame('desain-komunikasi-visual', $program->route_key);
        $this->assertSame('Program Keahlian', $program->category);
        $this->assertSame('published', $program->status);
        $this->assertSame('DKV', $program->metadata['abbreviation']);
        $this->assertSame('graduation-cap', $program->metadata['icon']);
        $this->assertSame(0, $program->sort_order);

        $program->update(['image_path' => '/images/program-uji.jpg']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee($summary)
            ->assertSee('/images/program-uji.jpg', false)
            ->assertDontSee($body);

        $this->get(route('program.show', $program->slug))
            ->assertOk()
            ->assertSee($body)
            ->assertDontSee($summary)
            ->assertSee('/images/program-uji.jpg', false);
    }

    public function test_program_summary_rejects_more_than_eighty_characters(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.content.store', 'programs'), [
                'title' => 'Program Ringkasan Panjang',
                'summary' => str_repeat('x', 81),
            ])
            ->assertSessionHasErrors([
                'summary' => 'Ringkasan beranda maksimal 80 karakter.',
            ]);

        $this->assertDatabaseMissing('programs', [
            'title' => 'Program Ringkasan Panjang',
        ]);
    }

    public function test_generated_program_urls_use_unique_suffixes_for_repeated_names(): void
    {
        $admin = $this->createAdmin();
        Program::query()->create([
            'title' => 'Program Duplikat',
            'slug' => 'program-duplikat',
            'route_key' => 'program-duplikat',
            'status' => 'published',
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.content.store', 'programs'), [
                'title' => 'Program Duplikat',
            ])
            ->assertRedirect(route('admin.content.index', 'programs'));

        $program = Program::query()->where('slug', 'program-duplikat-2')->firstOrFail();

        $this->assertSame('program-duplikat-2', $program->route_key);
        $this->assertSame('published', $program->status);
    }

    public function test_program_edit_preserves_hidden_metadata_and_existing_publication_values(): void
    {
        $admin = $this->createAdmin();
        $program = Program::query()->create([
            'title' => 'Kuliner',
            'slug' => 'kuliner-khusus',
            'route_key' => 'kuliner',
            'summary' => 'Ringkasan lama.',
            'body' => 'Deskripsi lama.',
            'category' => 'Program Keahlian',
            'image_path' => '/images/kuliner.jpg',
            'status' => 'draft',
            'featured' => true,
            'sort_order' => 6,
            'metadata' => [
                'abbreviation' => 'KUL',
                'icon' => 'chef-hat',
                'primary_competence' => 'Kompetensi lama',
                'facilities' => 'Dapur praktik',
                'career_opportunities' => 'Juru masak',
                'advantages' => 'Pengolahan makanan',
                'legacy_note' => 'Data lama dipertahankan',
            ],
        ]);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.content.update', ['type' => 'programs', 'entry' => $program->id]), [
                'title' => 'Kuliner',
                'summary' => 'Ringkasan baru.',
                'body' => 'Deskripsi detail baru.',
            ])
            ->assertRedirect(route('admin.content.index', 'programs'));

        $updatedProgram = $program->fresh();

        $this->assertSame('kuliner-khusus', $updatedProgram->slug);
        $this->assertSame('kuliner', $updatedProgram->route_key);
        $this->assertSame('draft', $updatedProgram->status);
        $this->assertSame(6, $updatedProgram->sort_order);
        $this->assertSame(true, $updatedProgram->featured);
        $this->assertSame('/images/kuliner.jpg', $updatedProgram->image_path);
        $this->assertSame('chef-hat', $updatedProgram->metadata['icon']);
        $this->assertSame('Kompetensi lama', $updatedProgram->metadata['primary_competence']);
        $this->assertSame('Data lama dipertahankan', $updatedProgram->metadata['legacy_note']);
        $this->assertSame('Ringkasan baru.', $updatedProgram->summary);
        $this->assertSame('Deskripsi detail baru.', $updatedProgram->body);
    }

    public function test_program_admin_can_update_detail_metadata_and_public_detail_uses_the_values(): void
    {
        $admin = $this->createAdmin();
        $program = Program::query()->create([
            'title' => 'Kuliner',
            'slug' => 'kuliner',
            'route_key' => 'kuliner',
            'summary' => 'Ringkasan Kuliner.',
            'body' => 'Tentang program kuliner.',
            'status' => 'published',
            'metadata' => [
                'abbreviation' => 'KUL',
                'primary_competence' => 'Kompetensi lama',
                'facilities' => 'Fasilitas lama',
                'career_opportunities' => 'Peluang lama',
                'advantages' => 'Kompetensi lama',
            ],
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.content.edit', ['type' => 'programs', 'entry' => $program->id]))
            ->assertOk()
            ->assertSee('value="Kompetensi lama"', false)
            ->assertSee('value="Peluang lama"', false)
            ->assertSee('>Fasilitas lama</textarea>', false)
            ->assertSee('>Kompetensi lama</textarea>', false);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.content.update', ['type' => 'programs', 'entry' => $program->id]), [
                'title' => 'Kuliner',
                'summary' => 'Ringkasan Kuliner.',
                'body' => 'Tentang program kuliner.',
                'metadata' => [
                    'primary_competence' => 'Kompetensi utama baru',
                    'facilities' => "Fasilitas baru satu\nFasilitas baru dua",
                    'career_opportunities' => 'Peluang karier baru',
                    'advantages' => "Kompetensi baru satu\nKompetensi baru dua",
                ],
            ])
            ->assertRedirect(route('admin.content.index', 'programs'));

        $updatedProgram = $program->fresh();

        $this->assertSame('Kompetensi utama baru', $updatedProgram->metadata['primary_competence']);
        $this->assertSame("Fasilitas baru satu\nFasilitas baru dua", $updatedProgram->metadata['facilities']);
        $this->assertSame('Peluang karier baru', $updatedProgram->metadata['career_opportunities']);
        $this->assertSame("Kompetensi baru satu\nKompetensi baru dua", $updatedProgram->metadata['advantages']);

        $this->get(route('program.show', $program->slug))
            ->assertOk()
            ->assertSee('Kompetensi utama baru')
            ->assertSee('Fasilitas baru satu')
            ->assertSee('Fasilitas baru dua')
            ->assertSee('Kompetensi baru satu')
            ->assertSee('Kompetensi baru dua')
            ->assertSee('Peluang karier baru');
    }

    public function test_homepage_limits_legacy_program_summaries_to_eighty_characters(): void
    {
        $summary = str_repeat('x', 100);
        Program::query()->create([
            'title' => 'Program Data Lama',
            'slug' => 'program-data-lama',
            'route_key' => 'program-data-lama',
            'summary' => $summary,
            'status' => 'published',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee(str_repeat('x', 77).'...')
            ->assertDontSee($summary);
    }

    public function test_program_detail_uses_only_photos_associated_with_its_route_key(): void
    {
        $program = Program::query()->create([
            'title' => 'Kuliner',
            'slug' => 'kuliner',
            'route_key' => 'kuliner',
            'summary' => 'Belajar keterampilan kuliner.',
            'status' => 'published',
            'metadata' => ['advantages' => 'Pengolahan makanan'],
        ]);

        GalleryPhoto::query()->create([
            'title' => 'Praktik Kuliner',
            'slug' => 'praktik-kuliner',
            'image_path' => '/images/kegiatan-kuliner.jpg',
            'status' => 'published',
            'metadata' => ['program_route_key' => 'kuliner'],
        ]);

        GalleryPhoto::query()->create([
            'title' => 'Foto Jurusan Lain',
            'slug' => 'foto-jurusan-lain',
            'image_path' => '/images/jurusan-lain.jpg',
            'status' => 'published',
            'metadata' => ['program_route_key' => 'dkv'],
        ]);

        $this->get(route('program.show', $program->slug))
            ->assertOk()
            ->assertSee('Praktik Kuliner')
            ->assertSee('/images/kegiatan-kuliner.jpg', false)
            ->assertDontSee('Galeri Kegiatan Kuliner')
            ->assertDontSee('#program-gallery', false)
            ->assertDontSee('Fasilitas Pendukung')
            ->assertDontSee('Foto Jurusan Lain')
            ->assertDontSee('/images/jurusan-lain.jpg', false);
    }

    private function createAdmin(): Admin
    {
        return Admin::query()->create([
            'name' => 'Admin Program',
            'username' => 'admin-program',
            'role' => 'admin',
            'password' => 'secure-password',
        ]);
    }
}
