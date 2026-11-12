<?php

namespace App\Support;

use App\Models\ContentRecord;
use App\Models\MediaAsset;
use App\Models\SiteSetting;
use App\Models\Teacher;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class PublicImageStorage
{
    public function __construct(private readonly MediaLibrary $mediaLibrary) {}

    public function store(
        UploadedFile $file,
        string $directory,
        string $category = 'general',
        ?UploadedFile $source = null,
        ?int $adminId = null,
    ): string {
        return $this->mediaLibrary
            ->storeImage($file, $category, 'optimized', $source, $adminId)
            ->publicPath();
    }

    public function storeFromAsset(int $assetId, string $expectedType = 'image'): string
    {
        $asset = MediaAsset::query()->findOrFail($assetId);

        abort_unless($asset->media_type === $expectedType, 422, 'Jenis media tidak sesuai dengan kolom ini.');

        return $asset->publicPath();
    }

    public function deleteIfUnused(?string $path): void
    {
        if (! $this->canDelete($path)) {
            return;
        }

        $asset = $this->mediaAssetForPath($path);

        if ($asset !== null) {
            if (! $this->mediaLibrary->delete($asset)) {
                throw new RuntimeException('Media lama masih digunakan atau gagal dihapus dari storage.');
            }

            return;
        }

        if (! Storage::disk('public')->delete($path)) {
            throw new RuntimeException('Media lama gagal dihapus dari storage.');
        }
    }

    public function delete(?string $path): void
    {
        if (blank($path) || Str::startsWith($path, ['http://', 'https://', '/'])) {
            return;
        }

        $asset = $this->mediaAssetForPath($path);

        if ($asset !== null) {
            if (! $this->mediaLibrary->delete($asset)) {
                throw new RuntimeException('Media masih digunakan atau gagal dihapus dari storage.');
            }

            return;
        }

        if (! Storage::disk('public')->delete($path)) {
            throw new RuntimeException('Media gagal dihapus dari storage.');
        }
    }

    public function canDelete(?string $path): bool
    {
        if (blank($path) || Str::startsWith($path, ['http://', 'https://', '/'])) {
            return false;
        }

        $asset = $this->mediaAssetForPath($path);

        if ($asset !== null) {
            return $this->mediaLibrary->canDelete($asset);
        }

        foreach (ContentRecord::modelClasses() as $model) {
            if ($model::query()->where('image_path', $path)->exists()) {
                return false;
            }
        }

        if (
            Teacher::query()->where('image_path', $path)->exists()
            || SiteSetting::query()->where('value', json_encode($path, JSON_THROW_ON_ERROR))->exists()
        ) {
            return false;
        }

        return true;
    }

    private function mediaAssetForPath(string $path): ?MediaAsset
    {
        return MediaAsset::query()
            ->where('original_path', $path)
            ->orWhere('optimized_path', $path)
            ->orWhere('poster_path', $path)
            ->first();
    }
}
