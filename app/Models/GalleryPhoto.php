<?php

namespace App\Models;

class GalleryPhoto extends ContentRecord
{
    protected $table = 'gallery_photos';

    public static function contentType(): string
    {
        return 'gallery';
    }
}
