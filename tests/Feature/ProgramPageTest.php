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
        $admin = Admin::query()->create([
            'name' => 'Admin Program',
            'username' => 'admin-program',
            'role' => 'admin',
            'password' => 'secure-password',
        ]);

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

        $this->get(route('program.show', 'program-keahlian-baru'))
            ->assertOk()
            ->assertSee('Program Keahlian Baru')
            ->assertSee('Kompetensi program baru')
            ->assertSee('Fasilitas program baru')
            ->assertSee('Peluang karier program baru')
            ->assertSee('Kompetensi kedua')
            ->assertSee(route('spmb'), false);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.content.create', 'gallery'))
            ->assertOk()
            ->assertSee('metadata[program_route_key]', false)
            ->assertSee('value="program-keahlian-baru"', false);
    }

    public function test_program_page_shows_only_photos_associated_with_its_route_key(): void
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
            ->assertDontSee('Foto Jurusan Lain')
            ->assertDontSee('/images/jurusan-lain.jpg', false);
    }
}
