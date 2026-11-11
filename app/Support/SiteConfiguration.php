<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SiteConfiguration
{
    private ?array $cachedValues = null;

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        if ($this->cachedValues !== null) {
            return $this->cachedValues;
        }

        $values = config('site-content.defaults', []);

        foreach (SiteSetting::query()->get(['key', 'value']) as $setting) {
            $values[$setting->key] = json_decode($setting->value, true, flags: JSON_THROW_ON_ERROR);
        }

        return $this->cachedValues = $values;
    }

    public function imageUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}
