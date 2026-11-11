<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $connection = 'teachers';

    protected $fillable = [
        'name',
        'slug',
        'position',
        'education',
        'category',
        'summary',
        'image_path',
        'status',
        'sort_order',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'sort_order' => 'integer',
        ];
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('status', 'published')
            ->where(function (Builder $query): void {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function getTitleAttribute(): string
    {
        return $this->name ?? '';
    }

    public function getMetadataAttribute(): array
    {
        return [
            'position' => $this->position,
            'education' => $this->education,
        ];
    }
}
