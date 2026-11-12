<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\MediaAsset;
use App\Models\NewsArticle;
use App\Models\Program;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_manager_requires_admin_authentication(): void
    {
        $this->get(route('admin.media.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_upload_and_index_an_optimized_image(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'admin')->postJson(route('admin.media.store'), [
            'file' => UploadedFile::fake()->image('school.jpg', 600, 400),
            'category' => 'news',
            'quality' => 'optimized',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('type', 'image')
            ->assertJsonPath('name', 'school.jpg');

        $asset = MediaAsset::query()->findOrFail($response->json('id'));
        $this->assertSame('image/webp', $asset->processing['output_mime_type']);
        $this->assertDatabaseHas('media_assets', [
            'id' => $asset->id,
            'original_path' => $asset->original_path,
            'optimized_path' => $asset->optimized_path,
            'category' => 'news',
        ]);
        Storage::disk('public')->assertExists($asset->original_path);
        Storage::disk('public')->assertExists($asset->optimized_path);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.media.index'))
            ->assertOk()
            ->assertSee('school.jpg');
    }

    public function test_media_manager_indexes_local_images_without_treating_text_settings_as_paths(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $path = UploadedFile::fake()->image('school.jpg')->storePublicly('cms/news', 'public');

        NewsArticle::query()->create([
            'title' => 'Berita Sekolah',
            'slug' => 'berita-sekolah-media-index',
            'image_path' => $path,
        ]);
        SiteSetting::query()->create([
            'key' => 'home.admissions.steps',
            'group' => 'home',
            'value' => json_encode("Isi formulir|Lengkapi data diri\nKirim berkas|Unggah dokumen", JSON_THROW_ON_ERROR),
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.media.index'))
            ->assertOk();

        $this->assertDatabaseHas('media_assets', [
            'original_path' => $path,
            'media_type' => 'image',
        ]);
    }

    public function test_admin_can_replace_a_media_asset_without_breaking_content_references(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $oldResponse = $this->actingAs($admin, 'admin')->postJson(route('admin.media.store'), [
            'file' => UploadedFile::fake()->image('old.jpg', 320, 240),
            'category' => 'news',
            'quality' => 'optimized',
        ]);
        $oldAsset = MediaAsset::query()->findOrFail($oldResponse->json('id'));
        $entry = NewsArticle::query()->create([
            'title' => 'Berita Media',
            'slug' => 'berita-media',
            'image_path' => $oldAsset->publicPath(),
            'status' => 'published',
            'published_at' => now(),
        ]);
        $program = Program::query()->create([
            'title' => 'Program Media',
            'slug' => 'program-media',
            'image_path' => $oldAsset->publicPath(),
            'status' => 'published',
        ]);

        $response = $this->actingAs($admin, 'admin')->postJson(route('admin.media.store'), [
            'file' => UploadedFile::fake()->image('new.jpg', 640, 480),
            'category' => 'news',
            'quality' => 'optimized',
            'replace_asset_id' => $oldAsset->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('replacement_cleanup_pending', false);
        $replacement = MediaAsset::query()->findOrFail($response->json('id'));
        $this->assertSame($replacement->publicPath(), $entry->fresh()->image_path);
        $this->assertSame($replacement->publicPath(), $program->fresh()->image_path);
        $this->assertDatabaseMissing('media_assets', ['id' => $oldAsset->id]);
        Storage::disk('public')->assertMissing($oldAsset->original_path);
        Storage::disk('public')->assertExists($replacement->publicPath());
    }

    public function test_admin_cannot_delete_media_still_referenced_by_content(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $response = $this->actingAs($admin, 'admin')->postJson(route('admin.media.store'), [
            'file' => UploadedFile::fake()->image('active.jpg', 320, 240),
            'category' => 'news',
            'quality' => 'optimized',
        ]);
        $asset = MediaAsset::query()->findOrFail($response->json('id'));
        NewsArticle::query()->create([
            'title' => 'Berita Aktif',
            'slug' => 'berita-aktif',
            'image_path' => $asset->publicPath(),
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->actingAs($admin, 'admin')
            ->delete(route('admin.media.destroy', $asset))
            ->assertSessionHasErrors('media');

        $this->assertDatabaseHas('media_assets', ['id' => $asset->id]);
        Storage::disk('public')->assertExists($asset->publicPath());
    }

    public function test_chunked_upload_can_resume_and_complete(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $admin = $this->createAdmin();
        $file = UploadedFile::fake()->image('chunked.jpg', 160, 120);
        $fileSize = $file->getSize();

        $start = $this->actingAs($admin, 'admin')->postJson(route('admin.media.uploads.store'), [
            'filename' => $file->getClientOriginalName(),
            'mime_type' => 'image/jpeg',
            'size' => $fileSize,
            'media_type' => 'image',
            'category' => 'general',
            'quality' => 'optimized',
        ])->assertStatus(201);
        $uploadId = $start->json('id');
        $chunk = new UploadedFile(
            $file->getRealPath(),
            'chunk.part',
            'application/octet-stream',
            UPLOAD_ERR_OK,
            true,
        );

        $this->actingAs($admin, 'admin')
            ->postJson(route('admin.media.uploads.chunk', [
                'uploadId' => $uploadId,
                'fileKey' => 'file',
                'chunkIndex' => 0,
            ]), ['chunk' => $chunk])
            ->assertOk()
            ->assertJsonPath('stored', true);

        $this->actingAs($admin, 'admin')
            ->getJson(route('admin.media.uploads.show', $uploadId))
            ->assertOk()
            ->assertJsonPath('slots.file.received.0', 0);

        $complete = $this->actingAs($admin, 'admin')
            ->postJson(route('admin.media.uploads.complete', $uploadId))
            ->assertStatus(201);

        $asset = MediaAsset::query()->findOrFail($complete->json('id'));
        $this->assertSame('image', $asset->media_type);
        Storage::disk('public')->assertExists($asset->publicPath());
        $this->assertSame([], Storage::disk('local')->allFiles('media-uploads'));
    }

    public function test_admin_can_cancel_an_incomplete_upload_session(): void
    {
        Storage::fake('local');
        $admin = $this->createAdmin();
        $uploadId = $this->actingAs($admin, 'admin')->postJson(route('admin.media.uploads.store'), [
            'filename' => 'pending.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 100,
            'media_type' => 'image',
            'category' => 'general',
            'quality' => 'optimized',
        ])->assertStatus(201)->json('id');

        $this->actingAs($admin, 'admin')
            ->deleteJson(route('admin.media.uploads.destroy', $uploadId))
            ->assertStatus(204);

        $this->actingAs($admin, 'admin')
            ->getJson(route('admin.media.uploads.show', $uploadId))
            ->assertStatus(404);
        $this->assertSame([], Storage::disk('local')->allFiles('media-uploads'));
    }

    public function test_upload_session_is_not_accessible_to_another_admin(): void
    {
        Storage::fake('local');
        $admin = $this->createAdmin();
        $otherAdmin = $this->createAdmin('other-editor');
        $uploadId = $this->actingAs($admin, 'admin')->postJson(route('admin.media.uploads.store'), [
            'filename' => 'private.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 100,
            'media_type' => 'image',
            'category' => 'general',
            'quality' => 'optimized',
        ])->assertStatus(201)->json('id');

        $this->actingAs($otherAdmin, 'admin')
            ->getJson(route('admin.media.uploads.show', $uploadId))
            ->assertStatus(404);
    }

    public function test_admin_upload_rejects_unsupported_media_types(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin, 'admin')
            ->postJson(route('admin.media.store'), [
                'file' => UploadedFile::fake()->create('payload.exe', 10, 'application/octet-stream'),
                'category' => 'general',
                'quality' => 'optimized',
            ])
            ->assertUnprocessable()
            ->assertInvalid(['file']);
    }

    private function createAdmin(string $username = 'editor'): Admin
    {
        return Admin::query()->create([
            'name' => 'CMS Editor',
            'username' => $username,
            'role' => 'superadmin',
            'email' => $username.'@example.test',
            'password' => Hash::make('Secure-password-1234'),
        ]);
    }
}
