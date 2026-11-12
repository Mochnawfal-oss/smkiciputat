<?php

namespace App\Support;

use App\Models\ContentRecord;
use App\Models\MediaAsset;
use App\Models\SiteSetting;
use App\Models\Teacher;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class MediaLibrary
{
    public function __construct(
        private readonly MediaImageProcessor $imageProcessor,
        private readonly MediaUsage $usage,
    ) {}

    /**
     * @param  array{duration_seconds?: int|null, width?: int|null, height?: int|null}  $metadata
     */
    public function storeUploadedMedia(
        UploadedFile $file,
        string $category,
        string $quality,
        ?int $adminId = null,
        array $metadata = [],
        ?UploadedFile $source = null,
        ?string $altText = null,
    ): MediaAsset {
        $mimeType = $file->getMimeType() ?: '';
        $extension = strtolower($file->getClientOriginalExtension());
        $imageExtensions = [
            'image/jpeg' => ['jpg', 'jpeg'],
            'image/png' => ['png'],
            'image/webp' => ['webp'],
            'image/avif' => ['avif'],
        ];
        $videoExtensions = [
            'video/mp4' => ['mp4', 'm4v'],
            'video/webm' => ['webm'],
            'video/ogg' => ['ogv', 'ogg'],
        ];

        if (isset($imageExtensions[$mimeType]) && in_array($extension, $imageExtensions[$mimeType], true)) {
            return $this->storeImage($file, $category, $quality, $source, $adminId, $altText);
        }

        if (isset($videoExtensions[$mimeType]) && in_array($extension, $videoExtensions[$mimeType], true)) {
            return $this->storeVideo($file, $category, $quality, $adminId, $metadata);
        }

        throw new RuntimeException('Format atau MIME type media tidak didukung.');
    }

    public function storeImage(
        UploadedFile $file,
        string $category,
        string $quality = 'optimized',
        ?UploadedFile $source = null,
        ?int $adminId = null,
        ?string $altText = null,
    ): MediaAsset {
        $source = $source ?? $file;
        $sourcePath = $source->getRealPath();
        $imageInfo = $sourcePath !== false ? @getimagesize($sourcePath) : false;

        if ($imageInfo === false || ! isset($imageInfo['mime'])) {
            throw new RuntimeException('File bukan gambar yang dapat diproses.');
        }

        $id = (string) Str::uuid();
        $extension = $this->imageExtension($imageInfo['mime']);
        $originalPath = 'cms/media/originals/'.$id.'.'.$extension;
        $storedOriginal = $source->storePubliclyAs('cms/media/originals', $id.'.'.$extension, 'public');
        $optimizedPath = null;

        if (! is_string($storedOriginal)) {
            throw new RuntimeException('File asli gagal disimpan. Silakan coba kembali.');
        }

        try {
            $processed = $this->imageProcessor->optimize($file, $category, $quality, 'cms/media/optimized');
            $optimizedPath = $processed['path'];

            return DB::transaction(fn (): MediaAsset => MediaAsset::query()->create([
                'disk' => 'public',
                'filename' => $this->safeFilename($source->getClientOriginalName()),
                'original_path' => $originalPath,
                'optimized_path' => $optimizedPath,
                'media_type' => 'image',
                'mime_type' => $imageInfo['mime'],
                'file_size' => $source->getSize(),
                'optimized_size' => $processed['size'],
                'width' => $processed['width'],
                'height' => $processed['height'],
                'quality' => $quality,
                'category' => $category,
                'alt_text' => $altText,
                'processing' => [
                    'source_width' => (int) $imageInfo[0],
                    'source_height' => (int) $imageInfo[1],
                    'output_mime_type' => $processed['mime_type'],
                    'resized_without_cropping' => $processed['width'] !== (int) $imageInfo[0]
                        || $processed['height'] !== (int) $imageInfo[1],
                    'orientation_corrected' => $imageInfo['mime'] === 'image/jpeg',
                ],
                'created_by_admin_id' => $adminId,
            ]));
        } catch (Throwable $exception) {
            if (! Storage::disk('public')->delete(array_filter([$storedOriginal, $optimizedPath]))) {
                Log::warning('An incomplete image upload could not be fully cleaned up.', [
                    'original_path' => $storedOriginal,
                    'optimized_path' => $optimizedPath,
                ]);
            }

            throw $exception;
        }
    }

    /**
     * @param  array{duration_seconds?: int|null, width?: int|null, height?: int|null}  $metadata
     */
    public function storeVideo(
        UploadedFile $file,
        string $category,
        string $quality = 'original',
        ?int $adminId = null,
        array $metadata = [],
    ): MediaAsset {
        $mimeType = $file->getMimeType() ?: 'application/octet-stream';
        $extension = match ($mimeType) {
            'video/mp4' => 'mp4',
            'video/webm' => 'webm',
            'video/ogg' => 'ogv',
            default => throw new RuntimeException('Format video tidak didukung.'),
        };
        $id = (string) Str::uuid();
        $path = $file->storePubliclyAs('cms/media/videos', $id.'.'.$extension, 'public');

        if (! is_string($path)) {
            throw new RuntimeException('Video gagal disimpan. Silakan coba kembali.');
        }

        try {
            return DB::transaction(fn (): MediaAsset => MediaAsset::query()->create([
                'disk' => 'public',
                'filename' => $this->safeFilename($file->getClientOriginalName()),
                'original_path' => $path,
                'optimized_path' => $path,
                'media_type' => 'video',
                'mime_type' => $mimeType,
                'file_size' => $file->getSize(),
                'optimized_size' => $file->getSize(),
                'width' => $metadata['width'] ?? null,
                'height' => $metadata['height'] ?? null,
                'duration_seconds' => $metadata['duration_seconds'] ?? null,
                'quality' => 'original',
                'category' => $category,
                'processing' => [
                    'video_optimization' => 'unavailable_without_ffmpeg',
                    'requested_quality' => $quality,
                ],
                'created_by_admin_id' => $adminId,
            ]));
        } catch (Throwable $exception) {
            if (! Storage::disk('public')->delete($path)) {
                Log::warning('An incomplete video upload could not be cleaned up.', [
                    'path' => $path,
                ]);
            }

            throw $exception;
        }
    }

    public function attachPoster(MediaAsset $asset, UploadedFile $poster): void
    {
        $processed = $this->imageProcessor->optimize($poster, 'gallery', 'optimized', 'cms/media/posters');
        $path = $processed['path'];

        $oldPath = $asset->poster_path;
        try {
            $asset->update(['poster_path' => $path]);
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($path);

            throw $exception;
        }

        if ($oldPath !== null && $oldPath !== $path) {
            Storage::disk($asset->disk)->delete($oldPath);
        }
    }

    public function canDelete(MediaAsset $asset): bool
    {
        return $this->usage->forAssets(collect([$asset]))[$asset->id] === [];
    }

    public function delete(MediaAsset $asset): bool
    {
        if (! $this->canDelete($asset)) {
            return false;
        }

        $paths = array_values(array_unique(array_filter([
            $asset->original_path,
            $asset->optimized_path,
            $asset->poster_path,
        ])));
        $disk = Storage::disk($asset->disk);

        if (! $disk->delete($paths)) {
            Log::warning('Media files could not be deleted from storage.', [
                'media_asset_id' => $asset->id,
                'paths' => $paths,
            ]);

            return false;
        }

        DB::transaction(fn () => $asset->delete());

        return true;
    }

    public function replaceReferences(MediaAsset $current, MediaAsset $replacement): bool
    {
        if ($current->media_type !== $replacement->media_type) {
            throw new RuntimeException('Jenis media pengganti harus sama dengan media saat ini.');
        }

        if ($current->is($replacement)) {
            return true;
        }

        $oldPaths = array_values(array_unique(array_filter([
            $current->original_path,
            $current->optimized_path,
            $current->poster_path,
        ])));
        $newPath = $replacement->publicPath();
        $contentRecords = [];
        foreach (ContentRecord::modelClasses() as $model) {
            $contentRecords[$model] = $model::query()
                ->whereIn('image_path', $oldPaths)
                ->get(['id', 'image_path']);
        }
        $teachers = Teacher::query()->whereIn('image_path', $oldPaths)->get(['id', 'image_path']);
        $encodedOldPaths = collect($oldPaths)->map(fn (string $path): string => json_encode($path, JSON_THROW_ON_ERROR));
        $settings = SiteSetting::query()->whereIn('value', $encodedOldPaths)->get(['key', 'value']);

        try {
            DB::transaction(function () use ($contentRecords, $oldPaths, $newPath, $encodedOldPaths): void {
                foreach (array_keys($contentRecords) as $model) {
                    $model::query()->whereIn('image_path', $oldPaths)->update(['image_path' => $newPath]);
                }

                SiteSetting::query()->whereIn('value', $encodedOldPaths)->update([
                    'value' => json_encode($newPath, JSON_THROW_ON_ERROR),
                ]);
            });

            DB::connection((new Teacher)->getConnectionName())->transaction(
                fn () => Teacher::query()->whereIn('image_path', $oldPaths)->update(['image_path' => $newPath]),
            );
        } catch (Throwable $exception) {
            try {
                DB::transaction(function () use ($contentRecords, $settings): void {
                    foreach ($contentRecords as $model => $entries) {
                        foreach ($entries as $entry) {
                            $model::query()->whereKey($entry->id)->update(['image_path' => $entry->image_path]);
                        }
                    }

                    foreach ($settings as $setting) {
                        SiteSetting::query()->where('key', $setting->key)->update(['value' => $setting->value]);
                    }
                });

                DB::connection((new Teacher)->getConnectionName())->transaction(function () use ($teachers): void {
                    foreach ($teachers as $teacher) {
                        Teacher::query()->whereKey($teacher->id)->update(['image_path' => $teacher->image_path]);
                    }
                });
            } catch (Throwable $rollbackException) {
                Log::critical('Media replacement could not restore all content references after a database failure.', [
                    'current_asset_id' => $current->id,
                    'replacement_asset_id' => $replacement->id,
                    'exception' => $rollbackException->getMessage(),
                ]);
            }

            throw $exception;
        }

        return $this->delete($current);
    }

    /**
     * Add database metadata for files uploaded before the media manager existed.
     */
    public function indexReferencedFiles(): void
    {
        $paths = collect();
        foreach (ContentRecord::modelClasses() as $model) {
            $paths = $paths->merge(
                $model::query()->whereNotNull('image_path')->distinct()->pluck('image_path'),
            );
        }
        $paths = $paths->merge(Teacher::query()->whereNotNull('image_path')->distinct()->pluck('image_path'));

        $imageSettingKeys = collect(config('site-content.fields', []))
            ->filter(fn (array $field): bool => ($field['type'] ?? null) === 'image')
            ->keys()
            ->all();

        foreach (SiteSetting::query()->whereIn('key', $imageSettingKeys)->get(['value']) as $setting) {
            $value = json_decode($setting->value, true);
            if (is_string($value)) {
                $paths->push($value);
            }
        }

        foreach ($paths->filter()->unique() as $path) {
            if (
                Str::startsWith($path, ['http://', 'https://', '/'])
                || ! Storage::disk('public')->exists($path)
                || MediaAsset::query()->where('original_path', $path)->exists()
            ) {
                continue;
            }

            $absolutePath = Storage::disk('public')->path($path);
            $mimeType = mime_content_type($absolutePath) ?: 'application/octet-stream';
            $imageInfo = str_starts_with($mimeType, 'image/') ? @getimagesize($absolutePath) : false;

            if ($imageInfo === false || ! isset($imageInfo['mime'])) {
                continue;
            }

            MediaAsset::query()->firstOrCreate(
                ['original_path' => $path],
                [
                    'disk' => 'public',
                    'filename' => basename($path),
                    'optimized_path' => $path,
                    'media_type' => 'image',
                    'mime_type' => $imageInfo['mime'],
                    'file_size' => Storage::disk('public')->size($path),
                    'optimized_size' => Storage::disk('public')->size($path),
                    'width' => (int) $imageInfo[0],
                    'height' => (int) $imageInfo[1],
                    'quality' => 'original',
                    'category' => 'general',
                    'processing' => ['legacy' => true],
                ],
            );
        }
    }

    private function safeFilename(string $filename): string
    {
        $basename = basename(str_replace('\\', '/', $filename));
        $safeName = preg_replace('/[^A-Za-z0-9._-]+/', '-', $basename);

        return Str::limit($safeName ?: 'media', 180, '');
    }

    private function imageExtension(string $mimeType): string
    {
        return match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/avif' => 'avif',
            default => throw new RuntimeException('Format gambar tidak didukung.'),
        };
    }
}
