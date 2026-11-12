<?php

namespace App\Support;

use App\Models\ContentRecord;
use App\Models\MediaAsset;
use App\Models\SiteSetting;
use App\Models\Teacher;
use Illuminate\Support\Collection;

class MediaUsage
{
    /**
     * @param  Collection<int, MediaAsset>  $assets
     * @return array<int, array<int, array{type: string, title: string}>>
     */
    public function forAssets(Collection $assets): array
    {
        $paths = $assets
            ->flatMap(fn (MediaAsset $asset): array => array_filter([
                $asset->original_path,
                $asset->optimized_path,
                $asset->poster_path,
            ]))
            ->unique()
            ->values()
            ->all();

        if ($paths === []) {
            return [];
        }

        $usage = [];

        foreach (ContentRecord::modelClasses() as $type => $model) {
            foreach ($model::query()->whereIn('image_path', $paths)->get(['image_path', 'title']) as $entry) {
                $usage[$entry->image_path][] = [
                    'type' => $this->contentTypeLabel($type),
                    'title' => $entry->title,
                ];
            }
        }

        foreach (Teacher::query()->whereIn('image_path', $paths)->get(['image_path', 'name']) as $teacher) {
            $usage[$teacher->image_path][] = [
                'type' => 'Tenaga Kependidikan',
                'title' => $teacher->name,
            ];
        }

        $encodedPaths = collect($paths)->map(fn (string $path): string => json_encode($path, JSON_THROW_ON_ERROR));

        foreach (SiteSetting::query()->whereIn('value', $encodedPaths)->get(['key', 'value']) as $setting) {
            $path = json_decode($setting->value, true, flags: JSON_THROW_ON_ERROR);
            $usage[$path][] = [
                'type' => 'Pengaturan Website',
                'title' => $setting->key,
            ];
        }

        $result = [];

        foreach ($assets as $asset) {
            $references = [];
            foreach ([$asset->original_path, $asset->optimized_path, $asset->poster_path] as $path) {
                if ($path !== null) {
                    $references = [...$references, ...($usage[$path] ?? [])];
                }
            }
            $result[$asset->id] = collect($references)
                ->unique(fn (array $reference): string => $reference['type'].'|'.$reference['title'])
                ->values()
                ->all();
        }

        return $result;
    }

    private function contentTypeLabel(string $type): string
    {
        return match ($type) {
            'teacher' => 'Tenaga Kependidikan',
            'news' => 'Berita',
            'program' => 'Program Keahlian',
            'gallery' => 'Galeri',
            default => 'Konten Website',
        };
    }
}
