<?php

return [
    'max_upload_mb' => (int) env('MEDIA_MAX_UPLOAD_MB', 2048),
    'chunk_size_kb' => (int) env('MEDIA_UPLOAD_CHUNK_KB', 8192),
    'max_pixels' => (int) env('MEDIA_MAX_IMAGE_PIXELS', 40000000),
    'webp_quality' => [
        'optimized' => 82,
        'high_quality' => 92,
        'original' => 95,
    ],
    'categories' => [
        'teacher' => ['label' => 'Foto tenaga kependidikan', 'width' => 1400, 'height' => 1800],
        'hero' => ['label' => 'Foto Hero', 'width' => 2400, 'height' => 1600],
        'news' => ['label' => 'Foto berita', 'width' => 1600, 'height' => 1200],
        'program' => ['label' => 'Program keahlian', 'width' => 1600, 'height' => 1200],
        'gallery' => ['label' => 'Galeri', 'width' => 2400, 'height' => 2400],
        'general' => ['label' => 'Umum', 'width' => 2000, 'height' => 2000],
    ],
    'video_mimes' => [
        'video/mp4',
        'video/webm',
        'video/ogg',
    ],
];
