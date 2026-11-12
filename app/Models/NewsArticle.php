<?php

namespace App\Models;

class NewsArticle extends ContentRecord
{
    protected $table = 'news_articles';

    public static function contentType(): string
    {
        return 'news';
    }
}
