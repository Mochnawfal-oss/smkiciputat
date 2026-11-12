<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

abstract class ContentRecord extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'route_key',
        'summary',
        'body',
        'category',
        'image_path',
        'status',
        'featured',
        'sort_order',
        'published_at',
        'meta_title',
        'meta_description',
        'metadata',
    ];

    /**
     * @return array<string, class-string<ContentRecord>>
     */
    public static function modelClasses(): array
    {
        return [
            'news' => NewsArticle::class,
            'program' => Program::class,
            'principal_welcome' => PrincipalWelcome::class,
            'job' => JobVacancy::class,
            'gallery' => GalleryPhoto::class,
        ];
    }

    /**
     * @return class-string<ContentRecord>
     */
    public static function modelForType(string $type): string
    {
        return self::modelClasses()[$type]
            ?? throw new InvalidArgumentException('Unsupported content type: '.$type);
    }

    protected function casts(): array
    {
        return [
            'featured' => 'boolean',
            'published_at' => 'datetime',
            'metadata' => 'array',
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

    public function getTypeAttribute(): string
    {
        return static::contentType();
    }

    abstract public static function contentType(): string;
}
