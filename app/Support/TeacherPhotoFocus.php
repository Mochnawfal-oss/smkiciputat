<?php

namespace App\Support;

use App\Models\MediaAsset;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class TeacherPhotoFocus
{
    /**
     * @param  Collection<int, Teacher>  $teachers
     */
    public function decorate(Collection $teachers): void
    {
        $paths = $teachers->pluck('image_path')->filter()->unique()->values()->all();
        $assetsByPath = [];

        if ($paths !== []) {
            $assets = MediaAsset::query()
                ->where(function (Builder $query) use ($paths): void {
                    $query->whereIn('original_path', $paths)
                        ->orWhereIn('optimized_path', $paths);
                })
                ->get(['original_path', 'optimized_path', 'processing']);

            foreach ($assets as $asset) {
                $assetsByPath[$asset->original_path] = $asset;
                if ($asset->optimized_path !== null) {
                    $assetsByPath[$asset->optimized_path] = $asset;
                }
            }
        }

        foreach ($teachers as $teacher) {
            $asset = $assetsByPath[$teacher->image_path ?? ''] ?? null;
            $processing = $asset?->processing ?? [];
            $teacher->setAttribute('photo_focal_point_x', (float) data_get($processing, 'focal_point.x', 50));
            $teacher->setAttribute('photo_focal_point_y', (float) data_get($processing, 'focal_point.y', 24));
        }
    }

    /**
     * @return array{x: float, y: float, available: bool}
     */
    public function forPath(?string $path): array
    {
        $asset = $this->assetForPath($path);
        $processing = $asset?->processing ?? [];

        return [
            'x' => (float) data_get($processing, 'focal_point.x', 50),
            'y' => (float) data_get($processing, 'focal_point.y', 24),
            'available' => $asset !== null,
        ];
    }

    public function updateForPath(?string $path, float $x, float $y): bool
    {
        $asset = $this->assetForPath($path);

        if ($asset === null) {
            return false;
        }

        $processing = $asset->processing ?? [];
        $processing['focal_point'] = ['x' => $x, 'y' => $y];
        $asset->update(['processing' => $processing]);

        return true;
    }

    private function assetForPath(?string $path): ?MediaAsset
    {
        if (blank($path)) {
            return null;
        }

        return MediaAsset::query()
            ->where('original_path', $path)
            ->orWhere('optimized_path', $path)
            ->first();
    }
}
