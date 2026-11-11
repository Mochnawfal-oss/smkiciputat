<?php

namespace App\Support;

use App\Models\MediaAsset;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class MediaUploadService
{
    public function __construct(private readonly MediaLibrary $library) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array{id: string, chunk_size: int, received: array<string, array<int, int>>}
     */
    public function start(array $data, int $adminId): array
    {
        $this->cleanExpired();

        $totalBytes = (int) $data['size'] + (int) ($data['source_size'] ?? 0);
        $freeBytes = @disk_free_space(storage_path());
        $reserveBytes = 1024 * 1024 * 1024;
        if (is_numeric($freeBytes) && $totalBytes * 3 + $reserveBytes > $freeBytes) {
            throw ValidationException::withMessages([
                'file' => 'Ruang storage tidak mencukupi untuk menyimpan file asli, proses, dan hasil media ini.',
            ]);
        }

        $id = (string) Str::uuid();
        $directory = $this->sessionDirectory($adminId, $id);
        $manifest = [
            'id' => $id,
            'admin_id' => $adminId,
            'created_at' => now()->toIso8601String(),
            'chunk_size' => $this->chunkSizeBytes(),
            'options' => [
                'category' => $data['category'],
                'quality' => $data['quality'],
                'media_type' => $data['media_type'],
                'duration_seconds' => $data['duration_seconds'] ?? null,
                'width' => $data['width'] ?? null,
                'height' => $data['height'] ?? null,
                'alt_text' => $data['alt_text'] ?? null,
                'replace_asset_id' => $data['replace_asset_id'] ?? null,
            ],
            'slots' => [
                'file' => [
                    'filename' => $data['filename'],
                    'mime_type' => $data['mime_type'] ?? '',
                    'size' => (int) $data['size'],
                    'received' => [],
                ],
            ],
        ];

        if (filled($data['source_filename'] ?? null) && (int) ($data['source_size'] ?? 0) > 0) {
            $manifest['slots']['source_file'] = [
                'filename' => $data['source_filename'],
                'mime_type' => $data['source_mime_type'] ?? '',
                'size' => (int) $data['source_size'],
                'received' => [],
            ];
        }

        $disk = Storage::disk('local');
        if (! $disk->put($directory.'/manifest.json', json_encode($manifest, JSON_THROW_ON_ERROR))) {
            throw new HttpException(500, 'Sesi unggahan tidak dapat disimpan.');
        }

        return [
            'id' => $id,
            'chunk_size' => $manifest['chunk_size'],
            'received' => [],
        ];
    }

    /**
     * @return array{id: string, chunk_size: int, slots: array<string, array{filename: string, size: int, received: array<int, int>}>}
     */
    public function status(string $id, int $adminId): array
    {
        $manifest = $this->manifest($id, $adminId);

        return [
            'id' => $id,
            'chunk_size' => $manifest['chunk_size'],
            'slots' => $manifest['slots'],
        ];
    }

    public function storeChunk(string $id, string $fileKey, int $index, UploadedFile $chunk, int $adminId): void
    {
        $manifest = $this->manifest($id, $adminId);
        abort_unless(isset($manifest['slots'][$fileKey]), 404);

        $slot = $manifest['slots'][$fileKey];
        $totalChunks = (int) ceil($slot['size'] / $manifest['chunk_size']);
        abort_if($index < 0 || $index >= $totalChunks, 404);

        $expectedSize = min($manifest['chunk_size'], $slot['size'] - $index * $manifest['chunk_size']);
        if ($chunk->getSize() !== $expectedSize) {
            throw ValidationException::withMessages([
                'chunk' => 'Ukuran bagian unggahan tidak sesuai. Unggah ulang bagian tersebut.',
            ]);
        }

        $directory = $this->sessionDirectory($adminId, $id).'/chunks';
        $filename = $fileKey.'-'.$index.'.part';
        $disk = Storage::disk('local');

        $chunkPath = $directory.'/'.$filename;
        if ($disk->exists($chunkPath)) {
            if ($disk->size($chunkPath) !== $expectedSize) {
                $disk->delete($directory.'/'.$filename);
            }
        }

        if (! $disk->exists($chunkPath)) {
            $storedPath = $disk->putFileAs($directory, $chunk, $filename);
            if (! is_string($storedPath)) {
                throw new HttpException(500, 'Bagian file gagal disimpan ke storage.');
            }
        }

        $manifest['slots'][$fileKey]['received'][] = $index;
        $manifest['slots'][$fileKey]['received'] = array_values(array_unique($manifest['slots'][$fileKey]['received']));

        if (! $disk->put(
            $this->sessionDirectory($adminId, $id).'/manifest.json',
            json_encode($manifest, JSON_THROW_ON_ERROR),
        )) {
            throw new HttpException(500, 'Status unggahan gagal diperbarui.');
        }
    }

    public function complete(string $id, int $adminId): MediaAsset
    {
        $manifest = $this->manifest($id, $adminId);
        $directory = $this->sessionDirectory($adminId, $id);
        $disk = Storage::disk('local');
        $assembled = [];

        foreach ($manifest['slots'] as $fileKey => $slot) {
            $totalChunks = (int) ceil($slot['size'] / $manifest['chunk_size']);
            $received = array_unique($slot['received']);

            if (count($received) !== $totalChunks) {
                throw ValidationException::withMessages([
                    'file' => 'Unggahan belum lengkap. Periksa koneksi dan lanjutkan kembali.',
                ]);
            }

            $assembledPath = $directory.'/assembled/'.$fileKey.'.tmp';
            $absolutePath = $disk->path($assembledPath);
            $absoluteDirectory = dirname($absolutePath);

            if (! is_dir($absoluteDirectory) && ! mkdir($absoluteDirectory, 0755, true) && ! is_dir($absoluteDirectory)) {
                throw new HttpException(500, 'Folder pemrosesan sementara tidak dapat dibuat.');
            }

            $output = fopen($absolutePath, 'wb');
            if ($output === false) {
                throw new HttpException(500, 'File unggahan tidak dapat disusun kembali.');
            }

            try {
                for ($index = 0; $index < $totalChunks; $index++) {
                    $chunkPath = $directory.'/chunks/'.$fileKey.'-'.$index.'.part';
                    $input = fopen($disk->path($chunkPath), 'rb');

                    if ($input === false) {
                        throw new HttpException(500, 'Bagian file tidak dapat dibaca.');
                    }

                    try {
                        stream_copy_to_stream($input, $output);
                    } finally {
                        fclose($input);
                    }
                }
            } catch (Throwable $exception) {
                fclose($output);
                @unlink($absolutePath);

                throw $exception;
            }

            fclose($output);

            if (filesize($absolutePath) !== $slot['size']) {
                @unlink($absolutePath);
                throw ValidationException::withMessages([
                    'file' => 'Ukuran file hasil penggabungan tidak sesuai.',
                ]);
            }

            $assembled[$fileKey] = new UploadedFile(
                $absolutePath,
                $slot['filename'],
                $slot['mime_type'] ?: null,
                UPLOAD_ERR_OK,
                true,
            );
        }

        try {
            $options = $manifest['options'];
            $actualMimeType = $assembled['file']->getMimeType() ?: '';
            $actualMediaType = str_starts_with($actualMimeType, 'image/')
                ? 'image'
                : (str_starts_with($actualMimeType, 'video/') ? 'video' : null);
            if ($actualMediaType !== $options['media_type']) {
                throw ValidationException::withMessages([
                    'file' => 'Tipe file yang diterima server berbeda dari tipe media yang dipilih.',
                ]);
            }

            $asset = $this->library->storeUploadedMedia(
                $assembled['file'],
                $options['category'],
                $options['quality'],
                $adminId,
                [
                    'duration_seconds' => $options['duration_seconds'],
                    'width' => $options['width'],
                    'height' => $options['height'],
                ],
                $assembled['source_file'] ?? null,
                $options['alt_text'],
            );

            if (filled($options['replace_asset_id'])) {
                $previousAsset = MediaAsset::query()->findOrFail($options['replace_asset_id']);
                $asset->setAttribute(
                    'replacement_cleanup_pending',
                    ! $this->library->replaceReferences($previousAsset, $asset),
                );
            }
        } catch (Throwable $exception) {
            foreach ($assembled as $file) {
                @unlink($file->getRealPath());
            }

            throw $exception;
        }

        foreach ($assembled as $file) {
            @unlink($file->getRealPath());
        }

        if (! $disk->deleteDirectory($directory)) {
            Log::warning('A media upload completed but temporary chunks could not be removed.', [
                'upload_id' => $id,
                'admin_id' => $adminId,
            ]);
        }

        return $asset;
    }

    public function cancel(string $id, int $adminId): void
    {
        $this->manifest($id, $adminId);
        if (! Storage::disk('local')->deleteDirectory($this->sessionDirectory($adminId, $id))) {
            throw new HttpException(500, 'File sementara unggahan gagal dibersihkan.');
        }
    }

    public function chunkSizeKilobytes(string $id, int $adminId): int
    {
        $manifest = $this->manifest($id, $adminId);

        return (int) ceil($manifest['chunk_size'] / 1024);
    }

    /**
     * @return array<string, mixed>
     */
    private function manifest(string $id, int $adminId): array
    {
        abort_unless(Str::isUuid($id), 404);
        $path = $this->sessionDirectory($adminId, $id).'/manifest.json';
        abort_unless(Storage::disk('local')->exists($path), 404);
        $contents = Storage::disk('local')->get($path);
        $manifest = json_decode($contents, true);

        abort_unless(is_array($manifest) && ($manifest['admin_id'] ?? null) === $adminId, 404);

        if (now()->diffInHours($manifest['created_at']) >= 24) {
            Storage::disk('local')->deleteDirectory(dirname($path));
            abort(410, 'Sesi unggahan kedaluwarsa. Mulai unggahan baru.');
        }

        return $manifest;
    }

    private function cleanExpired(): void
    {
        $disk = Storage::disk('local');

        foreach ($disk->allFiles('media-uploads') as $path) {
            if (basename($path) !== 'manifest.json') {
                continue;
            }

            $manifest = json_decode($disk->get($path), true);
            if (is_array($manifest) && isset($manifest['created_at']) && now()->diffInHours($manifest['created_at']) >= 24) {
                $disk->deleteDirectory(dirname($path));
            }
        }
    }

    private function sessionDirectory(int $adminId, string $id): string
    {
        return 'media-uploads/'.$adminId.'/'.$id;
    }

    private function chunkSizeBytes(): int
    {
        $serverLimit = min(
            $this->iniBytes((string) ini_get('upload_max_filesize')),
            $this->iniBytes((string) ini_get('post_max_size')),
        );
        $serverLimit = $serverLimit > 0 ? $serverLimit - 65536 : 0;
        $configuredLimit = (int) config('media.chunk_size_kb') * 1024;
        $chunkSize = min($configuredLimit, $serverLimit);

        if ($chunkSize < 65536) {
            throw ValidationException::withMessages([
                'file' => 'Konfigurasi upload PHP terlalu rendah untuk unggahan bertahap.',
            ]);
        }

        return $chunkSize;
    }

    private function iniBytes(string $value): int
    {
        $value = trim($value);
        if ($value === '' || $value === '-1') {
            return PHP_INT_MAX;
        }

        $unit = strtolower(substr($value, -1));
        $number = (float) $value;

        return (int) match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => $number,
        };
    }
}
