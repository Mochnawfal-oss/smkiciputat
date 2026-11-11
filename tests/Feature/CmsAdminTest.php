<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\ContentEntry;
use App\Models\MediaAsset;
use App\Models\SiteSetting;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class CmsAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_routes_require_authentication(): void
    {
        $this->get(route('admin.dashboard'))
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

    public function test_admin_can_publish_a_program_that_appears_on_public_pages(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.content.store', 'programs'), [
                'title' => 'Program Uji',
                'slug' => 'program-uji',
                'summary' => 'Deskripsi program uji.',
                'status' => 'published',
                'metadata' => [
                    'abbreviation' => 'PU',
                    'route_key' => 'program-uji',
                    'icon' => 'graduation-cap',
                ],
            ])
            ->assertRedirect(route('admin.content.index', 'programs'));

        $this->assertDatabaseHas('content_entries', [
            'type' => 'program',
            'slug' => 'program-uji',
            'status' => 'published',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Program Uji');

        $this->get(route('program.show', 'program-uji'))
            ->assertOk()
            ->assertSee('Deskripsi program uji.');
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

    public function test_public_news_escapes_admin_provided_html(): void
    {
        $article = ContentEntry::query()->create([
            'type' => 'news',
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
        $this->get(route('teachers'))->assertOk()->assertDontSee('Guru');
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
