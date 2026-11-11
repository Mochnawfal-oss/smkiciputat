<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaAsset extends Model
{
    protected $fillable = [
        'disk',
        'filename',
        'original_path',
        'optimized_path',
        'poster_path',
        'media_type',
        'mime_type',
        'file_size',
        'optimized_size',
        'width',
        'height',
        'duration_seconds',
        'quality',
        'category',
        'alt_text',
        'caption',
        'processing',
        'created_by_admin_id',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'optimized_size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'duration_seconds' => 'integer',
            'processing' => 'array',
        ];
    }

    public function publicPath(): string
    {
        return $this->optimized_path ?: $this->original_path;
    }
}
