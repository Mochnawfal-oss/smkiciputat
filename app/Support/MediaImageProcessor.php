<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class MediaImageProcessor
{
    /**
     * @return array{path: string, mime_type: string, width: int, height: int, size: int}
     */
    public function optimize(
        UploadedFile $file,
        string $category,
        string $quality,
        string $directory = 'cms/media/optimized',
    ): array {
        $sourcePath = $file->getRealPath();
        $imageInfo = $sourcePath !== false ? @getimagesize($sourcePath) : false;

        if ($imageInfo === false || ! isset($imageInfo[0], $imageInfo[1], $imageInfo['mime'])) {
            throw new RuntimeException('Gambar tidak dapat dibaca untuk diproses.');
        }

        $sourceWidth = (int) $imageInfo[0];
        $sourceHeight = (int) $imageInfo[1];

        if ($sourceWidth < 1 || $sourceHeight < 1 || $sourceWidth * $sourceHeight > config('media.max_pixels')) {
            throw new RuntimeException('Resolusi gambar melebihi kapasitas pemrosesan server.');
        }

        $image = $this->openImage($sourcePath, $imageInfo['mime']);
        $image = $this->correctOrientation($image, $sourcePath, $imageInfo['mime']);
        $sourceWidth = imagesx($image);
        $sourceHeight = imagesy($image);
        $categorySettings = config('media.categories.'.$category, config('media.categories.general'));
        $qualitySettings = config('media.webp_quality.'.$quality, config('media.webp_quality.optimized'));

        if ($quality === 'optimized') {
            $scale = min(
                (int) $categorySettings['width'] / $sourceWidth,
                (int) $categorySettings['height'] / $sourceHeight,
                1,
            );
        } else {
            $scale = 1;
        }

        $width = max(1, (int) round($sourceWidth * $scale));
        $height = max(1, (int) round($sourceHeight * $scale));
        $output = imagecreatetruecolor($width, $height);

        if ($output === false) {
            imagedestroy($image);

            throw new RuntimeException('Memori tidak cukup untuk mengoptimalkan gambar ini.');
        }

        imagealphablending($output, false);
        imagesavealpha($output, true);
        imagecopyresampled($output, $image, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);

        $temporaryPath = tempnam(sys_get_temp_dir(), 'smki-media-');

        if ($temporaryPath === false) {
            imagedestroy($output);
            imagedestroy($image);

            throw new RuntimeException('File sementara untuk pemrosesan gambar tidak dapat dibuat.');
        }

        try {
            if (! imagewebp($output, $temporaryPath, (int) $qualitySettings)) {
                throw new RuntimeException('Format WebP gagal dibuat. File asli tetap dipertahankan.');
            }

            $relativePath = $directory.'/'.Str::uuid().'.webp';
            $stream = fopen($temporaryPath, 'rb');

            if ($stream === false) {
                throw new RuntimeException('Hasil optimasi gambar tidak dapat dibaca.');
            }

            try {
                $stored = Storage::disk('public')->writeStream($relativePath, $stream);
            } finally {
                fclose($stream);
            }

            if (! $stored) {
                throw new RuntimeException('Hasil optimasi gambar gagal disimpan ke storage.');
            }

            return [
                'path' => $relativePath,
                'mime_type' => 'image/webp',
                'width' => $width,
                'height' => $height,
                'size' => filesize($temporaryPath) ?: 0,
            ];
        } finally {
            imagedestroy($output);
            imagedestroy($image);
            @unlink($temporaryPath);
        }
    }

    private function openImage(string $path, string $mimeType): \GdImage
    {
        $image = match ($mimeType) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => @imagecreatefromwebp($path),
            'image/avif' => function_exists('imagecreatefromavif') ? @imagecreatefromavif($path) : false,
            default => false,
        };

        if (! $image instanceof \GdImage) {
            throw new RuntimeException('Format gambar ini tidak didukung oleh pemroses server.');
        }

        return $image;
    }

    private function correctOrientation(\GdImage $image, string $path, string $mimeType): \GdImage
    {
        if ($mimeType !== 'image/jpeg' || ! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($path);
        $orientation = (int) ($exif['Orientation'] ?? 1);
        $rotation = match ($orientation) {
            3, 4 => 180,
            5, 6 => -90,
            7, 8 => 90,
            default => 0,
        };

        if (in_array($orientation, [2, 4, 5, 7], true)) {
            imageflip($image, IMG_FLIP_HORIZONTAL);
        }

        if ($rotation !== 0) {
            $rotated = imagerotate($image, $rotation, 0);

            if ($rotated instanceof \GdImage) {
                imagedestroy($image);
                $image = $rotated;
            }
        }

        return $image;
    }
}
